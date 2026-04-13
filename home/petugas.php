<?php
$sql = $koneksi->query("SELECT count(nis) as siswa  from tb_siswa where status='Aktif'");
while ($data= $sql->fetch_assoc()) {
	
	$siswa=$data['siswa'];
}
?>

<?php
$sql = $koneksi->query("SELECT SUM(setor) as Tsetor  from tb_tabungan where jenis='ST'");
while ($data= $sql->fetch_assoc()) {
	
	$setor=$data['Tsetor'];
}
?>

<?php
$sql = $koneksi->query("SELECT SUM(tarik) as Ttarik  from tb_tabungan where jenis='TR'");
while ($data= $sql->fetch_assoc()) {
	
	$tarik=$data['Ttarik'];
}

$saldo=$setor-$tarik;
?>

<?php
// Data grafik bulanan tahun berjalan
$currentYear = date('Y');
$currentMonth = (int)date('n');
$monthLabels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
$labels = array_slice($monthLabels, 0, $currentMonth);
$setoranCounts = array_fill(0, $currentMonth, 0);
$penarikanCounts = array_fill(0, $currentMonth, 0);
$sqlSetoran = @$koneksi->query("SELECT MONTH(tgl) AS m, COUNT(*) AS c FROM tb_tabungan WHERE jenis='ST' AND YEAR(tgl)='{$currentYear}' GROUP BY MONTH(tgl)");
if ($sqlSetoran) {
    while ($row = $sqlSetoran->fetch_assoc()) {
        $m = (int)$row['m'];
        if ($m >= 1 && $m <= $currentMonth) { $setoranCounts[$m - 1] = (int)$row['c']; }
    }
}
$sqlPenarikan = @$koneksi->query("SELECT MONTH(tgl) AS m, COUNT(*) AS c FROM tb_tabungan WHERE jenis='TR' AND YEAR(tgl)='{$currentYear}' GROUP BY MONTH(tgl)");
if ($sqlPenarikan) {
    while ($row = $sqlPenarikan->fetch_assoc()) {
        $m = (int)$row['m'];
        if ($m >= 1 && $m <= $currentMonth) { $penarikanCounts[$m - 1] = (int)$row['c']; }
    }
}
?>
<!-- Info atas -->
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
    <div>
        <h2 class="mt-1 text-xl font-semibold tracking-tight text-slate-900 sm:text-2xl">
            Selamat Datang, Petugas
        </h2>
        <p class="text-slate-500 text-xs mt-1">Snapshot performa sistem tabungan hari ini</p>
    </div>
    <div class="flex items-center gap-2 text-[11px] text-slate-500">
        <span id="connection-badge" class="inline-flex items-center rounded-full bg-emerald-500/10 px-2 py-1 text-[10px] font-semibold text-emerald-600 ring-1 ring-emerald-500/30 transition-all duration-300">
            <i id="connection-icon" class="fa-solid fa-circle text-[6px] mr-2"></i> 
            <span id="connection-text">Online</span>
        </span>
    </div>
</div>

<!-- Kartu metrik -->
<div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
    <!-- Siswa Aktif -->
    <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 shadow-xl transition-all hover:border-indigo-500/50 hover:bg-slate-50">
        <div class="relative flex items-start justify-between">
            <div class="min-w-0">
                <p class="text-[10px] sm:text-[11px] font-medium uppercase tracking-wide text-slate-500 truncate">Siswa Aktif</p>
                <p class="mt-1 sm:mt-2 text-2xl sm:text-3xl font-bold text-slate-900 metric-value"><?= is_numeric($siswa) ? $siswa : 0; ?></p>
            </div>
            <div class="rounded-xl bg-indigo-500/10 p-2.5 sm:p-3 ring-1 ring-indigo-500/30">
                <i class="fa-solid fa-users text-indigo-600 text-base sm:text-lg"></i>
            </div>
        </div>
        <div class="mt-3 sm:mt-4 flex items-center justify-between text-[10px] sm:text-[11px]">
            <a href="?page=petugas" class="text-indigo-600 hover:text-indigo-600 font-medium transition-colors">
                Lihat detail <i class="fa-solid fa-arrow-right ml-1 text-[9px] sm:text-[10px]"></i>
            </a>
        </div>
    </div>

    <!-- Total Setoran -->
    <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 shadow-xl transition-all hover:border-emerald-500/50 hover:bg-slate-50">
        <div class="relative flex items-start justify-between">
            <div class="min-w-0">
                <p class="text-[10px] sm:text-[11px] font-medium uppercase tracking-wide text-slate-500 truncate">Total Setoran</p>
                <p class="mt-1 sm:mt-2 text-lg sm:text-xl font-bold text-slate-900"><?= rupiah($setor); ?></p>
            </div>
            <div class="rounded-xl bg-emerald-500/10 p-2.5 sm:p-3 ring-1 ring-emerald-500/30">
                <i class="fa-solid fa-arrow-down-long text-emerald-600 text-base sm:text-lg"></i>
            </div>
        </div>
        <div class="mt-3 sm:mt-4 flex items-center justify-between text-[10px] sm:text-[11px]">
            <a href="?page=data_setor" class="text-emerald-600 hover:text-emerald-700 font-medium transition-colors">
                Lihat detail <i class="fa-solid fa-arrow-right ml-1 text-[9px] sm:text-[10px]"></i>
            </a>
        </div>
    </div>

    <!-- Total Penarikan -->
    <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 shadow-xl transition-all hover:border-rose-500/50 hover:bg-slate-50">
        <div class="relative flex items-start justify-between">
            <div class="min-w-0">
                <p class="text-[10px] sm:text-[11px] font-medium uppercase tracking-wide text-slate-500 truncate">Total Penarikan</p>
                <p class="mt-1 sm:mt-2 text-lg sm:text-xl font-bold text-slate-900 metric-value"><?= rupiah(is_numeric($tarik) ? $tarik : 0); ?></p>
            </div>
            <div class="rounded-xl bg-rose-500/10 p-2.5 sm:p-3 ring-1 ring-rose-500/30">
                <i class="fa-solid fa-arrow-up-long text-rose-600 text-base sm:text-lg"></i>
            </div>
        </div>
        <div class="mt-3 sm:mt-4 flex items-center justify-between text-[10px] sm:text-[11px]">
            <a href="?page=data_setor" class="text-rose-600 hover:text-rose-700 font-medium transition-colors">
                Lihat detail <i class="fa-solid fa-arrow-right ml-1 text-[9px] sm:text-[10px]"></i>
            </a>
        </div>
    </div>

    <!-- Saldo Kas -->
    <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 shadow-xl transition-all hover:border-amber-500/50 hover:bg-slate-50">
        <div class="relative flex items-start justify-between">
            <div class="min-w-0">
                <p class="text-[10px] sm:text-[11px] font-medium uppercase tracking-wide text-slate-500 truncate">Saldo Kas</p>
                <p class="mt-1 sm:mt-2 text-lg sm:text-xl font-bold text-slate-900 metric-value"><?= rupiah(is_numeric($saldo) ? $saldo : 0); ?></p>
            </div>
            <div class="rounded-xl bg-amber-500/10 p-2.5 sm:p-3 ring-1 ring-amber-500/30">
                <i class="fa-solid fa-wallet text-amber-600 text-base sm:text-lg"></i>
            </div>
        </div>
        <div class="mt-3 sm:mt-4 flex items-center justify-between text-[10px] sm:text-[11px]">
            <a href="?page=view_kas" class="text-amber-600 hover:text-amber-300 font-medium transition-colors">
                Lihat detail <i class="fa-solid fa-arrow-right ml-1 text-[9px] sm:text-[10px]"></i>
            </a>
        </div>
    </div>
</div>

<div class="grid gap-4 lg:grid-cols-3 mt-6">
    <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white p-4 sm:p-6 shadow-xl overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-2">
            <div>
                <h3 class="text-sm font-semibold text-slate-900">Statistik Tabungan</h3>
                <p class="text-[11px] text-slate-500">Perbandingan setoran dan penarikan (tahun berjalan)</p>
            </div>
        </div>
        <div class="h-64 w-full relative">
            <canvas id="petugasSavingChart"></canvas>
        </div>
    </div>
    
    <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-6 shadow-xl overflow-hidden">
        <h3 class="text-sm font-semibold text-slate-900 mb-4">Aksi Cepat</h3>
        <div class="grid gap-3">
            <a href="?page=data_setor" class="flex items-center gap-3 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 hover:bg-emerald-500/20 transition-all">
                <i class="fa-solid fa-plus-circle"></i>
                <span class="text-xs font-medium">Input Setoran Baru</span>
            </a>
            <a href="?page=data_tarik" class="flex items-center gap-3 p-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-600 hover:bg-rose-500/20 transition-all">
                <i class="fa-solid fa-minus-circle"></i>
                <span class="text-xs font-medium">Input Penarikan Baru</span>
            </a>
            <a href="?page=view_tabungan" class="flex items-center gap-3 p-3 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-600 hover:bg-indigo-500/20 transition-all">
                <i class="fa-solid fa-search"></i>
                <span class="text-xs font-medium">Cek Saldo Siswa</span>
            </a>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('petugasSavingChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'Setoran',
                    data: <?php echo json_encode($setoranCounts); ?>,
                    borderColor: '#6366f1',
                    tension: 0.4,
                    fill: true,
                    backgroundColor: 'rgba(99, 102, 241, 0.1)'
                },{
                    label: 'Penarikan',
                    data: <?php echo json_encode($penarikanCounts); ?>,
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
<!-- /.content -->
