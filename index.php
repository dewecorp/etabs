<?php
    //Mulai Sesion
session_start();
if (isset($_SESSION["ses_username"])==""){
	header("location: login.php");
	
}else{
	$data_id = $_SESSION["ses_id"];
	$data_nama = $_SESSION["ses_nama"];
	$data_user = $_SESSION["ses_username"];
	$data_level = $_SESSION["ses_level"];
}

    //KONEKSI DB
include "inc/koneksi.php";

// Cek koneksi database
if (!isset($koneksi) || !$koneksi) {
    die("Koneksi database gagal. Silakan periksa konfigurasi database di inc/koneksi.php");
}

// Cek apakah koneksi masih aktif
if (is_object($koneksi) && $koneksi->connect_error) {
    die("Koneksi database error: " . $koneksi->connect_error);
}

    //FUNGSI RUPIAH
include "inc/rupiah.php";
include "inc/config.php";
include_once "inc/activity_log.php";

	//Profil Sekolah
$nama = "e-Tabs";
try {
    $sql = @$koneksi->query("SELECT * from tb_profil LIMIT 1");
    if ($sql && $sql->num_rows > 0) {
        while ($data = $sql->fetch_assoc()) {
            $nama = $data['nama_sekolah'];
        }
    }
} catch (Exception $e) {
    // Jika error, gunakan default
    error_log("Error loading profil: " . $e->getMessage());
    $nama = "e-Tabs";
}

// Ambil page title dinamis
$current_page = isset($_GET['page']) ? $_GET['page'] : '';
if (empty($current_page)) {
    // Jika tidak ada page, tentukan berdasarkan level user
    if (isset($data_level)) {
        $current_page = ($data_level == "Administrator") ? 'admin' : 'petugas';
    }
}
$page_title = getPageTitle($current_page);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= htmlspecialchars($page_title) ?> | e-Tabs</title>
    <link rel="icon" href="images/logo.png">
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    
    <!-- Bootstrap 5.3.3 -->


    <!-- Tailwind CSS -->
    <script>
        (function () {
            var originalWarn = console.warn;
            console.warn = function () {
                try {
                    if (arguments && typeof arguments[0] === 'string' && arguments[0].indexOf('cdn.tailwindcss.com should not be used in production') !== -1) {
                        return;
                    }
                } catch (e) {}
                return originalWarn.apply(console, arguments);
            };
        })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- DataTables (no Bootstrap theme) -->
    <!-- Select2 -->
    <link rel="stylesheet" href="plugins/select2/select2.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/dashboard.css?v=<?php echo time(); ?>">
    <style>
        /* CRITICAL CSS OVERRIDES - DO NOT REMOVE */
        .badge-pill {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 9999px !important;
            padding: 0.25rem 0.75rem !important;
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            line-height: 1 !important;
            white-space: nowrap !important;
        }
        .badge-pill-success {
            background-color: #dcfce7 !important;
            color: #166534 !important;
            border: 1px solid #bbf7d0 !important;
        }
        .badge-pill-danger {
            background-color: #ffe4e6 !important;
            color: #9f1239 !important;
            border: 1px solid #fecdd3 !important;
        }
        .badge-pill-primary {
            background-color: #e0e7ff !important;
            color: #3730a3 !important;
            border: 1px solid #c7d2fe !important;
        }
        .badge-pill-secondary {
            background-color: #f1f5f9 !important;
            color: #334155 !important;
            border: 1px solid #e2e8f0 !important;
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>
</head>

<body class="min-h-screen bg-slate-50 text-slate-900 flex flex-col transition-colors duration-200">
    <!-- Navbar sticky -->
    <header class="sticky top-0 z-[100] border-b border-emerald-500 bg-emerald-600/95 backdrop-blur-xl shadow-md">
        <div class="flex w-full items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white shadow-lg shadow-emerald-900/20">
                    <img src="images/logo.png" alt="Logo" class="h-6 w-6 object-contain">
                </div>
                <div>
                    <h1 class="text-sm font-semibold tracking-tight text-white sm:text-base">e-Tabs Dashboard</h1>
                    <p class="text-[11px] text-emerald-100/80 sm:text-xs"><?= $nama ?></p>
                </div>
            </div>

            <div class="hidden items-center gap-3 md:flex">
                <div class="text-[11px] text-emerald-50 mr-2">
                    <i class="fa-regular fa-calendar-days mr-1 text-emerald-200"></i>
                    <?php $date = date('Y-m-d'); echo format_hari_tanggal($date) ?>
                </div>
                
                <div class="relative group">
                    <button class="flex items-center gap-2 rounded-full border border-emerald-500 bg-emerald-700/40 px-3 py-1.5 text-xs font-medium text-white hover:bg-emerald-700/60 transition-all">
                        <div class="h-6 w-6 rounded-full bg-white text-emerald-600 flex items-center justify-center text-[10px] font-bold">
                            <?= substr($data_nama, 0, 2) ?>
                        </div>
                        <span><?= $data_nama ?></span>
                        <i class="fa-solid fa-chevron-down text-[10px] text-emerald-300 group-hover:rotate-180 transition-transform"></i>
                    </button>
                    
                    <!-- User Dropdown Menu -->
                    <div class="absolute right-0 mt-2 w-48 origin-top-right rounded-2xl border border-slate-200 bg-white p-2 shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-[110]">
                        <div class="px-3 py-2 border-b border-slate-100 mb-1">
                            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Info Akun</p>
                            <p class="text-xs font-medium text-slate-700 truncate"><?= $data_nama ?></p>
                            <p class="text-[10px] text-slate-500 italic"><?= $data_level ?></p>
                        </div>
                        <a href="logout.php" onclick="confirmLogout(event)" class="flex items-center gap-2 rounded-xl px-3 py-2 text-xs text-rose-600 hover:bg-rose-50 transition-colors">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            <span>Keluar</span>
                        </a>
                    </div>
                </div>
            </div>

            <button class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-emerald-500 bg-emerald-700/40 text-emerald-100 hover:bg-emerald-700/60 md:hidden" id="menuToggle">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>
    </header>

    <!-- Konten utama -->
    <main class="flex w-full flex-1 flex-col gap-6 px-4 pb-10 pt-5 sm:px-6 lg:px-8 lg:flex-row lg:items-start relative">
        <!-- Sidebar -->
        <aside id="sidebar" class="order-1 w-full space-y-4 hidden md:block lg:w-64 lg:sticky lg:top-20 z-[110] bg-white lg:bg-transparent absolute lg:relative left-0 top-0 h-fit lg:h-auto p-4 lg:p-0 shadow-2xl lg:shadow-none rounded-b-3xl lg:rounded-none border-x border-b border-slate-100 lg:border-none">
            

            <nav class="rounded-2xl border border-slate-200 bg-white p-3 text-xs text-slate-600 shadow-sm">
                <p class="mb-2 px-1 text-[11px] font-semibold uppercase tracking-wide text-slate-400">Menu Utama</p>
                <ul class="space-y-1.5">
                    <?php if ($data_level == "Administrator") { ?>
                    <li>
                        <a href="?page=admin" class="flex items-center gap-2 rounded-xl px-3 py-2 text-xs <?= ($current_page == 'admin') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg <?= ($current_page == 'admin') ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-500' ?>">
                                <i class="fa-solid fa-house text-[10px]"></i>
                            </span>
                            Dashboard
                        </a>
                    </li>
                    
                    <!-- Master Data Dropdown -->
                    <li>
                        <div class="flex flex-col gap-1">
                            <button class="flex items-center justify-between rounded-xl px-3 py-2 text-xs text-slate-600 hover:bg-slate-50 hover:text-slate-900 w-full sidebar-dropdown-toggle" data-target="#masterDataMenu">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                                        <i class="fa-solid fa-folder text-[10px]"></i>
                                    </span>
                                    Master Data
                                </div>
                                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                            </button>
                            <div class="sidebar-dropdown-menu <?= (strpos($current_page, 'MyApp/data_') !== false) ? '' : 'hidden' ?>" id="masterDataMenu">
                                <ul class="mt-1 space-y-1 pl-8 border-l border-slate-200 ml-3">
                                    <li><a href="?page=MyApp/data_siswa" class="block py-1.5 hover:text-indigo-600 <?= ($current_page == 'MyApp/data_siswa') ? 'text-indigo-600 font-medium' : 'text-slate-500' ?>">Data Siswa</a></li>
                                    <li><a href="?page=MyApp/data_kelas" class="block py-1.5 hover:text-indigo-600 <?= ($current_page == 'MyApp/data_kelas') ? 'text-indigo-600 font-medium' : 'text-slate-500' ?>">Data Kelas</a></li>
                                </ul>
                            </div>
                        </div>
                    </li>

                    <!-- Transaksi Dropdown -->
                    <li>
                        <div class="flex flex-col gap-1">
                            <button class="flex items-center justify-between rounded-xl px-3 py-2 text-xs text-slate-600 hover:bg-slate-50 hover:text-slate-900 w-full sidebar-dropdown-toggle" data-target="#transaksiMenu">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                                        <i class="fa-solid fa-money-bill-transfer text-[10px]"></i>
                                    </span>
                                    Transaksi
                                </div>
                                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                            </button>
                            <div class="sidebar-dropdown-menu <?= (in_array($current_page, ['data_setor', 'data_tarik', 'view_kas'])) ? '' : 'hidden' ?>" id="transaksiMenu">
                                <ul class="mt-1 space-y-1 pl-8 border-l border-slate-200 ml-3">
                                    <li><a href="?page=data_setor" class="block py-1.5 hover:text-indigo-600 <?= ($current_page == 'data_setor') ? 'text-indigo-600 font-medium' : 'text-slate-500' ?>">Setoran</a></li>
                                    <li><a href="?page=data_tarik" class="block py-1.5 hover:text-indigo-600 <?= ($current_page == 'data_tarik') ? 'text-indigo-600 font-medium' : 'text-slate-500' ?>">Penarikan</a></li>
                                    <li><a href="?page=view_kas" class="block py-1.5 hover:text-indigo-600 <?= ($current_page == 'view_kas') ? 'text-indigo-600 font-medium' : 'text-slate-500' ?>">Info Kas</a></li>
                                </ul>
                            </div>
                        </div>
                    </li>

                    <li>
                        <a href="?page=view_tabungan" class="flex items-center gap-2 rounded-xl px-3 py-2 text-xs <?= ($current_page == 'view_tabungan') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg <?= ($current_page == 'view_tabungan') ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-500' ?>">
                                <i class="fa-solid fa-book text-[10px]"></i>
                            </span>
                            Tabungan
                        </a>
                    </li>

                    <li>
                        <a href="?page=laporan" class="flex items-center gap-2 rounded-xl px-3 py-2 text-xs <?= ($current_page == 'laporan') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg <?= ($current_page == 'laporan') ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-500' ?>">
                                <i class="fa-solid fa-file-invoice text-[10px]"></i>

                            </span>
                            Laporan
                        </a>
                    </li>

                    <li>
                        <a href="?page=data_riwayat" class="flex items-center gap-2 rounded-xl px-3 py-2 text-xs <?= ($current_page == 'data_riwayat') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg <?= ($current_page == 'data_riwayat') ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-500' ?>">
                                <i class="fa-solid fa-clock-rotate-left text-[10px]"></i>
                            </span>
                            Riwayat
                        </a>
                    </li>

                    <p class="mt-4 mb-2 px-1 text-[11px] font-semibold uppercase tracking-wide text-slate-500">Pengaturan</p>
                    
                    <li>
                        <a href="?page=MyApp/data_pengguna" class="flex items-center gap-2 rounded-xl px-3 py-2 text-xs <?= ($current_page == 'MyApp/data_pengguna') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg <?= ($current_page == 'MyApp/data_pengguna') ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-500' ?>">
                                <i class="fa-solid fa-users-gear text-[10px]"></i>
                            </span>
                            Pengguna Sistem
                        </a>
                    </li>

                    <li>
                        <a href="?page=MyApp/data_profil" class="flex items-center gap-2 rounded-xl px-3 py-2 text-xs <?= ($current_page == 'MyApp/data_profil') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg <?= ($current_page == 'MyApp/data_profil') ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-500' ?>">
                                <i class="fa-solid fa-gear text-[10px]"></i>
                            </span>
                            Pengaturan
                        </a>
                    </li>

                    <li>
                        <a href="?page=MyApp/backup_restore" class="flex items-center gap-2 rounded-xl px-3 py-2 text-xs <?= ($current_page == 'MyApp/backup_restore') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg <?= ($current_page == 'MyApp/backup_restore') ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-500' ?>">
                                <i class="fa-solid fa-database text-[10px]"></i>
                            </span>
                            Backup & Restore
                        </a>
                    </li>

                    <?php } else { ?>
                    <!-- Petugas Menu -->
                    <li>
                        <a href="?page=petugas" class="flex items-center gap-2 rounded-xl px-3 py-2 text-xs <?= ($current_page == 'petugas') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg <?= ($current_page == 'petugas') ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-500' ?>">
                                <i class="fa-solid fa-house text-[10px]"></i>
                            </span>
                            Dashboard
                        </a>
                    </li>

                    <li>
                        <div class="flex flex-col gap-1">
                            <button class="flex items-center justify-between rounded-xl px-3 py-2 text-xs text-slate-600 hover:bg-slate-50 hover:text-slate-900 w-full sidebar-dropdown-toggle" data-target="#transaksiMenuPetugas">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                                        <i class="fa-solid fa-money-bill-transfer text-[10px]"></i>
                                    </span>
                                    Transaksi
                                </div>
                                <i class="fa-solid fa-chevron-down text-[10px]"></i>
                            </button>
                            <div class="sidebar-dropdown-menu <?= (in_array($current_page, ['data_setor', 'data_tarik', 'view_kas'])) ? '' : 'hidden' ?>" id="transaksiMenuPetugas">
                                <ul class="mt-1 space-y-1 pl-8 border-l border-slate-200 ml-3">
                                    <li><a href="?page=data_setor" class="block py-1.5 hover:text-indigo-600 <?= ($current_page == 'data_setor') ? 'text-indigo-600 font-medium' : 'text-slate-600' ?>">Setoran</a></li>
                                    <li><a href="?page=data_tarik" class="block py-1.5 hover:text-indigo-600 <?= ($current_page == 'data_tarik') ? 'text-indigo-600 font-medium' : 'text-slate-600' ?>">Penarikan</a></li>
                                    <li><a href="?page=view_kas" class="block py-1.5 hover:text-indigo-600 <?= ($current_page == 'view_kas') ? 'text-indigo-600 font-medium' : 'text-slate-600' ?>">Info Kas</a></li>
                                </ul>
                            </div>
                        </div>
                    </li>

                    <li>
                        <a href="?page=view_tabungan" class="flex items-center gap-2 rounded-xl px-3 py-2 text-xs <?= ($current_page == 'view_tabungan') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg <?= ($current_page == 'view_tabungan') ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-500' ?>">
                                <i class="fa-solid fa-book text-[10px]"></i>
                            </span>
                            Tabungan
                        </a>
                    </li>

                    <li>
                        <a href="?page=laporan" class="flex items-center gap-2 rounded-xl px-3 py-2 text-xs <?= ($current_page == 'laporan') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg <?= ($current_page == 'laporan') ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-500' ?>">
                                <i class="fa-solid fa-file-invoice text-[10px]"></i>
                            </span>
                            Laporan
                        </a>
                    </li>

                    <li>
                        <a href="?page=data_riwayat" class="flex items-center gap-2 rounded-xl px-3 py-2 text-xs <?= ($current_page == 'data_riwayat') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg <?= ($current_page == 'data_riwayat') ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-500' ?>">
                                <i class="fa-solid fa-clock-rotate-left text-[10px]"></i>
                            </span>
                            Riwayat
                        </a>
                    </li>
                    <?php } ?>
                    
                    <li class="mt-6 border-t border-slate-200 pt-2">
                        <a href="logout.php" onclick="confirmLogout(event)" class="flex items-center gap-2 rounded-xl px-3 py-2 text-xs text-rose-500 hover:bg-rose-50 hover:text-rose-600 group">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg bg-rose-50 text-rose-600 group-hover:bg-rose-100 transition-colors">
                                <i class="fa-solid fa-arrow-right-from-bracket text-[10px]"></i>
                            </span>
                            <span class="font-medium">Keluar Sistem</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content Wrapper -->
        <section id="mainContent" class="order-2 flex-1 space-y-4">
            <nav class="mb-1 text-[11px] text-slate-500" aria-label="Breadcrumb">
                <a href="index.php" class="text-slate-500 hover:text-indigo-600">Home</a>
                <span class="mx-1 text-slate-400">/</span>
                <span class="text-slate-700"><?= htmlspecialchars($page_title) ?></span>
            </nav>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <?php 
                    if(isset($_GET['page'])){
                        $hal = $_GET['page'];

                        switch ($hal) {
				//Klik Halaman Home Pengguna
                            case 'admin':
                            include "home/admin.php";
                            break;
                            case 'petugas':
                            include "home/petugas.php";
                            break;

				//Pengguna
                            case 'MyApp/data_pengguna':
                            include "admin/pengguna/data_pengguna.php";
                            break;
                            case 'MyApp/add_pengguna':
                            include "admin/pengguna/add_pengguna.php";
                            break;
                            case 'MyApp/edit_pengguna':
                            include "admin/pengguna/edit_pengguna.php";
                            break;
                            case 'MyApp/del_pengguna':
                            include "admin/pengguna/del_pengguna.php";
                            break;

				//Profil
                            case 'MyApp/data_profil':
                            include "admin/profil/data_profil.php";
                            break;
                            case 'MyApp/edit_profil':
                            include "admin/profil/edit_profil.php";
                            break;


				//Kelas
                            case 'MyApp/data_kelas':
                            include "admin/kelas/data_kelas.php";
                            break;
                            case 'MyApp/add_kelas':
                            include "admin/kelas/add_kelas.php";
                            break;
                            case 'MyApp/edit_kelas':
                            include "admin/kelas/edit_kelas.php";
                            break;
                            case 'MyApp/del_kelas':
                            include "admin/kelas/del_kelas.php";
                            break;

				//Siswa
                            case 'MyApp/data_siswa':
                            include "admin/siswa/data_siswa.php";
                            break;
                            case 'MyApp/add_siswa':
                            include "admin/siswa/add_siswa.php";
                            break;
                            case 'MyApp/edit_siswa':
                            include "admin/siswa/edit_siswa.php";
                            break;
                            case 'MyApp/edit_siswa_multiple':
                            include "admin/siswa/edit_siswa_multiple.php";
                            break;
                            case 'MyApp/del_siswa':
                            include "admin/siswa/del_siswa.php";
                            break;
                            case 'MyApp/del_siswa_multiple':
                            include "admin/siswa/del_siswa_multiple.php";
                            break;

				//Backup & Restore
                            case 'MyApp/backup_restore':
                            include "admin/backup_restore/backup_restore.php";
                            break;

				//Aktivitas
                            case 'MyApp/view_activity':
                            case 'MyApp/activity_log':
                            include "admin/activity/activity_log.php";
                            break;

				//Setor
                            case 'data_setor':
                            include "petugas/setor/data_setor.php";
                            break;
                            case 'edit_setor_multiple':
                            include "petugas/setor/edit_setor_multiple.php";
                            break;
                            case 'del_setor':
                            include "petugas/setor/del_setor.php";
                            break;
                            case 'del_setor_multiple':
                            include "petugas/setor/del_setor_multiple.php";
                            break;

				//Tarik
                            case 'data_tarik':
                            include "petugas/tarik/data_tarik.php";
                            break;
                            case 'edit_tarik_multiple':
                            include "petugas/tarik/edit_tarik_multiple.php";
                            break;
                            case 'del_tarik':
                            include "petugas/tarik/del_tarik.php";
                            break;
                            case 'del_tarik_multiple':
                            include "petugas/tarik/del_tarik_multiple.php";
                            break;

				//Riwayat
                            case 'data_riwayat':
                            include "petugas/riwayat/data_riwayat.php";
                            break;
                            case 'del_riwayat':
                            include "petugas/riwayat/del_riwayat.php";
                            break;

				//Tabungan
                            case 'data_tabungan':
                            include "petugas/tabungan/data_tabungan.php";
                            break;
                            case 'view_tabungan':
                            include "petugas/tabungan/view_tabungan.php";
                            break;

				//kas
                            case 'kas_tabungan':
                            include "petugas/kas/data_kas.php";
                            break;
                            case 'kas_full':
                            include "petugas/kas/kas_full.php";
                            break;
                            case 'view_kas':
                            include "petugas/kas/data_kas.php";
                            break;

				//laporan
                            case 'laporan':
                            include "petugas/laporan/view_laporan.php";
                            break;



				//default
                            default:
                            echo "<center><br><br><br><br><br><br><br><br><br>
                            <h1> Halaman tidak ditemukan !</h1></center>";
                            break;    
                        }
                    }else{
				// Auto Halaman Home Pengguna
                        if($data_level=="Administrator"){
                         include "home/admin.php";
                     }
                     elseif($data_level=="Petugas"){
                         include "home/petugas.php";
                     }
                 }
                 ?>
            </div>
        </section>
    </main>

    <footer class="border-t border-slate-200 bg-white/80 px-4 py-4 sm:px-6 lg:px-8 mt-auto">
        <div class="mx-auto flex max-w-5xl flex-col items-center justify-between gap-2 text-[11px] text-slate-500 sm:flex-row">
            <p class="text-center sm:text-left">
                © 2026 e-Tabs • <?= $nama ?> • All rights reserved.
            </p>
            <div class="flex flex-wrap items-center justify-center gap-3">
                <a href="index.php" class="hover:text-indigo-600">Dashboard</a>
                <span class="text-slate-300">|</span>
                <span class="text-slate-500">v2.0-modern</span>
            </div>
        </div>
    </footer>

    <!-- DataTables -->
    <script src="plugins/datatables/jquery.dataTables.min.js"></script>
    
    <!-- Select2 -->
    <script src="plugins/select2/select2.full.min.js"></script>
    <!-- Custom Dashboard JS -->
    <script src="assets/js/dashboard.js"></script>

    <script>
        $(function() {
            // Initialize Select2 if available
            if ($.fn.select2) {
                $(".select2").select2();
            }

            // Custom Sidebar Dropdown Toggle
            $('.sidebar-dropdown-toggle').click(function() {
                const targetId = $(this).data('target');
                $(targetId).toggleClass('hidden');
                $(this).find('i.fa-chevron-down').toggleClass('rotate-180'); // Optional: rotate icon
            });
        });
    </script>
</body>

</html>
