<?php
// Gunakan path absolut untuk menghindari masalah di hosting
$base_path = dirname(dirname(__DIR__));
$koneksi_path = $base_path . '/inc/koneksi.php';
$activity_log_path = $base_path . '/inc/activity_log.php';

if (file_exists($koneksi_path)) {
    include $koneksi_path;
} else {
    include "../../inc/koneksi.php";
}

if (file_exists($activity_log_path)) {
    include $activity_log_path;
} else {
    include "../../inc/activity_log.php";
}

// Hapus aktivitas lama saat halaman dimuat
deleteOldActivities($koneksi);

// Ambil aktivitas terbaru
$activities = getRecentActivities($koneksi, 100);
$activity_count = getActivityCount($koneksi);
?>

<section class="content-header">
	<h1>
		<i class="fa fa-history"></i> Aktivitas Terbaru
		<small>Log semua aktivitas sistem</small>
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
						<span class="label label-primary">
							<i class="fa fa-bell"></i> <?php echo $activity_count; ?> Aktivitas
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
							
							// Tampilkan tanggal jika berbeda dengan sebelumnya
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
						
						<li>
							<i class="fa <?php echo getActivityIcon($activity['action']); ?>"></i>
							<div class="timeline-item">
								<span class="time">
									<i class="fa fa-clock-o"></i> <?php echo $activity_time; ?>
									<small class="text-muted">(<?php echo $time_ago; ?>)</small>
								</span>
								<h3 class="timeline-header">
									<span class="label label-<?php echo getActivityBadge($activity['action']); ?>">
										<?php echo $activity['action']; ?>
									</span>
									<?php if (!empty($activity['user_name'])): ?>
									<strong><?php echo htmlspecialchars($activity['user_name']); ?></strong>
									<?php endif; ?>
								</h3>
								<div class="timeline-body">
									<?php echo htmlspecialchars($activity['description']); ?>
									<br>
									<small class="text-muted">
										<i class="fa fa-table"></i> Tabel: <strong><?php echo htmlspecialchars($activity['table_name']); ?></strong>
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
				<div class="box-footer">
					<small class="text-muted">
						<i class="fa fa-info-circle"></i> Aktivitas akan otomatis terhapus setelah 24 jam.
					</small>
				</div>
			</div>
		</div>
	</div>
</section>

<?php
/**
 * Fungsi untuk menghitung waktu yang lalu
 */
function getTimeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return 'Baru saja';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' menit yang lalu';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' jam yang lalu';
    } else {
        $days = floor($diff / 86400);
        return $days . ' hari yang lalu';
    }
}
?>

<style>
.timeline {
	position: relative;
	padding: 20px 0;
	list-style: none;
}

.timeline:before {
	content: ' ';
	position: absolute;
	top: 0;
	bottom: 0;
	left: 40px;
	width: 3px;
	margin-left: -1.5px;
	background-color: #e0e0e0;
}

.timeline > li {
	position: relative;
	margin-bottom: 20px;
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
	position: relative;
	margin-left: 80px;
	margin-right: 15px;
	padding: 15px;
	background-color: #fff;
	border: 1px solid #ddd;
	border-radius: 4px;
	box-shadow: 0 1px 1px rgba(0,0,0,0.1);
}

.timeline > li > .fa {
	position: absolute;
	left: 15px;
	top: 16px;
	width: 50px;
	height: 50px;
	font-size: 20px;
	line-height: 50px;
	text-align: center;
	background-color: #fff;
	border: 3px solid #e0e0e0;
	border-radius: 50%;
	z-index: 100;
}

.timeline > li.time-label > span {
	position: relative;
	padding: 5px 10px;
	background-color: #3c8dbc;
	color: #fff;
	border-radius: 4px;
	font-weight: bold;
	z-index: 100;
}

.timeline > li.time-label {
	margin-bottom: 0;
}

.timeline > li.time-label:before {
	display: none;
}

.timeline-header {
	margin: 0 0 10px 0;
	font-size: 16px;
	font-weight: 600;
}

.timeline-body {
	margin-top: 10px;
}

.timeline-footer {
	margin-top: 10px;
}

.timeline .time {
	color: #999;
	font-size: 12px;
	float: right;
}
</style>

