<!-- Content Header (Page header) -->
<?php 
$data_nama = $_SESSION["ses_nama"];
?>

<section class="content-header">
	<h1>
		Transaksi
		<small>Tarikan</small>
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

	<!-- /.box-header -->

	<div class="alert alert-danger alert-dismissible">
		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
		<h4>
			<i class="icon fa fa-info"></i> Total Tarikan</h4>
		<?php
    		$sql = $koneksi->query("SELECT SUM(tarik) as total  from tb_tabungan where jenis='TR'");
    		while ($data= $sql->fetch_assoc()) {
  		?>
		<h3>
			<?php echo rupiah($data['total']); }?>
		</h3>
	</div>


	<div class="box box-primary">
		<div class="box-header">
			<a href="?page=add_tarik" class="btn btn-primary">
				<i class="glyphicon glyphicon-plus"></i> Tambah Data</a>
			<button type="button" id="btnEditTerpilih" class="btn btn-success" onclick="editTerpilih()">
				<i class="glyphicon glyphicon-edit"></i> Edit Terpilih
			</button>
			<button type="button" id="btnHapusTerpilih" class="btn btn-danger" onclick="hapusTerpilih()">
				<i class="glyphicon glyphicon-trash"></i> Hapus Terpilih
			</button>
			<div class="btn-group">
				<a href="../../admin/export_handler.php?type=excel&table=tarik" class="btn btn-info" title="Ekspor ke Excel">
					<i class="fa fa-file-excel-o"></i> Excel
				</a>
				<a href="../../admin/export_handler.php?type=pdf&table=tarik" class="btn btn-danger" title="Ekspor ke PDF" target="_blank">
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
				<form id="formTarik" method="post" action="">
					<table id="example1" class="table table-bordered table-striped">
						<thead>
							<tr>
								<th width="30">
									<input type="checkbox" id="checkAll" title="Pilih Semua" onclick="handleCheckAllClick(this)">
								</th>
								<th>No</th>
								<th>NIS</th>
								<th>Nama</th>
								<th>Tanggal</th>
								<th>Tarikan</th>
								<th>Petugas</th>
								<th>Aksi</th>
							</tr>
						</thead>
						<tbody>

							<?php

                  $no = 1;
				  $sql = $koneksi->query("select s.nis, s.nama_siswa, t.id_tabungan, t.tarik, t.tgl, t.petugas from 
				  tb_siswa s join tb_tabungan t on s.nis=t.nis 
				  where jenis ='TR' order by tgl desc, id_tabungan desc");
                  while ($data= $sql->fetch_assoc()) {
                ?>

							<tr data-id="<?php echo $data['id_tabungan']; ?>" data-nis="<?php echo $data['nis']; ?>" data-nama="<?php echo htmlspecialchars($data['nama_siswa']); ?>" data-tarik="<?php echo $data['tarik']; ?>" data-tgl="<?php echo $data['tgl']; ?>">
								<td>
									<input type="checkbox" name="id_tabungan[]" class="checkItem" value="<?php echo $data['id_tabungan']; ?>">
								</td>
								<td>
									<?php echo $no++; ?>
								</td>
							<td>
								<?php echo $data['nis']; ?>
							</td>
							<td>
								<?php echo $data['nama_siswa']; ?>
							</td>
							<td>
								<?php  $tgl = $data['tgl']; echo date("d/M/Y", strtotime($tgl))?>
							</td>
							<td align="right">
								<?php echo rupiah($data['tarik']); ?>
							</td>
							<td>
								<?php echo $data['petugas']; ?>
							</td>
							<td>

								<a href="?page=edit_tarik&kode=<?php echo $data['id_tabungan']; ?>" title="Ubah"
								 class="btn btn-success btn-sm">
									<i class="glyphicon glyphicon-edit"></i>
								</a>
								<a href="?page=del_tarik&kode=<?php echo $data['id_tabungan']; ?>" 
									onclick="return confirmHapusTarik(event, '<?php echo htmlspecialchars($data['nis']); ?>', '<?php echo htmlspecialchars($data['nama_siswa']); ?>', '<?php echo rupiah($data['tarik']); ?>')"
									title="Hapus" class="btn btn-danger btn-sm">
									<i class="glyphicon glyphicon-trash"></i>
								</a>
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

<!-- Modal Edit Multiple -->
<div class="modal fade" id="modalEditMultiple" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header" style="background: linear-gradient(to right, #605ca8, #9c88ff); color: white;">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 0.8;">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title">
					<i class="glyphicon glyphicon-edit"></i> Edit Multiple Tarikan
					<span id="countData" style="font-size: 14px; font-weight: normal;"></span>
				</h4>
			</div>
			<form id="formEditMultiple" method="post" action="?page=edit_tarik_multiple">
				<div class="modal-body" style="max-height: 500px; overflow-y: auto;">
					<div class="table-responsive">
						<table class="table table-bordered">
							<thead>
								<tr>
									<th>NIS</th>
									<th>Nama</th>
									<th>Tarikan</th>
									<th>Tanggal</th>
								</tr>
							</thead>
							<tbody id="tbodyEditMultiple">
								<!-- Data akan diisi via JavaScript -->
							</tbody>
						</table>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">
						<i class="fa fa-times"></i> Batal
					</button>
					<button type="submit" class="btn btn-success">
						<i class="fa fa-save"></i> Simpan Semua
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script>
// Fungsi global untuk toggle tombol
function toggleButtonsTarik() {
	var checkedCount = $('.checkItem:checked').length;
	if (checkedCount > 0) {
		$('#btnEditTerpilih').prop('disabled', false).removeClass('disabled');
		$('#btnHapusTerpilih').prop('disabled', false).removeClass('disabled');
	} else {
		$('#btnEditTerpilih').prop('disabled', true).addClass('disabled');
		$('#btnHapusTerpilih').prop('disabled', true).addClass('disabled');
	}
}

// Fungsi global untuk handle check all
function handleCheckAllClick(checkbox) {
	var isChecked = checkbox.checked;
	$('.checkItem').each(function() {
		this.checked = isChecked;
	});
	
	// Toggle tombol menggunakan fungsi global
	toggleButtonsTarik();
}

// Checkbox untuk pilih semua
$(document).ready(function() {
	// Fungsi untuk toggle tombol (local untuk kompatibilitas)
	function toggleButtons() {
		toggleButtonsTarik();
	}
	
	// Pastikan checkbox all sync dengan checkbox individual (menggunakan event delegation)
	$(document).on('change click', '.checkItem', function() {
		var totalCheckbox = $('.checkItem').length;
		var checkedCount = $('.checkItem:checked').length;
		$('#checkAll').prop('checked', (totalCheckbox > 0 && checkedCount === totalCheckbox));
		// Panggil fungsi global untuk toggle tombol
		toggleButtonsTarik();
	});
	
	// Setup handler langsung pada elemen setelah DOM ready sebagai backup
	setTimeout(function() {
		$('.checkItem').off('change click').on('change click', function() {
			var totalCheckbox = $('.checkItem').length;
			var checkedCount = $('.checkItem:checked').length;
			$('#checkAll').prop('checked', (totalCheckbox > 0 && checkedCount === totalCheckbox));
			toggleButtonsTarik();
		});
	}, 200);
	
	// Handle checkbox all di DataTable (jika menggunakan DataTable)
	$('#example1').on('draw.dt', function() {
		// Reset checkAll setelah draw
		var totalCheckbox = $('.checkItem').length;
		var checkedCount = $('.checkItem:checked').length;
		$('#checkAll').prop('checked', (totalCheckbox > 0 && checkedCount === totalCheckbox));
		// Re-attach onclick handler
		$('#checkAll').attr('onclick', 'handleCheckAllClick(this)');
		// Re-setup handler untuk checkbox individual
		$('.checkItem').off('change click').on('change click', function() {
			var totalCheckbox = $('.checkItem').length;
			var checkedCount = $('.checkItem:checked').length;
			$('#checkAll').prop('checked', (totalCheckbox > 0 && checkedCount === totalCheckbox));
			toggleButtonsTarik();
		});
		toggleButtonsTarik();
	});
	
	// Inisialisasi: disable tombol di awal
	toggleButtonsTarik();
});

// Expose fungsi ke global scope
window.handleCheckAllClick = handleCheckAllClick;

function editTerpilih() {
	var checked = $('.checkItem:checked').length;
	if (checked == 0) {
		Swal.fire({
			title: 'Peringatan!',
			text: 'Pilih data yang akan diedit!',
			icon: 'warning',
			confirmButtonText: 'OK',
			confirmButtonColor: '#f39c12'
		});
		return;
	}
	
	var checkedItems = [];
	$('.checkItem:checked').each(function() {
		checkedItems.push($(this).val());
	});
	
	// Jika hanya 1 data, redirect ke edit biasa
	if (checked == 1) {
		window.location.href = '?page=edit_tarik&kode=' + checkedItems[0];
		return;
	}
	
	// Jika lebih dari 1, load data dan buka modal
	if (typeof loadDataForEdit === 'function') {
		loadDataForEdit(checkedItems);
	} else {
		// Fallback jika fungsi belum didefinisikan
		setTimeout(function() {
			if (typeof loadDataForEdit === 'function') {
				loadDataForEdit(checkedItems);
			}
		}, 100);
	}
}

function formatNumber(num) {
	return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function loadDataForEdit(ids) {
	// Ambil data dari baris tabel yang terpilih
	var tbody = $('#tbodyEditMultiple');
	tbody.empty();
	$('#formEditMultiple').find('input[name="id_tabungan[]"]').remove();
	$('#formEditMultiple').find('input[name="tarik[]"]').remove();
	$('#formEditMultiple').find('input[name="tgl[]"]').remove();
	
	var count = 0;
	ids.forEach(function(id) {
		var row = $('tr[data-id="' + id + '"]');
		if (row.length > 0) {
			var nis = row.attr('data-nis');
			var nama = row.attr('data-nama');
			var tarik = row.attr('data-tarik');
			var tgl = row.attr('data-tgl');
			
			// Format tanggal untuk input type="date"
			var tglFormatted = tgl; // Format: YYYY-MM-DD
			
			var tr = $('<tr>')
				.append($('<td>').append($('<input>', {
					type: 'text',
					class: 'form-control',
					value: nis,
					readonly: true
				})))
				.append($('<td>').append($('<input>', {
					type: 'text',
					class: 'form-control',
					value: nama,
					readonly: true
				})))
				.append($('<td>').append($('<input>', {
					type: 'text',
					class: 'form-control',
					name: 'tarik[]',
					value: 'Rp ' + formatNumber(tarik),
					required: true
				})))
				.append($('<td>').append($('<input>', {
					type: 'date',
					class: 'form-control',
					name: 'tgl[]',
					value: tglFormatted,
					required: true
				})));
			
			tbody.append(tr);
			
			// Tambahkan hidden input untuk id_tabungan
			$('#formEditMultiple').append('<input type="hidden" name="id_tabungan[]" value="' + id + '">');
			count++;
		}
	});
	
	$('#countData').text(count + ' tarikan');
	
	$('#modalEditMultiple').modal('show');
	
	// Format angka untuk input tarik (gunakan event delegation karena elemen dibuat dinamis)
	setTimeout(function() {
		$(document).off('keyup', 'input[name="tarik[]"]').on('keyup', 'input[name="tarik[]"]', function() {
			var value = $(this).val().replace(/[^0-9]/g, '');
			if (value) {
				$(this).val('Rp ' + formatNumber(value));
			} else {
				$(this).val('');
			}
		});
	}, 100);
}

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
			  '<p style="font-size: 16px; margin-bottom: 20px; color: #495057;">Yakin hapus ' + checked + ' penarikan terpilih?</p>' +
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
			$('#formTarik').attr('action', '?page=del_tarik_multiple').submit();
		}
	});
}

// Fungsi konfirmasi hapus tarikan single
function confirmHapusTarik(event, nis, nama, tarik) {
    event.preventDefault();
    event.stopPropagation();
    event.stopImmediatePropagation();
    
    var url = event.currentTarget.getAttribute('href');
    if (!url) {
        url = event.currentTarget.closest('a').getAttribute('href');
    }
    
    // Tunggu SweetAlert dimuat
    function showConfirm() {
        if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
            Swal.fire({
                title: '<i class="fa fa-exclamation-triangle" style="color: #f39c12; font-size: 48px;"></i>',
                html: '<div style="text-align: center; padding: 10px;">' +
                      '<h3 style="color: #d33; margin-bottom: 20px; font-weight: bold;">Konfirmasi Hapus Tarikan</h3>' +
                      '<p style="font-size: 16px; margin-bottom: 15px; color: #495057;">Yakin hapus tarikan berikut?</p>' +
                      '<div style="background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 15px; margin: 15px 0; text-align: left;">' +
                      '<p style="margin: 5px 0;"><strong>NIS:</strong> ' + nis + '</p>' +
                      '<p style="margin: 5px 0;"><strong>Nama:</strong> ' + nama + '</p>' +
                      '<p style="margin: 5px 0;"><strong>Tarikan:</strong> ' + tarik + '</p>' +
                      '</div>' +
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
                    window.location.href = url;
                }
            });
            return true;
        }
        return false;
    }
    
    // Coba langsung
    if (showConfirm()) {
        return false;
    }
    
    // Jika belum, tunggu dengan interval
    var attempts = 0;
    var maxAttempts = 100;
    var checkInterval = setInterval(function() {
        attempts++;
        if (showConfirm()) {
            clearInterval(checkInterval);
        } else if (attempts >= maxAttempts) {
            clearInterval(checkInterval);
            // Fallback ke confirm biasa
            var message = 'Yakin hapus tarikan untuk NIS ' + nis + '?\n\nPERINGATAN: Data yang dihapus tidak dapat dikembalikan!';
            if (confirm(message)) {
                window.location.href = url;
            }
        }
    }, 50);
    
    return false;
}

// Ekspos fungsi ke global scope
window.confirmHapusTarik = confirmHapusTarik;

// Pastikan fungsi tersedia saat document ready
if (typeof jQuery !== 'undefined') {
    jQuery(document).ready(function($) {
        window.confirmHapusTarik = confirmHapusTarik;
    });
}
</script>

