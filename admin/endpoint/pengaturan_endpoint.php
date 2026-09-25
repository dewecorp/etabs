<?php
// Cek hak akses admin
if (!isset($_SESSION["ses_username"]) || $_SESSION["ses_level"] !== "Administrator") {
    echo "<script>alert('Anda tidak memiliki akses ke halaman ini!'); window.location='index.php';</script>";
    exit;
}

// 1. Inisialisasi Tabel & Seeding jika belum ada
$koneksi->query("CREATE TABLE IF NOT EXISTS `tb_endpoint_keluar` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `deskripsi` VARCHAR(255) NULL,
  `metode` VARCHAR(10) NOT NULL DEFAULT 'GET',
  `endpoint_path` VARCHAR(255) NOT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$koneksi->query("CREATE TABLE IF NOT EXISTS `tb_endpoint_masuk` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `kode_app` VARCHAR(50) NOT NULL UNIQUE,
  `nama_app` VARCHAR(100) NOT NULL,
  `deskripsi` VARCHAR(255) NULL,
  `base_url` VARCHAR(255) NOT NULL,
  `api_key` VARCHAR(255) NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `last_test_status` VARCHAR(255) NULL,
  `last_test_time` DATETIME NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Seed default Endpoint Keluar jika kosong
$checkKeluar = $koneksi->query("SELECT COUNT(*) as total FROM tb_endpoint_keluar");
if ($checkKeluar && $checkKeluar->fetch_assoc()['total'] == 0) {
    $koneksi->query("INSERT INTO `tb_endpoint_keluar` (`nama`, `deskripsi`, `metode`, `endpoint_path`, `status`) VALUES
    ('Data Tabungan Siswa (SIMAD)', 'Sinkron data saldo & info tabungan siswa ke aplikasi SIMAD.', 'GET', 'api/simad.php?action=tabungan&nis={nis}', 1),
    ('Info Tabungan & Riwayat (SIMAD)', 'Sinkronkan riwayat transaksi tabungan siswa ke aplikasi SIMAD.', 'GET', 'api/simad.php?action=riwayat&nis={nis}', 1),
    ('Sinkron Pembayaran Tabungan (Sibayar)', 'Potong saldo tabungan untuk penarikan tagihan Sibayar.', 'POST', 'api/payment.php', 1);");
}

// Seed default Endpoint Masuk jika kosong
$checkMasuk = $koneksi->query("SELECT COUNT(*) as total FROM tb_endpoint_masuk");
if ($checkMasuk && $checkMasuk->fetch_assoc()['total'] == 0) {
    $koneksi->query("INSERT INTO `tb_endpoint_masuk` (`kode_app`, `nama_app`, `deskripsi`, `base_url`, `api_key`, `status`) VALUES
    ('simad', 'simad', 'Ambil endpoint/data guru dari aplikasi SIMAD.', 'https://simad.misultanfattah.sch.id', 'SIMAD_SECRET_KEY_2026', 1),
    ('sibayar', 'sibayar', 'Ambil endpoint/data tagihan dari aplikasi Sibayar.', 'https://sibayar.misultanfattah.sch.id/api/etab.php', 'SPP_SECRET_KEY_2026', 1),
    ('sigaji', 'sigaji', 'Ambil endpoint/data dari aplikasi Sigaji.', 'https://sigaji.misultanfattah.sch.id', 'SIGAJI_SECRET_KEY_2026', 1),
    ('sims', 'sims', 'Ambil endpoint/data dari aplikasi SIMS.', 'https://sims.misultanfattah.sch.id', 'SIMS_SECRET_KEY_2026', 1);");
}

// Deteksi Auto Base URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https" : "http";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$scriptDir = rtrim($scriptDir, '/');
$autoBaseUrl = $protocol . "://" . $host . ($scriptDir ? $scriptDir : '');

// Process Actions
$alertMsg = '';
$alertType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action_type'] ?? '';

    // 1. Simpan Masuk
    if ($action === 'save_masuk') {
        if (isset($_POST['masuk']) && is_array($_POST['masuk'])) {
            foreach ($_POST['masuk'] as $id => $data) {
                $id = intval($id);
                $baseUrl = trim($data['base_url'] ?? '');
                $apiKey = trim($data['api_key'] ?? '');
                $status = isset($data['status']) ? 1 : 0;

                $stmt = $koneksi->prepare("UPDATE tb_endpoint_masuk SET base_url = ?, api_key = ?, status = ? WHERE id = ?");
                $stmt->bind_param("ssii", $baseUrl, $apiKey, $status, $id);
                $stmt->execute();
                $stmt->close();
            }
            $alertMsg = "Pengaturan Endpoint Masuk berhasil disimpan!";
            $alertType = "success";
        }
    }

    // 2. Tambah Aplikasi Masuk
    if ($action === 'add_masuk') {
        $kodeApp = strtolower(trim($_POST['kode_app'] ?? ''));
        $namaApp = trim($_POST['nama_app'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $baseUrl = trim($_POST['base_url'] ?? '');
        $apiKey = trim($_POST['api_key'] ?? '');

        if (!empty($kodeApp) && !empty($baseUrl)) {
            $stmt = $koneksi->prepare("INSERT INTO tb_endpoint_masuk (kode_app, nama_app, deskripsi, base_url, api_key, status) VALUES (?, ?, ?, ?, ?, 1)");
            $stmt->bind_param("sssss", $kodeApp, $namaApp, $deskripsi, $baseUrl, $apiKey);
            if ($stmt->execute()) {
                $alertMsg = "Aplikasi endpoint masuk berhasil ditambahkan!";
                $alertType = "success";
            } else {
                $alertMsg = "Gagal menambahkan aplikasi. Kode aplikasi mungkin sudah ada.";
                $alertType = "danger";
            }
            $stmt->close();
        }
    }

    // 3. Hapus Aplikasi Masuk
    if ($action === 'del_masuk') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $koneksi->prepare("DELETE FROM tb_endpoint_masuk WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $alertMsg = "Endpoint masuk berhasil dihapus!";
            $alertType = "success";
        }
    }

    // 4. Tambah Endpoint Keluar
    if ($action === 'add_keluar') {
        $nama = trim($_POST['nama'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $metode = strtoupper(trim($_POST['metode'] ?? 'GET'));
        $endpointPath = trim($_POST['endpoint_path'] ?? '');

        if (!empty($nama) && !empty($endpointPath)) {
            $stmt = $koneksi->prepare("INSERT INTO tb_endpoint_keluar (nama, deskripsi, metode, endpoint_path, status) VALUES (?, ?, ?, ?, 1)");
            $stmt->bind_param("ssss", $nama, $deskripsi, $metode, $endpointPath);
            $stmt->execute();
            $stmt->close();
            $alertMsg = "Endpoint keluar berhasil ditambahkan!";
            $alertType = "success";
        }
    }

    // 5. Toggle Status Endpoint Keluar
    if ($action === 'toggle_keluar') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $koneksi->query("UPDATE tb_endpoint_keluar SET status = IF(status=1, 0, 1) WHERE id = $id");
            $alertMsg = "Status endpoint keluar berhasil diperbarui!";
            $alertType = "success";
        }
    }

    // 6. Hapus Endpoint Keluar
    if ($action === 'del_keluar') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $koneksi->query("DELETE FROM tb_endpoint_keluar WHERE id = $id");
            $alertMsg = "Endpoint keluar berhasil dihapus!";
            $alertType = "success";
        }
    }
}
?>

<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-white p-6 shadow-sm border border-slate-100">
        <div>
            <h1 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-plug text-emerald-600"></i>
                Pengaturan Endpoint Integrasi
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Kelola API Endpoint Keluar (keluar ke web lain) & Endpoint Masuk (tarik data dari SIMAD/Sibayar/web lainnya).
            </p>
        </div>
    </div>

    <?php if (!empty($alertMsg)) { ?>
    <div class="rounded-xl p-4 text-xs font-medium <?= ($alertType == 'success') ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200' ?> flex items-center justify-between">
        <span><i class="fa-solid <?= ($alertType == 'success') ? 'fa-circle-check text-emerald-500' : 'fa-circle-exclamation text-rose-500' ?> mr-2"></i><?= htmlspecialchars($alertMsg) ?></span>
        <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <?php } ?>

    <!-- SECTION 1: ENDPOINT KELUAR -->
    <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-100 space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-slate-100">
            <div>
                <h2 class="text-base font-bold text-indigo-700 flex items-center gap-2">
                    Endpoint Keluar — dari ETABS ke web lain
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Salin URL lalu tempel di web lain (simad/sibayar/dll). Base URL terdeteksi otomatis: 
                    <span class="font-mono text-rose-500 bg-rose-50 px-2 py-0.5 rounded border border-rose-200 font-semibold"><?= htmlspecialchars($autoBaseUrl) ?></span>
                </p>
            </div>
            <button type="button" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700 transition-all tw-modal-open" data-target="#modalAddKeluar">
                <i class="fa-solid fa-plus text-xs"></i><span>Tambah Endpoint</span>
            </button>
        </div>

        <?php $outgoingApiKey = defined('SIMAD_API_KEY') ? SIMAD_API_KEY : 'SIMAD_SECRET_KEY_2026'; ?>
        <div class="rounded-xl bg-amber-50/80 border border-amber-200/80 p-3.5 text-xs text-amber-900 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-500/20 text-amber-700">
                    <i class="fa-solid fa-key text-xs"></i>
                </div>
                <div>
                    <div class="font-bold text-amber-900">API Key ETABS (Wajib untuk Endpoint Keluar)</div>
                    <div class="text-[11px] text-amber-800 mt-0.5">
                        Aplikasi eksternal wajib mengirim API Key ini di Header <code class="bg-amber-100 px-1 py-0.5 rounded font-mono text-[10px]">X-API-KEY</code> atau URL Query <code class="bg-amber-100 px-1 py-0.5 rounded font-mono text-[10px]">?api_key=...</code> saat memanggil endpoint ETABS.
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-1.5 w-full sm:w-auto">
                <input type="text" readonly value="<?= htmlspecialchars($outgoingApiKey) ?>" id="inputOutgoingApiKey" class="w-full sm:w-56 rounded-lg border border-amber-300 bg-white px-2.5 py-1.5 text-xs font-mono font-bold text-amber-900 focus:outline-none">
                <button type="button" onclick="copyToClipboard('inputOutgoingApiKey')" class="inline-flex items-center gap-1 shrink-0 rounded-lg bg-amber-600 hover:bg-amber-700 text-white px-3 py-1.5 text-xs font-semibold transition-colors" title="Salin API Key">
                    <i class="fa-solid fa-copy"></i><span>Salin API Key</span>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 border-b border-slate-200 font-semibold">
                        <th class="py-3 px-3 text-center" width="40px">No</th>
                        <th class="py-3 px-3">Nama</th>
                        <th class="py-3 px-3 text-center" width="80px">Metode</th>
                        <th class="py-3 px-3">URL Siap Salin</th>
                        <th class="py-3 px-3 text-center" width="90px">Status</th>
                        <th class="py-3 px-3 text-center" width="90px">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php
                    $no = 1;
                    $resKeluar = $koneksi->query("SELECT * FROM tb_endpoint_keluar ORDER BY id ASC");
                    if ($resKeluar && $resKeluar->num_rows > 0) {
                        while ($row = $resKeluar->fetch_assoc()) {
                            $fullUrl = $autoBaseUrl . '/' . ltrim($row['endpoint_path'], '/');
                            $isAktif = ($row['status'] == 1);
                    ?>
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-3 px-3 text-center text-slate-500 font-medium"><?= $no++ ?></td>
                        <td class="py-3 px-3">
                            <div class="font-bold text-slate-800"><?= htmlspecialchars($row['nama']) ?></div>
                            <?php if (!empty($row['deskripsi'])) { ?>
                            <div class="text-[11px] text-slate-400 mt-0.5"><?= htmlspecialchars($row['deskripsi']) ?></div>
                            <?php } ?>
                        </td>
                        <td class="py-3 px-3 text-center">
                            <?php if (strtoupper($row['metode']) === 'POST') { ?>
                                <span class="inline-block rounded-md bg-emerald-500 px-2 py-0.5 text-[10px] font-bold text-white uppercase">POST</span>
                            <?php } else { ?>
                                <span class="inline-block rounded-md bg-sky-500 px-2 py-0.5 text-[10px] font-bold text-white uppercase">GET</span>
                            <?php } ?>
                        </td>
                        <td class="py-3 px-3">
                            <div class="flex items-center gap-1.5 max-w-md">
                                <input type="text" readonly value="<?= htmlspecialchars($fullUrl) ?>" id="copyInput_<?= $row['id'] ?>" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-xs text-slate-600 font-mono focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                <button type="button" onclick="copyToClipboard('copyInput_<?= $row['id'] ?>')" class="inline-flex items-center justify-center rounded-lg bg-slate-200 hover:bg-indigo-600 hover:text-white px-2.5 py-1.5 text-xs text-slate-600 transition-colors" title="Salin URL">
                                    <i class="fa-solid fa-copy"></i>
                                </button>
                            </div>
                        </td>
                        <td class="py-3 px-3 text-center">
                            <?php if ($isAktif) { ?>
                                <span class="inline-block rounded-full bg-emerald-100 px-3 py-0.5 text-[11px] font-bold text-emerald-700">Aktif</span>
                            <?php } else { ?>
                                <span class="inline-block rounded-full bg-slate-100 px-3 py-0.5 text-[11px] font-bold text-slate-500">Nonaktif</span>
                            <?php } ?>
                        </td>
                        <td class="py-3 px-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action_type" value="toggle_keluar">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <button type="submit" class="h-7 w-7 rounded-lg <?= $isAktif ? 'bg-amber-100 text-amber-700 hover:bg-amber-200' : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' ?> transition-colors inline-flex items-center justify-center" title="<?= $isAktif ? 'Nonaktifkan' : 'Aktifkan' ?>">
                                        <i class="fa-solid fa-power-off text-xs"></i>
                                    </button>
                                </form>
                                <button type="button" onclick="deleteKeluar(<?= $row['id'] ?>)" class="h-7 w-7 rounded-lg bg-rose-100 text-rose-600 hover:bg-rose-200 transition-colors inline-flex items-center justify-center" title="Hapus">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else { 
                    ?>
                    <tr>
                        <td colspan="6" class="py-4 text-center text-slate-400">Belum ada endpoint keluar.</td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>


    <!-- SECTION 2: ENDPOINT MASUK -->
    <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-100 space-y-4">
        <form method="POST" id="formSaveMasuk">
            <input type="hidden" name="action_type" value="save_masuk">

            <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-slate-100 mb-4">
                <div>
                    <h2 class="text-base font-bold text-indigo-700 flex items-center gap-2">
                        Endpoint Masuk — dari web lain ke ETABS
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Isi base URL + API key tiap aplikasi (simad, sibayar, sigaji, dll). ETABS memakai ini saat menarik data — ganti domain cukup edit di sini.
                    </p>
                </div>
                <button type="button" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700 transition-all tw-modal-open" data-target="#modalAddMasuk">
                    <i class="fa-solid fa-plus text-xs"></i><span>Tambah Aplikasi</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead>
                        <tr class="bg-slate-50 text-slate-600 border-b border-slate-200 font-semibold">
                            <th class="py-3 px-3">Aplikasi</th>
                            <th class="py-3 px-3" width="30%">Base URL</th>
                            <th class="py-3 px-3" width="25%">API Key</th>
                            <th class="py-3 px-3 text-center" width="60px">Aktif</th>
                            <th class="py-3 px-3 text-center" width="180px">Tes Terakhir</th>
                            <th class="py-3 px-3 text-center" width="100px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php
                        $resMasuk = $koneksi->query("SELECT * FROM tb_endpoint_masuk ORDER BY id ASC");
                        if ($resMasuk && $resMasuk->num_rows > 0) {
                            while ($row = $resMasuk->fetch_assoc()) {
                                $id = $row['id'];
                                $testStatus = $row['last_test_status'] ?? 'Belum dites';
                                $testTime = $row['last_test_time'] ? date('Y-m-d H:i:s', strtotime($row['last_test_time'])) : '';
                        ?>
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-3">
                                <div class="font-bold text-slate-800 font-mono"><?= htmlspecialchars($row['kode_app']) ?></div>
                                <div class="text-[11px] text-slate-400 mt-0.5"><?= htmlspecialchars($row['deskripsi'] ?: $row['nama_app']) ?></div>
                            </td>
                            <td class="py-3 px-3">
                                <input type="text" name="masuk[<?= $id ?>][base_url]" value="<?= htmlspecialchars($row['base_url']) ?>" placeholder="https://..." class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            </td>
                            <td class="py-3 px-3">
                                <input type="text" name="masuk[<?= $id ?>][api_key]" value="<?= htmlspecialchars($row['api_key']) ?>" placeholder="API key" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            </td>
                            <td class="py-3 px-3 text-center">
                                <input type="checkbox" name="masuk[<?= $id ?>][status]" value="1" <?= ($row['status'] == 1) ? 'checked' : '' ?> class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            </td>
                            <td class="py-3 px-3 text-center" id="testBadgeContainer_<?= $id ?>">
                                <?php if (strpos($testStatus, 'OK') !== false) { ?>
                                    <div class="inline-block rounded-lg bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-700 border border-emerald-200">
                                        <div class="flex items-center gap-1">
                                            <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                            <span>OK</span>
                                        </div>
                                        <div class="text-[9px] font-normal text-slate-500 mt-0.5"><?= htmlspecialchars($testTime) ?></div>
                                        <div class="text-[9px] font-mono text-emerald-600"><?= htmlspecialchars($testStatus) ?></div>
                                    </div>
                                <?php } else if (strpos($testStatus, 'GAGAL') !== false) { ?>
                                    <div class="inline-block rounded-lg bg-rose-50 px-2.5 py-1 text-[10px] font-bold text-rose-700 border border-rose-200">
                                        <div class="flex items-center gap-1 justify-center">
                                            <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                                            <span>GAGAL</span>
                                        </div>
                                        <div class="text-[9px] font-normal text-slate-500 mt-0.5"><?= htmlspecialchars($testTime) ?></div>
                                        <div class="text-[9px] font-mono text-rose-600"><?= htmlspecialchars($testStatus) ?></div>
                                    </div>
                                <?php } else { ?>
                                    <span class="text-[11px] text-slate-400 font-medium">Belum dites</span>
                                <?php } ?>
                            </td>
                            <td class="py-3 px-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button type="button" onclick="testEndpoint(<?= $id ?>)" id="btnTest_<?= $id ?>" class="inline-flex items-center gap-1 rounded-lg bg-sky-500 text-white hover:bg-sky-600 px-2.5 py-1.5 text-xs font-medium transition-colors">
                                        <i class="fa-solid fa-plug-circle-bolt text-xs"></i><span>Tes</span>
                                    </button>
                                    <button type="button" onclick="deleteMasuk(<?= $id ?>)" class="h-7 w-7 rounded-lg bg-rose-100 text-rose-600 hover:bg-rose-200 transition-colors inline-flex items-center justify-center" title="Hapus Aplikasi">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php 
                            }
                        } else {
                        ?>
                        <tr>
                            <td colspan="6" class="py-4 text-center text-slate-400">Belum ada aplikasi endpoint masuk.</td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="pt-4 border-t border-slate-100">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-semibold text-white hover:bg-indigo-700 transition-all shadow-sm">
                    <i class="fa-solid fa-floppy-disk"></i><span>Simpan Semua</span>
                </button>
            </div>
        </form>
    </div>

</div>

<!-- Hidden Delete Forms -->
<form method="POST" id="formDeleteKeluar">
    <input type="hidden" name="action_type" value="del_keluar">
    <input type="hidden" name="id" id="delKeluarId" value="">
</form>

<form method="POST" id="formDeleteMasuk">
    <input type="hidden" name="action_type" value="del_masuk">
    <input type="hidden" name="id" id="delMasukId" value="">
</form>

<!-- MODAL TAMBAH ENDPOINT KELUAR -->
<div id="modalAddKeluar" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm hidden">
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl border border-slate-100">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-plus text-indigo-600"></i> Tambah Endpoint Keluar
            </h3>
            <button type="button" class="tw-modal-close text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="action_type" value="add_keluar">

            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Nama Endpoint</label>
                <input type="text" name="nama" required placeholder="misal: Data Siswa SIMAD" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Deskripsi Singkat</label>
                <input type="text" name="deskripsi" placeholder="misal: Sinkron data siswa ke aplikasi eksternal" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Metode HTTP</label>
                <select name="metode" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="GET">GET</option>
                    <option value="POST">POST</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Path Endpoint</label>
                <input type="text" name="endpoint_path" required placeholder="misal: api/simad.php?action=tabungan" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-mono focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <span class="text-[10px] text-slate-400 mt-1 block">Akan otomatis digabung dengan Base URL server.</span>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" class="tw-modal-close rounded-xl border border-slate-200 px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL TAMBAH APLIKASI MASUK -->
<div id="modalAddMasuk" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm hidden">
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl border border-slate-100">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-plus text-indigo-600"></i> Tambah Aplikasi Endpoint Masuk
            </h3>
            <button type="button" class="tw-modal-close text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="action_type" value="add_masuk">

            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Kode Aplikasi (Unik)</label>
                <input type="text" name="kode_app" required placeholder="misal: simad, sibayar" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-mono focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Nama Aplikasi</label>
                <input type="text" name="nama_app" required placeholder="misal: SIMAD / Sibayar" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Deskripsi Singkat</label>
                <input type="text" name="deskripsi" placeholder="misal: Ambil data guru dari SIMAD" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Base URL Aplikasi</label>
                <input type="url" name="base_url" required placeholder="https://simad.misultanfattah.sch.id" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">API Key Secret</label>
                <input type="text" name="api_key" placeholder="API key secret" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" class="tw-modal-close rounded-xl border border-slate-200 px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- JS Helpers -->
<script>
function copyToClipboard(inputId) {
    const copyText = document.getElementById(inputId);
    if (!copyText) return;

    copyText.select();
    copyText.setSelectionRange(0, 99999);
    
    if (navigator.clipboard) {
        navigator.clipboard.writeText(copyText.value).then(() => {
            showToast('URL berhasil disalin!');
        });
    } else {
        document.execCommand("copy");
        showToast('URL berhasil disalin!');
    }
}

function showToast(msg) {
    const toast = document.createElement('div');
    toast.className = 'fixed bottom-5 right-5 bg-slate-900 text-white text-xs px-4 py-2.5 rounded-xl shadow-lg z-50 flex items-center gap-2 transition-opacity duration-300';
    toast.innerHTML = '<i class="fa-solid fa-circle-check text-emerald-400"></i> ' + msg;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 2000);
}

function deleteKeluar(id) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Hapus Endpoint Keluar?',
            text: 'Data endpoint keluar ini akan dihapus secara permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delKeluarId').value = id;
                document.getElementById('formDeleteKeluar').submit();
            }
        });
    } else if (confirm('Hapus endpoint keluar ini?')) {
        document.getElementById('delKeluarId').value = id;
        document.getElementById('formDeleteKeluar').submit();
    }
}

function deleteMasuk(id) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Hapus Aplikasi Endpoint Masuk?',
            text: 'Aplikasi endpoint masuk ini akan dihapus dari daftar.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delMasukId').value = id;
                document.getElementById('formDeleteMasuk').submit();
            }
        });
    } else if (confirm('Hapus aplikasi endpoint masuk ini?')) {
        document.getElementById('delMasukId').value = id;
        document.getElementById('formDeleteMasuk').submit();
    }
}

function testEndpoint(id) {
    const container = document.getElementById('testBadgeContainer_' + id);
    const btn = document.getElementById('btnTest_' + id);
    
    if (btn) {
        btn.disabled = true;
        btn.classList.add('opacity-50');
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs"></i> <span>Testing...</span>';
    }

    if (container) {
        container.innerHTML = '<span class="text-xs text-indigo-600 font-medium flex items-center gap-1"><i class="fa-solid fa-spinner fa-spin"></i> Menghubungi...</span>';
    }

    const formData = new FormData();
    formData.append('id', id);

    fetch('admin/endpoint/test_endpoint.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (btn) {
            btn.disabled = false;
            btn.classList.remove('opacity-50');
            btn.innerHTML = '<i class="fa-solid fa-plug-circle-bolt text-xs"></i> <span>Tes</span>';
        }
        
        if (container) {
            if (res.success) {
                container.innerHTML = `
                    <div class="inline-block rounded-lg bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-700 border border-emerald-200">
                        <div class="flex items-center gap-1">
                            <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>OK</span>
                        </div>
                        <div class="text-[9px] font-normal text-slate-500 mt-0.5">${res.last_test_time}</div>
                        <div class="text-[9px] font-mono text-emerald-600">${res.formatted}</div>
                    </div>
                `;
            } else {
                container.innerHTML = `
                    <div class="inline-block rounded-lg bg-rose-50 px-2.5 py-1 text-[10px] font-bold text-rose-700 border border-rose-200">
                        <div class="flex items-center gap-1 justify-center">
                            <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                            <span>GAGAL</span>
                        </div>
                        <div class="text-[9px] font-normal text-slate-500 mt-0.5">${res.last_test_time}</div>
                        <div class="text-[9px] font-mono text-rose-600">${res.formatted}</div>
                    </div>
                `;
            }
        }
    })
    .catch(err => {
        if (btn) {
            btn.disabled = false;
            btn.classList.remove('opacity-50');
            btn.innerHTML = '<i class="fa-solid fa-plug-circle-bolt text-xs"></i> <span>Tes</span>';
        }
        if (container) {
            container.innerHTML = '<span class="text-xs text-rose-600 font-medium">Error testing</span>';
        }
    });
}
</script>
