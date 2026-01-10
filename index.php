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
    die("Koneksi database gagal. Silakan periksa konfigurasi database di inc/koneksi.php atau inc/config_db.php");
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
$nama = "e-TABS";
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
    $nama = "e-TABS";
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
    <title><?= htmlspecialchars($page_title) ?> | e-TABS | <?= htmlspecialchars($nama) ?></title>
    <link rel="icon" href="dist/img/logo.png">
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.6 -->
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="plugins/datatables/dataTables.bootstrap.css">
    <!-- Select2 -->
    <link rel="stylesheet" href="plugins/select2/select2.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
    <!-- AdminLTE Skins. Choose a skin from the css/skins
      folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css" integrity="sha512-6S2HWzVFxruDlZxI3FeXOspiGkXH0pYqltg8Ai8a3gHmumZX8fiT5O8Mc0C05lHZ6f8YgOI5q1gJd0wQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js" integrity="sha512-lbwH47l/tPXJYG9AcFNoJaTMhGv2WhlBxpbK7pSUJytVqef6MxXU7pS4gFyR2b0j1z5f0PwHD3r4n8Ef3hC+w==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <!-- Custom CSS untuk Sticky Navbar -->
    <style>
        /* Sticky Navbar - hanya navbar, bukan seluruh header */
        .main-header .navbar {
            position: fixed !important;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            margin-left: 0 !important;
            margin-right: 0 !important;
            width: 100% !important;
        }
        
        /* Pastikan main-header tidak fixed, hanya navbar */
        .main-header {
            position: relative;
            margin-bottom: 0;
            height: 50px;
        }
        
        /* Logo tetap di tempat dan tidak ikut sticky */
        .main-header .logo {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1031;
            width: 230px;
            height: 50px;
        }
        
        /* Adjust body untuk mengakomodasi fixed navbar */
        body {
            padding-top: 50px;
        }
        
        /* Fix untuk sidebar - sidebar tidak ikut sticky dan tidak bergerak saat scroll */
        .main-sidebar {
            position: fixed !important;
            top: 50px;
            left: 0;
            padding-top: 0;
            z-index: 1000;
            height: calc(100vh - 50px) !important;
            max-height: calc(100vh - 50px) !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            -webkit-overflow-scrolling: touch;
        }
        
        /* Pastikan sidebar tidak bergerak dan bisa di-scroll */
        .sidebar {
            position: relative;
            min-height: calc(100vh - 50px);
            height: auto;
            padding-bottom: 60px !important;
            margin-bottom: 20px;
            /* Pastikan pointer events bekerja dengan baik */
            pointer-events: auto;
        }
        
        /* Pastikan sidebar-menu bisa di-scroll */
        .sidebar-menu {
            list-style: none;
            margin: 0;
            padding: 0;
            padding-bottom: 20px;
            /* Pastikan pointer events bekerja */
            pointer-events: auto;
        }
        
        /* Pastikan semua item menu bisa diklik */
        .sidebar-menu > li {
            position: relative;
            pointer-events: auto;
        }
        
        .sidebar-menu > li > a {
            pointer-events: auto;
            cursor: pointer;
            position: relative;
            z-index: 1;
        }
        
        /* Pastikan treeview menu (dropdown) bisa ditampilkan dengan benar */
        .sidebar-menu .treeview-menu {
            position: relative !important;
            z-index: 10 !important;
            pointer-events: auto !important;
            /* Pastikan dropdown tidak dipotong */
            overflow: visible !important;
            max-height: none !important;
        }
        
        /* Pastikan treeview menu item bisa diklik */
        .sidebar-menu .treeview-menu > li > a {
            pointer-events: auto !important;
            cursor: pointer !important;
            position: relative;
            z-index: 1;
        }
        
        /* Pastikan sidebar container tidak memotong dropdown menu */
        .sidebar {
            overflow: visible !important;
        }
        
        /* Pastikan sidebar menu tidak memotong dropdown */
        .sidebar-menu {
            overflow: visible !important;
        }
        
        /* Pastikan menu logout terlihat */
        .sidebar-menu li:last-child {
            margin-bottom: 20px;
        }
        
        /* Style scrollbar untuk sidebar */
        .main-sidebar::-webkit-scrollbar {
            width: 8px;
        }
        
        .main-sidebar::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        .main-sidebar::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        
        .main-sidebar::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        
        /* Pastikan user panel tidak menghalangi scroll */
        .user-panel {
            position: relative;
            margin-bottom: 10px;
        }
        
        /* Pastikan semua elemen sidebar bisa diakses */
        .sidebar .form-control,
        .sidebar .btn {
            position: relative;
        }
        
        /* Content wrapper adjust untuk navbar fixed */
        .content-wrapper {
            margin-top: 0;
            margin-left: 0;
        }
        
        /* Pastikan content tidak tertutup oleh navbar */
        @media (min-width: 768px) {
            .sidebar-mini.sidebar-collapse .content-wrapper {
                margin-left: 50px;
            }
            
            body:not(.sidebar-collapse) .content-wrapper {
                margin-left: 230px;
            }
        }
        
        /* Fix untuk sidebar collapsed */
        @media (min-width: 768px) {
            .sidebar-mini.sidebar-collapse .main-header .navbar {
                left: 50px;
                width: calc(100% - 50px) !important;
            }
            
            .sidebar-mini.sidebar-collapse .main-header .logo {
                width: 50px;
            }
            
            .sidebar-mini.sidebar-collapse .main-sidebar {
                width: 50px;
            }
            
            body:not(.sidebar-collapse) .main-header .navbar {
                left: 230px;
                width: calc(100% - 230px) !important;
            }
            
            body:not(.sidebar-collapse) .main-header .logo {
                width: 230px;
            }
            
            body:not(.sidebar-collapse) .main-sidebar {
                width: 230px;
            }
        }
        
        /* Fix untuk mobile */
        @media (max-width: 767px) {
            .main-header .navbar {
                left: 0 !important;
                width: 100% !important;
            }
            
            .main-sidebar {
                top: 50px;
            }
        }
        
        /* Shadow saat scroll untuk efek visual */
        .main-header .navbar.navbar-sticky-active {
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
    </style>

</head>

<body class="hold-transition skin-green sidebar-mini">
    <!-- Site wrapper -->
    <div class="wrapper">
        <header class="main-header">
            <!-- Logo -->
            <a href="index.php" class="logo">
                <span class="logo-lg">
                    <img src="dist/img/logo.png" width="45px">
                    <b>e-TABS</b>
                </span>
            </a>
            <!-- Header Navbar: style can be found in header.less -->
            <nav class="navbar navbar-static-top">
                <!-- Sidebar toggle button-->
                <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </a>

                <div class="navbar-custom-menu">

                    <ul class="nav navbar-nav pull-left">
                        <li class="dropdown messages-menu">
                            <a class="dropdown-toggle" data-toggle="dropdown">
                                <span>
                                    <?php $date = date('Y-m-d'); echo format_hari_tanggal($date) ?>
                                </span>
                            </a>
                        </li>
                    </ul>
                    <ul class="nav navbar-nav">
                        <!-- Messages: style can be found in dropdown.less-->
                        <li class="dropdown user user-menu">
                            <a href="" class="dropdown-toggle" data-toggle="dropdown">
                                <img src="dist/img/avatar.png" class="rounded-circle" width="25" height="25">
                                <span>
                                    <?= $data_nama;?>
                                </span>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="user-header">
                                    <img src="dist/img/avatar.png" class="img-circle" alt="User Image" width="60"
                                        height="60">
                                    <p>
                                        <?= $data_nama;?>
                                    </p>
                                </li>
                                <!-- Menu Body -->

                                <!-- Menu Footer-->
                                <li class="user-footer">
                                    <div class="pull-left">
                                        <a href="#" class="btn btn-default btn-flat">Profile
                                        </a>
                                    </div>
                                    <div class="pull-right">
                                        <a href="logout.php"
                                            onclick="return confirmLogout(event)"
                                            class="btn btn-default btn-flat">Sign out
                                        </a>
                                    </div>
                                </li>
                            </ul>
                        </li>

                    </ul>
                </div>
            </nav>
        </header>

        <!-- =============================================== -->

        <!-- Left side column. contains the sidebar -->
        <aside class="main-sidebar">
            <!-- sidebar: style can be found in sidebar.less -->
            <section class="sidebar">
                <!-- Sidebar user panel -->
                </<b>
                <div class="user-panel">
                    <div class="pull-left image">
                        <img src="dist/img/avatar.png" class="img-circle" alt="User Image">
                    </div>
                    <div class="pull-left info">
                        <p>
                            <?php echo $data_nama; ?>
                        </p>
                        <span class="label label-success">
                            <?php echo $data_level; ?>
                        </span>
                    </div>
                </div>
                </br>
                <!-- /.search form -->
                <!-- sidebar menu: : style can be found in sidebar.less -->
                <ul class="sidebar-menu">
                    <li class="header">MAIN NAVIGATION</li>

                    <!-- Level  -->
                    <?php
                if ($data_level=="Administrator"){
                   ?>

                    <li class="treeview">
                        <a href="?page=admin">
                            <i class="fa fa-dashboard"></i>
                            <span>Dashboard</span>
                            <span class="pull-right-container">
                            </span>
                        </a>
                    </li>

                    <li class="treeview">
                        <a href="#">
                            <i class="fa fa-folder"></i>
                            <span>Master Data</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">

                            <li>
                                <a href="?page=MyApp/data_siswa">
                                    <i class="fa fa-users"></i>Siswa
                                </a>
                            </li>
                            <li>
                                <a href="?page=MyApp/data_kelas">
                                    <i class="fa fa-feed"></i>Kelas
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="treeview">
                        <a href="#">
                            <i class="fa fa-refresh"></i>
                            <span>Transaksi</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right">
                                </i>
                            </span>
                        </a>
                        <ul class="treeview-menu">

                            <li>
                                <a href="?page=data_setor">
                                    <i class="fa fa-arrow-circle-o-down">
                                    </i>Setoran
                                </a>
                            </li>
                            <li>
                                <a href="?page=data_tarik">
                                    <i class="fa fa-arrow-circle-o-up">
                                    </i>Penarikan
                                </a>
                            </li>
                            <li>
                                <a href="?page=view_kas">
                                    <i class="fa  fa-pie-chart">
                                    </i>Info Kas
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="treeview">
                        <a href="?page=view_tabungan">
                            <i class="fa fa-book"></i>
                            <span>Tabungan</span>
                            <span class="pull-right-container">
                            </span>
                        </a>
                    </li>

                    <li class="treeview">
                        <a href="?page=laporan">
                            <i class="fa fa-file"></i>
                            <span>Laporan</span>
                            <span class="pull-right-container">
                            </span>
                        </a>
                    </li>

                    <li class="treeview">
                        <a href="?page=data_riwayat">
                            <i class="fa fa-history"></i>
                            <span>Riwayat</span>
                            <span class="pull-right-container">
                            </span>
                        </a>
                    </li>

                    <li class="header">SETTING</li>

                    <li class="treeview">
                        <a href="?page=MyApp/data_pengguna">
                            <i class="fa fa-user"></i>
                            <span>Pengguna Sistem</span>
                            <span class="pull-right-container">
                            </span>
                        </a>
                    </li>

                    <li class="treeview">
                        <a href="?page=MyApp/data_profil">
                            <i class="fa fa-bank"></i>
                            <span>Profil Sekolah</span>
                            <span class="pull-right-container">
                            </span>
                        </a>
                    </li>

                    <li class="treeview">
                        <a href="?page=MyApp/backup_restore">
                            <i class="fa fa-database"></i>
                            <span>Backup & Restore</span>
                            <span class="pull-right-container">
                            </span>
                        </a>
                    </li>


                    <?php
            } elseif($data_level=="Petugas"){
             ?>

                    <li class="treeview">
                        <a href="?page=petugas">
                            <i class="fa fa-dashboard"></i>
                            <span>Dashboard</span>
                            <span class="pull-right-container">
                            </span>
                        </a>
                    </li>

                    <li class="treeview">
                        <a href="#">
                            <i class="fa fa-refresh"></i>
                            <span>Transaksi</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">

                            <li>
                                <a href="?page=data_setor">
                                    <i class="fa fa-arrow-circle-o-down"></i>Setoran</a>
                            </li>
                            <li>
                                <a href="?page=data_tarik">
                                    <i class="fa fa-arrow-circle-o-up"></i>Penarikan</a>
                            </li>
                            <li>
                                <a href="?page=view_kas">
                                    <i class="fa  fa-pie-chart"></i>Info Kas</a>
                            </li>
                        </ul>
                    </li>

                    <li class="treeview">
                        <a href="?page=view_tabungan">
                            <i class="fa fa-book"></i>
                            <span>Tabungan</span>
                            <span class="pull-right-container">
                            </span>
                        </a>
                    </li>

                    <li class="treeview">
                        <a href="?page=laporan">
                            <i class="fa fa-file"></i>
                            <span>Laporan</span>
                            <span class="pull-right-container">
                            </span>
                        </a>
                    </li>

                    <li class="treeview">
                        <a href="?page=data_riwayat">
                            <i class="fa fa-history"></i>
                            <span>Riwayat</span>
                            <span class="pull-right-container">
                            </span>
                        </a>
                    </li>

                    <li class="header">SETTING</li>

                    <?php
                    }
                    ?>

                    <li>
                        <a href="logout.php" onclick="return confirmLogout(event)">
                            <i class="fa fa-sign-out"></i>
                            <span>Logout</span>
                            <span class="pull-right-container"></span>
                        </a>
                    </li>


            </section>
            <!-- /.sidebar -->
        </aside>

        <!-- =============================================== -->

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <!-- Main content -->
            <section class="content">
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
                            case 'add_setor':
                            include "petugas/setor/add_setor.php";
                            break;
                            case 'edit_setor':
                            include "petugas/setor/edit_setor.php";
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
                            case 'add_tarik':
                            include "petugas/tarik/add_tarik.php";
                            break;
                            case 'edit_tarik':
                            include "petugas/tarik/edit_tarik.php";
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
                            include "petugas/kas/view_kas.php";
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

            </section>
            <!-- /.content -->
        </div>

        <!-- /.content-wrapper -->

        <footer class="main-footer">
            <div class="pull-right hidden-xs">
            </div>
            <strong>Copyright &copy; e-Tabs
                <a href="https://misultanfattah.sch.id/" target="blank">MI Sultan Fattah Jepara
                </a><?=date('Y')?>
            </strong>
        </footer>
        <div class="control-sidebar-bg"></div>

        <!-- ./wrapper -->

        <!-- jQuery 2.2.3 -->
        <script src="plugins/jQuery/jquery-2.2.3.min.js"></script>
        <!-- Bootstrap 3.3.6 -->
        <script src="bootstrap/js/bootstrap.min.js"></script>

        <script src="plugins/select2/select2.full.min.js"></script>
        <!-- DataTables -->
        <script src="plugins/datatables/jquery.dataTables.min.js"></script>
        <script src="plugins/datatables/dataTables.bootstrap.min.js"></script>

        <!-- AdminLTE App -->
        <script src="dist/js/app.min.js"></script>
        <!-- AdminLTE for demo purposes -->
        <script src="dist/js/demo.js"></script>
        <!-- page script -->


        <script>
        $(function() {
            $("#example1").DataTable();
            $('#example2').DataTable({
                "paging": true,
                "lengthChange": false,
                "searching": false,
                "ordering": true,
                "info": true,
                "autoWidth": false
            });
            
            // Pastikan navbar sticky bekerja
            $(window).on('scroll', function() {
                var scrollTop = $(window).scrollTop();
                if (scrollTop > 0) {
                    $('.main-header .navbar').addClass('navbar-sticky-active');
                } else {
                    $('.main-header .navbar').removeClass('navbar-sticky-active');
                }
            });
            
            // Update navbar position saat sidebar toggle
            $(document).on('click', '.sidebar-toggle', function() {
                setTimeout(function() {
                    updateNavbarPosition();
                }, 350);
            });
            
            // Fungsi untuk update posisi navbar dan logo
            function updateNavbarPosition() {
                var navbar = $('.main-header .navbar');
                var logo = $('.main-header .logo');
                
                if ($('body').hasClass('sidebar-collapse')) {
                    navbar.css({
                        'left': '50px',
                        'width': 'calc(100% - 50px)'
                    });
                    logo.css({
                        'width': '50px'
                    });
                } else {
                    navbar.css({
                        'left': '230px',
                        'width': 'calc(100% - 230px)'
                    });
                    logo.css({
                        'width': '230px'
                    });
                }
            }
            
            // Update posisi saat pertama kali load
            setTimeout(function() {
                updateNavbarPosition();
            }, 100);
            
            // Pastikan sidebar bisa di-scroll dan menu logout terlihat
            function ensureSidebarScroll() {
                var sidebar = $('.main-sidebar');
                var sidebarContent = $('.sidebar');
                
                if (sidebar.length > 0 && sidebarContent.length > 0) {
                    // Pastikan overflow-y aktif
                    sidebar.css({
                        'overflow-y': 'auto',
                        'overflow-x': 'hidden'
                    });
                    
                    // Scroll ke bawah sedikit untuk memastikan scrollbar aktif
                    setTimeout(function() {
                        var scrollHeight = sidebarContent[0].scrollHeight;
                        var clientHeight = sidebar[0].clientHeight;
                        
                        if (scrollHeight > clientHeight) {
                            // Sidebar bisa di-scroll, pastikan scrollbar terlihat
                            sidebar.css('overflow-y', 'auto');
                        }
                    }, 200);
                }
            }
            
            // Pastikan sidebar scroll saat pertama kali load
            setTimeout(function() {
                ensureSidebarScroll();
            }, 300);
            
            // Pastikan sidebar scroll saat window resize
            $(window).on('resize', function() {
                ensureSidebarScroll();
            });
        });
        </script>

        <script>
        $(function() {
            //Initialize Select2 Elements
            $(".select2").select2();
            
            // Fungsi untuk inisialisasi treeview menu
            function initTreeviewMenu() {
                // Hapus event handler sebelumnya untuk menghindari duplikasi
                $(document).off('click.treeview', '.sidebar-menu .treeview > a');
                
                // Gunakan event delegation dengan namespace untuk memastikan click bekerja bahkan setelah scroll
                $(document).on('click.treeview', '.sidebar-menu .treeview > a', function(e) {
                    var $this = $(this);
                    var $parent = $this.parent('li');
                    var checkElement = $this.next('.treeview-menu');
                    
                    // Hanya proses jika ini adalah menu dengan submenu
                    if (checkElement.length > 0 && checkElement.hasClass('treeview-menu')) {
                        // Hentikan event default dan propagation
                        e.preventDefault();
                        e.stopPropagation();
                        e.stopImmediatePropagation();
                        
                        // Jika submenu sudah terbuka, tutup
                        if ($parent.hasClass('active') && checkElement.is(':visible')) {
                            checkElement.slideUp(300, function() {
                                $parent.removeClass('active');
                            });
                            // Rotate icon kembali
                            $this.find('.fa-angle-left, .fa-angle-down').removeClass('fa-angle-down').addClass('fa-angle-left').css('transform', 'rotate(0deg)');
                        } else {
                            // Tutup semua submenu lain
                            $('.sidebar-menu .treeview-menu:visible').not(checkElement).slideUp(300);
                            $('.sidebar-menu .treeview.active').not($parent).removeClass('active');
                            
                            // Reset icon semua treeview
                            $('.sidebar-menu .treeview > a .fa-angle-left').css('transform', 'rotate(0deg)');
                            
                            // Buka submenu ini
                            $parent.addClass('active');
                            checkElement.slideDown(300);
                            // Rotate icon
                            $this.find('.fa-angle-left').css('transform', 'rotate(-90deg)');
                        }
                        
                        return false;
                    }
                });
                
                // Pastikan submenu item juga bisa diklik dengan benar
                $(document).off('click.treeviewmenu', '.sidebar-menu .treeview-menu > li > a');
                $(document).on('click.treeviewmenu', '.sidebar-menu .treeview-menu > li > a', function(e) {
                    // Biarkan link bekerja normal, hanya pastikan event tidak terhenti oleh parent
                    e.stopPropagation();
                    $(this).css('pointer-events', 'auto');
                });
            }
            
            // Inisialisasi treeview menu dengan delay untuk memastikan DOM siap
            setTimeout(function() {
                // Pastikan treeview menu ter-inisialisasi dengan benar
                // Coba gunakan AdminLTE jika tersedia
                if (typeof $.AdminLTE !== 'undefined' && typeof $.AdminLTE.tree === 'function') {
                    try {
                        // Inisialisasi AdminLTE tree
                        $.AdminLTE.tree('.sidebar');
                    } catch(e) {
                        console.log('AdminLTE tree initialization error, using fallback:', e);
                    }
                }
                
                // Selalu gunakan inisialisasi manual sebagai backup dan untuk memastikan bekerja dengan scroll
                initTreeviewMenu();
                
                // Pastikan semua element menu bisa diklik
                $('.sidebar-menu .treeview > a').css({
                    'pointer-events': 'auto',
                    'cursor': 'pointer',
                    'user-select': 'none'
                });
                $('.sidebar-menu .treeview-menu > li > a').css({
                    'pointer-events': 'auto',
                    'cursor': 'pointer'
                });
            }, 200);
            
            // Pastikan inisialisasi ulang saat sidebar di-scroll (jika diperlukan)
            var scrollTimeout;
            $('.main-sidebar').off('scroll.treeview').on('scroll.treeview', function() {
                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(function() {
                    // Pastikan pointer events tetap aktif setelah scroll
                    $('.sidebar-menu .treeview > a').css('pointer-events', 'auto');
                    $('.sidebar-menu .treeview-menu > li > a').css('pointer-events', 'auto');
                }, 50);
            });
            
            // Pastikan inisialisasi ulang saat window resize
            $(window).off('resize.treeview').on('resize.treeview', function() {
                setTimeout(function() {
                    initTreeviewMenu();
                }, 300);
            });
        });
        
        
        
        // Fungsi konfirmasi logout
        function confirmLogout(event) {
            event.preventDefault();
            var url = event.currentTarget.getAttribute('href');
            
            Swal.fire({
                title: '<i class="fa fa-sign-out" style="color: #f39c12; font-size: 48px;"></i>',
                html: '<div style="text-align: center; padding: 10px;">' +
                      '<h3 style="color: #2c3e50; margin-bottom: 20px; font-weight: bold;">Konfirmasi Logout</h3>' +
                      '<p style="font-size: 16px; margin-bottom: 20px; color: #495057;">Anda yakin ingin keluar dari aplikasi?</p>' +
                      '<div style="background-color: #e3f2fd; border: 2px solid #2196F3; border-radius: 8px; padding: 15px; margin-top: 15px;">' +
                      '<p style="margin: 0; color: #1565C0; font-size: 14px; font-weight: bold;">' +
                      '<i class="fa fa-info-circle" style="margin-right: 8px;"></i>' +
                      'Anda akan diarahkan ke halaman login setelah logout.</p>' +
                      '</div>' +
                      '</div>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fa fa-sign-out"></i> Ya, Logout',
                cancelButtonText: '<i class="fa fa-times"></i> Batal',
                reverseButtons: true,
                focusCancel: true,
                allowOutsideClick: false,
                allowEscapeKey: true,
                width: '500px'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
            
            return false;
        }
        </script>
</body>

</html>