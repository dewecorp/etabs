<?php
// Pastikan activity_log.php sudah di-include
// Gunakan path absolut untuk menghindari masalah di hosting
if (!function_exists('createActivityTable')) {
    $activity_log_path = __DIR__ . '/../inc/activity_log.php';
    if (file_exists($activity_log_path)) {
        include_once $activity_log_path;
    } else {
        // Fallback ke path relatif
        include_once "../inc/activity_log.php";
    }
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

<!-- Info atas -->
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
    <div>
        <p class="text-xs font-medium uppercase tracking-[0.18em] text-indigo-600">Ringkasan Dashboard</p>
        <h2 class="mt-1 text-xl font-semibold tracking-tight text-slate-900 sm:text-2xl">
            Selamat Datang, Administrator
        </h2>
    </div>
    <div class="flex items-center gap-2 text-[11px] text-slate-500">
        <span class="inline-flex items-center rounded-full bg-emerald-500/10 px-2 py-1 text-[10px] font-semibold text-emerald-600 ring-1 ring-emerald-500/30">
            <i class="fa-solid fa-circle text-[6px] mr-2"></i> Sistem Stabil
        </span>
    </div>
</div>

<!-- Kartu metrik -->
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <!-- Siswa Aktif -->
    <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-xl transition-all hover:border-indigo-500/50 hover:bg-slate-50">
        <div class="relative flex items-start justify-between">
            <div>
                <p class="text-[11px] font-medium uppercase tracking-wide text-slate-500">Siswa Aktif</p>
                <p class="mt-2 text-3xl font-bold text-slate-900 metric-value"><?= is_numeric($siswa) ? $siswa : 0; ?></p>
            </div>
            <div class="rounded-xl bg-indigo-500/10 p-3 ring-1 ring-indigo-500/30">
                <i class="fa-solid fa-users text-indigo-600 text-lg"></i>
            </div>
        </div>
        <div class="mt-4 flex items-center justify-between text-[11px]">
            <a href="?page=MyApp/data_siswa" class="text-indigo-600 hover:text-indigo-600 font-medium transition-colors">
                Lihat detail <i class="fa-solid fa-arrow-right ml-1 text-[10px]"></i>
            </a>
        </div>
    </div>

    <!-- Total Setoran -->
    <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-xl transition-all hover:border-emerald-500/50 hover:bg-slate-50">
        <div class="relative flex items-start justify-between">
            <div>
                <p class="text-[11px] font-medium uppercase tracking-wide text-slate-500">Total Setoran</p>
                <p class="mt-2 text-xl font-bold text-slate-900 metric-value"><?= rupiah(is_numeric($setor) ? $setor : 0); ?></p>
            </div>
            <div class="rounded-xl bg-emerald-500/10 p-3 ring-1 ring-emerald-500/30">
                <i class="fa-solid fa-arrow-down-long text-emerald-600 text-lg"></i>
            </div>
        </div>
        <div class="mt-4 flex items-center justify-between text-[11px]">
            <a href="?page=data_setor" class="text-emerald-600 hover:text-emerald-700 font-medium transition-colors">
                Lihat detail <i class="fa-solid fa-arrow-right ml-1 text-[10px]"></i>
            </a>
        </div>
    </div>

    <!-- Total Penarikan -->
    <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-xl transition-all hover:border-rose-500/50 hover:bg-slate-50">
        <div class="relative flex items-start justify-between">
            <div>
                <p class="text-[11px] font-medium uppercase tracking-wide text-slate-500">Total Penarikan</p>
                <p class="mt-2 text-xl font-bold text-slate-900 metric-value"><?= rupiah(is_numeric($tarik) ? $tarik : 0); ?></p>
            </div>
            <div class="rounded-xl bg-rose-500/10 p-3 ring-1 ring-rose-500/30">
                <i class="fa-solid fa-arrow-up-long text-rose-600 text-lg"></i>
            </div>
        </div>
        <div class="mt-4 flex items-center justify-between text-[11px]">
            <a href="?page=data_tarik" class="text-rose-600 hover:text-rose-700 font-medium transition-colors">
                Lihat detail <i class="fa-solid fa-arrow-right ml-1 text-[10px]"></i>
            </a>
        </div>
    </div>

    <!-- Saldo Kas -->
    <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-xl transition-all hover:border-amber-500/50 hover:bg-slate-50">
        <div class="relative flex items-start justify-between">
            <div>
                <p class="text-[11px] font-medium uppercase tracking-wide text-slate-500">Saldo Kas</p>
                <p class="mt-2 text-xl font-bold text-slate-900 metric-value"><?= rupiah(is_numeric($saldo) ? $saldo : 0); ?></p>
            </div>
            <div class="rounded-xl bg-amber-500/10 p-3 ring-1 ring-amber-500/30">
                <i class="fa-solid fa-wallet text-amber-600 text-lg"></i>
            </div>
        </div>
        <div class="mt-4 flex items-center justify-between text-[11px]">
            <a href="?page=view_kas" class="text-amber-600 hover:text-amber-300 font-medium transition-colors">
                Lihat detail <i class="fa-solid fa-arrow-right ml-1 text-[10px]"></i>
            </a>
        </div>
    </div>
</div>

<!-- Visualisasi & Aktivitas -->
<div class="grid gap-4 lg:grid-cols-1 mt-6">
    <!-- Chart Section -->
    <div class="lg:col-span-1 rounded-2xl border border-slate-200 bg-white p-6 shadow-xl">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-sm font-semibold text-slate-900">Statistik Tabungan</h3>
                <p class="text-[11px] text-slate-500">Perbandingan setoran dan penarikan</p>
            </div>
            <div class="flex gap-2">
                <span class="inline-flex items-center gap-1.5 text-[10px] text-slate-500">
                    <span class="h-2 w-2 rounded-full bg-indigo-500"></span> Setoran
                </span>
                <span class="inline-flex items-center gap-1.5 text-[10px] text-slate-500">
                    <span class="h-2 w-2 rounded-full bg-rose-500"></span> Penarikan
                </span>
            </div>
        </div>
        <div class="h-64 w-full">
            <canvas id="savingChart"></canvas>
        </div>
    </div>

    <!-- Aktivitas Terbaru (Tailwind Timeline) -->
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-slate-900">Aktivitas Terbaru</h3>
            <?php
            $activity_count = 0;
            if (function_exists('getActivityCount') && isset($koneksi)) {
                try {
                    if (function_exists('createActivityTable')) { @createActivityTable($koneksi); }
                    if (function_exists('cleanupOldActivities')) { @cleanupOldActivities($koneksi); }
                    $activity_count = @getActivityCount($koneksi);
                } catch (Exception $e) { $activity_count = 0; }
            }
            ?>
            <span class="inline-flex items-center rounded-full bg-indigo-500/10 px-2 py-1 text-[10px] font-semibold text-indigo-600 ring-1 ring-indigo-500/30">
                Total: <?= number_format($activity_count); ?> aktivitas
            </span>
        </div>
        <div class="max-h-[500px] overflow-y-auto">
            <?php
            if (!function_exists('getTimeAgo')) {
                function getTimeAgo($datetime) {
                    if (empty($datetime)) return '';
                    $timestamp = false;
                    try { $dt = new DateTime($datetime); $timestamp = $dt->getTimestamp(); } catch (Exception $e) { $timestamp = @strtotime($datetime); }
                    if (!$timestamp || $timestamp <= 0) { $timestamp = @strtotime(str_replace('/', '-', $datetime)); if (!$timestamp || $timestamp <= 0) { return ''; } }
                    $diff = time() - $timestamp; if ($diff < 0) { return 'Baru saja'; }
                    if ($diff < 60) return 'Baru saja';
                    if ($diff < 3600) return floor($diff/60) . ' menit yang lalu';
                    if ($diff < 86400) return floor($diff/3600) . ' jam yang lalu';
                    if ($diff < 604800) return floor($diff/86400) . ' hari yang lalu';
                    return date('d M Y', $timestamp);
                }
            }
            $recent_activities = [];
            if (isset($koneksi) && $koneksi) {
                try {
                    if (function_exists('createActivityTable')) { @createActivityTable($koneksi); }
                    $check_table = @$koneksi->query("SHOW TABLES LIKE 'tb_activity_log'");
                    if ($check_table && $check_table->num_rows > 0) {
                        $sql = "SELECT *, UNIX_TIMESTAMP(created_at) as timestamp_unix FROM tb_activity_log ORDER BY created_at DESC LIMIT 10";
                        $result = @$koneksi->query($sql);
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) { $recent_activities[] = $row; }
                        }
                    }
                } catch (Exception $e) { $recent_activities = []; }
            }
            if (empty($recent_activities)) {
                echo '<div class="text-[12px] text-slate-500">Belum ada aktivitas yang tercatat.</div>';
            } else {
                echo '<ol class="relative pl-8">';
                $total_items = count($recent_activities);
                $item_index = 0;
                foreach ($recent_activities as $activity) {
                    $item_index++;
                    $timestamp = isset($activity['timestamp_unix']) && $activity['timestamp_unix'] > 0 ? (int)$activity['timestamp_unix'] : time();
                    $time_ago = getTimeAgo(isset($activity['created_at']) ? $activity['created_at'] : date('Y-m-d H:i:s'));
                    $color = isset($activity['color']) ? strtolower($activity['color']) : 'indigo';
                    $tw = 'indigo';
                    if (in_array($color, ['success'])) $tw = 'emerald';
                    elseif (in_array($color, ['danger'])) $tw = 'rose';
                    elseif (in_array($color, ['warning'])) $tw = 'amber';
                    elseif (in_array($color, ['info','primary'])) $tw = 'sky';
                    $icon = isset($activity['icon']) ? $activity['icon'] : 'fa-info-circle';
                    echo '<li class="mb-6 relative">';
                    echo '<span class="absolute left-0 -translate-x-1/2 z-20 flex h-6 w-6 items-center justify-center rounded-full bg-'.$tw.'-500/10 ring-1 ring-'.$tw.'-500/30 text-'.$tw.'-400">';
                    echo '<i class="fa '.$icon.' text-[12px]"></i>';
                    echo '</span>';
                    $connectorClass = 'absolute left-0 -translate-x-1/2 w-px bg-gradient-to-b from-'.$tw.'-400/30 to-'.$tw.'-400/5';
                    if ($item_index === 1 && $total_items > 1) {
                        // Pertama: mulai dari bawah bullet ke bawah item
                        echo '<span class="'.$connectorClass.' top-6 bottom-0"></span>';
                    } elseif ($item_index < $total_items) {
                        // Tengah: garis penuh melewati bullet atas ke bawah item
                        echo '<span class="'.$connectorClass.' top-0 bottom-0"></span>';
                    } else {
                        // Terakhir: sambungkan ke bullet sebelumnya (garis dari atas sampai tengah bullet)
                        echo '<span class="'.$connectorClass.' top-0 h-6"></span>';
                    }
                    echo '<div class="p-3 rounded-xl bg-white border border-slate-200 hover:bg-slate-50">';
                    echo '<div class="flex items-center justify-between">';
                    echo '<p class="text-xs text-slate-700"><span class="font-semibold">'.htmlspecialchars($activity['user_name']).'</span> ';
                    echo '<span class="uppercase tracking-wide text-'.$tw.'-600 ml-1">'.htmlspecialchars($activity['action']).'</span>';
                    if (!empty($activity['user_level'])) { echo ' <span class="text-slate-500">('.htmlspecialchars($activity['user_level']).')</span>'; }
                    echo '</p>';
                    echo '<span class="text-[11px] text-slate-500">'.$time_ago.'</span>';
                    echo '</div>';
                    if (!empty($activity['description'])) {
                        echo '<p class="mt-2 text-[12px] text-slate-700">'.htmlspecialchars($activity['description']).'</p>';
                    }
                    if (!empty($activity['table_name'])) {
                        echo '<p class="mt-1 text-[11px] text-slate-600"><i class="fa fa-table mr-1"></i>'.htmlspecialchars($activity['table_name']).'</p>';
                    }
                    echo '</div>';
                    echo '</li>';
                }
                echo '</ol>';
            }
            ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('savingChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Setoran',
                    data: [12, 19, 3, 5, 2, 3],
                    borderColor: '#6366f1',
                    tension: 0.4,
                    fill: true,
                    backgroundColor: 'rgba(99, 102, 241, 0.1)'
                }, {
                    label: 'Penarikan',
                    data: [7, 11, 5, 8, 3, 6],
                    borderColor: '#f43f5e',
                    tension: 0.4,
                    fill: true,
                    backgroundColor: 'rgba(244, 63, 94, 0.1)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: { color: '#64748b', font: { size: 10 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748b', font: { size: 10 } }
                    }
                }
            }
        });
    });
</script>



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
					return; // Tidak ada elemen yang ditemukan
				}
				
				timeElements.forEach(function(element) {
					var timestampStr = element.getAttribute('data-timestamp');
					if (timestampStr) {
						var timestamp = parseInt(timestampStr, 10);
						if (timestamp && timestamp > 0) {
							var timeText = element.querySelector('.time-text');
							if (timeText) {
								var newTime = getTimeAgo(timestamp);
								
								// Selalu update, tidak peduli apakah berbeda atau tidak
								if (newTime) {
									timeText.textContent = newTime;
								}
							}
						}
					}
				});
			} catch (e) {
				// Silent error handling
			}
		}
		
		var updateInterval = null;
		var initialized = false;
		
		function initTimeUpdate() {
			if (initialized) {
				return;
			}
			initialized = true;
			
			// Update segera
			updateTimeAgo();
			
			// Hapus interval sebelumnya jika ada
			if (updateInterval) {
				clearInterval(updateInterval);
			}
			
			// Set interval baru - update setiap 5 detik untuk responsif
			updateInterval = setInterval(updateTimeAgo, 5000);
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

