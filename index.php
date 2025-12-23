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
    //FUNGSI RUPIAH
include "inc/rupiah.php";
include "inc/config.php";
include_once "inc/activity_log.php";
	//Profil Sekolah
$nama = "e-TABS";
$sql = $koneksi->query("SELECT * from tb_profil");
while ($data = $sql->fetch_assoc()) {
	$nama = $data['nama_sekolah'];
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>e-TABS | <?=$nama?></title>
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
        });
        </script>

        <script>
        $(function() {
            //Initialize Select2 Elements
            $(".select2").select2();
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