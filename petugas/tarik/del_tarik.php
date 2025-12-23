<?php
if(isset($_GET['kode'])){
    // Ambil data penarikan lengkap sebelum dihapus untuk log dan riwayat
    $sql_get = "SELECT t.*, s.nama_siswa FROM tb_tabungan t 
                JOIN tb_siswa s ON t.nis = s.nis 
                WHERE t.id_tabungan='".mysqli_real_escape_string($koneksi, $_GET['kode'])."' AND t.jenis='TR'";
    $query_get = mysqli_query($koneksi, $sql_get);
    $data_tarik = mysqli_fetch_assoc($query_get);
    
    if (!$data_tarik) {
        echo '<section class="content"><div class="row"><div class="col-md-12"><div class="box"><div class="box-body"><p>Memproses...</p></div></div></div></div></section>';
        echo "<script>window.location.href='index.php?page=data_tarik';</script>";
        return;
    }
    
    $desc = 'Menghapus penarikan untuk ' . $data_tarik['nama_siswa'] . ' sebesar Rp ' . number_format($data_tarik['tarik'], 0, ',', '.');
    
    // Update status di riwayat menjadi Tidak Aktif
    $data_nama = $_SESSION["ses_nama"];
    $tgl_hapus = date('Y-m-d H:i:s');
    $sql_update_riwayat = "UPDATE tb_riwayat 
                           SET status = 'Tidak Aktif', 
                               tgl_hapus = '".$tgl_hapus."', 
                               petugas_hapus = '".mysqli_real_escape_string($koneksi, $data_nama)."'
                           WHERE id_tabungan_asli = '".mysqli_real_escape_string($koneksi, $data_tarik['id_tabungan'])."' 
                           AND jenis = 'TR'";
    mysqli_query($koneksi, $sql_update_riwayat);
    
    // Hapus dari tabel asli
    $sql_hapus = "DELETE FROM tb_tabungan WHERE id_tabungan='".mysqli_real_escape_string($koneksi, $_GET['kode'])."'";
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
            logActivity($koneksi, 'DELETE', 'tb_tabungan', $desc);
        }
        
        echo '<section class="content"><div class="row"><div class="col-md-12"><div class="box"><div class="box-body"><p>Memproses...</p></div></div></div></div></section>';
        echo "<script>
        (function(){
            Swal.fire({
                title:'Berhasil!',
                text:'Penarikan berhasil dihapus',
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
            function showAlert(){
                if(typeof Swal!=='undefined'){
                    Swal.fire({
                        title:'Gagal!',
                        text:'Penarikan gagal dihapus',
                        icon:'error',
                        confirmButtonText:'OK',
                        confirmButtonColor:'#dc3545',
                        allowOutsideClick:false,
                        allowEscapeKey:false
                    }).then(function(){
                        window.location.href='index.php?page=data_tarik';
                    });
                }else{
                    alert('Penarikan gagal dihapus');
                    window.location.href='index.php?page=data_tarik';
                }
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
    echo "<script>window.location.href='index.php?page=data_tarik';</script>";
    return;
}

?>
