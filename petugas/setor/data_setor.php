<!-- Content Header (Page header) -->
<?php 
$data_nama = $_SESSION["ses_nama"];

date_default_timezone_set("Asia/Jakarta"); 
$tanggal = date("Y-m-d");

if (isset($_POST['Simpan'])) {
    //menangkap post setor
    $setor = $_POST['setor'];
    //membuang Rp dan Titik
    $setor_hasil = preg_replace("/[^0-9]/", "", $setor);

    $sql_simpan = "INSERT INTO tb_tabungan (nis,setor,tarik,tgl,jenis,petugas) VALUES (
        '".$_POST['nis']."',
        '".$setor_hasil."',
        '0',
        '".$tanggal."',
        'ST',
        '".$data_nama."')";
    $query_simpan = mysqli_query($koneksi, $sql_simpan);
    
    // Ambil ID yang baru saja diinsert
    $id_tabungan_baru = mysqli_insert_id($koneksi);

    if ($query_simpan && $id_tabungan_baru) {
        // Simpan ke riwayat saat transaksi dibuat
        $sql_riwayat = "INSERT INTO tb_riwayat (id_tabungan_asli, nis, setor, tarik, tgl, jenis, petugas, status) 
                        VALUES (
                            '".$id_tabungan_baru."',
                            '".mysqli_real_escape_string($koneksi, $_POST['nis'])."',
                            '".$setor_hasil."',
                            '0',
                            '".$tanggal."',
                            'ST',
                            '".mysqli_real_escape_string($koneksi, $data_nama)."',
                            'Aktif'
                        )";
        mysqli_query($koneksi, $sql_riwayat);

        if (!function_exists('logActivity')) {
            $paths = [
                __DIR__ . '/../../inc/activity_log.php',
                dirname(dirname(__DIR__)) . '/inc/activity_log.php',
                'inc/activity_log.php',
                '../../inc/activity_log.php'
            ];
            foreach ($paths as $path) {
                if (file_exists($path)) {
                    include_once $path;
                    break;
                }
            }
        }
        if (function_exists('logActivity')) {
            $sql_siswa = "SELECT nama_siswa FROM tb_siswa WHERE nis='".$_POST['nis']."'";
            $query_siswa = mysqli_query($koneksi, $sql_siswa);
            $data_siswa = mysqli_fetch_assoc($query_siswa);
            $nama_siswa = $data_siswa ? $data_siswa['nama_siswa'] : 'NIS: ' . $_POST['nis'];
            logActivity($koneksi, 'CREATE', 'tb_tabungan', 'Menambah setoran untuk ' . $nama_siswa . ' sebesar Rp ' . number_format($setor_hasil, 0, ',', '.'), $_POST['nis']);
        }

        echo "<script>
        (function(){
            if(typeof Swal!=='undefined'){
                Swal.fire({
                    title:'Berhasil!',
                    text:'Setoran berhasil ditambahkan',
                    icon:'success',
                    confirmButtonText:'OK',
                    confirmButtonColor:'#28a745',
                    allowOutsideClick:false,
                    allowEscapeKey:false,
                    timer:2500,
                    timerProgressBar:true
                }).then(function(){
                    window.location.href='index.php?page=data_setor';
                });
                
                setTimeout(function(){
                    window.location.href='index.php?page=data_setor';
                }, 2500);
            } else {
                alert('Setoran berhasil ditambahkan');
                window.location.href='index.php?page=data_setor';
            }
        })();
        </script>";
    } else {
        echo "<script>
        (function(){
            if(typeof Swal!=='undefined'){
                Swal.fire({
                    title:'Gagal!',
                    text:'Setoran gagal ditambahkan',
                    icon:'error',
                    confirmButtonText:'OK',
                    confirmButtonColor:'#d33'
                });
            } else {
                alert('Setoran gagal ditambahkan');
            }
        })();
        </script>";
    }
}

if (isset($_POST['Ubah'])) {
    //menangkap post setor
    $setor = $_POST['setor'];
    //membuang Rp dan Titik
    $setor_hasil = preg_replace("/[^0-9]/", "", $setor);

    $sql_ubah = "UPDATE tb_tabungan SET
        nis='".$_POST['nis']."',
        setor='".$setor_hasil."',
        tgl='".$tanggal."'
        WHERE id_tabungan='".$_POST['id_tabungan']."'";
    $query_ubah = mysqli_query($koneksi, $sql_ubah);

    if ($query_ubah) {
        if (!function_exists('logActivity')) {
            $paths = [
                __DIR__ . '/../../inc/activity_log.php',
                dirname(dirname(__DIR__)) . '/inc/activity_log.php',
                'inc/activity_log.php',
                '../../inc/activity_log.php'
            ];
            foreach ($paths as $path) {
                if (file_exists($path)) {
                    include_once $path;
                    break;
                }
            }
        }
        if (function_exists('logActivity')) {
            $sql_siswa = "SELECT nama_siswa FROM tb_siswa WHERE nis='".$_POST['nis']."'";
            $query_siswa = mysqli_query($koneksi, $sql_siswa);
            $data_siswa = mysqli_fetch_assoc($query_siswa);
            $nama_siswa = $data_siswa ? $data_siswa['nama_siswa'] : 'NIS: ' . $_POST['nis'];
            logActivity($koneksi, 'UPDATE', 'tb_tabungan', 'Mengubah setoran untuk ' . $nama_siswa . ' menjadi Rp ' . number_format($setor_hasil, 0, ',', '.'), $_POST['nis']);
        }
        
        echo "<script>
        (function(){
            if(typeof Swal!=='undefined'){
                Swal.fire({
                    title:'Berhasil!',
                    text:'Setoran berhasil diubah',
                    icon:'success',
                    confirmButtonText:'OK',
                    confirmButtonColor:'#28a745',
                    allowOutsideClick:false,
                    allowEscapeKey:false,
                    timer:2500,
                    timerProgressBar:true
                }).then(function(){
                    window.location.href='index.php?page=data_setor';
                });
                
                setTimeout(function(){
                    window.location.href='index.php?page=data_setor';
                }, 2500);
            } else {
                alert('Setoran berhasil diubah');
                window.location.href='index.php?page=data_setor';
            }
        })();
        </script>";
    } else {
        echo "<script>
        (function(){
            if(typeof Swal!=='undefined'){
                Swal.fire({
                    title:'Gagal!',
                    text:'Setoran gagal diubah',
                    icon:'error',
                    confirmButtonText:'OK',
                    confirmButtonColor:'#d33'
                });
            } else {
                alert('Setoran gagal diubah');
            }
        })();
        </script>";
    }
}
?>

<section class="content-header">
	<h1>
		Transaksi
		<small>Setoran</small>
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

	<div class="alert alert-success alert-dismissible">
		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
		<h4>
			<i class="icon fa fa-info"></i> Total Setoran</h4>
		<?php
    		$sql = $koneksi->query("SELECT SUM(setor) as total  from tb_tabungan where jenis='ST'");
    		while ($data= $sql->fetch_assoc()) {
  		?>
		<h3>
			<?php echo rupiah($data['total']); }?>
		</h3>
	</div>


	<div class="box box-primary">
		<div class="box-header">
			<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addModal">
				<i class="glyphicon glyphicon-plus"></i> Tambah Data
			</button>
			<button type="button" id="btnEditTerpilih" class="btn btn-success" onclick="editTerpilih()">
				<i class="glyphicon glyphicon-edit"></i> Edit Terpilih
			</button>
			<button type="button" id="btnHapusTerpilih" class="btn btn-danger" onclick="hapusTerpilih()">
				<i class="glyphicon glyphicon-trash"></i> Hapus Terpilih
			</button>
			<div class="btn-group">
				<a href="../../admin/export_handler.php?type=excel&table=setor" class="btn btn-info" title="Ekspor ke Excel">
					<i class="fa fa-file-excel-o"></i> Excel
				</a>
				<a href="../../admin/export_handler.php?type=pdf&table=setor" class="btn btn-danger" title="Ekspor ke PDF" target="_blank">
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
				<form id="formSetor" method="post" action="">
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
								<th>Setoran</th>
								<th>Petugas</th>
								<th>Aksi</th>
							</tr>
						</thead>
						<tbody>

							<?php

                  $no = 1;
				  $sql = $koneksi->query("select s.nis, s.nama_siswa, t.id_tabungan, t.setor, t.tgl, t.petugas from 
				  tb_siswa s join tb_tabungan t on s.nis=t.nis 
				  where jenis ='ST' order by tgl desc, id_tabungan desc");
                  while ($data= $sql->fetch_assoc()) {
                ?>

							<tr data-id="<?php echo $data['id_tabungan']; ?>" data-nis="<?php echo $data['nis']; ?>" data-nama="<?php echo htmlspecialchars($data['nama_siswa']); ?>" data-setor="<?php echo $data['setor']; ?>" data-tgl="<?php echo $data['tgl']; ?>">
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
								<?php echo rupiah($data['setor']); ?>
							</td>
							<td>
								<?php echo $data['petugas']; ?>
							</td>
							<td>

								<button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#editModal"
								data-id="<?php echo $data['id_tabungan']; ?>"
								data-nis="<?php echo $data['nis']; ?>"
								data-nama="<?php echo htmlspecialchars($data['nama_siswa']); ?>"
								data-setor="<?php echo $data['setor']; ?>"
								title="Ubah">
									<i class="glyphicon glyphicon-edit"></i>
								</button>
							<a href="?page=del_setor&kode=<?php echo $data['id_tabungan']; ?>" 
								onclick="return confirmHapusSetor(event, '<?php echo htmlspecialchars($data['nis']); ?>', '<?php echo htmlspecialchars($data['nama_siswa']); ?>', '<?php echo rupiah($data['setor']); ?>')"
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

<!-- Modal Tambah -->
<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="addModalLabel">Tambah Setoran</h4>
            </div>
            <form action="" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Siswa</label>
                        <select name="nis" id="nis_add" class="form-control select2" style="width: 100%;" required>
                            <option value="">-- Pilih --</option>
                            <?php
                            $query = "select * from tb_siswa where status='Aktif'";
                            $hasil = mysqli_query($koneksi, $query);
                            while ($row = mysqli_fetch_array($hasil)) {
                            ?>
                            <option value="<?php echo $row['nis'] ?>">
                                <?php echo $row['nis'] ?> - <?php echo $row['nama_siswa'] ?>
                            </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Saldo Tabungan</label>
                        <input type="text" name="saldo" id="saldo_add" class="form-control" placeholder="Saldo" readonly>
                    </div>
                    <div class="form-group">
                        <label>Setoran</label>
                        <input type="text" name="setor" id="setor_add" class="form-control" placeholder="Jumlah setoran" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <input type="submit" name="Simpan" value="Simpan" class="btn btn-primary">
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="editModalLabel">Ubah Setoran</h4>
            </div>
            <form action="" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id_tabungan" id="id_tabungan_edit">
                    <div class="form-group">
                        <label>Siswa</label>
                        <select name="nis" id="nis_edit" class="form-control select2" style="width: 100%;" required>
                            <option value="">-- Pilih --</option>
                            <?php
                            $query = "select * from tb_siswa";
                            $hasil = mysqli_query($koneksi, $query);
                            while ($row = mysqli_fetch_array($hasil)) {
                            ?>
                            <option value="<?php echo $row['nis'] ?>">
                                <?php echo $row['nis'] ?> - <?php echo $row['nama_siswa'] ?>
                            </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Setoran</label>
                        <input type="text" name="setor" id="setor_edit" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <input type="submit" name="Ubah" value="Ubah" class="btn btn-success">
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function() {
        var waitForJQuery = setInterval(function() {
            if (typeof $ !== 'undefined') {
                clearInterval(waitForJQuery);

                $(document).ready(function() {
                    // Initialize Select2
                    $('.select2').select2();

                    // Helper function for format
                    function formatRupiah(angka, prefix) {
                        var number_string = angka.replace(/[^,\d]/g, '').toString(),
                            split = number_string.split(','),
                            sisa = split[0].length % 3,
                            rupiah = split[0].substr(0, sisa),
                            ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                        if (ribuan) {
                            separator = sisa ? '.' : '';
                            rupiah += separator + ribuan.join('.');
                        }

                        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
                        return prefix == undefined ? rupiah : (rupiah ? 'Rp ' + rupiah : '');
                    }

                    // AJAX for Saldo in Add Modal
                    $('#nis_add').change(function(){
                        var nis = $(this).val();
                        $.ajax({
                            url:"plugins/proses-ajax.php",
                            method:"POST",
                            data:{nis:nis},
                            success:function(data){
                                $('#saldo_add').val(data);
                            }
                        });
                    });

                    // Format Rupiah for Add Modal
                    var setor_add = document.getElementById('setor_add');
                    if(setor_add){
                        setor_add.addEventListener('keyup', function(e) {
                            setor_add.value = formatRupiah(this.value, 'Rp ');
                        });
                    }

                    // Format Rupiah for Edit Modal
                    var setor_edit = document.getElementById('setor_edit');
                    if(setor_edit){
                        setor_edit.addEventListener('keyup', function(e) {
                            setor_edit.value = formatRupiah(this.value, 'Rp ');
                        });
                    }

                    // Handle Edit Modal Data
                    $('#editModal').on('show.bs.modal', function(event) {
                        var button = $(event.relatedTarget);
                        var id = button.data('id');
                        var nis = button.data('nis');
                        var setor = button.data('setor');

                        var modal = $(this);
                        modal.find('#id_tabungan_edit').val(id);
                        modal.find('#nis_edit').val(nis).trigger('change');
                        
                        // Format setor for display
                        var setor_formatted = formatRupiah(setor.toString(), 'Rp ');
                        modal.find('#setor_edit').val(setor_formatted);
                    });
                });
            }
        }, 100);
    })();
</script>

<!-- Modal Edit Multiple -->
<div class="modal fade" id="modalEditMultiple" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header" style="background: linear-gradient(to right, #605ca8, #9c88ff); color: white;">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 0.8;">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title">
					<i class="glyphicon glyphicon-edit"></i> Edit Multiple Setoran
					<span id="countData" style="font-size: 14px; font-weight: normal;"></span>
				</h4>
			</div>
			<form id="formEditMultiple" method="post" action="?page=edit_setor_multiple">
				<div class="modal-body" style="max-height: 500px; overflow-y: auto;">
					<div class="table-responsive">
						<table class="table table-bordered">
							<thead>
								<tr>
									<th>NIS</th>
									<th>Nama</th>
									<th>Setoran</th>
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
// Fungsi global untuk handle check all
function handleCheckAllClick(checkbox) {
	var isChecked = checkbox.checked;
	$('.checkItem').each(function() {
		this.checked = isChecked;
	});
	
	// Toggle tombol
	var checkedCount = $('.checkItem:checked').length;
	if (checkedCount > 0) {
		$('#btnEditTerpilih').prop('disabled', false).removeClass('disabled');
		$('#btnHapusTerpilih').prop('disabled', false).removeClass('disabled');
	} else {
		$('#btnEditTerpilih').prop('disabled', true).addClass('disabled');
		$('#btnHapusTerpilih').prop('disabled', true).addClass('disabled');
	}
}

// Checkbox untuk pilih semua
$(document).ready(function() {
	// Fungsi untuk toggle tombol
	function toggleButtons() {
		var checkedCount = $('.checkItem:checked').length;
		if (checkedCount > 0) {
			$('#btnEditTerpilih').prop('disabled', false).removeClass('disabled');
			$('#btnHapusTerpilih').prop('disabled', false).removeClass('disabled');
		} else {
			$('#btnEditTerpilih').prop('disabled', true).addClass('disabled');
			$('#btnHapusTerpilih').prop('disabled', true).addClass('disabled');
		}
	}
	
	// Checkbox pilih semua
	$(document).on('click', '#checkAll', function() {
		var isChecked = $(this).prop('checked');
		$('.checkItem').prop('checked', isChecked);
		toggleButtons();
	});
	
	// Pastikan checkbox all sync dengan checkbox individual (menggunakan event delegation)
	$(document).on('change', '.checkItem', function() {
		var totalCheckbox = $('.checkItem').length;
		var checkedCount = $('.checkItem:checked').length;
		$('#checkAll').prop('checked', (totalCheckbox > 0 && checkedCount === totalCheckbox));
		toggleButtons();
	});
	
	// Handle checkbox all di DataTable (jika menggunakan DataTable)
	$('#example1').on('draw.dt', function() {
		// Re-initialize checkAll handler setelah DataTable draw
		$(document).off('click', '#checkAll').on('click', '#checkAll', function() {
			var isChecked = $(this).prop('checked');
			$('.checkItem').prop('checked', isChecked);
			toggleButtons();
		});
	});
	
	
	// Inisialisasi: disable tombol di awal
	toggleButtons();
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
		window.location.href = '?page=edit_setor&kode=' + checkedItems[0];
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
	$('#formEditMultiple').find('input[name="setor[]"]').remove();
	$('#formEditMultiple').find('input[name="tgl[]"]').remove();
	
	var count = 0;
	ids.forEach(function(id) {
		var row = $('tr[data-id="' + id + '"]');
		if (row.length > 0) {
			var nis = row.attr('data-nis');
			var nama = row.attr('data-nama');
			var setor = row.attr('data-setor');
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
					name: 'setor[]',
					value: 'Rp ' + formatNumber(setor),
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
			
			tbody.append(tr);
			
			// Tambahkan hidden input untuk id_tabungan
			$('#formEditMultiple').append('<input type="hidden" name="id_tabungan[]" value="' + id + '">');
			count++;
		}
	});
	
	$('#countData').text(count + ' setoran');
	
	$('#modalEditMultiple').modal('show');
	
	// Format angka untuk input setoran (gunakan event delegation karena elemen dibuat dinamis)
	setTimeout(function() {
		$(document).off('keyup', 'input[name="setor[]"]').on('keyup', 'input[name="setor[]"]', function() {
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
			  '<p style="font-size: 16px; margin-bottom: 20px; color: #495057;">Yakin hapus ' + checked + ' setoran terpilih?</p>' +
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
			$('#formSetor').attr('action', '?page=del_setor_multiple').submit();
		}
	});
}

// Fungsi konfirmasi hapus setoran single
function confirmHapusSetor(event, nis, nama, setor) {
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
                      '<h3 style="color: #d33; margin-bottom: 20px; font-weight: bold;">Konfirmasi Hapus Setoran</h3>' +
                      '<p style="font-size: 16px; margin-bottom: 15px; color: #495057;">Yakin hapus setoran berikut?</p>' +
                      '<div style="background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 15px; margin: 15px 0; text-align: left;">' +
                      '<p style="margin: 5px 0;"><strong>NIS:</strong> ' + nis + '</p>' +
                      '<p style="margin: 5px 0;"><strong>Nama:</strong> ' + nama + '</p>' +
                      '<p style="margin: 5px 0;"><strong>Setoran:</strong> ' + setor + '</p>' +
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
            var message = 'Yakin hapus setoran untuk NIS ' + nis + '?\n\nPERINGATAN: Data yang dihapus tidak dapat dikembalikan!';
            if (confirm(message)) {
                window.location.href = url;
            }
        }
    }, 50);
    
    return false;
}

// Ekspos fungsi ke global scope
window.confirmHapusSetor = confirmHapusSetor;

// Pastikan fungsi tersedia saat document ready
if (typeof jQuery !== 'undefined') {
    jQuery(document).ready(function($) {
        window.confirmHapusSetor = confirmHapusSetor;
    });
}
</script>


