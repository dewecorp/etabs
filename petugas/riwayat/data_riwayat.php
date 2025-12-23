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
	<ol class="breadcrumb">
		<li>
			<a href="index.php">
				<i class="fa fa-home"></i>
				<b>e-TABS</b>
			</a>
		</li>
	</ol>
</section>
<!-- Main content -->

<section class="content">

	<div class="box box-primary">
		<div class="box-header">
			<button type="button" id="btnHapusTerpilih" class="btn btn-danger" onclick="hapusTerpilih()">
				<i class="glyphicon glyphicon-trash"></i> Hapus Terpilih
			</button>
			<div class="btn-group">
				<a href="../../admin/export_handler.php?type=excel&table=riwayat" class="btn btn-info" title="Ekspor ke Excel">
					<i class="fa fa-file-excel-o"></i> Excel
				</a>
				<a href="../../admin/export_handler.php?type=pdf&table=riwayat" class="btn btn-danger" title="Ekspor ke PDF" target="_blank">
					<i class="fa fa-file-pdf-o"></i> PDF
				</a>
			</div>
			<div class="box-tools pull-right">
				<button type="button" class="btn btn-box-tool" data-widget="collapse">
					<i class="fa fa-minus"></i>
				</button>
				<button type="button" class="btn btn-box-tool" data-widget="remove">
					<i class="fa fa-remove"></i>
				</button>
			</div>
		</div>
		<!-- /.box-header -->
		<div class="box-body">
			<div class="table-responsive">
				<form id="formRiwayat" method="post" action="?page=del_riwayat">
					<table id="example1" class="table table-bordered table-striped">
						<thead>
							<tr>
								<th width="30">
									<input type="checkbox" id="checkAll" title="Pilih Semua (hanya Tidak Aktif)" onclick="handleCheckAllClickRiwayat(this)">
								</th>
								<th>No</th>
								<th>Jenis</th>
								<th>Status</th>
								<th>NIS</th>
								<th>Nama</th>
								<th>Tanggal Transaksi</th>
								<th>Jumlah</th>
								<th>Petugas Transaksi</th>
								<th>Tanggal Hapus</th>
								<th>Petugas Hapus</th>
							</tr>
						</thead>
						<tbody>

							<?php

                  $no = 1;
				  $sql = $koneksi->query("SELECT r.*, s.nama_siswa 
				  							FROM tb_riwayat r 
				  							LEFT JOIN tb_siswa s ON r.nis = s.nis 
				  							ORDER BY r.tgl DESC, r.id_riwayat DESC");
                  while ($data= $sql->fetch_assoc()) {
					$is_tidak_aktif = ($data['status'] == 'Tidak Aktif');
                ?>

							<tr>
								<td>
									<?php if ($is_tidak_aktif): ?>
									<input type="checkbox" name="id_riwayat[]" class="checkItem" value="<?php echo $data['id_riwayat']; ?>">
									<?php else: ?>
									<input type="checkbox" disabled title="Data Aktif tidak dapat dihapus">
									<?php endif; ?>
								</td>
								<td>
									<?php echo $no++; ?>
								</td>
								<td>
									<?php 
									if ($data['jenis'] == 'ST') {
										echo '<span class="label label-success">Setoran</span>';
									} else {
										echo '<span class="label label-danger">Penarikan</span>';
									}
									?>
								</td>
								<td>
									<?php 
									if ($data['status'] == 'Aktif') {
										echo '<span class="label label-primary">Aktif</span>';
									} else {
										echo '<span class="label label-default">Tidak Aktif</span>';
									}
									?>
								</td>
								<td>
									<?php echo $data['nis']; ?>
								</td>
								<td>
									<?php echo $data['nama_siswa'] ?? 'Siswa tidak ditemukan'; ?>
								</td>
								<td>
									<?php $tgl = $data['tgl']; echo date("d/M/Y", strtotime($tgl)); ?>
								</td>
								<td align="right">
									<?php 
									if ($data['jenis'] == 'ST') {
										echo rupiah($data['setor']);
									} else {
										echo rupiah($data['tarik']);
									}
									?>
								</td>
								<td>
									<?php echo $data['petugas']; ?>
								</td>
								<td>
									<?php 
									if ($data['tgl_hapus']) {
										$tgl_hapus = $data['tgl_hapus']; 
										echo date("d/M/Y H:i", strtotime($tgl_hapus)); 
									} else {
										echo '-';
									}
									?>
								</td>
								<td>
									<?php echo $data['petugas_hapus'] ?? '-'; ?>
								</td>
							</tr>
							<?php
                  }
                ?>
						</tbody>

					</table>
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
			confirmButtonColor: '#f39c12'
		});
		return;
	}
	
	Swal.fire({
		title: '<i class="fa fa-exclamation-triangle" style="color: #f39c12; font-size: 48px;"></i>',
		html: '<div style="text-align: center; padding: 10px;">' +
			  '<h3 style="color: #d33; margin-bottom: 20px; font-weight: bold;">Konfirmasi Hapus</h3>' +
			  '<p style="font-size: 16px; margin-bottom: 20px; color: #495057;">Yakin hapus ' + checked + ' riwayat transaksi terpilih?</p>' +
			  '<div style="background-color: #fff3cd; border: 2px solid #ffc107; border-radius: 8px; padding: 15px; margin-top: 15px;">' +
			  '<p style="margin: 0; color: #856404; font-size: 14px; font-weight: bold;">' +
			  '<i class="fa fa-warning" style="margin-right: 8px;"></i>' +
			  'PERINGATAN: Data yang dihapus tidak dapat dikembalikan!</p>' +
			  '</div>' +
			  '</div>',
		icon: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#d33',
		cancelButtonColor: '#6c757d',
		confirmButtonText: '<i class="fa fa-trash"></i> Ya, Hapus!',
		cancelButtonText: '<i class="fa fa-times"></i> Batal',
		reverseButtons: true,
		focusCancel: true,
		allowOutsideClick: false,
		allowEscapeKey: true,
		width: '500px'
	}).then((result) => {
		if (result.isConfirmed) {
			$('#formRiwayat').submit();
		}
	});
}
</script>

