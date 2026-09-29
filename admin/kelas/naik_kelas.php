<?php
// Tahun ajaran asal dari profil
$tahunAsal = '2026/2027';
$qProfil = @$koneksi->query("SELECT tahun_ajaran FROM tb_profil LIMIT 1");
if ($qProfil && $rP = $qProfil->fetch_assoc()) {
    if (!empty($rP['tahun_ajaran'])) $tahunAsal = $rP['tahun_ajaran'];
}
function nextTahunAjaran($ta) {
    if (preg_match('/(\d{4})\s*\/\s*(\d{4})/', $ta, $m)) {
        return ((int)$m[1] + 1) . '/' . ((int)$m[2] + 1);
    }
    $y = (int)date('Y');
    return $y . '/' . ($y + 1);
}
$tahunTujuan = nextTahunAjaran($tahunAsal);

// Daftar kelas sesuai sistem (urutan id_kelas)
$kelasList = [];
$qK = @$koneksi->query("SELECT id_kelas, kelas FROM tb_kelas ORDER BY id_kelas ASC");
if ($qK) { while ($rk = $qK->fetch_assoc()) { $kelasList[] = $rk; } }

$asalId = isset($_GET['asal']) ? (int)$_GET['asal'] : 0;
// Validasi asal ada di list, default 0 = belum pilih
$asalIdx = -1;
foreach ($kelasList as $i => $kl) { if ((int)$kl['id_kelas'] === $asalId) { $asalIdx = $i; break; } }
if ($asalIdx < 0) { $asalId = 0; }
$tujuanRow = ($asalIdx >= 0 && isset($kelasList[$asalIdx + 1])) ? $kelasList[$asalIdx + 1] : null;
$tujuanId = $tujuanRow ? (int)$tujuanRow['id_kelas'] : 0;

function nk_log($koneksi, $aksi, $ket) {
    if (!function_exists('logActivity')) {
        $paths = [__DIR__ . '/../../inc/activity_log.php', dirname(dirname(__DIR__)) . '/inc/activity_log.php', 'inc/activity_log.php'];
        foreach ($paths as $p) { if (file_exists($p)) { include_once $p; break; } }
    }
    if (function_exists('logActivity')) { @logActivity($koneksi, $aksi, 'tb_siswa', $ket, null); }
}

// Deteksi aksi: hidden field aksi, fallback nama tombol (tahan browser lama)
$aksiPost = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['aksi'])) $aksiPost = trim($_POST['aksi']);
    elseif (isset($_POST['ProsesNaik'])) $aksiPost = 'naik';
    elseif (isset($_POST['BatalNaik'])) $aksiPost = 'batal';
}

// Proses naik
if ($aksiPost === 'naik') {
    $nisList = isset($_POST['nis_naik']) && is_array($_POST['nis_naik']) ? $_POST['nis_naik'] : [];
    $nisList = array_values(array_filter(array_map('trim', $nisList)));
    if (empty($nisList)) { $errNaik = 'Pilih minimal 1 siswa di kelas asal.'; }
    elseif ($asalId <= 0) { $errNaik = 'Pilih kelas asal terlebih dahulu.'; }
    elseif ($tujuanId <= 0) { $errNaik = 'Kelas ini tingkat tertinggi. Tidak ada kelas tujuan.'; }
    else {
        $esc = array_map(function($v) use ($koneksi) { return "'" . $koneksi->real_escape_string($v) . "'"; }, $nisList);
        $in = implode(',', $esc);
        $q = @$koneksi->query("UPDATE tb_siswa SET id_kelas=$tujuanId WHERE nis IN ($in) AND id_kelas=$asalId AND status='Aktif'");
        if ($q && $koneksi->affected_rows > 0) {
            $n = $koneksi->affected_rows;
            nk_log($koneksi, 'UPDATE', 'Naik kelas ' . $n . ' siswa dari id_kelas ' . $asalId . ' ke ' . $tujuanId);
            echo "<script>window.location.href='?page=MyApp/naik_kelas&asal=$asalId&ok=naik&n=$n';</script>";
            return;
        } elseif ($q) {
            $errNaik = 'Tidak ada data berubah. Siswa mungkin sudah pindah kelas.';
        } else {
            $errNaik = 'Gagal memproses kenaikan kelas: ' . htmlspecialchars($koneksi->error);
        }
    }
}
// Proses batal
if ($aksiPost === 'batal') {
    $nisList = isset($_POST['nis_batal']) && is_array($_POST['nis_batal']) ? $_POST['nis_batal'] : [];
    $nisList = array_values(array_filter(array_map('trim', $nisList)));
    if (empty($nisList)) { $errBatal = 'Pilih minimal 1 siswa di kelas tujuan.'; }
    elseif ($asalId <= 0 || $tujuanId <= 0) { $errBatal = 'Pilih kelas asal terlebih dahulu.'; }
    else {
        $esc = array_map(function($v) use ($koneksi) { return "'" . $koneksi->real_escape_string($v) . "'"; }, $nisList);
        $in = implode(',', $esc);
        $q = @$koneksi->query("UPDATE tb_siswa SET id_kelas=$asalId WHERE nis IN ($in) AND id_kelas=$tujuanId AND status='Aktif'");
        if ($q && $koneksi->affected_rows > 0) {
            $n = $koneksi->affected_rows;
            nk_log($koneksi, 'UPDATE', 'Batal naik ' . $n . ' siswa dari id_kelas ' . $tujuanId . ' ke ' . $asalId);
            echo "<script>window.location.href='?page=MyApp/naik_kelas&asal=$asalId&ok=batal&n=$n';</script>";
            return;
        } elseif ($q) {
            $errBatal = 'Tidak ada data berubah.';
        } else { $errBatal = 'Gagal membatalkan kenaikan kelas: ' . htmlspecialchars($koneksi->error); }
    }
}

// Data siswa asal & tujuan
$siswaAsal = [];
if ($asalId > 0) {
    $qa = @$koneksi->query("SELECT nis, nama_siswa, jekel FROM tb_siswa WHERE id_kelas=$asalId AND status='Aktif' ORDER BY nama_siswa ASC");
    if ($qa) { while ($ra = $qa->fetch_assoc()) { $siswaAsal[] = $ra; } }
}
$siswaTujuan = [];
if ($tujuanId > 0) {
    $qt = @$koneksi->query("SELECT nis, nama_siswa, jekel FROM tb_siswa WHERE id_kelas=$tujuanId AND status='Aktif' ORDER BY nama_siswa ASC");
    if ($qt) { while ($rt = $qt->fetch_assoc()) { $siswaTujuan[] = $rt; } }
}
$namaAsal = ''; $namaTujuan = '';
foreach ($kelasList as $kl) {
    if ((int)$kl['id_kelas'] === $asalId) $namaAsal = trim($kl['kelas']);
    if ($tujuanId && (int)$kl['id_kelas'] === $tujuanId) $namaTujuan = trim($kl['kelas']);
}
?>

<section class="content-header">
    <h1>Master Data <small>Kenaikan Kelas</small></h1>
</section>

<section class="content">
<?php if (isset($_GET['ok'])) { $nOk = isset($_GET['n']) ? (int)$_GET['n'] : 0; $isBatal = ($_GET['ok'] === 'batal');
    $msgOk = $isBatal ? ('Berhasil mengembalikan ' . $nOk . ' siswa ke kelas ' . $namaAsal . '.') : ('Berhasil menaikkan ' . $nOk . ' siswa ke kelas ' . $namaTujuan . '.'); ?>
<script>
document.addEventListener('DOMContentLoaded', function(){
    var nkMsg = <?php echo json_encode($msgOk); ?>;
    if (typeof Swal !== 'undefined') {
        Swal.fire({ title: 'Berhasil!', text: nkMsg, icon: 'success', showConfirmButton: false, timer: 3000, timerProgressBar: true });
    } else if (window.toastr) {
        toastr.success(nkMsg, 'Berhasil!');
    } else { alert(nkMsg); }
    if (window.history && history.replaceState) {
        try {
            var u = new URL(window.location.href);
            u.searchParams.delete('ok'); u.searchParams.delete('n');
            history.replaceState(null, '', u.toString());
        } catch (e) {}
    }
});
</script>
<?php } ?>
<?php if (!empty($errNaik)) { ?><div class="mb-3 rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700 ring-1 ring-rose-200"><?php echo $errNaik; ?></div><?php } ?>
<?php if (!empty($errBatal)) { ?><div class="mb-3 rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700 ring-1 ring-rose-200"><?php echo $errBatal; ?></div><?php } ?>

<div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
    <!-- PANEL ASAL -->
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="border-b border-slate-100 px-5 py-4">
            <h3 class="text-sm font-bold text-emerald-700"><?php echo htmlspecialchars($tahunAsal); ?> Tahun Ajaran Asal</h3>
        </div>
        <div class="p-5">
            <label class="text-xs font-semibold text-slate-700">Kelas</label>
            <select id="kelasAsal" class="mt-1 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20" onchange="window.location.href='?page=MyApp/naik_kelas'+(this.value?'&asal='+this.value:'')">
                <option value="">-- Pilih Kelas --</option>
                <?php foreach ($kelasList as $kl) { ?>
                    <option value="<?php echo (int)$kl['id_kelas']; ?>" <?php echo ((int)$kl['id_kelas'] === $asalId) ? 'selected' : ''; ?>><?php echo htmlspecialchars(trim($kl['kelas'])); ?></option>
                <?php } ?>
            </select>

            <form method="post" action="?page=MyApp/naik_kelas&asal=<?php echo $asalId; ?>" id="formNaik" autocomplete="off">
            <input type="hidden" name="aksi" value="naik">
            <div class="mt-4 flex items-center gap-2 text-xs text-slate-500">
                <span>Tampilkan</span>
                <select id="sizeAsal" class="rounded-lg border border-slate-200 px-2 py-1" onchange="nkSetSize('asal',this.value)">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
                <span>entri</span>
                <span class="ml-auto">Cari: <input id="cariAsal" class="rounded-lg border border-slate-200 px-2 py-1" oninput="nkSetQuery('asal',this.value)" onkeydown="if(event.key==='Enter'){event.preventDefault();return false;}"></span>
            </div>
            <div class="mt-2 max-h-96 overflow-auto rounded-xl border border-slate-200">
            <table class="w-full text-xs">
                <thead class="sticky top-0 bg-slate-50">
                    <tr>
                        <th class="px-2 py-2"><input type="checkbox" id="checkAsalAll" onclick="nkCheckAll('asal',this.checked)"></th>
                        <th class="px-2 py-2">No</th>
                        <th class="px-2 py-2">NIS</th>
                        <th class="px-2 py-2 text-left">Nama</th>
                        <th class="px-2 py-2">L/P</th>
                    </tr>
                </thead>
                <tbody id="bodyAsal">
                <?php $no=1; foreach ($siswaAsal as $s) { $lp = ($s['jekel'] === 'LK') ? 'L' : 'P'; ?>
                    <tr class="border-t border-slate-100 hover:bg-slate-50" data-nama="<?php echo htmlspecialchars(strtolower($s['nis'] . ' ' . $s['nama_siswa'])); ?>">
                        <td class="px-2 py-2 text-center"><input type="checkbox" class="ckAsal" name="nis_naik[]" value="<?php echo htmlspecialchars($s['nis']); ?>" onchange="nkRowChanged('asal')"></td>
                        <td class="px-2 py-2 text-center nk-no"><?php echo $no++; ?></td>
                        <td class="px-2 py-2 text-center"><?php echo htmlspecialchars($s['nis']); ?></td>
                        <td class="px-2 py-2 font-medium text-sky-600"><?php echo htmlspecialchars($s['nama_siswa']); ?></td>
                        <td class="px-2 py-2 text-center"><?php echo $lp; ?></td>
                    </tr>
                <?php } if (empty($siswaAsal)) { ?>
                    <tr data-empty="1"><td colspan="5" class="px-2 py-6 text-center text-slate-400"><?php echo $asalId <= 0 ? 'Pilih kelas asal terlebih dahulu.' : 'Tidak ada siswa di kelas ini.'; ?></td></tr>
                <?php } ?>
                </tbody>
            </table>
            </div>
            <p id="infoAsal" class="mt-2 text-[11px] text-slate-500"></p>
            <div id="pagAsal" class="mt-2 flex flex-wrap items-center gap-1"></div>
            <button type="button" id="btnNaik" style="display:none" onclick="nkAskNaik()" class="mt-3 w-full rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800">&#8593; Proses Naik Kelas</button>
            </form>
        </div>
    </div>

    <!-- PANEL TUJUAN -->
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="border-b border-slate-100 px-5 py-4">
            <h3 class="text-sm font-bold text-emerald-700"><?php echo htmlspecialchars($tahunTujuan); ?> Tahun Ajaran Tujuan</h3>
        </div>
        <div class="p-5">
            <label class="text-xs font-semibold text-slate-700">Kelas</label>
            <input type="text" disabled value="<?php echo htmlspecialchars($namaTujuan !== '' ? $namaTujuan : '-'); ?>" class="mt-1 block w-full rounded-xl border border-slate-200 bg-slate-100 px-4 py-2.5 text-sm text-slate-500">
            <p class="mt-1 text-[11px] text-slate-400">Kelas tujuan otomatis berdasarkan kelas asal yang dipilih</p>

            <?php if ($tujuanRow) { ?>
            <div class="mt-3 rounded-xl bg-sky-50 px-4 py-3 text-xs text-sky-800 ring-1 ring-sky-200">
                <strong>&#9432; Info:</strong> Kelas tujuan memiliki <?php echo count($siswaTujuan); ?> siswa. Siswa yang akan naik akan ditambahkan ke kelas ini.
            </div>
            <form method="post" action="?page=MyApp/naik_kelas&asal=<?php echo $asalId; ?>" id="formBatal" autocomplete="off">
            <input type="hidden" name="aksi" value="batal">
            <div class="mt-4 flex items-center gap-2 text-xs text-slate-500">
                <span>Tampilkan</span>
                <select id="sizeTujuan" class="rounded-lg border border-slate-200 px-2 py-1" onchange="nkSetSize('tujuan',this.value)">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
                <span>entri</span>
                <span class="ml-auto">Cari: <input id="cariTujuan" class="rounded-lg border border-slate-200 px-2 py-1" oninput="nkSetQuery('tujuan',this.value)" onkeydown="if(event.key==='Enter'){event.preventDefault();return false;}"></span>
            </div>
            <div class="mt-2 max-h-96 overflow-auto rounded-xl border border-slate-200">
            <table class="w-full text-xs">
                <thead class="sticky top-0 bg-slate-50">
                    <tr>
                        <th class="px-2 py-2"><input type="checkbox" id="checkTujuanAll" onclick="nkCheckAll('tujuan',this.checked)"></th>
                        <th class="px-2 py-2">No</th>
                        <th class="px-2 py-2">NIS</th>
                        <th class="px-2 py-2 text-left">Nama</th>
                        <th class="px-2 py-2">L/P</th>
                    </tr>
                </thead>
                <tbody id="bodyTujuan">
                <?php $no=1; foreach ($siswaTujuan as $s) { $lp = ($s['jekel'] === 'LK') ? 'L' : 'P'; ?>
                    <tr class="border-t border-slate-100 hover:bg-slate-50" data-nama="<?php echo htmlspecialchars(strtolower($s['nis'] . ' ' . $s['nama_siswa'])); ?>">
                        <td class="px-2 py-2 text-center"><input type="checkbox" class="ckTujuan" name="nis_batal[]" value="<?php echo htmlspecialchars($s['nis']); ?>" onchange="nkRowChanged('tujuan')"></td>
                        <td class="px-2 py-2 text-center nk-no"><?php echo $no++; ?></td>
                        <td class="px-2 py-2 text-center"><?php echo htmlspecialchars($s['nis']); ?></td>
                        <td class="px-2 py-2 font-medium text-sky-600"><?php echo htmlspecialchars($s['nama_siswa']); ?></td>
                        <td class="px-2 py-2 text-center"><?php echo $lp; ?></td>
                    </tr>
                <?php } if (empty($siswaTujuan)) { ?>
                    <tr data-empty="1"><td colspan="5" class="px-2 py-6 text-center text-slate-400">Belum ada siswa di kelas tujuan.</td></tr>
                <?php } ?>
                </tbody>
            </table>
            </div>
            <p id="infoTujuan" class="mt-2 text-[11px] text-slate-500"></p>
            <div id="pagTujuan" class="mt-2 flex flex-wrap items-center gap-1"></div>
            <button type="button" id="btnBatal" style="display:none" onclick="nkAskBatal()" class="mt-3 w-full rounded-xl border border-rose-400 bg-white px-4 py-2.5 text-sm font-semibold text-rose-600 hover:bg-rose-50">&#8635; Batal Naik (Kembalikan ke Kelas Asal)</button>
            </form>
            <?php } elseif ($asalId <= 0) { ?>
            <div class="mt-3 rounded-xl bg-slate-50 px-4 py-3 text-xs text-slate-500 ring-1 ring-slate-200">Pilih kelas asal terlebih dahulu untuk melihat kelas tujuan.</div>
            <?php } else { ?>
            <div class="mt-3 rounded-xl bg-amber-50 px-4 py-3 text-xs text-amber-700 ring-1 ring-amber-200">Kelas <?php echo htmlspecialchars($namaAsal); ?> adalah tingkat tertinggi. Tidak ada kelas tujuan.</div>
            <?php } ?>
        </div>
    </div>
</div>
</section>

<script>
var nkState = { asal: { page: 1, size: 10, q: '' }, tujuan: { page: 1, size: 10, q: '' } };
var nkNamaAsal = <?php echo json_encode($namaAsal !== '' ? $namaAsal : '-'); ?>;
var nkNamaTujuan = <?php echo json_encode($namaTujuan !== '' ? $namaTujuan : '-'); ?>;

function nkIds(which) {
    if (which === 'asal') return { body: 'bodyAsal', info: 'infoAsal', pag: 'pagAsal', checkAll: 'checkAsalAll', ck: 'ckAsal' };
    return { body: 'bodyTujuan', info: 'infoTujuan', pag: 'pagTujuan', checkAll: 'checkTujuanAll', ck: 'ckTujuan' };
}
function nkRows(which) {
    var ids = nkIds(which);
    var tb = document.getElementById(ids.body);
    if (!tb) return [];
    var out = [];
    tb.querySelectorAll('tr').forEach(function(r){ if (!r.getAttribute('data-empty')) out.push(r); });
    return out;
}
function nkFiltered(which) {
    var st = nkState[which];
    var q = (st.q || '').toLowerCase();
    return nkRows(which).filter(function(r){
        if (!q) return true;
        return (r.getAttribute('data-nama') || '').indexOf(q) !== -1;
    });
}
function nkRender(which) {
    var ids = nkIds(which);
    var st = nkState[which];
    var rows = nkFiltered(which);
    var total = rows.length;
    var pages = Math.max(1, Math.ceil(total / st.size));
    if (st.page > pages) st.page = pages;
    if (st.page < 1) st.page = 1;
    var start = (st.page - 1) * st.size;
    var end = Math.min(start + st.size, total);
    nkRows(which).forEach(function(r){ r.style.display = 'none'; });
    for (var i = start; i < end; i++) rows[i].style.display = '';
    var info = document.getElementById(ids.info);
    if (info) info.textContent = total > 0 ? ('Menampilkan ' + (start + 1) + ' sampai ' + end + ' dari ' + total + ' entri') : 'Menampilkan 0 dari 0 entri';
    var pag = document.getElementById(ids.pag);
    if (pag) {
        var h = '';
        h += '<button type="button" class="rounded-lg border border-slate-300 bg-white px-2 py-1 text-[11px] text-slate-600 hover:bg-slate-50" ' + (st.page <= 1 ? 'disabled' : '') + ' onclick="nkGoto(\'' + which + '\',' + (st.page - 1) + ')">Sebelumnya</button>';
        for (var p = 1; p <= pages; p++) {
            h += '<button type="button" class="rounded-lg border px-2 py-1 text-[11px] ' + (p === st.page ? 'border-sky-500 bg-sky-500 text-white' : 'border-slate-300 bg-white text-slate-600 hover:bg-slate-50') + '" onclick="nkGoto(\'' + which + '\',' + p + ')">' + p + '</button>';
        }
        h += '<button type="button" class="rounded-lg border border-slate-300 bg-white px-2 py-1 text-[11px] text-slate-600 hover:bg-slate-50" ' + (st.page >= pages ? 'disabled' : '') + ' onclick="nkGoto(\'' + which + '\',' + (st.page + 1) + ')">Selanjutnya</button>';
        pag.innerHTML = h;
    }
    nkSyncCheckAll(which);
}
function nkGoto(which, p) { nkState[which].page = p; nkRender(which); }
function nkSetSize(which, v) { nkState[which].size = parseInt(v, 10) || 10; nkState[which].page = 1; nkRender(which); }
function nkSetQuery(which, v) { nkState[which].q = v || ''; nkState[which].page = 1; nkRender(which); }
function nkVisibleRows(which) {
    return nkRows(which).filter(function(r){ return r.style.display !== 'none'; });
}
function nkCheckedCount(which) {
    var ids = nkIds(which);
    return document.querySelectorAll('.' + ids.ck + ':checked').length;
}
function nkToggle() {
    var bN = document.getElementById('btnNaik'), bB = document.getElementById('btnBatal');
    if (bN) bN.style.display = nkCheckedCount('asal') > 0 ? '' : 'none';
    if (bB) bB.style.display = nkCheckedCount('tujuan') > 0 ? '' : 'none';
}
function nkSyncCheckAll(which) {
    var ids = nkIds(which);
    var box = document.getElementById(ids.checkAll);
    if (!box) return;
    var vis = nkVisibleRows(which);
    if (!vis.length) { box.checked = false; return; }
    box.checked = vis.every(function(r){ var c = r.querySelector('.' + ids.ck); return c && c.checked; });
}
function nkCheckAll(which, checked) {
    var ids = nkIds(which);
    nkVisibleRows(which).forEach(function(r){ var c = r.querySelector('.' + ids.ck); if (c) c.checked = checked; });
    nkToggle();
}
function nkRowChanged(which) { nkSyncCheckAll(which); nkToggle(); }
function nkWarn(msg) {
    if (typeof Swal !== 'undefined') Swal.fire('Peringatan', msg, 'warning');
    else alert(msg);
}
function nkAskNaik() {
    var n = nkCheckedCount('asal');
    if (n <= 0) { nkWarn('Pilih minimal 1 siswa di kelas asal.'); return; }
    if (typeof Swal !== 'undefined') {
        Swal.fire({ title: 'Naikkan ' + n + ' siswa?', html: 'Siswa dipindah dari kelas <b>' + nkNamaAsal + '</b> ke kelas <b>' + nkNamaTujuan + '</b>. Lanjutkan?', icon: 'question', showCancelButton: true, confirmButtonColor: '#047857', cancelButtonColor: '#64748b', confirmButtonText: 'Ya, Naikkan!', cancelButtonText: 'Batal' }).then(function(r){ if (r.isConfirmed) document.getElementById('formNaik').submit(); });
    } else if (confirm('Naikkan ' + n + ' siswa dari kelas ' + nkNamaAsal + ' ke kelas ' + nkNamaTujuan + '?')) {
        document.getElementById('formNaik').submit();
    }
}
function nkAskBatal() {
    var n = nkCheckedCount('tujuan');
    if (n <= 0) { nkWarn('Pilih minimal 1 siswa di kelas tujuan.'); return; }
    if (typeof Swal !== 'undefined') {
        Swal.fire({ title: 'Kembalikan ' + n + ' siswa?', html: 'Siswa dikembalikan dari kelas <b>' + nkNamaTujuan + '</b> ke kelas <b>' + nkNamaAsal + '</b>. Lanjutkan?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#e11d48', cancelButtonColor: '#64748b', confirmButtonText: 'Ya, Kembalikan!', cancelButtonText: 'Batal' }).then(function(r){ if (r.isConfirmed) document.getElementById('formBatal').submit(); });
    } else if (confirm('Kembalikan ' + n + ' siswa ke kelas ' + nkNamaAsal + '?')) {
        document.getElementById('formBatal').submit();
    }
}
document.addEventListener('DOMContentLoaded', function(){ nkRender('asal'); nkRender('tujuan'); nkToggle(); });
</script>
