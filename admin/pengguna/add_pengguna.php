<section class="content-header">
	<h1>
		Pengguna Sistem
	</h1>
	<ol class="breadcrumb">
		<li>
			<a href="index.php">
				<i class="fa fa-home"></i>
				<b>eTABS</b>
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
					<h3 class="box-title">Tambah Pengguna</h3>
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
							<label for="exampleInputEmail1">Nama Pengguna</label>
							<input type="text" name="nama_pengguna" id="nama_pengguna" class="form-control" placeholder="Nama pengguna">
						</div>

						<div class="form-group">
							<label for="exampleInputEmail1">Username</label>
							<input type="text" name="username" id="username" class="form-control" placeholder="Username">
						</div>

						<div class="form-group">
							<label for="exampleInputPassword1">Password</label>
							<input type="password" name="password" id="password" class="form-control" placeholder="Password">
						</div>

						<div class="form-group">
							<label>Level</label>
							<select name="level" id="level" class="form-control">
								<option>-- Pilih Level --</option>
								<option>Administrator</option>
								<option>Petugas</option>
							</select>
						</div>

					</div>
					<!-- /.box-body -->

					<div class="box-footer">
						<input type="submit" name="Simpan" value="Simpan" class="btn btn-info">
						<a href="?page=MyApp/data_pengguna" title="Kembali" class="btn btn-warning">Batal</a>
					</div>
				</form>
			</div>
			<!-- /.box -->
</section>

<?php

    if (isset ($_POST['Simpan'])){
    //mulai proses simpan data
        $sql_simpan = "INSERT INTO tb_pengguna (nama_pengguna,username,password,level) VALUES (
        '".$_POST['nama_pengguna']."',
        '".$_POST['username']."',
        '".$_POST['password']."',
        '".$_POST['level']."')";
        $query_simpan = mysqli_query($koneksi, $sql_simpan);
    if ($query_simpan) {
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
          logActivity($koneksi, 'CREATE', 'tb_pengguna', 'Menambah pengguna: ' . $_POST['nama_pengguna'] . ' (Username: ' . $_POST['username'] . ', Level: ' . $_POST['level'] . ')', $_POST['id_pengguna'] ?? null);
      }
      
      echo "<script>
      (function(){
          if(typeof Swal!=='undefined'){
              Swal.fire({
                  title:'Berhasil!',
                  text:'Data pengguna berhasil ditambahkan',
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
              alert('Data pengguna berhasil ditambahkan');
              window.location.href='index.php?page=MyApp/data_pengguna';
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
                  text:'Data pengguna gagal ditambahkan',
                  icon:'error',
                  confirmButtonText:'OK',
                  confirmButtonColor:'#dc3545',
                  allowOutsideClick:false,
                  allowEscapeKey:false
              }).then(function(){
                  window.location.href='index.php?page=MyApp/add_pengguna';
              });
          }else{
              alert('Data pengguna gagal ditambahkan');
              window.location.href='index.php?page=MyApp/add_pengguna';
          }
      })();
      </script>";
      return;
    }
     //selesai proses simpan data
}
    
