<?php
// Proses upload logo
$upload_dir = dirname(dirname(__DIR__)) . '/uploads/logo/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Cek apakah kolom nama_bendahara sudah ada, jika tidak tambahkan
$check_column_bendahara = mysqli_query($koneksi, "SHOW COLUMNS FROM tb_profil LIKE 'nama_bendahara'");
if (mysqli_num_rows($check_column_bendahara) == 0) {
    // Tambahkan kolom nama_bendahara jika belum ada
    mysqli_query($koneksi, "ALTER TABLE tb_profil ADD COLUMN nama_bendahara VARCHAR(100) NULL AFTER logo_sekolah");
}

if(isset($_GET['kode'])){
    $sql_cek = "SELECT * FROM tb_profil WHERE id_profil='".$_GET['kode']."'";
    $query_cek = mysqli_query($koneksi, $sql_cek);
    $data_cek = mysqli_fetch_array($query_cek, MYSQLI_BOTH);
    
    // Cek apakah ada logo
    $current_logo = !empty($data_cek['logo_sekolah']) ? $data_cek['logo_sekolah'] : '';
    $logo_path = !empty($current_logo) ? '../uploads/logo/' . $current_logo : '../images/logo.png';
}
?>

<section class="content-header">
	<h1>
		<i class="fa fa-edit"></i> Edit Profil Sekolah
	</h1>
	<ol class="breadcrumb">
		<li>
			<a href="index.php">
				<i class="fa fa-home"></i>
				<b>e-TABS</b>
			</a>
		</li>
		<li><a href="?page=MyApp/data_profil">Profil Sekolah</a></li>
		<li class="active">Edit</li>
	</ol>
</section>

<section class="content">
	<div class="row">
		<div class="col-md-8">
			<!-- general form elements -->
			<div class="box box-primary">
				<div class="box-header with-border">
					<h3 class="box-title">
						<i class="fa fa-building"></i> Informasi Profil
					</h3>
					<div class="box-tools pull-right">
						<button type="button" class="btn btn-box-tool" data-widget="collapse">
							<i class="fa fa-minus"></i>
						</button>
					</div>
				</div>
				<!-- /.box-header -->
				<!-- form start -->
				<form action="" method="post" enctype="multipart/form-data" id="formProfil">
					<div class="box-body">
						<?php if(isset($data_cek['id_profil'])): ?>
						<input type='hidden' class="form-control" name="id_profil" value="<?php echo $data_cek['id_profil']; ?>" readonly/>
						<?php endif; ?>

						<div class="form-group">
							<label><i class="fa fa-building"></i> Nama Sekolah</label>
							<input type="text" class="form-control" name="nama_sekolah" 
							       value="<?php echo htmlspecialchars(isset($data_cek['nama_sekolah']) ? $data_cek['nama_sekolah'] : ''); ?>" 
							       placeholder="Masukkan Nama Sekolah" required>
						</div>

						<div class="form-group">
							<label><i class="fa fa-map-marker"></i> Alamat</label>
							<textarea class="form-control" name="alamat" rows="3" 
							          placeholder="Masukkan Alamat Sekolah" required><?php echo htmlspecialchars(isset($data_cek['alamat']) ? $data_cek['alamat'] : ''); ?></textarea>
						</div>

						<div class="form-group">
							<label><i class="fa fa-star"></i> Akreditasi</label>
							<select class="form-control" name="akreditasi" required>
								<option value="">Pilih Akreditasi</option>
								<option value="A" <?php echo (isset($data_cek['akreditasi']) && $data_cek['akreditasi'] == 'A') ? 'selected' : ''; ?>>A</option>
								<option value="B" <?php echo (isset($data_cek['akreditasi']) && $data_cek['akreditasi'] == 'B') ? 'selected' : ''; ?>>B</option>
								<option value="C" <?php echo (isset($data_cek['akreditasi']) && $data_cek['akreditasi'] == 'C') ? 'selected' : ''; ?>>C</option>
								<option value="TT" <?php echo (isset($data_cek['akreditasi']) && $data_cek['akreditasi'] == 'TT') ? 'selected' : ''; ?>>TT (Belum Terakreditasi)</option>
							</select>
						</div>

						<div class="form-group">
							<label><i class="fa fa-calendar"></i> Tahun Ajaran Aktif</label>
							<input type="text" class="form-control" name="tahun_ajaran" 
							       value="<?php echo htmlspecialchars(isset($data_cek['tahun_ajaran']) ? $data_cek['tahun_ajaran'] : ''); ?>" 
							       placeholder="Contoh: 2023/2024">
						</div>
						
						<div class="form-group">
							<label><i class="fa fa-image"></i> Logo Sekolah</label>
							<input type="file" class="form-control" name="logo_sekolah" id="logoInput" 
							       accept="image/*" onchange="previewLogo(this)">
							<small class="help-block">
								Format: JPG, PNG, GIF (Max: 2MB)<br>
								Ukuran disarankan: 200x200px atau lebih besar<br>
								<?php if (!empty($current_logo)): ?>
								<strong>Logo saat ini:</strong> <?php echo htmlspecialchars($current_logo); ?>
								<?php else: ?>
								Belum ada logo yang diupload
								<?php endif; ?>
							</small>
						</div>
						
						<div class="form-group">
							<label><i class="fa fa-user"></i> Nama Bendahara</label>
							<input type="text" class="form-control" name="nama_bendahara" 
							       value="<?php echo htmlspecialchars(isset($data_cek['nama_bendahara']) ? $data_cek['nama_bendahara'] : ''); ?>" 
							       placeholder="Masukkan Nama Bendahara">
							<small class="help-block">Nama bendahara akan ditampilkan di laporan PDF</small>
						</div>
					</div>
					<!-- /.box-body -->
					
					<div class="box-footer">
						<button type="submit" name="Ubah" class="btn btn-primary btn-lg">
							<i class="fa fa-save"></i> Simpan Perubahan
						</button>
						<a href="?page=MyApp/data_profil" class="btn btn-default btn-lg">
							<i class="fa fa-times"></i> Batal
						</a>
					</div>
				</form>
			</div>
			<!-- /.box -->
		</div>
		
		<div class="col-md-4">
			<!-- Logo Preview Box -->
			<div class="box box-success">
				<div class="box-header with-border">
					<h3 class="box-title">
						<i class="fa fa-image"></i> Preview Logo
					</h3>
				</div>
				<div class="box-body text-center">
					<div class="logo-upload-container">
						<img src="<?php echo $logo_path; ?>" 
						     alt="Logo Sekolah" 
						     class="logo-upload-preview" 
						     id="logoUploadPreview"
						     onerror="this.src='../images/logo.png'">
					</div>
					<p class="text-muted" style="margin-top: 15px;">
						<small>Preview logo akan berubah saat Anda memilih file baru</small>
					</p>
				</div>
			</div>
		</div>
	</div>
</section>

<script>
function previewLogo(input) {
	if (input.files && input.files[0]) {
		var reader = new FileReader();
		
		reader.onload = function(e) {
			document.getElementById('logoUploadPreview').src = e.target.result;
		}
		
		reader.readAsDataURL(input.files[0]);
	}
}
</script>

<style>
.logo-upload-container {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	padding: 30px;
	border-radius: 10px;
	margin-bottom: 15px;
	box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.logo-upload-preview {
	max-width: 100%;
	max-height: 200px;
	height: auto;
	border-radius: 8px;
	background: white;
	padding: 10px;
	box-shadow: 0 2px 10px rgba(0,0,0,0.2);
	object-fit: contain;
	transition: transform 0.3s;
}

.logo-upload-preview:hover {
	transform: scale(1.05);
}

.box-primary {
	border-top-color: #3c8dbc;
}

.box-success {
	border-top-color: #00a65a;
}

.form-group label {
	font-weight: 600;
	color: #555;
}

.form-group label i {
	margin-right: 5px;
	color: #3c8dbc;
}
</style>

<?php
if (isset($_POST['Ubah'])) {
    $id_profil = mysqli_real_escape_string($koneksi, $_POST['id_profil']);
    $nama_sekolah = mysqli_real_escape_string($koneksi, $_POST['nama_sekolah']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $akreditasi = mysqli_real_escape_string($koneksi, $_POST['akreditasi']);
    
    $logo_sekolah = $current_logo; // Default: keep existing logo
    
    // Handle logo upload
    if (isset($_FILES['logo_sekolah']) && $_FILES['logo_sekolah']['error'] == 0) {
        $file = $_FILES['logo_sekolah'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        if (in_array($file['type'], $allowed_types) && $file['size'] <= $max_size) {
            $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = 'logo_' . $id_profil . '_' . time() . '.' . $file_ext;
            $upload_path = $upload_dir . $new_filename;
            
            // Delete old logo if exists
            if (!empty($current_logo) && file_exists($upload_dir . $current_logo)) {
                @unlink($upload_dir . $current_logo);
            }
            
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                $logo_sekolah = $new_filename;
            }
        }
    }
    
    $nama_bendahara = isset($_POST['nama_bendahara']) ? mysqli_real_escape_string($koneksi, $_POST['nama_bendahara']) : '';
    $tahun_ajaran = isset($_POST['tahun_ajaran']) ? mysqli_real_escape_string($koneksi, $_POST['tahun_ajaran']) : '';
    
    // Update database - cek apakah kolom logo_sekolah sudah ada
    $check_column = mysqli_query($koneksi, "SHOW COLUMNS FROM tb_profil LIKE 'logo_sekolah'");
    if (mysqli_num_rows($check_column) == 0) {
        // Tambahkan kolom logo_sekolah jika belum ada
        mysqli_query($koneksi, "ALTER TABLE tb_profil ADD COLUMN logo_sekolah VARCHAR(255) NULL AFTER akreditasi");
    }
    
    // Cek apakah kolom nama_bendahara sudah ada
    $check_column_bendahara = mysqli_query($koneksi, "SHOW COLUMNS FROM tb_profil LIKE 'nama_bendahara'");
    if (mysqli_num_rows($check_column_bendahara) == 0) {
        // Tambahkan kolom nama_bendahara jika belum ada
        mysqli_query($koneksi, "ALTER TABLE tb_profil ADD COLUMN nama_bendahara VARCHAR(100) NULL AFTER logo_sekolah");
    }

    // Cek apakah kolom tahun_ajaran sudah ada
    $check_column_ta = mysqli_query($koneksi, "SHOW COLUMNS FROM tb_profil LIKE 'tahun_ajaran'");
    if (mysqli_num_rows($check_column_ta) == 0) {
        // Tambahkan kolom tahun_ajaran jika belum ada
        mysqli_query($koneksi, "ALTER TABLE tb_profil ADD COLUMN tahun_ajaran VARCHAR(20) NULL AFTER nama_bendahara");
    }
    
    $sql_ubah = "UPDATE tb_profil SET
        nama_sekolah='$nama_sekolah',
        alamat='$alamat',
        akreditasi='$akreditasi',
        logo_sekolah='$logo_sekolah',
        nama_bendahara='$nama_bendahara',
        tahun_ajaran='$tahun_ajaran'
        WHERE id_profil='$id_profil'";
    
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
            logActivity($koneksi, 'UPDATE', 'tb_profil', 'Mengubah profil sekolah: ' . $nama_sekolah);
        }
        
        echo "<script>
        (function(){
            if(typeof Swal!=='undefined'){
                Swal.fire({
                    title:'Berhasil!',
                    text:'Profil sekolah berhasil diperbarui',
                    icon:'success',
                    confirmButtonText:'OK',
                    confirmButtonColor:'#28a745',
                    allowOutsideClick:false,
                    allowEscapeKey:false,
                    timer:2500,
                    timerProgressBar:true
                }).then(function(){
                    window.location.href='index.php?page=MyApp/data_profil';
                });
                
                // Auto redirect setelah 2.5 detik jika tidak diklik
                setTimeout(function(){
                    window.location.href='index.php?page=MyApp/data_profil';
                }, 2500);
            }else{
                alert('Profil sekolah berhasil diperbarui');
                window.location.href='index.php?page=MyApp/data_profil';
            }
        })();
        </script>";
        return;
    } else {
        echo "<script>
        (function(){
            if(typeof Swal!=='undefined'){
                Swal.fire({
                    title:'Gagal!',
                    text:'Gagal memperbarui profil sekolah',
                    icon:'error',
                    confirmButtonText:'OK',
                    confirmButtonColor:'#dc3545',
                    allowOutsideClick:false,
                    allowEscapeKey:false
                }).then(function(){
                    window.location.href='index.php?page=MyApp/edit_profil&kode=" . $_POST['id_profil'] . "';
                });
            }else{
                alert('Gagal memperbarui profil sekolah');
                window.location.href='index.php?page=MyApp/edit_profil&kode=" . $_POST['id_profil'] . "';
            }
        })();
        </script>";
        return;
    }
}
?>
