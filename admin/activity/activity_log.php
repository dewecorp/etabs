<?php
include "../../inc/activity_log.php";

// Hapus aktivitas lama
cleanupOldActivities($koneksi);

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
						<i class="fa fa-clock-o"></i> Timeline Aktivitas
					</h3>
					<div class="box-tools pull-right">
						<span class="label label-info" style="font-size: 14px; padding: 5px 10px;">
							<i class="fa fa-list"></i> Total: <?php echo $activity_count; ?> aktivitas
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
								<i class="fa fa-calendar"></i> <?php echo $date_label; ?>
							</span>
						</li>
						<?php endif; ?>
						
						<li>
							<i class="fa <?php echo htmlspecialchars($activity['icon']); ?> bg-<?php echo htmlspecialchars($activity['color']); ?>"></i>
							<div class="timeline-item">
								<span class="time">
									<i class="fa fa-clock-o"></i> <?php echo $time_ago; ?>
								</span>
								<h3 class="timeline-header">
									<strong><?php echo htmlspecialchars($activity['user_name']); ?></strong>
									<span class="label label-<?php echo htmlspecialchars($activity['color']); ?>" style="margin-left: 5px;">
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
										<i class="fa fa-clock-o"></i> <?php echo date('d/m/Y H:i:s', strtotime($activity['created_at'])); ?>
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
	padding: 0;
	list-style: none;
	margin: 0;
}

.timeline:before {
	content: '';
	position: absolute;
	top: 0;
	bottom: 0;
	left: 40px;
	width: 2px;
	margin-left: -1.5px;
	background-color: #ddd;
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
	left: 15px;
	top: 0;
}

.timeline > li > i.bg-blue {
	background-color: #3c8dbc;
}

.timeline > li > i.bg-green {
	background-color: #00a65a;
}

.timeline > li > i.bg-yellow {
	background-color: #f39c12;
}

.timeline > li > i.bg-red {
	background-color: #dd4b39;
}

.timeline > li > i.bg-aqua {
	background-color: #00c0ef;
}

.timeline > li > i.bg-gray {
	background-color: #d2d6de;
}

.timeline > li > i.bg-success {
	background-color: #00a65a;
}

.timeline > li > i.bg-info {
	background-color: #00c0ef;
}

.timeline > li > i.bg-warning {
	background-color: #f39c12;
}

.timeline > li > i.bg-danger {
	background-color: #dd4b39;
}

.timeline > li > i.bg-primary {
	background-color: #3c8dbc;
}

.timeline > li > i.bg-default {
	background-color: #d2d6de;
}
</style>

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
		$mins = floor($diff / 60);
		return $mins . ' menit yang lalu';
	} elseif ($diff < 86400) {
		$hours = floor($diff / 3600);
		return $hours . ' jam yang lalu';
	} elseif ($diff < 604800) {
		$days = floor($diff / 86400);
		return $days . ' hari yang lalu';
	} else {
		return date('d/m/Y H:i', $timestamp);
	}
}
?>

