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

// Proses naik
if (isset($_POST['ProsesNaik']) && $asalId > 0 && $tujuanId > 0) {
    $nisList = isset($_POST['nis_naik']) && is_array($_POST['nis_naik']) ? $_POST['nis_naik'] : [];
    $nisList = array_values(array_filter(array_map('trim', $nisList)));
    if (!empty($nisList)) {
        $esc = array_map(function($v) use ($koneksi) { return "'" . $koneksi->real_escape_string($v) . "'"; }, $nisList);
        $in = implode(',', $esc);
        $q = @$koneksi->query("UPDATE tb_siswa SET id_kelas=$tujuanId WHERE nis IN ($in) AND id_kelas=$asalId AND status='Aktif'");
        if ($q) {
            nk_log($koneksi, 'UPDATE', 'Naik kelas ' . count($nisList) . ' siswa dari id_kelas ' . $asalId . ' ke ' . $tujuanId);
            echo "<script>window.location.href='index.php?page=MyApp/naik_kelas&asal=$asalId&ok=naik';</script>";
            return;
        } else {
            $errNaik = 'Gagal memproses kenaikan kelas.';
        }
    } else { $errNaik = 'Pilih minimal 1 siswa di kelas asal.'; }
}
// Proses batal
if (isset($_POST['BatalNaik'])) {
    $nisList = isset($_POST['nis_batal']) && is_array($_POST['nis_batal']) ? $_POST['nis_batal'] : [];
    $nisList = array_values(array_filter(array_map('trim', $nisList)));
    if (empty($nisList)) { $errBatal = 'Pilih minimal 1 siswa di kelas tujuan.'; }
    elseif ($asalId <= 0 || $tujuanId <= 0) { $errBatal = 'Pilih kelas asal terlebih dahulu.'; }
    else {
        $esc = array_map(function($v) use ($koneksi) { return "'" . $koneksi->real_escape_string($v) . "'"; }, $nisList);
        $in = implode(',', $esc);
        $q = @$koneksi->query("UPDATE tb_siswa SET id_kelas=$asalId WHERE nis IN ($in) AND id_kelas=$tujuanId AND status='Aktif'");
        if ($q) {
            nk_log($koneksi, 'UPDATE', 'Batal naik ' . count($nisList) . ' siswa dari id_kelas ' . $tujuanId . ' ke ' . $asalId);
            echo "<script>window.location.href='index.php?page=MyApp/naik_kelas&asal=$asalId&ok=batal';</script>";
            return;
        } else { $errBatal = 'Gagal membatalkan kenaikan kelas.'; }
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
<?php if (isset($_GET['ok'])) { ?>
    <div class="mb-3 rounded-xl px-4 py-3 text-sm <?php echo $_GET['ok'] === 'batal' ? 'bg-amber-50 text-amber-700 ring-1 ring-amber-200' : 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200'; ?>">
        <?php echo $_GET['ok'] === 'batal' ? 'Siswa berhasil dikembalikan ke kelas asal.' : 'Siswa berhasil dinaikkan ke kelas tujuan.'; ?>
    </div>
<?php } ?>
<?php if (!empty($errNaik)) { ?><div class="mb-3 rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700 ring-1 ring-rose-200"><?php echo htmlspecialchars($errNaik); ?></div><?php } ?>
<?php if (!empty($errBatal)) { ?><div class="mb-3 rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700 ring-1 ring-rose-200"><?php echo htmlspecialchars($errBatal); ?></div><?php } ?>

<div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
    <!-- PANEL ASAL -->
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="border-b border-slate-100 px-5 py-4">
            <h3 class="text-sm font-bold text-emerald-700"><?php echo htmlspecialchars($tahunAsal); ?> Tahun Ajaran Asal</h3>
        </div>
        <div class="p-5">
            <label class="text-xs font-semibold text-slate-700">Kelas</label>
            <select id="kelasAsal" class="mt-1 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20" onchange="window.location.href='index.php?page=MyApp/naik_kelas'+(this.value?'&asal='+this.value:'')">
                <option value="">-- Pilih Kelas --</option>
                <?php foreach ($kelasList as $kl) { ?>
                    <option value="<?php echo (int)$kl['id_kelas']; ?>" <?php echo ((int)$kl['id_kelas'] === $asalId) ? 'selected' : ''; ?>><?php echo htmlspecialchars(trim($kl['kelas'])); ?></option>
                <?php } ?>
            </select>

            <form method="post" action="index.php?page=MyApp/naik_kelas&asal=<?php echo $asalId; ?>">
            <div class="mt-4 flex items-center gap-2 text-xs text-slate-500">
                <span>Tampilkan</span>
                <select class="rounded-lg border border-slate-200 px-2 py-1" onchange="nkPageSize('asal',this.value)">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
                <span>entri</span>
                <span class="ml-auto">Cari: <input id="cariAsal" class="rounded-lg border border-slate-200 px-2 py-1" onkeyup="nkFilter('asal')"></span>
            </div>
            <div class="mt-2 max-h-96 overflow-auto rounded-xl border border-slate-200">
            <table class="w-full text-xs">
                <thead class="sticky top-0 bg-slate-50">
                    <tr>
                        <th class="px-2 py-2"><input type="checkbox" id="checkAsalAll" onclick="nkCheckAll('asal',this.checked)"></th>
                        <th class="px-2 py-2">No</th>
                        <th class="px-2 py-2">NISN</th>
                        <th class="px-2 py-2 text-left">Nama</th>
                        <th class="px-2 py-2">L/P</th>
                    </tr>
                </thead>
                <tbody id="bodyAsal">
                <?php $no=1; foreach ($siswaAsal as $s) { $lp = ($s['jekel'] === 'LK') ? 'L' : 'P'; ?>
                    <tr class="border-t border-slate-100 hover:bg-slate-50">
                        <td class="px-2 py-2 text-center"><input type="checkbox" class="ckAsal" name="nis_naik[]" value="<?php echo htmlspecialchars($s['nis']); ?>" onchange="nkToggle()"></td>
                        <td class="px-2 py-2 text-center"><?php echo $no++; ?></td>
                        <td class="px-2 py-2 text-center"><?php echo htmlspecialchars($s['nis']); ?></td>
                        <td class="px-2 py-2 font-medium text-sky-600"><?php echo htmlspecialchars($s['nama_siswa']); ?></td>
                        <td class="px-2 py-2 text-center"><?php echo $lp; ?></td>
                    </tr>
                <?php } if (empty($siswaAsal)) { ?>
                    <tr><td colspan="5" class="px-2 py-6 text-center text-slate-400"><?php echo $asalId <= 0 ? 'Pilih kelas asal terlebih dahulu.' : 'Tidak ada siswa di kelas ini.'; ?></td></tr>
                <?php } ?>
                </tbody>
            </table>
            </div>
            <p id="infoAsal" class="mt-2 text-[11px] text-slate-500"></p>
            <button type="submit" name="ProsesNaik" id="btnNaik" style="display:none" class="mt-3 w-full rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800">&#8593; Proses Naik Kelas</button>
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
            <form method="post" action="index.php?page=MyApp/naik_kelas&asal=<?php echo $asalId; ?>">
            <div class="mt-4 flex items-center gap-2 text-xs text-slate-500">
                <span>Tampilkan</span>
                <select class="rounded-lg border border-slate-200 px-2 py-1" onchange="nkPageSize('tujuan',this.value)">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
                <span>entri</span>
                <span class="ml-auto">Cari: <input id="cariTujuan" class="rounded-lg border border-slate-200 px-2 py-1" onkeyup="nkFilter('tujuan')"></span>
            </div>
            <div class="mt-2 max-h-96 overflow-auto rounded-xl border border-slate-200">
            <table class="w-full text-xs">
                <thead class="sticky top-0 bg-slate-50">
                    <tr>
                        <th class="px-2 py-2"><input type="checkbox" id="checkTujuanAll" onclick="nkCheckAll('tujuan',this.checked)"></th>
                        <th class="px-2 py-2">No</th>
                        <th class="px-2 py-2">NISN</th>
                        <th class="px-2 py-2 text-left">Nama</th>
                        <th class="px-2 py-2">L/P</th>
                    </tr>
                </thead>
                <tbody id="bodyTujuan">
                <?php $no=1; foreach ($siswaTujuan as $s) { $lp = ($s['jekel'] === 'LK') ? 'L' : 'P'; ?>
                    <tr class="border-t border-slate-100 hover:bg-slate-50">
                        <td class="px-2 py-2 text-center"><input type="checkbox" class="ckTujuan" name="nis_batal[]" value="<?php echo htmlspecialchars($s['nis']); ?>" onchange="nkToggle()"></td>
                        <td class="px-2 py-2 text-center"><?php echo $no++; ?></td>
                        <td class="px-2 py-2 text-center"><?php echo htmlspecialchars($s['nis']); ?></td>
                        <td class="px-2 py-2 font-medium text-sky-600"><?php echo htmlspecialchars($s['nama_siswa']); ?></td>
                        <td class="px-2 py-2 text-center"><?php echo $lp; ?></td>
                    </tr>
                <?php } if (empty($siswaTujuan)) { ?>
                    <tr><td colspan="5" class="px-2 py-6 text-center text-slate-400">Belum ada siswa di kelas tujuan.</td></tr>
                <?php } ?>
                </tbody>
            </table>
            </div>
            <p id="infoTujuan" class="mt-2 text-[11px] text-slate-500"></p>
            <button type="submit" name="BatalNaik" id="btnBatal" style="display:none" onclick="return confirm('Kembalikan siswa terpilih ke kelas asal (<?php echo htmlspecialchars($namaAsal); ?>)?')" class="mt-3 w-full rounded-xl border border-rose-400 bg-white px-4 py-2.5 text-sm font-semibold text-rose-600 hover:bg-rose-50">&#8635; Batal Naik (Kembalikan ke Kelas Asal)</button>
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
var nkSize = { asal: 10, tujuan: 10 };
function nkToggle() {
    var nAsal = document.querySelectorAll('.ckAsal:checked').length;
    var nTuj = document.querySelectorAll('.ckTujuan:checked').length;
    var bN = document.getElementById('btnNaik'), bB = document.getElementById('btnBatal');
    if (bN) bN.style.display = nAsal > 0 ? '' : 'none';
    if (bB) bB.style.display = nTuj > 0 ? '' : 'none';
}
function nkCheckAll(which, checked) {
    var cls = which === 'asal' ? '.ckAsal' : '.ckTujuan';
    document.querySelectorAll(cls).forEach(function(c){ if(c.offsetParent!==null) c.checked = checked; });
    nkToggle();
}
function nkFilter(which) {
    var q = (document.getElementById(which==='asal'?'cariAsal':'cariTujuan').value||'').toLowerCase();
    var rows = document.querySelectorAll((which==='asal'?'#bodyAsal':'#bodyTujuan')+' tr');
    var shown = [];
    rows.forEach(function(r){
        if (r.querySelector('td[colspan]')) { r.style.display=''; return; }
        var t = r.textContent.toLowerCase();
        var ok = t.indexOf(q) !== -1;
        r.style.display = ok ? '' : 'none';
        if (ok) shown.push(r);
    });
    nkPaging(which, shown);
}
function nkPageSize(which, v){ nkSize[which]=parseInt(v,10)||10; nkFilter(which); }
function nkPaging(which, rows) {
    var size = nkSize[which]||10;
    var info = document.getElementById(which==='asal'?'infoAsal':'infoTujuan');
    rows = rows || Array.from(document.querySelectorAll((which==='asal'?'#bodyAsal':'#bodyTujuan')+' tr')).filter(function(r){return !r.querySelector('td[colspan]') && r.style.display!=='none';});
    rows.forEach(function(r,i){ r.style.display = i < size ? '' : 'none'; });
    if (info) info.textContent = 'Menampilkan ' + Math.min(size, rows.length) + ' dari ' + rows.length + ' entri';
}
document.addEventListener('DOMContentLoaded', function(){ nkFilter('asal'); nkFilter('tujuan'); nkToggle(); });
</script>
