<!-- Content Header (Page header) -->
<?php 
$data_nama = $_SESSION["ses_nama"];

// Cek dan buat tabel tb_riwayat jika belum ada
try {
    $check_table = $koneksi->query("SHOW TABLES LIKE 'tb_riwayat'");
    if ($check_table && $check_table->num_rows == 0) {
        // Buat tabel tb_riwayat dengan struktur baru
        $sql_create = "CREATE TABLE IF NOT EXISTS `tb_riwayat` (
          `id_riwayat` int(11) NOT NULL AUTO_INCREMENT,
          `id_tabungan_asli` int(11) NOT NULL COMMENT 'ID dari tb_tabungan',
          `nis` char(12) NOT NULL,
          `setor` int(11) NOT NULL,
          `tarik` int(11) NOT NULL,
          `tgl` date NOT NULL,
          `jenis` enum('ST','TR') NOT NULL,
          `petugas` varchar(20) NOT NULL,
          `status` enum('Aktif','Tidak Aktif') NOT NULL DEFAULT 'Aktif' COMMENT 'Aktif jika masih ada di tb_tabungan, Tidak Aktif jika sudah dihapus',
          `tgl_hapus` datetime DEFAULT NULL COMMENT 'Tanggal dan waktu ketika data dihapus',
          `petugas_hapus` varchar(20) DEFAULT NULL COMMENT 'Petugas yang menghapus data',
          PRIMARY KEY (`id_riwayat`),
          KEY `id_tabungan_asli` (`id_tabungan_asli`),
          KEY `nis` (`nis`),
          KEY `status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1";
        $koneksi->query($sql_create);
    } else {
        // Cek apakah kolom status sudah ada, jika belum tambahkan
        $check_column = $koneksi->query("SHOW COLUMNS FROM tb_riwayat LIKE 'status'");
        if ($check_column && $check_column->num_rows == 0) {
            // Tambahkan kolom status
            $koneksi->query("ALTER TABLE tb_riwayat ADD COLUMN `status` enum('Aktif','Tidak Aktif') NOT NULL DEFAULT 'Tidak Aktif' AFTER `petugas`");
            // Update status menjadi Aktif untuk semua data yang ada (karena ini migrasi)
            $koneksi->query("UPDATE tb_riwayat SET status = 'Tidak Aktif' WHERE status IS NULL OR status = ''");
            // Ubah tgl_hapus dan petugas_hapus menjadi nullable
            $koneksi->query("ALTER TABLE tb_riwayat MODIFY `tgl_hapus` datetime DEFAULT NULL");
            $koneksi->query("ALTER TABLE tb_riwayat MODIFY `petugas_hapus` varchar(20) DEFAULT NULL");
        }
    }
} catch (Exception $e) {
    // Silent fail
}
?>

<section class="content-header">
	<h1>
		Riwayat
		<small>Transaksi</small>
	</h1>
</section>
<!-- Main content -->

<section class="content">

	<div class="rounded-2xl bg-white shadow-sm">
		<div class="flex flex-col md:flex-row items-center justify-between gap-4 border-b border-slate-100 px-6 py-4">
            <div class="flex items-center gap-2">
                <button type="button" id="btnHapusTerpilih" class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-medium text-white ring-1 ring-rose-600/30 hover:bg-rose-700 disabled:opacity-50" onclick="hapusTerpilih()" disabled>
                    <i class="fa-solid fa-trash"></i>
                    <span>Hapus Terpilih</span>
                </button>
            </div>
            
			<div class="flex items-center gap-2">
                <a href="../../admin/export_handler.php?type=excel&table=riwayat" class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-600/20 hover:bg-emerald-100" title="Ekspor ke Excel">
					<i class="fa-solid fa-file-excel"></i><span>Excel</span>
				</a>
				<a href="../../admin/export_handler.php?type=pdf&table=riwayat" class="inline-flex items-center gap-1.5 rounded-xl bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 ring-1 ring-rose-600/20 hover:bg-rose-100" title="Ekspor ke PDF" target="_blank">
					<i class="fa-solid fa-file-pdf"></i><span>PDF</span>
				</a>
			</div>
		</div>
		<!-- /.box-header -->
		<div class="p-6">
			<div class="table-responsive">
				<form id="formRiwayat" method="post" action="?page=del_riwayat">
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
								<th class="px-4 py-3 text-center" width="30">
									<input type="checkbox" id="checkAll" title="Pilih Semua (hanya Tidak Aktif)">
								</th>
								<th class="px-4 py-3 font-medium text-center" width="40px">No</th>
								<th class="px-4 py-3 font-medium text-center">Jenis</th>
								<th class="px-4 py-3 font-medium text-center">Status</th>
								<th class="px-4 py-3 font-medium">NIS</th>
								<th class="px-4 py-3 font-medium">Nama</th>
								<th class="px-4 py-3 font-medium">Tanggal Transaksi</th>
								<th class="px-4 py-3 font-medium text-left">Jumlah</th>
								<th class="px-4 py-3 font-medium">Petugas Transaksi</th>
								<th class="px-4 py-3 font-medium">Tanggal Hapus</th>
								<th class="px-4 py-3 font-medium">Petugas Hapus</th>
							</tr>
						</thead>
						<tbody id="datatableUsersBody" class="divide-y divide-slate-200 bg-white">

							<?php

                  $no = 1;
				  $sql = $koneksi->query("SELECT r.*, s.nama_siswa 
				  							FROM tb_riwayat r 
				  							LEFT JOIN tb_siswa s ON r.nis = s.nis 
				  							ORDER BY r.tgl DESC, r.id_riwayat DESC");
                  while ($data= $sql->fetch_assoc()) {
					$is_tidak_aktif = ($data['status'] == 'Tidak Aktif');
                ?>

							<tr class="hover:bg-slate-50 transition-colors">
								<td class="px-4 py-3 text-center">
									<?php if ($is_tidak_aktif): ?>
									<input type="checkbox" name="id_riwayat[]" class="checkItem rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" value="<?php echo $data['id_riwayat']; ?>">
									<?php else: ?>
									<input type="checkbox" disabled class="rounded border-slate-200 bg-slate-100 text-slate-400" title="Data Aktif tidak dapat dihapus">
									<?php endif; ?>
								</td>
								<td class="px-4 py-3 text-center">
									<?php echo $no++; ?>
								</td>
								<td class="px-4 py-3 text-center">
									<?php 
									if ($data['jenis'] == 'ST') {
										echo '<span class="badge-pill badge-pill-success">Setoran</span>';
									} else {
										echo '<span class="badge-pill badge-pill-danger">Penarikan</span>';
									}
									?>
								</td>
								<td class="px-4 py-3 text-center">
									<?php 
									if ($data['status'] == 'Aktif') {
										echo '<span class="badge-pill badge-pill-success">Aktif</span>';
									} else {
										echo '<span class="badge-pill badge-pill-danger">Tidak Aktif</span>';
									}
									?>
								</td>
								<td class="px-4 py-3 font-medium text-slate-900">
									<?php echo $data['nis']; ?>
								</td>
								<td class="px-4 py-3">
									<?php echo $data['nama_siswa'] ?? 'Siswa tidak ditemukan'; ?>
								</td>
								<td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1 rounded bg-slate-100 px-2 py-1 text-xs font-medium text-slate-600 whitespace-nowrap">
                                        <i class="fa-regular fa-calendar"></i>
                                        <?php $tgl = $data['tgl']; echo date("d M Y", strtotime($tgl)); ?>
                                    </span>
								</td>
								<td class="px-4 py-3 text-left font-medium text-slate-900">
									<?php 
									if ($data['jenis'] == 'ST') {
										echo '<span class="text-emerald-600 whitespace-nowrap">' . rupiah($data['setor']) . '</span>';
									} else {
										echo '<span class="text-rose-600 whitespace-nowrap">' . rupiah($data['tarik']) . '</span>';
									}
									?>
								</td>
								<td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">
									    <?php echo $data['petugas']; ?>
                                    </span>
								</td>
								<td class="px-4 py-3">
									<?php 
									if ($data['tgl_hapus']) {
										$tgl_hapus = $data['tgl_hapus']; 
										echo '<span class="text-xs text-slate-500">' . date("d M Y H:i", strtotime($tgl_hapus)) . '</span>'; 
									} else {
										echo '-';
									}
									?>
								</td>
								<td class="px-4 py-3">
									<?php echo $data['petugas_hapus'] ?? '-'; ?>
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
				</form>
			</div>
		</div>
	</div>
</section>

<script>
// Checkbox untuk pilih semua (hanya yang Tidak Aktif)
$(document).ready(function() {
	// Fungsi untuk toggle tombol hapus
	function toggleHapusButton() {
		var checkedCount = $('.checkItem:checked').length;
		if (checkedCount > 0) {
			$('#btnHapusTerpilih').prop('disabled', false).removeClass('disabled');
		} else {
			$('#btnHapusTerpilih').prop('disabled', true).addClass('disabled');
		}
	}
	
	// Checkbox pilih semua hanya memilih yang bisa dicentang (Tidak Aktif)
	$(document).on('click', '#checkAll', function() {
		var isChecked = $(this).prop('checked');
		$('.checkItem').each(function() {
			if (!$(this).prop('disabled')) {
				$(this).prop('checked', isChecked);
			}
		});
		toggleHapusButton();
	});
	
	// Event handler untuk checkbox individual (menggunakan event delegation)
	$(document).on('change', '.checkItem', function() {
		var totalCheckbox = $('.checkItem:not(:disabled)').length;
		var checkedCount = $('.checkItem:checked').length;
		if (totalCheckbox > 0 && checkedCount === totalCheckbox) {
			$('#checkAll').prop('checked', true);
		} else {
			$('#checkAll').prop('checked', false);
		}
		toggleHapusButton();
	});
	
	// Handle checkbox all di DataTable (jika menggunakan DataTable)
	$('#example1').on('draw.dt', function() {
		// Re-initialize checkAll handler setelah DataTable draw
		$(document).off('click', '#checkAll').on('click', '#checkAll', function() {
			var isChecked = $(this).prop('checked');
			$('.checkItem').each(function() {
				if (!$(this).prop('disabled')) {
					$(this).prop('checked', isChecked);
				}
			});
			toggleHapusButton();
		});
	});
	
	// Inisialisasi: sembunyikan tombol hapus di awal
	toggleHapusButton();
});

function hapusTerpilih() {
	var checked = $('.checkItem:checked').length;
	if (checked == 0) {
		Swal.fire({
			title: 'Peringatan!',
			text: 'Pilih data yang akan dihapus!',
			icon: 'warning',
			confirmButtonText: 'OK',
            customClass: {
                popup: 'rounded-3xl border border-slate-200 shadow-xl',
                title: 'text-lg font-semibold text-slate-900',
                confirmButton: 'rounded-xl px-4 py-2 text-sm font-medium',
            }
		});
		return;
	}
	
	Swal.fire({
        title: 'Konfirmasi Hapus',
        html: `Yakin hapus <strong>${checked}</strong> riwayat transaksi terpilih?<br><small class='text-red-500'>Data yang dihapus tidak dapat dikembalikan!</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48', // rose-600
        cancelButtonColor: '#64748b', // slate-500
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-3xl border border-slate-200 shadow-xl',
            title: 'text-lg font-semibold text-slate-900',
            confirmButton: 'rounded-xl',
            cancelButton: 'rounded-xl'
        }
	}).then((result) => {
		if (result.isConfirmed) {
			$('#formRiwayat').submit();
		}
	});
}
</script>



