<?php
include "../../inc/koneksi.php";
include "../../inc/activity_log.php";
createActivityTable($koneksi);
cleanupOldActivities($koneksi);

// Ambil jumlah aktivitas
$total_activities = getActivityCount($koneksi);

// Ambil aktivitas terbaru
$activities = getRecentActivities($koneksi, 100);
?>

<section class="content-header">
	<h1>
		<i class="fa fa-history"></i> Aktivitas Terbaru
		<small>Log Aktivitas Sistem</small>
	</h1>
	<ol class="breadcrumb">
		<li>
			<a href="index.php">
				<i class="fa fa-home"></i>
				<b>e-TABS</b>
			</a>
		</li>
		<li class="active">Aktivitas Terbaru</li>
	</ol>
</section>

<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box box-primary">
				<div class="box-header with-border">
					<h3 class="box-title">
						<i class="fa fa-list"></i> Timeline Aktivitas
					</h3>
					<div class="box-tools pull-right">
						<span class="label label-info" style="font-size: 14px; padding: 5px 10px;">
							<i class="fa fa-bell"></i> Total: <?php echo number_format($total_activities); ?> Aktivitas
						</span>
						<button type="button" class="btn btn-box-tool" data-widget="collapse">
							<i class="fa fa-minus"></i>
						</button>
					</div>
				</div>
				<div class="box-body">
					<?php if (empty($activities)): ?>
					<div class="alert alert-info">
						<i class="fa fa-info-circle"></i> Belum ada aktivitas yang tercatat.
					</div>
					<?php else: ?>
					<ul class="timeline">
						<?php
						$current_date = '';
						foreach ($activities as $index => $activity):
							$activity_date = date('Y-m-d', strtotime($activity['created_at']));
							$activity_time = date('H:i:s', strtotime($activity['created_at']));
							$time_ago = getTimeAgo($activity['created_at']);
							
							// Tampilkan label tanggal jika berbeda dengan sebelumnya
							if ($current_date != $activity_date):
								$current_date = $activity_date;
								$date_label = date('d M Y', strtotime($activity_date));
								if ($activity_date == date('Y-m-d')) {
									$date_label = 'Hari Ini';
								} elseif ($activity_date == date('Y-m-d', strtotime('-1 day'))) {
									$date_label = 'Kemarin';
								}
						?>
						<li class="time-label">
							<span class="bg-blue">
								<?php echo $date_label; ?>
							</span>
						</li>
						<?php endif; ?>
						
						<?php
						// Pastikan format datetime benar
						$created_at = $activity['created_at'];
						// Konversi datetime MySQL ke timestamp Unix
						$timestamp = strtotime($created_at);
						// Jika strtotime gagal, coba format lain
						if (!$timestamp && !empty($created_at)) {
							// Coba parse dengan DateTime
							try {
								$dt = new DateTime($created_at);
								$timestamp = $dt->getTimestamp();
							} catch (Exception $e) {
								$timestamp = time(); // Fallback ke waktu sekarang
							}
						}
						// Pastikan timestamp valid
						if (!$timestamp || $timestamp <= 0) {
							$timestamp = time();
						}
						?>
						<li>
							<i class="fa <?php echo htmlspecialchars($activity['icon']); ?> bg-<?php echo htmlspecialchars($activity['color']); ?>"></i>
							<div class="timeline-item">
								<span class="time" data-timestamp="<?php echo $timestamp; ?>">
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
										<i class="fa fa-table"></i> Tabel: <strong><?php echo htmlspecialchars($activity['table_name']); ?></strong>
										<?php if ($activity['record_id']): ?>
										| ID: <strong><?php echo htmlspecialchars($activity['record_id']); ?></strong>
										<?php endif; ?>
									</small>
									<?php endif; ?>
								</div>
								<div class="timeline-footer">
									<small class="text-muted">
										<i class="fa fa-calendar"></i> <?php echo date('d/m/Y H:i:s', strtotime($activity['created_at'])); ?>
									</small>
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
	margin-bottom: 50px;
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
	font-size: 16px;
	line-height: 1.1;
}

.timeline > li > .timeline-item > .timeline-body,
.timeline > li > .timeline-item > .timeline-footer {
	padding: 10px;
}

.timeline > li > .timeline-item > .timeline-body {
	font-size: 14px;
}

.timeline > li > .timeline-item > .timeline-footer {
	border-top: 1px solid #f4f4f4;
}

.timeline > li.time-label > span {
	font-weight: 600;
	padding: 5px 10px;
	display: inline-block;
	background-color: #fff;
	border-radius: 4px;
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

.timeline > li > .fa,
.timeline > li > .glyphicon,
.timeline > li > .ion {
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

.timeline > li > .bg-blue {
	background-color: #3c8dbc !important;
}

.timeline > li > .bg-green {
	background-color: #00a65a !important;
}

.timeline > li > .bg-yellow {
	background-color: #f39c12 !important;
}

.timeline > li > .bg-red {
	background-color: #dd4b39 !important;
}

.timeline > li > .bg-aqua {
	background-color: #00c0ef !important;
}

.timeline > li > .bg-purple {
	background-color: #605ca8 !important;
}

.timeline > li > .bg-maroon {
	background-color: #d81b60 !important;
}

.timeline > li > .bg-gray {
	background-color: #d2d6de !important;
}

.timeline > li > .bg-navy {
	background-color: #001f3f !important;
}

.timeline > li > .bg-teal {
	background-color: #39cccc !important;
}

.timeline > li > .bg-lime {
	background-color: #01ff70 !important;
}

.timeline > li > .bg-orange {
	background-color: #ff851b !important;
}

.timeline > li > .bg-fuchsia {
	background-color: #f012be !important;
}
</style>

<?php
/**
 * Fungsi untuk menghitung waktu yang lalu
 */
function getTimeAgo($datetime) {
	if (empty($datetime)) return '';
	$timestamp = strtotime($datetime);
	if (!$timestamp) return '';
	$now = time();
	$diff = $now - $timestamp;
	
	// Pastikan diff positif (tidak ada data masa depan)
	if ($diff < 0) return 'Baru saja';
	
	if ($diff < 60) {
		return 'Baru saja';
	} elseif ($diff < 3600) {
		$minutes = floor($diff / 60);
		return $minutes . ' menit yang lalu';
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
?>

<script>
// Fungsi untuk update waktu aktivitas secara real-time
(function() {
	function getTimeAgo(timestamp) {
		var now = Math.floor(Date.now() / 1000);
		var diff = now - timestamp;
		
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
		var timeElements = document.querySelectorAll('.time[data-timestamp]');
		timeElements.forEach(function(element) {
			var timestamp = parseInt(element.getAttribute('data-timestamp'));
			if (timestamp) {
				var timeText = element.querySelector('.time-text');
				if (timeText) {
					timeText.textContent = getTimeAgo(timestamp);
				}
			}
		});
	}
	
	// Update segera dan setiap 10 detik
	function initTimeUpdate() {
		updateTimeAgo();
		setInterval(updateTimeAgo, 10000); // Update setiap 10 detik
	}
	
	if (document.readyState === 'complete' || document.readyState === 'interactive') {
		// Halaman sudah dimuat
		setTimeout(initTimeUpdate, 100);
	} else {
		window.addEventListener('load', function() {
			setTimeout(initTimeUpdate, 100);
		});
	}
	
	// Juga update saat DOM ready (jika jQuery tersedia)
	if (typeof jQuery !== 'undefined') {
		jQuery(document).ready(function() {
			setTimeout(initTimeUpdate, 100);
		});
	}
})();
</script>

