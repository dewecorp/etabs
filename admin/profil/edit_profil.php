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
</section>

<section class="content">
	<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
		<div class="md:col-span-2">
			<!-- general form elements -->
			<div class="rounded-2xl bg-white shadow-sm  h-full">
				<div class="border-b border-slate-100 px-6 py-4">
					<h3 class="text-lg font-semibold text-slate-900  flex items-center gap-2">
						<i class="fa-solid fa-building text-indigo-500"></i> Informasi Profil
					</h3>
				</div>
				<!-- /.box-header -->
				<!-- form start -->
				<form action="" method="post" enctype="multipart/form-data" id="formProfil">
					<div class="p-6 space-y-6">
						<?php if(isset($data_cek['id_profil'])): ?>
						<input type='hidden' class="form-control" name="id_profil" value="<?php echo $data_cek['id_profil']; ?>" readonly/>
						<?php endif; ?>

						<div class="space-y-1.5">
							<label class="text-sm font-medium text-slate-700  class="fa-solid fa-building mr-1.5 text-indigo-500"></i> Nama Sekolah</label>
							<input type="text" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20     name="nama_sekolah" 
							       value="<?php echo htmlspecialchars(isset($data_cek['nama_sekolah']) ? $data_cek['nama_sekolah'] : ''); ?>" 
							       placeholder="Masukkan Nama Sekolah" required>
						</div>

						<div class="space-y-1.5">
							<label class="text-sm font-medium text-slate-700  class="fa-solid fa-map-location-dot mr-1.5 text-indigo-500"></i> Alamat</label>
							<textarea class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20     name="alamat" rows="3" 
							          placeholder="Masukkan Alamat Sekolah" required><?php echo htmlspecialchars(isset($data_cek['alamat']) ? $data_cek['alamat'] : ''); ?></textarea>
						</div>

						<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium text-slate-700  class="fa-solid fa-star mr-1.5 text-indigo-500"></i> Akreditasi</label>
                                <select class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20     name="akreditasi" required>
                                    <option value="">Pilih Akreditasi</option>
                                    <option value="A" <?php echo (isset($data_cek['akreditasi']) && $data_cek['akreditasi'] == 'A') ? 'selected' : ''; ?>>A</option>
                                    <option value="B" <?php echo (isset($data_cek['akreditasi']) && $data_cek['akreditasi'] == 'B') ? 'selected' : ''; ?>>B</option>
                                    <option value="C" <?php echo (isset($data_cek['akreditasi']) && $data_cek['akreditasi'] == 'C') ? 'selected' : ''; ?>>C</option>
                                    <option value="TT" <?php echo (isset($data_cek['akreditasi']) && $data_cek['akreditasi'] == 'TT') ? 'selected' : ''; ?>>TT (Belum Terakreditasi)</option>
                                </select>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-sm font-medium text-slate-700  class="fa-regular fa-calendar mr-1.5 text-indigo-500"></i> Tahun Ajaran Aktif</label>
                                <input type="text" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20     name="tahun_ajaran" 
                                       value="<?php echo htmlspecialchars(isset($data_cek['tahun_ajaran']) ? $data_cek['tahun_ajaran'] : ''); ?>" 
                                       placeholder="Contoh: 2023/2024">
                            </div>
                        </div>
						
						<div class="space-y-1.5">
							<label class="text-sm font-medium text-slate-700  class="fa-solid fa-image mr-1.5 text-indigo-500"></i> Logo Sekolah</label>
							<input type="file" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20     file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100   name="logo_sekolah" id="logoInput" 
							       accept="image/*" onchange="previewLogo(this)">
							<p class="text-xs text-slate-500  mt-1">
								Format: JPG, PNG, GIF (Max: 2MB). Ukuran disarankan: 200x200px.
								<?php if (!empty($current_logo)): ?>
								<br><span class="text-indigo-600  font-medium">Logo saat ini: <?php echo htmlspecialchars($current_logo); ?></span>
								<?php else: ?>
								<br><span class="text-slate-400">Belum ada logo yang diupload</span>
								<?php endif; ?>
							</p>
						</div>
						
						<div class="space-y-1.5">
							<label class="text-sm font-medium text-slate-700  class="fa-solid fa-user-tie mr-1.5 text-indigo-500"></i> Nama Bendahara</label>
							<input type="text" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20     name="nama_bendahara" 
							       value="<?php echo htmlspecialchars(isset($data_cek['nama_bendahara']) ? $data_cek['nama_bendahara'] : ''); ?>" 
							       placeholder="Masukkan Nama Bendahara">
							<p class="text-xs text-slate-500  mt-1">Nama bendahara akan ditampilkan di laporan PDF</p>
						</div>
					</div>
					<!-- /.box-body -->
					
					<div class="px-6 py-4 bg-slate-50 border-t border-slate-100   flex justify-end gap-3">
						<a href="?page=MyApp/data_profil" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-200/50      transition-all">
							<i class="fa-solid fa-xmark mr-2"></i> Batal
						</a>
						<button type="submit" name="Ubah" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all">
							<i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
						</button>
					</div>
				</form>
			</div>
			<!-- /.box -->
		</div>
		
		<div class="col-md-4">
			<!-- Logo Preview Box -->
			<div class="rounded-2xl bg-white shadow-sm  h-full">
				<div class="border-b border-slate-100 px-6 py-4">
					<h3 class="text-lg font-semibold text-slate-900  flex items-center gap-2">
						<i class="fa-solid fa-image text-emerald-500"></i> Preview Logo
					</h3>
				</div>
				<div class="p-6 text-center">
					<div class="rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 p-8 shadow-lg mb-4 flex items-center justify-center min-h-[200px]">
						<img src="<?php echo $logo_path; ?>" 
						     alt="Logo Sekolah" 
						     class="max-w-full max-h-[150px] h-auto rounded-lg bg-white p-2 shadow-md object-contain transition-transform hover:scale-105" 
						     id="logoUploadPreview"
						     onerror="this.src='../images/logo.png'">
					</div>
					<p class="text-sm text-slate-500">
						Preview logo akan berubah saat Anda memilih file baru
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


