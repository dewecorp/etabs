<?php
if(isset($_GET['kode'])){
    // Ambil data siswa sebelum dihapus untuk log
    $sql_get = "SELECT nama_siswa FROM tb_siswa WHERE nis='".mysqli_real_escape_string($koneksi, $_GET['kode'])."'";
    $query_get = mysqli_query($koneksi, $sql_get);
    $data_siswa = mysqli_fetch_assoc($query_get);
    $nama_siswa = $data_siswa ? $data_siswa['nama_siswa'] : 'NIS: ' . $_GET['kode'];
    
    $sql_hapus = "DELETE FROM tb_siswa WHERE nis='".mysqli_real_escape_string($koneksi, $_GET['kode'])."'";
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
            logActivity($koneksi, 'DELETE', 'tb_siswa', 'Menghapus data siswa: ' . $nama_siswa . ' (NIS: ' . $_GET['kode'] . ')', $_GET['kode']);
        }
        
        // Tambahkan konten HTML minimal agar halaman tidak blank
        echo '<section class="content"><div class="row"><div class="col-md-12"><div class="box"><div class="box-body"><p>Memproses...</p></div></div></div></div></section>';
        echo "<script>
        (function(){
            function showAlert(){
                if(typeof Swal!=='undefined'){
                    Swal.fire({
                        title:'Berhasil!',
                        text:'Data siswa berhasil dihapus',
                        icon:'success',
                        confirmButtonText:'OK',
                        confirmButtonColor:'#28a745',
                        allowOutsideClick:false,
                        allowEscapeKey:false,
                        timer:2500,
                        timerProgressBar:true
                    }).then(function(){
                        window.location.href='index.php?page=MyApp/data_siswa';
                    });
                    
                    // Auto redirect setelah 2.5 detik jika tidak diklik
                    setTimeout(function(){
                        window.location.href='index.php?page=MyApp/data_siswa';
                    }, 2500);
                }else{
                    alert('Data siswa berhasil dihapus');
                    window.location.href='index.php?page=MyApp/data_siswa';
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
    } else {
        echo '<section class="content"><div class="row"><div class="col-md-12"><div class="box"><div class="box-body"><p>Memproses...</p></div></div></div></div></section>';
        echo "<script>
        (function(){
            function showAlert(){
                if(typeof Swal!=='undefined'){
                    Swal.fire({
                        title:'Gagal!',
                        text:'Data siswa gagal dihapus',
                        icon:'error',
                        confirmButtonText:'OK',
                        confirmButtonColor:'#dc3545',
                        allowOutsideClick:false,
                        allowEscapeKey:false
                    }).then(function(){
                        window.location.href='index.php?page=MyApp/data_siswa';
                    });
                }else{
                    alert('Data siswa gagal dihapus');
                    window.location.href='index.php?page=MyApp/data_siswa';
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
    echo "<script>window.location.href='index.php?page=MyApp/data_siswa';</script>";
    return;
}
