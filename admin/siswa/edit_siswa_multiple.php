<?php
if(isset($_POST['nis']) && is_array($_POST['nis']) && 
   isset($_POST['nama_siswa']) && is_array($_POST['nama_siswa']) && 
   isset($_POST['jekel']) && is_array($_POST['jekel']) && 
   isset($_POST['id_kelas']) && is_array($_POST['id_kelas']) && 
   isset($_POST['th_masuk']) && is_array($_POST['th_masuk']) && 
   isset($_POST['status']) && is_array($_POST['status']) && 
   count($_POST['nis']) > 0){
    
    $nis_array = $_POST['nis'];
    $nama_siswa_array = $_POST['nama_siswa'];
    $jekel_array = $_POST['jekel'];
    $id_kelas_array = $_POST['id_kelas'];
    $th_masuk_array = $_POST['th_masuk'];
    $status_array = $_POST['status'];
    
    $jumlah_update = 0;
    $jumlah_error = 0;
    
    // Update setiap data satu per satu berdasarkan index array
    for ($i = 0; $i < count($nis_array); $i++) {
        if (isset($nis_array[$i]) && isset($nama_siswa_array[$i]) && isset($jekel_array[$i]) && 
            isset($id_kelas_array[$i]) && isset($th_masuk_array[$i]) && isset($status_array[$i])) {
            $nis = mysqli_real_escape_string($koneksi, $nis_array[$i]);
            $nama_siswa = mysqli_real_escape_string($koneksi, $nama_siswa_array[$i]);
            $jekel = mysqli_real_escape_string($koneksi, $jekel_array[$i]);
            $id_kelas = mysqli_real_escape_string($koneksi, $id_kelas_array[$i]);
            $th_masuk = mysqli_real_escape_string($koneksi, $th_masuk_array[$i]);
            $status = mysqli_real_escape_string($koneksi, $status_array[$i]);
            
            $sql_update = "UPDATE tb_siswa 
                           SET nama_siswa = '".$nama_siswa."',
                               jekel = '".$jekel."',
                               id_kelas = '".$id_kelas."',
                               th_masuk = '".$th_masuk."',
                               status = '".$status."'
                           WHERE nis = '".$nis."'";
            if (mysqli_query($koneksi, $sql_update)) {
                $jumlah_update++;
            } else {
                $jumlah_error++;
            }
        }
    }
    
    if ($jumlah_update > 0) {
        // Log aktivitas
        if (!function_exists('logActivity')) {
            $paths = [
                dirname(dirname(__DIR__)) . '/inc/activity_log.php',
                __DIR__ . '/../../inc/activity_log.php',
                'inc/activity_log.php',
                '../../inc/activity_log.php'
            ];
            foreach ($paths as $path) {
                if (file_exists($path)) {
                    include_once $path;
                    break;
                }
            }
        }
        if (function_exists('logActivity')) {
            logActivity($koneksi, 'UPDATE', 'tb_siswa', 'Mengubah ' . $jumlah_update . ' data siswa');
        }
        
        echo '<section class="content"><div class="row"><div class="col-md-12"><div class="box"><div class="box-body"><p>Memproses...</p></div></div></div></div></section>';
        echo "<script>
        (function(){
            Swal.fire({
                title:'Berhasil!',
                text:'Berhasil mengubah " . $jumlah_update . " data siswa',
                icon:'success',
                timer: 2000,
                timerProgressBar: true,
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then(function(){
                window.location.href='index.php?page=MyApp/data_siswa';
            });
        })();
        </script>";
        return;
    } else {
        echo '<section class="content"><div class="row"><div class="col-md-12"><div class="box"><div class="box-body"><p>Memproses...</p></div></div></div></div></section>';
        echo "<script>
        (function(){
            Swal.fire({
                title:'Gagal!',
                text:'Gagal mengubah data siswa',
                icon:'error',
                confirmButtonText:'OK',
                confirmButtonColor:'#dc3545',
                allowOutsideClick:false,
                allowEscapeKey:false
            }).then(function(){
                window.location.href='index.php?page=MyApp/data_siswa';
            });
        })();
        </script>";
        return;
    }
} else {
    echo '<section class="content"><div class="row"><div class="col-md-12"><div class="box"><div class="box-body"><p>Memproses...</p></div></div></div></div></section>';
    echo "<script>window.location.href='index.php?page=MyApp/data_siswa';</script>";
    return;
}

?>

