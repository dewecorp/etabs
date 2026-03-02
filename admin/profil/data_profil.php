<section class="content-header">
	<h1>
		<i class="fa fa-building"></i> Profil Sekolah
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

<!-- Main content -->
<section class="content">
	<?php
	// Cek apakah kolom nama_bendahara sudah ada, jika tidak tambahkan
	$check_column_bendahara = mysqli_query($koneksi, "SHOW COLUMNS FROM tb_profil LIKE 'nama_bendahara'");
	if (mysqli_num_rows($check_column_bendahara) == 0) {
		// Tambahkan kolom nama_bendahara jika belum ada
		mysqli_query($koneksi, "ALTER TABLE tb_profil ADD COLUMN nama_bendahara VARCHAR(100) NULL AFTER logo_sekolah");
	}

	// Cek apakah kolom tahun_ajaran sudah ada, jika tidak tambahkan
	$check_column_ta = mysqli_query($koneksi, "SHOW COLUMNS FROM tb_profil LIKE 'tahun_ajaran'");
	if (mysqli_num_rows($check_column_ta) == 0) {
		// Tambahkan kolom tahun_ajaran jika belum ada
		mysqli_query($koneksi, "ALTER TABLE tb_profil ADD COLUMN tahun_ajaran VARCHAR(20) NULL AFTER nama_bendahara");
	}
	
	$sql = $koneksi->query("SELECT * FROM tb_profil LIMIT 1");
	$profil = $sql->fetch_assoc();
	$logo_path = !empty($profil['logo_sekolah']) ? '../uploads/logo/' . $profil['logo_sekolah'] : '../images/logo.png';
	?>
	
	<div class="row equal-height-boxes">
		<!-- Profil Card -->
		<div class="col-md-8">
			<div class="box box-primary equal-height-box">
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
				<div class="box-body">
					<div class="row">
						<div class="col-md-12">
							<div class="profile-info">
								<div class="form-group">
									<label class="control-label"><i class="fa fa-building"></i> Nama Sekolah</label>
									<div class="info-value">
										<?php echo htmlspecialchars($profil['nama_sekolah']); ?>
									</div>
								</div>
								
								<div class="form-group">
									<label class="control-label"><i class="fa fa-map-marker"></i> Alamat</label>
									<div class="info-value">
										<?php echo htmlspecialchars($profil['alamat']); ?>
									</div>
								</div>
								
								<div class="form-group">
									<label class="control-label"><i class="fa fa-star"></i> Akreditasi</label>
									<div class="info-value">
										<span class="label label-success" style="font-size: 14px; padding: 5px 10px;">
											Akreditasi <?php echo htmlspecialchars($profil['akreditasi']); ?>
										</span>
									</div>
								</div>

								<div class="form-group">
									<label class="control-label"><i class="fa fa-calendar"></i> Tahun Ajaran Aktif</label>
									<div class="info-value">
										<span class="label label-info" style="font-size: 14px; padding: 5px 10px;">
											<?php echo htmlspecialchars(!empty($profil['tahun_ajaran']) ? $profil['tahun_ajaran'] : '-'); ?>
										</span>
									</div>
								</div>
								
								<?php if (!empty($profil['nama_bendahara'])): ?>
								<div class="form-group">
									<label class="control-label"><i class="fa fa-user"></i> Nama Bendahara</label>
									<div class="info-value">
										<?php echo htmlspecialchars($profil['nama_bendahara']); ?>
									</div>
								</div>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
				<div class="box-footer">
					<a href="?page=MyApp/edit_profil&kode=<?php echo $profil['id_profil']; ?>" class="btn btn-primary btn-lg">
						<i class="fa fa-edit"></i> Edit Profil Sekolah
					</a>
				</div>
			</div>
		</div>
		
		<!-- Logo Card -->
		<div class="col-md-4">
			<div class="box box-success equal-height-box">
				<div class="box-header with-border">
					<h3 class="box-title">
						<i class="fa fa-image"></i> Logo Sekolah
					</h3>
				</div>
				<div class="box-body text-center">
					<div class="logo-upload-container">
						<img src="<?php echo $logo_path; ?>" 
						     alt="Logo Sekolah" 
						     class="logo-upload-preview" 
						     id="logoPreview"
						     onerror="this.src='../images/logo.png'">
					</div>
					<p class="text-muted" style="margin-top: 15px;">
						<small>Logo akan ditampilkan di berbagai bagian aplikasi</small>
					</p>
				</div>
			</div>
		</div>
	</div>
</section>

<style>
.profile-info {
	padding: 10px 0;
}

.profile-info .form-group {
	margin-bottom: 15px;
	padding-bottom: 10px;
	border-bottom: 1px solid #f0f0f0;
}

.profile-info .form-group:last-child {
	border-bottom: none;
	margin-bottom: 0;
}

.profile-info .control-label {
	font-weight: 600;
	color: #555;
	font-size: 12px;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	margin-bottom: 5px;
	display: block;
}

.profile-info .control-label i {
	margin-right: 8px;
	color: #3c8dbc;
}

.profile-info .info-value {
	font-size: 14px;
	color: #333;
	line-height: 1.4;
	padding: 5px 0;
}

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

.box {
	box-shadow: 0 1px 3px rgba(0,0,0,.12), 0 1px 2px rgba(0,0,0,.24);
	border-radius: 4px;
}

.equal-height-boxes {
	display: flex;
	align-items: stretch;
}

.equal-height-box {
	display: flex;
	flex-direction: column;
	height: 100%;
}

.equal-height-box .box-body {
	flex: 1;
	display: flex;
	flex-direction: column;
}

@media (max-width: 768px) {
	.col-md-8, .col-md-4 {
		margin-bottom: 20px;
	}
}
</style>


