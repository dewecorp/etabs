<?php

    if(isset($_GET['kode'])){
        $sql_cek = "select s.nis, s.nama_siswa, t.id_tabungan, t.tarik, t.tgl, t.petugas from 
        tb_siswa s join tb_tabungan t on s.nis=t.nis 
        where jenis ='TR' and id_tabungan='".$_GET['kode']."'";
        $query_cek = mysqli_query($koneksi, $sql_cek);
        $data_cek = mysqli_fetch_array($query_cek,MYSQLI_BOTH);
    }

    $tanggal = date("Y-m-d");
?>

<section class="content-header">
	<h1>
		Transaksi
		<small>Ubah Tarikan</small>
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
					<h3 class="box-title">Ubah Tarikan</h3>
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
							<input type="hidden" class="form-control" name="id_tabungan" class="form-control" value="<?php echo $data_cek['id_tabungan']; ?>"
							 readonly/>
						</div>

						<div class="form-group">
							<label>Siswa</label>
							<select name="nis" id="nis" class="form-control select2" style="width: 100%; ">
								<option selected="">-- Pilih --</option>
								<?php
                        // ambil data dari database
                        $query = "select * from tb_siswa";
                        $hasil = mysqli_query($koneksi, $query);
                        while ($row = mysqli_fetch_array($hasil)) {
                        ?>
								<option value="<?php echo $row['nis'] ?>" <?=$data_cek[
								 'nis']==$row[ 'nis'] ? "selected" : null ?>>
									<?php echo $row['nama_siswa'] ?>
								</option>
								<?php
                        }
                        ?>
							</select>
						</div>

						<div class="form-group">
							<label>Tarikan</label>
							<input type="text" class="form-control" id="tarik" name="tarik" value="Rp <?php echo number_format(($data_cek['tarik']),0,'','.')?>"
							/>
						</div>

					</div>
					<!-- /.box-body -->

					<div class="box-footer">
						<input type="submit" name="Ubah" value="Ubah" class="btn btn-success">
						<a href="?page=data_tarik" class="btn btn-warning">Batal</a>
					</div>
				</form>
			</div>
			<!-- /.box -->
</section>

<?php

if (isset ($_POST['Ubah'])){

		//menangkap post tarik
		$tarik=$_POST['tarik'];
		//membuang Rp dan Titik
		$tarik_hasil=preg_replace("/[^0-9]/", "", $tarik);

        $sql_ubah = "UPDATE tb_tabungan SET
            nis='".$_POST['nis']."',
            tarik='".$tarik_hasil."',
            tgl='".$tanggal."'
            WHERE id_tabungan='".$_POST['id_tabungan']."'";
		$query_ubah = mysqli_query($koneksi, $sql_ubah);

    if ($query_ubah) {
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
            logActivity($koneksi, 'UPDATE', 'tb_tabungan', 'Mengubah penarikan untuk ' . $nama_siswa . ' menjadi Rp ' . number_format($tarik_hasil, 0, ',', '.'), $_POST['nis']);
        }
		mysqli_close($koneksi);
        
        echo "<script>
        (function(){
            if(typeof Swal!=='undefined'){
                Swal.fire({
                    title:'Berhasil!',
                    text:'Penarikan berhasil diubah',
                    icon:'success',
                    confirmButtonText:'OK',
                    confirmButtonColor:'#28a745',
                    allowOutsideClick:false,
                    allowEscapeKey:false,
                    timer:2500,
                    timerProgressBar:true
                }).then(function(){
                    window.location.href='index.php?page=data_tarik';
                });
                
                // Auto redirect setelah 2.5 detik jika tidak diklik
                setTimeout(function(){
                    window.location.href='index.php?page=data_tarik';
                }, 2500);
            }else{
                alert('Penarikan berhasil diubah');
                window.location.href='index.php?page=data_tarik';
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
                    text:'Penarikan gagal diubah',
                    icon:'error',
                    confirmButtonText:'OK',
                    confirmButtonColor:'#dc3545',
                    allowOutsideClick:false,
                    allowEscapeKey:false
                }).then(function(){
                    window.location.href='index.php?page=data_tarik';
                });
            }else{
                alert('Penarikan gagal diubah');
                window.location.href='index.php?page=data_tarik';
            }
        })();
        </script>";
        return;
    }
}

?>

<script type="text/javascript">
	var tarik = document.getElementById('tarik');
	tarik.addEventListener('keyup', function (e) {
		// tambahkan 'Rp.' pada saat form di ketik
		// gunakan fungsi formattarik() untuk mengubah angka yang di ketik menjadi format angka
		tarik.value = formattarik(this.value, 'Rp ');
	});

	/* Fungsi formattarik */
	function formattarik(angka, prefix) {
		var number_string = angka.replace(/[^,\d]/g, '').toString(),
			split = number_string.split(','),
			sisa = split[0].length % 3,
			tarik = split[0].substr(0, sisa),
			ribuan = split[0].substr(sisa).match(/\d{3}/gi);

		// tambahkan titik jika yang di input sudah menjadi angka ribuan
		if (ribuan) {
			separator = sisa ? '.' : '';
			tarik += separator + ribuan.join('.');
		}

		tarik = split[1] != undefined ? tarik + ',' + split[1] : tarik;
		return prefix == undefined ? tarik : (tarik ? 'Rp ' + tarik : '');
	}
</script>