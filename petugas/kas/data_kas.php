<!-- Content Header (Page header) -->
<?php
// Hitung langsung total tanpa filter tanggal
$sql_setor = $koneksi->query("SELECT SUM(setor) as Tsetor FROM tb_tabungan WHERE jenis='ST'");
$data_setor = $sql_setor ? $sql_setor->fetch_assoc() : ['Tsetor' => 0];
$setor = isset($data_setor['Tsetor']) && is_numeric($data_setor['Tsetor']) ? (float)$data_setor['Tsetor'] : 0;

$sql_tarik = $koneksi->query("SELECT SUM(tarik) as Ttarik FROM tb_tabungan WHERE jenis='TR'");
$data_tarik = $sql_tarik ? $sql_tarik->fetch_assoc() : ['Ttarik' => 0];
$tarik = isset($data_tarik['Ttarik']) && is_numeric($data_tarik['Ttarik']) ? (float)$data_tarik['Ttarik'] : 0;

$saldo = $setor - $tarik;
?>


<link rel="stylesheet" href="../../assets/css/print.css">
<section class="content-header">
    <h1>
        Info Kas
    </h1>
</section>
<!-- Main content -->

<section class="content">

    <div class="rounded-2xl bg-white shadow-sm" id="infoKas">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <h3 class="text-lg font-semibold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-vault text-indigo-500"></i>
                Saldo Tabungan (Kas)
            </h3>
            <a href="report/cetak_kas.php" target="_blank" class="inline-flex items-center gap-2 rounded-xl bg-indigo-100 px-4 py-2 text-sm font-medium text-indigo-600 hover:bg-indigo-200 transition-colors print:hidden">
                <i class="fa-solid fa-print"></i>
                <span>Cetak</span>
            </a>
        </div>
        <div class="p-6 space-y-6">
            <div class="grid gap-4 sm:grid-cols-3">
                <a href="?page=data_setor" class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-emerald-50 to-white p-5 shadow-sm transition-all hover:border-emerald-500/40 hover:shadow-lg">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-[11px] font-medium uppercase tracking-wide text-emerald-600">Total Setoran</p>
                            <p class="mt-2 text-2xl font-bold text-emerald-700"><?= rupiah($setor); ?></p>
                        </div>
                        <div class="rounded-xl bg-emerald-500/10 p-3 ring-1 ring-emerald-500/30">
                            <i class="fa-solid fa-arrow-down-long text-emerald-600 text-lg"></i>
                        </div>
                    </div>
                    <div class="mt-3 h-1 w-full rounded-full bg-emerald-200/40">
                        <div class="h-1 rounded-full bg-emerald-500" style="width: 70%"></div>
                    </div>
                </a>
                <a href="?page=data_tarik" class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-rose-50 to-white p-5 shadow-sm transition-all hover:border-rose-500/40 hover:shadow-lg">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-[11px] font-medium uppercase tracking-wide text-rose-600">Total Penarikan</p>
                            <p class="mt-2 text-2xl font-bold text-rose-700"><?= rupiah($tarik); ?></p>
                        </div>
                        <div class="rounded-xl bg-rose-500/10 p-3 ring-1 ring-rose-500/30">
                            <i class="fa-solid fa-arrow-up-long text-rose-600 text-lg"></i>
                        </div>
                    </div>
                    <div class="mt-3 h-1 w-full rounded-full bg-rose-200/40">
                        <div class="h-1 rounded-full bg-rose-500" style="width: 40%"></div>
                    </div>
                </a>
                <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-amber-50 to-white p-5 shadow-sm transition-all hover:border-amber-500/40 hover:shadow-lg">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-[11px] font-medium uppercase tracking-wide text-amber-700">Saldo Akhir Kas</p>
                            <p class="mt-2 text-3xl font-bold text-slate-900 tracking-tight"><?= rupiah($saldo); ?></p>
                        </div>
                        <div class="rounded-xl bg-amber-500/10 p-3 ring-1 ring-amber-500/30">
                            <i class="fa-solid fa-wallet text-amber-600 text-lg"></i>
                        </div>
                    </div>
                    <div class="mt-3 h-1 w-full rounded-full bg-amber-200/40">
                        <div class="h-1 rounded-full bg-amber-500" style="width: 55%"></div>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-between text-[11px] text-slate-500">
                <span>Terakhir diperbarui: <?= date('d M Y H:i'); ?></span>
                <span class="inline-flex items-center gap-1">
                    <i class="fa-solid fa-circle-info"></i>
                    Klik kartu untuk melihat detail transaksi
                </span>
            </div>
        </div>
    </div>
</section>

