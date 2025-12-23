<?php
if(isset($_POST['id_riwayat']) && is_array($_POST['id_riwayat']) && count($_POST['id_riwayat']) > 0){
    $data_nama = $_SESSION["ses_nama"];
    $ids = $_POST['id_riwayat'];
    $ids_escaped = array_map(function($id) use ($koneksi) {
        return "'" . mysqli_real_escape_string($koneksi, $id) . "'";
    }, $ids);
    $ids_string = implode(',', $ids_escaped);
    
    // Ambil data riwayat yang akan dihapus untuk log
    $sql_get = "SELECT id_riwayat, nis, jenis, setor, tarik FROM tb_riwayat WHERE id_riwayat IN ($ids_string)";
    $query_get = mysqli_query($koneksi, $sql_get);
    $jumlah_hapus = mysqli_num_rows($query_get);
    
    // Hapus riwayat terpilih
    $sql_hapus = "DELETE FROM tb_riwayat WHERE id_riwayat IN ($ids_string)";
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
            logActivity($koneksi, 'DELETE', 'tb_riwayat', 'Menghapus ' . $jumlah_hapus . ' riwayat transaksi terpilih');
        }
        
        echo '<section class="content"><div class="row"><div class="col-md-12"><div class="box"><div class="box-body"><p>Memproses...</p></div></div></div></div></section>';
        echo "<script>
        (function(){
            Swal.fire({
                title:'Berhasil!',
                text:'Berhasil menghapus " . $jumlah_hapus . " riwayat transaksi',
                icon:'success',
                timer: 2000,
                timerProgressBar: true,
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then(function(){
                window.location.href='index.php?page=data_riwayat';
            });
        })();
        </script>";
        return;
    } else {
        echo '<section class="content"><div class="row"><div class="col-md-12"><div class="box"><div class="box-body"><p>Memproses...</p></div></div></div></div></section>';
        echo "<script>
        (function(){
            function showAlert(){
                Swal.fire({
                    title:'Gagal!',
                    text:'Gagal menghapus riwayat transaksi',
                    icon:'error',
                    confirmButtonText:'OK',
                    confirmButtonColor:'#dc3545',
                    allowOutsideClick:false,
                    allowEscapeKey:false
                }).then(function(){
                    window.location.href='index.php?page=data_riwayat';
                });
            }
            if(document.readyState==='complete'||document.readyState==='interactive'){
                setTimeout(showAlert,100);
            }else{
                window.addEventListener('load',function(){setTimeout(showAlert,100);});
            }
        })();
        </script>";
        return;
    }
} else {
    echo '<section class="content"><div class="row"><div class="col-md-12"><div class="box"><div class="box-body"><p>Memproses...</p></div></div></div></div></section>';
    echo "<script>window.location.href='index.php?page=data_riwayat';</script>";
    return;
}

?>

