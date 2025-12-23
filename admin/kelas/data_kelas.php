<section class="content-header">
	<h1>
		Master Data
		<small>Kelas</small>
	</h1>
	<ol class="breadcrumb">
		<li>
			<a href="index.php">
				<i class="fa fa-home"></i>
				<b>eTABS</b>
			</a>
		</li>
	</ol>
</section>
<!-- Main content -->
<section class="content">
	<div class="box box-primary">
		<div class="box-header with-border">
			<a href="?page=MyApp/add_kelas" title="Tambah Data" class="btn btn-primary">
				<i class="glyphicon glyphicon-plus"></i> Tambah Data</a>
			<div class="btn-group">
				<a href="admin/export_handler.php?type=excel&table=kelas" class="btn btn-info" title="Ekspor ke Excel">
					<i class="fa fa-file-excel-o"></i> Excel
				</a>
				<a href="admin/export_handler.php?type=pdf&table=kelas" class="btn btn-danger" title="Ekspor ke PDF" target="_blank">
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
				<table id="example1" class="table table-bordered table-striped">
					<thead>
						<tr>
							<th class="text-center" width=30px>No</th>
							<th class="text-center">Kelas</th>
							<th class="text-center">Aksi</th>
						</tr>
					</thead>
					<tbody>

				<?php
                  $no = 1;
                  $sql = $koneksi->query("SELECT * FROM tb_kelas");
                  while ($data= $sql->fetch_assoc()) {
                ?>

						<tr>
							<td class="text-center">
								<?php echo $no++; ?>
							</td>
							<td>
								<?php echo $data['kelas']; ?>
							</td>
							<td class="text-center">
								<a href="?page=MyApp/edit_kelas&kode=<?php echo $data['id_kelas']; ?>" title="Ubah"
								 class="btn btn-success">
									<i class="glyphicon glyphicon-edit"></i>
								</a>
								<a href="?page=MyApp/del_kelas&kode=<?php echo $data['id_kelas']; ?>" 
									onclick="return confirmHapus(event, 'Yakin hapus kelas <?php echo htmlspecialchars($data['kelas']); ?>?')"
									title="Hapus" class="btn btn-danger">
									<i class="glyphicon glyphicon-trash"></i>
									</>
							</td>
						</tr>
						<?php
                  }
                ?>
					</tbody>

				</table>
			</div>
		</div>
	</div>
</section>

<script>
// Pastikan fungsi confirmHapus didefinisikan setelah semua library dimuat
(function() {
    function confirmHapus(event, message) {
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
                          '<h3 style="color: #d33; margin-bottom: 20px; font-weight: bold;">Konfirmasi Hapus</h3>' +
                          '<p style="font-size: 16px; margin-bottom: 20px; color: #495057;">' + message + '</p>' +
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
                if (confirm(message + '\n\nPERINGATAN: Data yang dihapus tidak dapat dikembalikan!')) {
                    window.location.href = url;
                }
            }
        }, 50);
        
        return false;
    }
    
    // Ekspos fungsi ke global scope
    window.confirmHapus = confirmHapus;
    
    // Jika jQuery tersedia, juga attach setelah ready
    if (typeof jQuery !== 'undefined') {
        jQuery(document).ready(function($) {
            window.confirmHapus = confirmHapus;
        });
    }
})();
</script>

