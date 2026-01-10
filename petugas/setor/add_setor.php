<!-- Content Header (Page header) -->

<?php 
$data_nama = $_SESSION["ses_nama"];

date_default_timezone_set("Asia/Jakarta"); 
$tanggal = date("Y-m-d");
?>

<section class="content-header">
	<h1>
		Transaksi
		<small>Setoran</small>
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
					<h3 class="box-title">Tambah Setoran</h3>
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
							<label>Siswa</label>
							<select name="nis" id="nis" class="form-control select2" style="width: 100%;">
								<option selected="selected">-- Pilih --</option>
								<?php
                        // ambil data dari database
                        $query = "select * from tb_siswa where status='Aktif'";
                        $hasil = mysqli_query($koneksi, $query);
                        while ($row = mysqli_fetch_array($hasil)) {
                        ?>
								<option value="<?php echo $row['nis'] ?>">
									<?php echo $row['nis'] ?>
									-
									<?php echo $row['nama_siswa'] ?>
								</option>
								<?php
                        }
                        ?>
							</select>
						</div>

						<div class="form-group">
							<label>Saldo Tabungan</label>
							<input type="text" name="saldo" id="saldo" class="form-control" placeholder="Saldo" readonly>
						</div>

						<div class="form-group">
							<label>Setoran</label>
							<input type="text" name="setor" id="setor" class="form-control" placeholder="Jumlah setoran" required>
						</div>


					</div>
					<!-- /.box-body -->

					<div class="box-footer">
						<input type="submit" name="Simpan" value="Setor" class="btn btn-primary">
						<a href="?page=data_setor" class="btn btn-warning">Batal</a>
					</div>
				</form>
			</div>
			<!-- /.box -->
</section>

<?php

    if (isset ($_POST['Simpan'])){

		//menangkap post setor
		$setor=$_POST['setor'];
		//membuang Rp dan Titik
		$setor_hasil=preg_replace("/[^0-9]/", "", $setor);

        $sql_simpan = "INSERT INTO tb_tabungan (nis,setor,tarik,tgl,jenis,petugas) VALUES (
          '".$_POST['nis']."',
          '".$setor_hasil."',
          '0',
          '".$tanggal."',
          'ST',
          '".$data_nama."')";
		$query_simpan = mysqli_query($koneksi, $sql_simpan);
		
		// Ambil ID yang baru saja diinsert
		$id_tabungan_baru = mysqli_insert_id($koneksi);

    if ($query_simpan && $id_tabungan_baru) {
		// Simpan ke riwayat saat transaksi dibuat (status Aktif karena masih ada di tb_tabungan)
		$sql_riwayat = "INSERT INTO tb_riwayat (id_tabungan_asli, nis, setor, tarik, tgl, jenis, petugas, status) 
						VALUES (
							'".$id_tabungan_baru."',
							'".mysqli_real_escape_string($koneksi, $_POST['nis'])."',
							'".$setor_hasil."',
							'0',
							'".$tanggal."',
							'ST',
							'".mysqli_real_escape_string($koneksi, $data_nama)."',
							'Aktif'
						)";
		mysqli_query($koneksi, $sql_riwayat);
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
            $sql_siswa = "SELECT nama_siswa FROM tb_siswa WHERE nis='".$_POST['nis']."'";
            $query_siswa = mysqli_query($koneksi, $sql_siswa);
            $data_siswa = mysqli_fetch_assoc($query_siswa);
            $nama_siswa = $data_siswa ? $data_siswa['nama_siswa'] : 'NIS: ' . $_POST['nis'];
            logActivity($koneksi, 'CREATE', 'tb_tabungan', 'Menambah setoran untuk ' . $nama_siswa . ' sebesar Rp ' . number_format($setor_hasil, 0, ',', '.'), $_POST['nis']);
        }
        mysqli_close($koneksi);
        
        echo "<script>
        (function(){
            if(typeof Swal!=='undefined'){
                Swal.fire({
                    title:'Berhasil!',
                    text:'Setoran berhasil ditambahkan',
                    icon:'success',
                    confirmButtonText:'OK',
                    confirmButtonColor:'#28a745',
                    allowOutsideClick:false,
                    allowEscapeKey:false,
                    timer:2500,
                    timerProgressBar:true
                }).then(function(){
                    window.location.href='index.php?page=data_setor';
                });
                
                // Auto redirect setelah 2.5 detik jika tidak diklik
                setTimeout(function(){
                    window.location.href='index.php?page=data_setor';
                }, 2500);
            }else{
                alert('Setoran berhasil ditambahkan');
                window.location.href='index.php?page=data_setor';
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
                    text:'Setoran gagal ditambahkan',
                    icon:'error',
                    confirmButtonText:'OK',
                    confirmButtonColor:'#dc3545',
                    allowOutsideClick:false,
                    allowEscapeKey:false
                }).then(function(){
                    window.location.href='index.php?page=add_setor';
                });
            }else{
                alert('Setoran gagal ditambahkan');
                window.location.href='index.php?page=add_setor';
            }
        })();
        </script>";
        return;
    }
  }
    ?>

<script src="././bootstrap/lookup.js"></script>  
<script>
    $(document).ready(function(){  
        $('#nis').change(function(){  
            var nis = $(this).val();  
            $.ajax({  
                url:"plugins/proses-ajax.php",  
                method:"POST",  
                data:{nis:nis},  
                success:function(data){  
                    $('#saldo').val(data);  
                }  
            });  
        });  
    }); 
</script>

<script type="text/javascript">
	var setor = document.getElementById('setor');
	setor.addEventListener('keyup', function (e) {
		// tambahkan 'Rp.' pada saat form di ketik
		// gunakan fungsi formatsetor() untuk mengubah angka yang di ketik menjadi format angka
		setor.value = formatsetor(this.value, 'Rp ');
	});

	/* Fungsi formatsetor */
	function formatsetor(angka, prefix) {
		var number_string = angka.replace(/[^,\d]/g, '').toString(),
			split = number_string.split(','),
			sisa = split[0].length % 3,
			setor = split[0].substr(0, sisa),
			ribuan = split[0].substr(sisa).match(/\d{3}/gi);

		// tambahkan titik jika yang di input sudah menjadi angka ribuan
		if (ribuan) {
			separator = sisa ? '.' : '';
			setor += separator + ribuan.join('.');
		}

		setor = split[1] != undefined ? setor + ',' + split[1] : setor;
		return prefix == undefined ? setor : (setor ? 'Rp ' + setor : '');
	}
</script>