<!-- Content Header (Page header) -->
<?php

if(isset($_POST["btnCetak"])){

	$dt1 = $_POST["tgl_1"];
	$dt2 = $_POST["tgl_2"];
	
	$sql = $koneksi->query("SELECT SUM(setor) as Tsetor  from tb_tabungan where jenis='ST' and tgl BETWEEN '$dt1' AND '$dt2'");
}
while ($data= $sql->fetch_assoc()) {
	
	$setor=$data['Tsetor'];
}

$sql = $koneksi->query("SELECT SUM(tarik) as Ttarik  from tb_tabungan where jenis='TR' and tgl BETWEEN '$dt1' AND '$dt2'");
while ($data= $sql->fetch_assoc()) {
	
	$tarik=$data['Ttarik'];
}
$saldo=$setor-$tarik;
?>


<section class="content-header">
    <h1>
        Info Kas
    </h1>
</section>
<!-- Main content -->

<section class="content">

    <div class="rounded-2xl bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <h3 class="text-lg font-semibold text-slate-900  flex items-center gap-2">
                <i class="fa-solid fa-vault text-indigo-500"></i>
                Saldo Tabungan (Kas)
            </h3>
        </div>
        <!-- /.box-header -->
        <div class="p-6">
            <div class="table-responsive">
                <table class="w-full table-dashboard">
                    <thead>
                        <tr>
                            <th class="px-6 py-4 font-medium">Total Setoran</th>
                            <th class="px-6 py-4 font-medium">Total Tarikan</th>
                            <th class="px-6 py-4 font-medium">Saldo Tabungan</th>
                        </tr>
                    </thead>
                    <tbody>

                        <tr class="hover:bg-slate-50  transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <a href="?page=data_setor" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 hover:bg-emerald-200    transition-colors" title="Detail">
                                        <i class="fa-solid fa-circle-info"></i>
                                    </a>
                                    <span class="text-lg font-semibold text-emerald-600">
                                        <?php echo rupiah($setor); ?>
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <a href="?page=data_tarik" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-rose-100 text-rose-600 hover:bg-rose-200    transition-colors" title="Detail">
                                        <i class="fa-solid fa-circle-info"></i>
                                    </a>
                                    <span class="text-lg font-semibold text-rose-600">
                                        <?php echo rupiah($tarik); ?>
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-2xl font-bold text-slate-900">
                                    <?php echo rupiah($saldo); ?>
                                </span>
                            </td>
                        </tr>
                    </tbody>

                </table>
            </div>
        </div>
    </div>
</section>

