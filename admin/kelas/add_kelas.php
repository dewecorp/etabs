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
			<div class="box box-info">
				<div class="box-header with-border">
					<h3 class="box-title">Tambah Kelas</h3>
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
							<label>Kelas</label>
							<input type="text" name="kelas" id="kelas" class="form-control" placeholder="Kelas">
						</div>

					</div>
					<!-- /.box-body -->

					<div class="box-footer">
						<input type="submit" name="Simpan" value="Simpan" class="btn btn-info">
						<a href="?page=MyApp/data_kelas" class="btn btn-warning">Batal</a>
					</div>
				</form>
			</div>
			<!-- /.box -->
</section>

<?php

    if (isset ($_POST['Simpan'])){
    
        $sql_simpan = "INSERT INTO tb_kelas (kelas) VALUES ('".$_POST['kelas']."')";
		$query_simpan = mysqli_query($koneksi, $sql_simpan);
		
    if ($query_simpan){
      // Log aktivitas - karena file di-include dari index.php, fungsi sudah tersedia
      // Tapi untuk memastikan, kita include lagi dengan path yang benar
      if (!function_exists('logActivity')) {
          // Coba beberapa path yang mungkin
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
          logActivity($koneksi, 'CREATE', 'tb_kelas', 'Menambah kelas: ' . $_POST['kelas'], $_POST['id_kelas'] ?? null);
      }
      mysqli_close($koneksi);

      echo "<script>
      (function(){
          if(typeof Swal!=='undefined'){
              Swal.fire({
                  title:'Berhasil!',
                  text:'Data kelas berhasil ditambahkan',
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
              alert('Data kelas berhasil ditambahkan');
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
                  text:'Data kelas gagal ditambahkan',
                  icon:'error',
                  confirmButtonText:'OK',
                  confirmButtonColor:'#dc3545',
                  allowOutsideClick:false,
                  allowEscapeKey:false
              }).then(function(){
                  window.location.href='index.php?page=MyApp/add_kelas';
              });
          }else{
              alert('Data kelas gagal ditambahkan');
              window.location.href='index.php?page=MyApp/add_kelas';
          }
      })();
      </script>";
      return;
    }
  }
    
