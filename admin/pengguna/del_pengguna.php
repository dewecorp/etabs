<?php
if(isset($_GET['kode'])){
    // Ambil data pengguna sebelum dihapus untuk log
    $sql_get = "SELECT nama_pengguna, username, level FROM tb_pengguna WHERE id_pengguna='".mysqli_real_escape_string($koneksi, $_GET['kode'])."'";
    $query_get = mysqli_query($koneksi, $sql_get);
    $data_pengguna = mysqli_fetch_assoc($query_get);
    
    // Cek jika pengguna adalah Administrator, jangan izinkan penghapusan
    if ($data_pengguna && $data_pengguna['level'] == 'Administrator') {
        echo '<section class="content"><div class="row"><div class="col-md-12"><div class="box"><div class="box-body"><p>Memproses...</p></div></div></div></div></section>';
        echo "<script>
        (function(){
            function showAlert(){
                if(typeof Swal!=='undefined'){
                    Swal.fire({
                        title:'Dilarang!',
                        text:'Akun Administrator tidak dapat dihapus',
                        icon:'error',
                        confirmButtonText:'OK',
                        confirmButtonColor:'#dc3545',
                        allowOutsideClick:false,
                        allowEscapeKey:false
                    }).then(function(){
                        window.location.href='index.php?page=MyApp/data_pengguna';
                    });
                }else{
                    alert('Akun Administrator tidak dapat dihapus');
                    window.location.href='index.php?page=MyApp/data_pengguna';
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
    
    $nama_pengguna = $data_pengguna ? $data_pengguna['nama_pengguna'] . ' (Username: ' . $data_pengguna['username'] . ')' : 'ID: ' . $_GET['kode'];
    
    $sql_hapus = "DELETE FROM tb_pengguna WHERE id_pengguna='".mysqli_real_escape_string($koneksi, $_GET['kode'])."'";
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
            logActivity($koneksi, 'DELETE', 'tb_pengguna', 'Menghapus pengguna: ' . $nama_pengguna, $_GET['kode'] ?? null);
        }
        
        echo '<section class="content"><div class="row"><div class="col-md-12"><div class="box"><div class="box-body"><p>Memproses...</p></div></div></div></div></section>';
        echo "<script>
        (function(){
            function showAlert(){
                if(typeof Swal!=='undefined'){
                    Swal.fire({
                        title:'Berhasil!',
                        text:'Data pengguna berhasil dihapus',
                        icon:'success',
                        confirmButtonText:'OK',
                        confirmButtonColor:'#28a745',
                        allowOutsideClick:false,
                        allowEscapeKey:false,
                        timer:2500,
                        timerProgressBar:true
                    }).then(function(){
                        window.location.href='index.php?page=MyApp/data_pengguna';
                    });
                    
                    // Auto redirect setelah 2.5 detik jika tidak diklik
                    setTimeout(function(){
                        window.location.href='index.php?page=MyApp/data_pengguna';
                    }, 2500);
                }else{
                    alert('Data pengguna berhasil dihapus');
                    window.location.href='index.php?page=MyApp/data_pengguna';
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
                        text:'Data pengguna gagal dihapus',
                        icon:'error',
                        confirmButtonText:'OK',
                        confirmButtonColor:'#dc3545',
                        allowOutsideClick:false,
                        allowEscapeKey:false
                    }).then(function(){
                        window.location.href='index.php?page=MyApp/data_pengguna';
                    });
                }else{
                    alert('Data pengguna gagal dihapus');
                    window.location.href='index.php?page=MyApp/data_pengguna';
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
    echo "<script>window.location.href='index.php?page=MyApp/data_pengguna';</script>";
    return;
}
