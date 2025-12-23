<?php
if(isset($_POST['id_tabungan']) && is_array($_POST['id_tabungan']) && count($_POST['id_tabungan']) > 0){
    $ids = $_POST['id_tabungan'];
    
    $tariks = $_POST['tarik'];
    $tgls = $_POST['tgl'];
    
    $jumlah_update = 0;
    $jumlah_error = 0;
    
    // Update setiap data satu per satu berdasarkan index array
    for ($i = 0; $i < count($ids); $i++) {
        if (isset($ids[$i]) && isset($tariks[$i]) && isset($tgls[$i])) {
            $id = mysqli_real_escape_string($koneksi, $ids[$i]);
            $tarik_baru = preg_replace("/[^0-9]/", "", $tariks[$i]);
            $tgl_baru = mysqli_real_escape_string($koneksi, $tgls[$i]);
            
            $sql_update = "UPDATE tb_tabungan 
                           SET tarik = '".mysqli_real_escape_string($koneksi, $tarik_baru)."',
                               tgl = '".$tgl_baru."'
                           WHERE id_tabungan = '".$id."' AND jenis = 'TR'";
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
            logActivity($koneksi, 'UPDATE', 'tb_tabungan', 'Mengubah ' . $jumlah_update . ' penarikan');
        }
        
        echo '<section class="content"><div class="row"><div class="col-md-12"><div class="box"><div class="box-body"><p>Memproses...</p></div></div></div></div></section>';
        echo "<script>
        (function(){
            Swal.fire({
                title:'Berhasil!',
                text:'Berhasil mengubah " . $jumlah_update . " penarikan',
                icon:'success',
                timer: 2000,
                timerProgressBar: true,
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then(function(){
                window.location.href='index.php?page=data_tarik';
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
                text:'Gagal mengubah penarikan',
                icon:'error',
                confirmButtonText:'OK',
                confirmButtonColor:'#dc3545',
                allowOutsideClick:false,
                allowEscapeKey:false
            }).then(function(){
                window.location.href='index.php?page=data_tarik';
            });
        })();
        </script>";
        return;
    }
} else {
    echo '<section class="content"><div class="row"><div class="col-md-12"><div class="box"><div class="box-body"><p>Memproses...</p></div></div></div></div></section>';
    echo "<script>window.location.href='index.php?page=data_tarik';</script>";
    return;
}

?>

