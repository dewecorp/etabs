<?php

    if(isset($_GET['kode'])){
        $sql_cek = "SELECT * FROM tb_kelas WHERE id_kelas='".$_GET['kode']."'";
        $query_cek = mysqli_query($koneksi, $sql_cek);
        $data_cek = mysqli_fetch_array($query_cek,MYSQLI_BOTH);
    }
?>

<section class="content-header">
    <h1>
        Master Data
        <small>Kelas</small>
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="index.php">
                <i class="fa fa-home"></i>
                <b>e-TABS</b>
            </a>
        </li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- general form elements -->
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">Ubah kelas</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-box-tool" data-widget="remove">
                            <i class="fa fa-remove"></i>
                        </button>
                    </div>
                </div>
                <!-- /.box-header -->
                <!-- form start -->
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="box-body">

                        <div class="form-group">
                            <label>ID Kelas</label>
                            <input type='text' class="form-control" name="id_kelas" value="<?php echo $data_cek['id_kelas']; ?>"
                                readonly>
                        </div>

                        <div class="form-group">
                            <label>Kelas</label>
                            <input class="form-control" name="kelas"
                                value="<?php echo $data_cek['kelas']; ?>" />
                        </div>

                    </div>
                    <!-- /.box-body -->

                    <div class="box-footer">
                        <input type="submit" name="Ubah" value="Ubah" class="btn btn-success">
                        <a href="?page=MyApp/data_kelas" class="btn btn-warning">Batal</a>
                    </div>
                </form>
            </div>
            <!-- /.box -->
</section>

<?php

if (isset ($_POST['Ubah'])){
    //mulai proses ubah
    $sql_ubah = "UPDATE tb_kelas SET
        kelas='".$_POST['kelas']."'
        WHERE id_kelas='".$_POST['id_kelas']."'";
    $query_ubah = mysqli_query($koneksi, $sql_ubah);

    if ($query_ubah) {
        // Log aktivitas
        if (!function_exists('logActivity')) {
            $paths = [
                __DIR__ . '/../../inc/activity_log.php',
                dirname(dirname(__DIR__)) . '/inc/activity_log.php',
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
            logActivity($koneksi, 'UPDATE', 'tb_kelas', 'Mengubah kelas: ' . $_POST['kelas'], $_POST['id_kelas'] ?? null);
        }
        
        echo "<script>
        (function(){
            if(typeof Swal!=='undefined'){
                Swal.fire({
                    title:'Berhasil!',
                    text:'Data kelas berhasil diubah',
                    icon:'success',
                    confirmButtonText:'OK',
                    confirmButtonColor:'#28a745',
                    allowOutsideClick:false,
                    allowEscapeKey:false,
                    timer:2500,
                    timerProgressBar:true
                }).then(function(){
                    window.location.href='index.php?page=MyApp/data_kelas';
                });
                
                // Auto redirect setelah 2.5 detik jika tidak diklik
                setTimeout(function(){
                    window.location.href='index.php?page=MyApp/data_kelas';
                }, 2500);
            }else{
                alert('Data kelas berhasil diubah');
                window.location.href='index.php?page=MyApp/data_kelas';
            }
        })();
        </script>";
        return;
        }else{
        echo "<script>
        (function(){
            if(typeof Swal!=='undefined'){
                Swal.fire({
                    title:'Gagal!',
                    text:'Data kelas gagal diubah',
                    icon:'error',
                    confirmButtonText:'OK',
                    confirmButtonColor:'#dc3545',
                    allowOutsideClick:false,
                    allowEscapeKey:false
                }).then(function(){
                    window.location.href='index.php?page=MyApp/data_kelas';
                });
            }else{
                alert('Data kelas gagal diubah');
                window.location.href='index.php?page=MyApp/data_kelas';
            }
        })();
        </script>";
        return;
    }
}
