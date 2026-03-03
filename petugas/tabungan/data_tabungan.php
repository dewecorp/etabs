<!-- Content Header (Page header) -->
<?php
    $nis = isset($_POST["nis"]) ? $_POST["nis"] : '';
    $nis = mysqli_real_escape_string($koneksi, $nis);
    $setor = 0;
    $tarik = 0;
    $saldo = 0;
?>

<?php
	$sql = $koneksi->query("SELECT SUM(setor) as Tsetor  from tb_tabungan where jenis='ST' and nis='$nis'");
	while ($data= $sql->fetch_assoc()) {
	
		$setor = $data['Tsetor'] ? (int)$data['Tsetor'] : 0;
	}
?>

<?php
	$sql = $koneksi->query("SELECT SUM(tarik) as Ttarik  from tb_tabungan where jenis='TR' and nis='$nis'");
	while ($data= $sql->fetch_assoc()) {
	
		$tarik = $data['Ttarik'] ? (int)$data['Ttarik'] : 0;
	}
?>

<?php
    $saldo = $setor - $tarik;
?>


<section class="content-header">
	<h1>
		Tabungan Siswa
	</h1>
</section>
<!-- Main content -->

<section class="content">

	<!-- Info Banner -->
	<div class="mb-6 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-600 p-6 text-white shadow-lg relative overflow-hidden">
        <div class="absolute right-0 top-0 h-full w-1/3 bg-white/10 skew-x-12 transform"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <h4 class="text-lg font-semibold flex items-center gap-2 mb-4">
                    <i class="fa-solid fa-wallet"></i> Info Tabungan
                </h4>
                <div class="space-y-1">
                    <div class="text-emerald-100 text-sm">Total Setoran</div>
                    <div class="text-2xl font-bold"><?php echo rupiah($setor); ?></div>
                </div>
            </div>
            
            <div class="flex flex-col md:items-end gap-1">
                <div class="text-emerald-100 text-sm">Total Penarikan</div>
                <div class="text-2xl font-bold"><?php echo rupiah($tarik); ?></div>
            </div>
            
            <div class="md:border-l md:border-white/20 md:pl-6">
                <div class="text-emerald-100 text-sm">Saldo Akhir</div>
                <div class="text-4xl font-bold tracking-tight"><?php echo rupiah($saldo); ?></div>
            </div>
        </div>
	</div>

	<div class="rounded-2xl bg-white shadow-sm">
		<div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <div class="flex gap-2">
                <a href="?page=view_tabungan" class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-200 hover:text-slate-800">
                    <i class="fa-solid fa-arrow-left"></i> Kembali
                </a>
                <a href="./report/cetak-tabungan.php?nis=<?php echo $nis ?>" target="_blank" class="inline-flex items-center gap-2 rounded-xl bg-indigo-50 px-4 py-2 text-sm font-medium text-indigo-600 hover:bg-indigo-100 hover:text-indigo-700">
                    <i class="fa-solid fa-print"></i> Cetak Laporan
                </a>
            </div>
		</div>
		<!-- /.box-header -->
		<div class="p-6">
			<div class="table-responsive">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <input id="datatableSearch" class="auth-input" placeholder="Cari di tabel...">
                        <select id="datatablePageSize" class="auth-input">
                            <option value="10">10</option>
                            <option value="25" selected>25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
				<table id="example1" class="w-full table-dashboard text-xs">
					<thead>
						<tr>
							<th class="px-4 py-3 font-medium text-center" width="40px">No</th>
							<th class="px-4 py-3 font-medium">NIS</th>
							<th class="px-4 py-3 font-medium">Nama</th>
							<th class="px-4 py-3 font-medium">Tanggal</th>
							<th class="px-4 py-3 font-medium text-right">Setoran</th>
							<th class="px-4 py-3 font-medium text-right">Penarikan</th>
							<th class="px-4 py-3 font-medium">Petugas</th>
						</tr>
					</thead>
					<tbody id="datatableUsersBody">

						<?php

                  $no = 1;
				  $sql = $koneksi->query("select s.nis, s.nama_siswa, t.id_tabungan, t.setor, t.tarik, t.tgl, t.petugas from 
				  tb_siswa s join tb_tabungan t on s.nis=t.nis 
				  where s.nis ='$nis' order by tgl asc");
                  while ($data= $sql->fetch_assoc()) {
                ?>

						<tr class="hover:bg-slate-50  transition-colors">
							<td class="px-4 py-3 text-center">
								<?php echo $no++; ?>
							</td>
							<td class="px-4 py-3 font-medium text-slate-900">
								<?php echo $data['nis']; ?>
							</td>
							<td class="px-4 py-3">
								<?php echo $data['nama_siswa']; ?>
							</td>
							<td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1 rounded bg-slate-100 px-2 py-1 text-xs font-medium text-slate-600">
                                    <i class="fa-regular fa-calendar"></i>
                                    <?php  $tgl = $data['tgl']; echo date("d M Y", strtotime($tgl))?>
                                </span>
							</td>
							<td class="px-4 py-3 text-right font-medium text-emerald-600">
								<?php echo rupiah($data['setor']); ?>
							</td>
							<td class="px-4 py-3 text-right font-medium text-rose-600">
								<?php echo rupiah($data['tarik']); ?>
							</td>
							<td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">
                                    <?php echo $data['petugas']; ?>
                                </span>
							</td>
						</tr>
						<?php
                  }
                ?>
					</tbody>

				</table>
                <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <span id="datatableInfoText" class="text-[11px] text-slate-500"></span>
                    <ul id="datatablePagination" class="pagination"></ul>
                </div>
			</div>
		</div>
	</div>
</section>


