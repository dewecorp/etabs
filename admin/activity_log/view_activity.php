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
</section>

<section class="content">
	<div class="rounded-2xl bg-white shadow-sm">
		<div class="flex flex-col md:flex-row items-center justify-between gap-4 border-b border-slate-100 px-6 py-4">
			<h3 class="text-lg font-semibold text-slate-900">
				<i class="fa-solid fa-list-ul text-indigo-500 mr-2"></i> Timeline Aktivitas
			</h3>
			<span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-700/10">
				<i class="fa-solid fa-bell mr-1.5"></i> Total: <?php echo number_format($total_activities); ?> Aktivitas
			</span>
		</div>
		<div class="p-6">
			<?php if (empty($activities)): ?>
			<div class="rounded-xl border border-indigo-100 bg-indigo-50 p-4 text-sm text-indigo-700">
				<div class="flex items-center gap-3">
					<i class="fa-solid fa-circle-info text-lg"></i>
					<span>Belum ada aktivitas yang tercatat.</span>
				</div>
			</div>
			<?php else: ?>
			<div class="relative space-y-8 before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-slate-300 before:to-transparent">
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
				<div class="relative flex items-center justify-center md:justify-between md:gap-10 before:absolute before:left-0 before:ml-5 before:h-full before:w-0.5 before:-translate-x-px before:bg-slate-200 before:md:ml-auto before:md:mr-auto before:md:left-1/2 before:hidden">
					<!-- Date separator handled by spacing -->
				</div>
				<div class="relative z-10 text-center my-8 first:mt-0">
					<span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700 ring-1 ring-inset ring-slate-200">
						<?php echo $date_label; ?>
					</span>
				</div>
				<?php endif; ?>
				
				<?php
				// Pastikan format datetime benar
				$created_at = $activity['created_at'];
				$timestamp = strtotime($created_at);
				if (!$timestamp && !empty($created_at)) {
					try {
						$dt = new DateTime($created_at);
						$timestamp = $dt->getTimestamp();
					} catch (Exception $e) {
						$timestamp = time();
					}
				}
				if (!$timestamp || $timestamp <= 0) {
					$timestamp = time();
				}
				
				// Map colors
				$bg_color = 'bg-indigo-500';
				$text_color = 'text-indigo-600';
				$bg_light = 'bg-indigo-50';
				
				switch($activity['color']) {
					case 'blue': $bg_color = 'bg-blue-500'; $text_color = 'text-blue-600'; $bg_light = 'bg-blue-50'; break;
					case 'green': $bg_color = 'bg-emerald-500'; $text_color = 'text-emerald-600'; $bg_light = 'bg-emerald-50'; break;
					case 'yellow': $bg_color = 'bg-amber-500'; $text_color = 'text-amber-600'; $bg_light = 'bg-amber-50'; break;
					case 'red': $bg_color = 'bg-rose-500'; $text_color = 'text-rose-600'; $bg_light = 'bg-rose-50'; break;
					case 'purple': $bg_color = 'bg-purple-500'; $text_color = 'text-purple-600'; $bg_light = 'bg-purple-50'; break;
					default: $bg_color = 'bg-slate-500'; $text_color = 'text-slate-600'; $bg_light = 'bg-slate-50'; break;
				}
				?>
				
				<div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
					<!-- Icon -->
					<div class="absolute left-0 ml-5 -translate-x-1/2 flex h-10 w-10 items-center justify-center rounded-full border-4 border-white"><?php echo $bg_color; ?> text-white shadow-sm  md:left-1/2 md:translate-y-0">
						<i class="fa"><?php echo htmlspecialchars($activity['icon']); ?> text-sm"></i>
					</div>
					
					<!-- Card -->
					<div class="ml-16 w-full rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200   md:ml-0 md:w-[calc(50%-2.5rem)] hover:bg-slate-50  transition-all">
						<div class="flex items-center justify-between gap-x-4 border-b border-slate-200 pb-2 mb-2">
							<div class="text-xs text-slate-600  time" data-timestamp="<?php echo $timestamp; ?>">
								<i class="fa-regular fa-clock mr-1"></i> <span class="time-text"><?php echo $time_ago; ?></span>
							</div>
							<div class="text-xs text-slate-500">
								<?php echo date('H:i', strtotime($activity['created_at'])); ?>
							</div>
						</div>
						
						<div class="group relative">
							<h3 class="mt-1 text-sm font-semibold leading-6 text-slate-800  flex items-center gap-2 flex-wrap">
								<span><?php echo htmlspecialchars($activity['user_name']); ?></span>
								<span class="inline-flex items-center rounded-md"><?php echo $bg_light; ?> px-2 py-1 text-xs font-medium <?php echo $text_color; ?> ring-1 ring-inset ring-current/10">
									<?php echo htmlspecialchars($activity['action']); ?>
								</span>
								<?php if ($activity['user_level']): ?>
								<span class="text-xs font-normal text-slate-600  echo htmlspecialchars($activity['user_level']); ?>)</span>">
								<?php endif; ?>
							</h3>
							<p class="mt-2 text-sm text-slate-700  line-clamp-3">
								<?php echo htmlspecialchars($activity['description']); ?>
							</p>
						</div>
						
						<?php if ($activity['table_name']): ?>
						<div class="mt-3 flex items-center gap-x-2 text-xs leading-5 text-slate-600  border-t border-slate-200 pt-2">
							<div class="flex items-center gap-1">
								<i class="fa-solid fa-table"></i>
								<span><?php echo htmlspecialchars($activity['table_name']); ?></span>
							</div>
							<?php if ($activity['record_id']): ?>
							<div class="flex items-center gap-1 text-slate-600">
								<i class="fa-solid fa-hashtag"></i>
								<span>ID: <?php echo htmlspecialchars($activity['record_id']); ?></span>
							</div>
							<?php endif; ?>
						</div>
						<?php endif; ?>
					</div>
				</div>
				<?php endforeach; ?>
				
				<div class="relative flex items-center justify-center">
					<div class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-200 text-slate-500 ring-4 ring-white">
						<i class="fa-solid fa-check"></i>
					</div>
				</div>
			</div>
			<?php endif; ?>
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



