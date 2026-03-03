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
</section>

<section class="content">
	<div class="rounded-2xl bg-white shadow-sm">
		<div class="flex flex-col md:flex-row items-center justify-between gap-4 border-b border-slate-100 px-6 py-4">
			<h3 class="text-lg font-semibold text-slate-900">
				<i class="fa-solid fa-list-ul text-indigo-500 mr-2"></i> Timeline Aktivitas
			</h3>
			<span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-700/10">
				<i class="fa-solid fa-bell mr-1.5"></i> <?php echo $activity_count; ?> Aktivitas
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
				<div class="relative flex items-center justify-center md:justify-between md:gap-10 before:absolute before:left-0 before:ml-5 before:h-full before:w-0.5 before:-translate-x-px before:bg-slate-200 before:md:ml-auto before:md:mr-auto before:md:left-1/2 before:hidden">
					<!-- Date separator -->
				</div>
				<div class="relative z-10 text-center my-8 first:mt-0">
					<span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700 ring-1 ring-inset ring-slate-200">
						<?php echo $date_label; ?>
					</span>
				</div>
				<?php endif; ?>
				
				<?php
				// Map badge colors
				$badge_type = getActivityBadge($activity['action']);
				$badge_class = 'bg-slate-50 text-slate-600 ring-slate-500/10'; // default
				$icon_bg = 'bg-slate-500';
				
				switch($badge_type) {
					case 'success': $badge_class = 'bg-emerald-50 text-emerald-600 ring-emerald-600/10'; $icon_bg = 'bg-emerald-500'; break;
					case 'danger': $badge_class = 'bg-rose-50 text-rose-600 ring-rose-600/10'; $icon_bg = 'bg-rose-500'; break;
					case 'warning': $badge_class = 'bg-amber-50 text-amber-600 ring-amber-600/10'; $icon_bg = 'bg-amber-500'; break;
					case 'info': $badge_class = 'bg-blue-50 text-blue-600 ring-blue-600/10'; $icon_bg = 'bg-blue-500'; break;
					case 'primary': $badge_class = 'bg-indigo-50 text-indigo-600 ring-indigo-600/10'; $icon_bg = 'bg-indigo-500'; break;
				}
				?>
				
				<div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
					<!-- Icon -->
					<div class="absolute left-0 ml-5 -translate-x-1/2 flex h-10 w-10 items-center justify-center rounded-full border-4 border-white"><?php echo $icon_bg; ?> text-white shadow-sm  md:left-1/2 md:translate-y-0">
						<i class="fa"><?php echo getActivityIcon($activity['action']); ?> text-sm"></i>
					</div>
					
					<!-- Card -->
					<div class="ml-16 w-full rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200   md:ml-0 md:w-[calc(50%-2.5rem)] hover:bg-slate-50  transition-all">
						<div class="flex items-center justify-between gap-x-4 border-b border-slate-200 pb-2 mb-2">
							<div class="text-xs text-slate-600">
								<i class="fa-regular fa-clock mr-1"></i> <?php echo $time_ago; ?>
							</div>
							<div class="text-xs text-slate-500">
								<?php echo $activity_time; ?>
							</div>
						</div>
						
						<div class="group relative">
							<h3 class="mt-1 text-sm font-semibold leading-6 text-slate-800  flex items-center gap-2 flex-wrap">
								<span class="inline-flex items-center rounded-md"><?php echo $badge_class; ?> px-2 py-1 text-xs font-medium ring-1 ring-inset">
									<?php echo $activity['action']; ?>
								</span>
								<?php if (!empty($activity['user_name'])): ?>
								<span><?php echo htmlspecialchars($activity['user_name']); ?></span>
								<?php endif; ?>
							</h3>
							<p class="mt-2 text-sm text-slate-700  line-clamp-3">
								<?php echo htmlspecialchars($activity['description']); ?>
							</p>
						</div>
						
						<div class="mt-3 flex items-center gap-x-2 text-xs leading-5 text-slate-600  border-t border-slate-200 pt-2">
							<div class="flex items-center gap-1">
								<i class="fa-solid fa-table"></i>
								<span>Tabel: <strong><?php echo htmlspecialchars($activity['table_name']); ?></strong></span>
							</div>
						</div>
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
		<div class="px-6 py-4 bg-white border-t border-slate-200">
			<small class="text-slate-600  flex items-center gap-2">
				<i class="fa-solid fa-circle-info"></i> Aktivitas akan otomatis terhapus setelah 24 jam.
			</small>
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



