<?php
if(isset($_POST['id_tabungan']) && is_array($_POST['id_tabungan']) && count($_POST['id_tabungan']) > 0){
    $data_nama = $_SESSION["ses_nama"];
    $ids = $_POST['id_tabungan'];
    $ids_escaped = array_map(function($id) use ($koneksi) {
        return "'" . mysqli_real_escape_string($koneksi, $id) . "'";
    }, $ids);
    $ids_string = implode(',', $ids_escaped);
    
    // Ambil data penarikan yang akan dihapus
    $sql_get = "SELECT t.*, s.nama_siswa FROM tb_tabungan t 
                JOIN tb_siswa s ON t.nis = s.nis 
                WHERE t.id_tabungan IN ($ids_string) AND t.jenis='TR'";
    $query_get = mysqli_query($koneksi, $sql_get);
    $jumlah_hapus = mysqli_num_rows($query_get);
    
    $tgl_hapus = date('Y-m-d H:i:s');
    
    // Update status di riwayat menjadi Tidak Aktif untuk semua data terpilih
    $sql_update_riwayat = "UPDATE tb_riwayat 
                           SET status = 'Tidak Aktif', 
                               tgl_hapus = '".$tgl_hapus."', 
                               petugas_hapus = '".mysqli_real_escape_string($koneksi, $data_nama)."'
                           WHERE id_tabungan_asli IN ($ids_string) AND jenis = 'TR'";
    mysqli_query($koneksi, $sql_update_riwayat);
    
    // Hapus dari tabel asli
    $sql_hapus = "DELETE FROM tb_tabungan WHERE id_tabungan IN ($ids_string) AND jenis = 'TR'";
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
            logActivity($koneksi, 'DELETE', 'tb_tabungan', 'Menghapus ' . $jumlah_hapus . ' penarikan terpilih');
        }
        
        echo '<section class="content"><div class="row"><div class="col-md-12"><div class="box"><div class="box-body"><p>Memproses...</p></div></div></div></div></section>';
        echo "<script>
        (function(){
            Swal.fire({
                title:'Berhasil!',
                text:'Berhasil menghapus " . $jumlah_hapus . " penarikan',
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
                text:'Gagal menghapus penarikan',
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

