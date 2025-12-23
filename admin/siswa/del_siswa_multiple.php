<?php
if(isset($_POST['nis']) && is_array($_POST['nis']) && count($_POST['nis']) > 0){
    $nis_array = $_POST['nis'];
    $nis_escaped = array_map(function($nis) use ($koneksi) {
        return "'" . mysqli_real_escape_string($koneksi, $nis) . "'";
    }, $nis_array);
    $nis_string = implode(',', $nis_escaped);
    
    // Ambil data siswa yang akan dihapus untuk log
    $sql_get = "SELECT nis, nama_siswa FROM tb_siswa WHERE nis IN ($nis_string)";
    $query_get = mysqli_query($koneksi, $sql_get);
    $jumlah_hapus = mysqli_num_rows($query_get);
    
    // Hapus dari tabel
    $sql_hapus = "DELETE FROM tb_siswa WHERE nis IN ($nis_string)";
    $query_hapus = mysqli_query($koneksi, $sql_hapus);

    if ($query_hapus) {
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
            logActivity($koneksi, 'DELETE', 'tb_siswa', 'Menghapus ' . $jumlah_hapus . ' data siswa terpilih');
        }
        
        echo '<section class="content"><div class="row"><div class="col-md-12"><div class="box"><div class="box-body"><p>Memproses...</p></div></div></div></div></section>';
        echo "<script>
        (function(){
            Swal.fire({
                title:'Berhasil!',
                text:'Berhasil menghapus " . $jumlah_hapus . " data siswa',
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
                text:'Gagal menghapus data siswa',
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

