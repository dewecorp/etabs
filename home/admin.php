<?php
// Pastikan activity_log.php sudah di-include
if (!function_exists('createActivityTable')) {
    include_once "../inc/activity_log.php";
}

// Buat tabel activity log jika belum ada
if (function_exists('createActivityTable') && isset($koneksi)) {
    @createActivityTable($koneksi);
}

$siswa = 0;
$setor = 0;
$tarik = 0;
$saldo = 0;

$sql = @$koneksi->query("SELECT count(nis) as siswa from tb_siswa where status='Aktif'");
if ($sql) {
    while ($data = $sql->fetch_assoc()) {
        $siswa = $data['siswa'];
    }
}

$sql = @$koneksi->query("SELECT SUM(setor) as Tsetor from tb_tabungan where jenis='ST'");
if ($sql) {
    while ($data = $sql->fetch_assoc()) {
        $setor = $data['Tsetor'] ? $data['Tsetor'] : 0;
    }
}

$sql = @$koneksi->query("SELECT SUM(tarik) as Ttarik from tb_tabungan where jenis='TR'");
if ($sql) {
    while ($data = $sql->fetch_assoc()) {
        $tarik = $data['Ttarik'] ? $data['Ttarik'] : 0;
    }
}

$saldo = $setor - $tarik;
?>

<!-- Content Header (Page header) -->
<section class="content-header">
	<h1>
		Dashboard
		<small>Administrator</small>
	</h1>
</section>

<!-- Main content -->
<section class="content">
	<!-- Small boxes (Stat box) -->
	<div class="row">

		<div class="col-lg-3 col-xs-6">
			<!-- small box -->
			<div class="small-box bg-yellow">
				<div class="inner">
					<h4>
						<?= $siswa; ?>
					</h4>

					<p>Siswa Aktif</p>
				</div>
				<div class="icon">
					<i class="ion ion-person-add"></i>
				</div>
				<a href="?page=MyApp/data_siswa" class="small-box-footer">More info
					<i class="fa fa-arrow-circle-right"></i>
				</a>
			</div>
		</div>
		<!-- ./col -->

		<div class="col-lg-3 col-xs-6">
			<!-- small box -->
			<div class="small-box bg-aqua">
				<div class="inner">
					<h4>
						<?= rupiah($setor); ?>
					</h4>

					<p>Total Setoran</p>
				</div>
				<div class="icon">
					<i class="ion ion-bag"></i>
				</div>
				<a href="?page=data_setor" class="small-box-footer">More info
					<i class="fa fa-arrow-circle-right"></i>
				</a>
			</div>
		</div>
		<!-- ./col -->

		<div class="col-lg-3 col-xs-6">
			<!-- small box -->
			<div class="small-box bg-red">
				<div class="inner">
					<h4>
						<?= rupiah($tarik); ?>
					</h4>
					<p>Total Penarikan</p>
				</div>
				<div class="icon">
					<i class="ion ion-stats-bars"></i>
				</div>
				<a href="?page=data_tarik" class="small-box-footer">More info
					<i class="fa fa-arrow-circle-right"></i>
				</a>
			</div>
		</div>
		<!-- ./col -->

		<div class="col-lg-3 col-xs-6">
			<!-- small box -->
			<div class="small-box bg-green">
				<div class="inner">
					<h4>
						<?= rupiah($saldo); ?>
					</h4>
					<p>Total Saldo</p>
				</div>
				<div class="icon">
					<i class="ion ion-pie-graph"></i>
				</div>
				<a href="#" class="small-box-footer">More info
					<i class="fa fa-arrow-circle-right"></i>
				</a>
			</div>
		</div>
		<!-- ./col -->
	</div>

	<!-- /.box-body -->

	<!-- Profil Sekolah -->
	<?php
	// Cek apakah kolom nama_bendahara sudah ada
	$check_column_bendahara = @mysqli_query($koneksi, "SHOW COLUMNS FROM tb_profil LIKE 'nama_bendahara'");
	if ($check_column_bendahara && mysqli_num_rows($check_column_bendahara) == 0) {
		@mysqli_query($koneksi, "ALTER TABLE tb_profil ADD COLUMN nama_bendahara VARCHAR(100) NULL AFTER logo_sekolah");
	}
	
	$sql_profil = @$koneksi->query("SELECT * FROM tb_profil LIMIT 1");
	$profil_data = $sql_profil ? $sql_profil->fetch_assoc() : null;
	$logo_path = (!empty($profil_data['logo_sekolah']) && file_exists('../uploads/logo/' . $profil_data['logo_sekolah'])) 
		? '../uploads/logo/' . $profil_data['logo_sekolah'] 
		: '../images/logo.png';
	?>
	<div class="row">
		<div class="col-md-12">
			<div class="box box-gradient-primary" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
				<div class="box-header" style="background: rgba(255,255,255,0.1); border-bottom: 1px solid rgba(255,255,255,0.2);">
					<h3 class="box-title" style="color: white; font-weight: bold;">
						<i class="fa fa-building"></i> Profil Sekolah
					</h3>
					<div class="box-tools pull-right">
						<button type="button" class="btn btn-box-tool" data-widget="collapse" style="color: white;">
							<i class="fa fa-minus"></i>
						</button>
					</div>
				</div>
				<div class="box-body" style="background: white; padding: 25px;">
					<div class="row">
						<div class="col-md-3 text-center">
							<div class="logo-container" style="background: #f8f9fa; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
								<img src="<?php echo $logo_path; ?>" alt="Logo Sekolah" 
									style="max-width: 150px; max-height: 150px; width: auto; height: auto; border-radius: 5px;">
							</div>
						</div>
						<div class="col-md-9">
							<div class="profile-details">
								<div class="info-item" style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #e9ecef;">
									<label style="color: #6c757d; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px;">
										<i class="fa fa-building text-primary"></i> Nama Sekolah
									</label>
									<div style="font-size: 18px; font-weight: 600; color: #2c3e50;">
										<?php echo htmlspecialchars($profil_data ? $profil_data['nama_sekolah'] : 'Belum diatur'); ?>
									</div>
								</div>
								
								<div class="info-item" style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #e9ecef;">
									<label style="color: #6c757d; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px;">
										<i class="fa fa-map-marker text-danger"></i> Alamat
									</label>
									<div style="font-size: 15px; color: #495057;">
										<?php echo htmlspecialchars($profil_data ? $profil_data['alamat'] : 'Belum diatur'); ?>
									</div>
								</div>
								
								<div class="row">
									<div class="col-md-6">
										<div class="info-item" style="margin-bottom: 15px;">
											<label style="color: #6c757d; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px;">
												<i class="fa fa-star text-warning"></i> Akreditasi
											</label>
											<div>
												<span class="label label-success" style="font-size: 14px; padding: 8px 15px; border-radius: 20px;">
													Akreditasi <?php echo htmlspecialchars($profil_data ? $profil_data['akreditasi'] : '-'); ?>
												</span>
											</div>
										</div>
									</div>
									<?php if (!empty($profil_data['nama_bendahara'])): ?>
									<div class="col-md-6">
										<div class="info-item" style="margin-bottom: 15px;">
											<label style="color: #6c757d; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px;">
												<i class="fa fa-user text-info"></i> Bendahara
											</label>
											<div style="font-size: 15px; color: #495057; font-weight: 500;">
												<?php echo htmlspecialchars($profil_data['nama_bendahara']); ?>
											</div>
										</div>
									</div>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Grafik Setoran dan Penarikan -->
	<div class="row">
		<div class="col-md-12">
			<div class="box box-info">
				<div class="box-header with-border">
					<h3 class="box-title">
						<i class="fa fa-line-chart"></i> Grafik Setoran dan Penarikan (12 Bulan Terakhir)
					</h3>
					<div class="box-tools pull-right">
						<button type="button" class="btn btn-box-tool" data-widget="collapse">
							<i class="fa fa-minus"></i>
						</button>
						<button type="button" class="btn btn-box-tool" data-widget="remove">
							<i class="fa fa-remove"></i>
						</button>
					</div>
				</div>
				<div class="box-body">
					<div class="chart">
						<canvas id="chartSetoranPenarikan" style="height: 300px;"></canvas>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Aktivitas Terbaru -->
	<div class="row">
		<div class="col-md-12">
			<div class="box box-primary">
				<div class="box-header with-border">
					<h3 class="box-title">
						<i class="fa fa-history"></i> Aktivitas Terbaru
					</h3>
					<div class="box-tools pull-right">
						<?php
						// Ambil jumlah aktivitas
						$activity_count = 0;
						if (function_exists('getActivityCount') && isset($koneksi)) {
							try {
								if (function_exists('createActivityTable')) {
									@createActivityTable($koneksi);
								}
								if (function_exists('cleanupOldActivities')) {
									@cleanupOldActivities($koneksi);
								}
								$activity_count = @getActivityCount($koneksi);
							} catch (Exception $e) {
								$activity_count = 0;
							}
						}
						?>
						<span class="label label-primary" style="font-size: 14px; padding: 5px 10px; margin-right: 10px;">
							<i class="fa fa-list"></i> Total: <?php echo number_format($activity_count); ?> aktivitas
						</span>
						<button type="button" class="btn btn-box-tool" data-widget="collapse">
							<i class="fa fa-minus"></i>
						</button>
					</div>
				</div>
				<div class="box-body" style="max-height: 500px; overflow-y: auto;">
					<?php
					if (!function_exists('getTimeAgo')) {
						function getTimeAgo($datetime) {
							if (empty($datetime)) return '';
							
							// Coba parse dengan DateTime untuk akurasi lebih baik
							$timestamp = false;
							try {
								$dt = new DateTime($datetime);
								$timestamp = $dt->getTimestamp();
							} catch (Exception $e) {
								// Fallback ke strtotime
								$timestamp = @strtotime($datetime);
							}
							
							if (!$timestamp || $timestamp <= 0) {
								// Jika masih gagal, coba format lain
								$timestamp = @strtotime(str_replace('/', '-', $datetime));
								if (!$timestamp || $timestamp <= 0) {
									return '';
								}
							}
							
							$now = time();
							$diff = $now - $timestamp;
							
							// Pastikan diff positif (tidak ada data masa depan)
							if ($diff < 0) {
								return 'Baru saja';
							}
							
							// Hitung dengan lebih akurat
							if ($diff < 60) {
								return 'Baru saja';
							} elseif ($diff < 3600) {
								$mins = floor($diff / 60);
								return $mins . ' menit yang lalu';
							} elseif ($diff < 86400) {
								$hours = floor($diff / 3600);
								return $hours . ' jam yang lalu';
							} elseif ($diff < 604800) {
								$days = floor($diff / 86400);
								return $days . ' hari yang lalu';
							} else {
								return date('d M Y', $timestamp);
							}
						}
					}
					
					$recent_activities = [];
					if (function_exists('getRecentActivities') && isset($koneksi)) {
						try {
							// Pastikan tabel sudah dibuat
							if (function_exists('createActivityTable')) {
								@createActivityTable($koneksi);
							}
							// Ambil aktivitas terbaru langsung dari database dengan format yang jelas
							if (is_object($koneksi) && method_exists($koneksi, 'query')) {
								$sql = "SELECT *, UNIX_TIMESTAMP(created_at) as timestamp_unix FROM tb_activity_log ORDER BY created_at DESC LIMIT 10";
								$result = $koneksi->query($sql);
								if ($result) {
									while ($row = $result->fetch_assoc()) {
										$recent_activities[] = $row;
									}
								}
							} elseif (is_resource($koneksi)) {
								$sql = "SELECT *, UNIX_TIMESTAMP(created_at) as timestamp_unix FROM tb_activity_log ORDER BY created_at DESC LIMIT 10";
								$result = mysqli_query($koneksi, $sql);
								if ($result) {
									while ($row = mysqli_fetch_assoc($result)) {
										$recent_activities[] = $row;
									}
								}
							} else {
								// Fallback ke fungsi getRecentActivities
								$recent_activities = @getRecentActivities($koneksi, 10);
							}
							if (!is_array($recent_activities)) {
								$recent_activities = [];
							}
						} catch (Exception $e) {
							$recent_activities = [];
						}
					}
					
					if (empty($recent_activities)):
					?>
					<div class="alert alert-info">
						<i class="fa fa-info-circle"></i> Belum ada aktivitas yang tercatat.
					</div>
					<?php else: ?>
					<ul class="timeline timeline-inverse">
						<?php
						foreach ($recent_activities as $activity):
							// Gunakan timestamp_unix jika ada (dari query UNIX_TIMESTAMP)
							if (isset($activity['timestamp_unix']) && $activity['timestamp_unix'] > 0) {
								$timestamp = (int)$activity['timestamp_unix'];
							} else {
								// Fallback: parse created_at
								$created_at = isset($activity['created_at']) ? $activity['created_at'] : '';
								if (empty($created_at)) {
									$timestamp = time();
								} else {
									$timestamp = strtotime($created_at);
									if (!$timestamp || $timestamp <= 0) {
										$timestamp = time();
									}
								}
							}
							
							// Hitung selisih waktu
							$now = time();
							$diff = $now - $timestamp;
							
							// Pastikan diff valid (tidak negatif)
							if ($diff < 0) {
								$diff = 0;
							}
							
							// Hitung time_ago berdasarkan diff
							if ($diff < 60) {
								$time_ago = 'Baru saja';
							} elseif ($diff < 3600) {
								$mins = floor($diff / 60);
								$time_ago = $mins . ' menit yang lalu';
							} elseif ($diff < 86400) {
								$hours = floor($diff / 3600);
								$time_ago = $hours . ' jam yang lalu';
							} elseif ($diff < 604800) {
								$days = floor($diff / 86400);
								$time_ago = $days . ' hari yang lalu';
							} else {
								$time_ago = date('d M Y', $timestamp);
							}
							
							// Simpan created_at untuk JavaScript
							$created_at = isset($activity['created_at']) ? $activity['created_at'] : date('Y-m-d H:i:s');
						?>
						<li>
							<i class="fa <?php echo htmlspecialchars($activity['icon']); ?> bg-<?php echo htmlspecialchars($activity['color']); ?>"></i>
							<div class="timeline-item">
								<span class="time" data-timestamp="<?php echo $timestamp; ?>" data-created="<?php echo htmlspecialchars($created_at); ?>" data-diff="<?php echo isset($diff) ? $diff : 0; ?>">
									<i class="fa fa-clock-o"></i> <span class="time-text"><?php echo $time_ago; ?></span>
								</span>
								<h3 class="timeline-header">
									<a href="#"><?php echo htmlspecialchars($activity['user_name']); ?></a>
									<span class="label label-<?php echo htmlspecialchars($activity['color']); ?>" style="margin-left: 10px;">
										<?php echo htmlspecialchars($activity['action']); ?>
									</span>
									<?php if ($activity['user_level']): ?>
									<small class="text-muted">(<?php echo htmlspecialchars($activity['user_level']); ?>)</small>
									<?php endif; ?>
								</h3>
								<div class="timeline-body">
									<?php echo htmlspecialchars($activity['description']); ?>
									<?php if ($activity['table_name']): ?>
									<br><small class="text-muted">
										<i class="fa fa-table"></i> <?php echo htmlspecialchars($activity['table_name']); ?>
									</small>
									<?php endif; ?>
								</div>
							</div>
						</li>
						<?php endforeach; ?>
						<li>
							<i class="fa fa-clock-o bg-gray"></i>
						</li>
					</ul>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

</section>
<!-- /.content -->

	<script>
	// Data untuk grafik - ambil dari database
	<?php
	// Query untuk mendapatkan data setoran dan penarikan per bulan (12 bulan terakhir)
	$chart_data = [];
	$chart_labels = [];
	
	for ($i = 11; $i >= 0; $i--) {
		$month = date('Y-m', strtotime("-$i months"));
		$month_label = date('M Y', strtotime("-$i months"));
		$chart_labels[] = $month_label;
		
		$sql_setor = $koneksi->query("SELECT COALESCE(SUM(setor), 0) as total FROM tb_tabungan WHERE jenis='ST' AND DATE_FORMAT(tgl, '%Y-%m') = '$month'");
		$data_setor = $sql_setor->fetch_assoc();
		$chart_data['setor'][] = (float)$data_setor['total'];
		
		$sql_tarik = $koneksi->query("SELECT COALESCE(SUM(tarik), 0) as total FROM tb_tabungan WHERE jenis='TR' AND DATE_FORMAT(tgl, '%Y-%m') = '$month'");
		$data_tarik = $sql_tarik->fetch_assoc();
		$chart_data['tarik'][] = (float)$data_tarik['total'];
	}
	?>
	
	// Inisialisasi Chart
	var ctx = document.getElementById('chartSetoranPenarikan').getContext('2d');
	var chartSetoranPenarikan = new Chart(ctx, {
		type: 'line',
		data: {
			labels: <?php echo json_encode($chart_labels); ?>,
			datasets: [{
				label: 'Setoran',
				data: <?php echo json_encode($chart_data['setor']); ?>,
				borderColor: 'rgb(0, 166, 90)',
				backgroundColor: 'rgba(0, 166, 90, 0.1)',
				borderWidth: 2,
				fill: true,
				tension: 0.4,
				pointRadius: 5,
				pointHoverRadius: 7
			}, {
				label: 'Penarikan',
				data: <?php echo json_encode($chart_data['tarik']); ?>,
				borderColor: 'rgb(245, 105, 84)',
				backgroundColor: 'rgba(245, 105, 84, 0.1)',
				borderWidth: 2,
				fill: true,
				tension: 0.4,
				pointRadius: 5,
				pointHoverRadius: 7
			}]
		},
		options: {
			responsive: true,
			maintainAspectRatio: false,
			plugins: {
				legend: {
					display: true,
					position: 'top',
				},
				tooltip: {
					mode: 'index',
					intersect: false,
					callbacks: {
						label: function(context) {
							var label = context.dataset.label || '';
							if (label) {
								label += ': ';
							}
							label += 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
							return label;
						}
					}
				}
			},
			scales: {
				y: {
					beginAtZero: true,
					ticks: {
						callback: function(value) {
							if (value >= 1000000) {
								return 'Rp ' + (value / 1000000).toFixed(1) + 'J';
							} else if (value >= 1000) {
								return 'Rp ' + (value / 1000).toFixed(0) + 'K';
							}
							return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
						},
						maxTicksLimit: 10
					},
					grid: {
						color: 'rgba(0, 0, 0, 0.1)'
					}
				},
				x: {
					grid: {
						display: false
					},
					ticks: {
						maxRotation: 45,
						minRotation: 45
					}
				}
			},
			interaction: {
				mode: 'nearest',
				axis: 'x',
				intersect: false
			}
		}
	});
	</script>
	
	<style>
	.timeline {
		position: relative;
		margin: 0 0 30px 0;
		padding: 0;
		list-style: none;
	}

	.timeline:before {
		content: '';
		position: absolute;
		top: 0;
		bottom: 0;
		left: 40px;
		width: 4px;
		margin-left: -1.5px;
		background-color: #ddd;
	}

	.timeline > li {
		position: relative;
		margin-bottom: 20px;
		min-height: 50px;
	}

	.timeline > li:before,
	.timeline > li:after {
		content: " ";
		display: table;
	}

	.timeline > li:after {
		clear: both;
	}

	.timeline > li > .timeline-item {
		-webkit-box-shadow: 0 1px 1px rgba(0,0,0,0.1);
		box-shadow: 0 1px 1px rgba(0,0,0,0.1);
		border-radius: 3px;
		margin-top: 0;
		background: #fff;
		color: #444;
		margin-left: 60px;
		margin-right: 15px;
		padding: 0;
		position: relative;
	}

	.timeline > li > .timeline-item > .time {
		color: #999;
		float: right;
		padding: 10px;
		font-size: 12px;
	}

	.timeline > li > .timeline-item > .timeline-header {
		margin: 0;
		color: #555;
		border-bottom: 1px solid #f4f4f4;
		padding: 10px;
		font-size: 14px;
		line-height: 1.1;
	}

	.timeline > li > .timeline-item > .timeline-body {
		padding: 10px;
		font-size: 13px;
	}

	.timeline > li > i {
		width: 30px;
		height: 30px;
		font-size: 15px;
		line-height: 30px;
		position: absolute;
		color: #fff;
		background: #d2d6de;
		border-radius: 50%;
		text-align: center;
		left: 25px;
		top: 0;
	}

	.timeline > li > .bg-blue { background-color: #3c8dbc !important; }
	.timeline > li > .bg-green { background-color: #00a65a !important; }
	.timeline > li > .bg-yellow { background-color: #f39c12 !important; }
	.timeline > li > .bg-red { background-color: #dd4b39 !important; }
	.timeline > li > .bg-aqua { background-color: #00c0ef !important; }
	.timeline > li > .bg-purple { background-color: #605ca8 !important; }
	.timeline > li > .bg-gray { background-color: #d2d6de !important; }
	.timeline > li > .bg-navy { background-color: #001f3f !important; }
	.timeline > li > .bg-teal { background-color: #39cccc !important; }
	.timeline > li > .bg-lime { background-color: #01ff70 !important; }
	.timeline > li > .bg-orange { background-color: #ff851b !important; }
	.timeline > li > .bg-fuchsia { background-color: #f012be !important; }
	</style>

	<script>
	// Fungsi untuk update waktu aktivitas secara real-time
	// Pastikan script berjalan setelah semua library dimuat
	(function() {
		'use strict';
		
		function getTimeAgo(timestamp) {
			if (!timestamp || timestamp <= 0) return 'Baru saja';
			
			var now = Math.floor(Date.now() / 1000);
			var diff = now - timestamp;
			
			// Pastikan diff positif
			if (diff < 0) return 'Baru saja';
			
			if (diff < 60) {
				return 'Baru saja';
			} else if (diff < 3600) {
				var mins = Math.floor(diff / 60);
				return mins + ' menit yang lalu';
			} else if (diff < 86400) {
				var hours = Math.floor(diff / 3600);
				return hours + ' jam yang lalu';
			} else if (diff < 604800) {
				var days = Math.floor(diff / 86400);
				return days + ' hari yang lalu';
			} else {
				var date = new Date(timestamp * 1000);
				var day = date.getDate();
				var month = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'][date.getMonth()];
				var year = date.getFullYear();
				return day + ' ' + month + ' ' + year;
			}
		}
		
		function updateTimeAgo() {
			try {
				var timeElements = document.querySelectorAll('.time[data-timestamp]');
				if (timeElements.length === 0) {
					console.log('Time update: No elements found');
					return; // Tidak ada elemen yang ditemukan
				}
				
				console.log('Time update: Found ' + timeElements.length + ' elements');
				
				timeElements.forEach(function(element) {
					var timestampStr = element.getAttribute('data-timestamp');
					if (timestampStr) {
						var timestamp = parseInt(timestampStr, 10);
						if (timestamp && timestamp > 0) {
							var timeText = element.querySelector('.time-text');
							if (timeText) {
								var newTime = getTimeAgo(timestamp);
								var oldTime = timeText.textContent;
								
								// Debug log
								var now = Math.floor(Date.now() / 1000);
								var diff = now - timestamp;
								console.log('Time update: timestamp=' + timestamp + ', now=' + now + ', diff=' + diff + 's, old=' + oldTime + ', new=' + newTime);
								
								// Selalu update, tidak peduli apakah berbeda atau tidak
								if (newTime) {
									timeText.textContent = newTime;
								}
							} else {
								console.log('Time update: time-text element not found');
							}
						} else {
							console.log('Time update: Invalid timestamp: ' + timestampStr);
						}
					} else {
						console.log('Time update: No timestamp attribute');
					}
				});
			} catch (e) {
				console.error('Time update error:', e);
			}
		}
		
		var updateInterval = null;
		var initialized = false;
		
		function initTimeUpdate() {
			if (initialized) {
				console.log('Time update: Already initialized');
				return;
			}
			initialized = true;
			
			console.log('Time update: Initializing...');
			
			// Update segera
			updateTimeAgo();
			
			// Hapus interval sebelumnya jika ada
			if (updateInterval) {
				clearInterval(updateInterval);
			}
			
			// Set interval baru - update setiap 5 detik untuk responsif
			updateInterval = setInterval(updateTimeAgo, 5000);
			console.log('Time update: Interval set to 5 seconds');
		}
		
		// Tunggu sampai semua script dimuat
		function waitForScripts() {
			// Pastikan jQuery sudah dimuat
			if (typeof jQuery !== 'undefined') {
				jQuery(document).ready(function() {
					setTimeout(initTimeUpdate, 100);
				});
			} else {
				// Jika jQuery belum ada, tunggu sedikit lagi
				setTimeout(function() {
					if (typeof jQuery !== 'undefined') {
						jQuery(document).ready(function() {
							setTimeout(initTimeUpdate, 100);
						});
					} else {
						// Fallback tanpa jQuery
						if (document.readyState === 'complete' || document.readyState === 'interactive') {
							setTimeout(initTimeUpdate, 200);
						} else {
							window.addEventListener('load', function() {
								setTimeout(initTimeUpdate, 200);
							});
						}
					}
				}, 500);
			}
		}
		
		// Mulai proses
		if (document.readyState === 'complete' || document.readyState === 'interactive') {
			waitForScripts();
		} else {
			window.addEventListener('load', waitForScripts);
			document.addEventListener('DOMContentLoaded', waitForScripts);
		}
		
		// Fallback: update setelah 1 detik dan 2 detik
		setTimeout(function() {
			if (!initialized) {
				initTimeUpdate();
			} else {
				// Jika sudah initialized, tetap update untuk memastikan
				updateTimeAgo();
			}
		}, 1000);
		
		setTimeout(function() {
			if (!initialized) {
				initTimeUpdate();
			} else {
				// Jika sudah initialized, tetap update untuk memastikan
				updateTimeAgo();
			}
		}, 2000);
		
		// Update setiap 3 detik juga sebagai backup
		setInterval(function() {
			if (initialized) {
				updateTimeAgo();
			}
		}, 3000);
	})();
	</script>