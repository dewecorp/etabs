<section class="content-header">
	<h1>
		Pengguna Sistem
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
		<div class="box-header">
			<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addModal">
				<i class="glyphicon glyphicon-plus"></i> Tambah Data
			</button>
			<div class="btn-group">
				<a href="admin/export_handler.php?type=excel&table=pengguna" class="btn btn-info" title="Ekspor ke Excel">
					<i class="fa fa-file-excel-o"></i> Excel
				</a>
				<a href="admin/export_handler.php?type=pdf&table=pengguna" class="btn btn-danger" title="Ekspor ke PDF" target="_blank">
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
							<th>No</th>
							<th>Nama</th>
							<th>Username</th>
							<th>Level</th>
							<th>Aksi</th>
						</tr>
					</thead>
					<tbody>

						<?php
                  $no = 1;
                  $sql = $koneksi->query("select * from tb_pengguna");
                  while ($data= $sql->fetch_assoc()) {
                ?>

						<tr>
							<td>
								<?php echo $no++; ?>
							</td>
							<td>
								<?php echo $data['nama_pengguna']; ?>
							</td>
							<td>
								<?php echo $data['username']; ?>
							</td>
							<td>
								<?php echo $data['level']; ?>
							</td>
							<td>
								<button type="button" class="btn btn-success" data-toggle="modal" data-target="#editModal"
									data-id="<?php echo $data['id_pengguna']; ?>"
									data-nama="<?php echo $data['nama_pengguna']; ?>"
									data-username="<?php echo $data['username']; ?>"
									data-password="<?php echo $data['password']; ?>"
									data-level="<?php echo $data['level']; ?>"
									title="Ubah">
									<i class="glyphicon glyphicon-edit"></i>
								</button>
								<?php if ($data['level'] != 'Administrator'): ?>
								<a href="?page=MyApp/del_pengguna&kode=<?php echo $data['id_pengguna']; ?>"
								 onclick="return confirmHapus(event, 'Yakin hapus pengguna <?php echo htmlspecialchars($data['nama_pengguna']); ?>?')" title="Hapus" class="btn btn-danger">
									<i class="glyphicon glyphicon-trash"></i>
								</a>
								<?php endif; ?>
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

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="addModalLabel">Tambah Pengguna</h4>
      </div>
      <form action="" method="post">
      <div class="modal-body">
        <div class="form-group">
            <label>Nama Pengguna</label>
            <input type="text" name="nama_pengguna" class="form-control" placeholder="Nama pengguna" required>
        </div>
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" class="form-control" placeholder="Username" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>
        <div class="form-group">
            <label>Level</label>
            <select name="level" class="form-control" required>
                <option value="">-- Pilih Level --</option>
                <option value="Administrator">Administrator</option>
                <option value="Petugas">Petugas</option>
            </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
        <input type="submit" name="Simpan" value="Simpan" class="btn btn-primary">
      </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="editModalLabel">Ubah Pengguna</h4>
      </div>
      <form action="" method="post">
      <div class="modal-body">
        <input type="hidden" name="id_pengguna" id="edit_id_pengguna">
        <div class="form-group">
            <label>Nama Pengguna</label>
            <input type="text" name="nama_pengguna" id="edit_nama_pengguna" class="form-control" placeholder="Nama pengguna" required>
        </div>
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" id="edit_username" class="form-control" placeholder="Username" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" id="edit_password" class="form-control" placeholder="Password" required>
            <input type="checkbox" onclick="toggleEditPassword()"> Lihat Password
        </div>
        <div class="form-group">
            <label>Level</label>
            <select name="level" id="edit_level" class="form-control" required>
                <option value="">-- Pilih Level --</option>
                <option value="Administrator">Administrator</option>
                <option value="Petugas">Petugas</option>
            </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
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
                
                $('#editModal').on('show.bs.modal', function (event) {
                    var button = $(event.relatedTarget)
                    var id = button.data('id')
                    var nama = button.data('nama')
                    var username = button.data('username')
                    var password = button.data('password')
                    var level = button.data('level')
                    
                    var modal = $(this)
                    modal.find('#edit_id_pengguna').val(id)
                    modal.find('#edit_nama_pengguna').val(nama)
                    modal.find('#edit_username').val(username)
                    modal.find('#edit_password').val(password)
                    modal.find('#edit_level').val(level)
                })
            }
        }, 100);
    })();

    function toggleEditPassword() {
        var x = document.getElementById("edit_password");
        if (x.type === "password") {
            x.type = "text";
        } else {
            x.type = "password";
        }
    }
</script>

<?php
if (isset ($_POST['Simpan'])){
    $sql_simpan = "INSERT INTO tb_pengguna (nama_pengguna,username,password,level) VALUES (
    '".$_POST['nama_pengguna']."',
    '".$_POST['username']."',
    '".$_POST['password']."',
    '".$_POST['level']."')";
    $query_simpan = mysqli_query($koneksi, $sql_simpan);
    
    if ($query_simpan) {
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
            logActivity($koneksi, 'CREATE', 'tb_pengguna', 'Menambah pengguna: ' . $_POST['nama_pengguna'] . ' (Username: ' . $_POST['username'] . ', Level: ' . $_POST['level'] . ')', $_POST['id_pengguna'] ?? null);
        }

        echo "<script>
        (function(){
            if(typeof Swal!=='undefined'){
                Swal.fire({
                    title:'Berhasil!',
                    text:'Data pengguna berhasil ditambahkan',
                    icon:'success',
                    confirmButtonText:'OK',
                    confirmButtonColor:'#28a745',
                    allowOutsideClick:false,
                    allowEscapeKey:false,
                    timer:2500,
                    timerProgressBar:true
                }).then(function(){
                    window.location.href='index.php?page=MyApp/data_pengguna';
                });
                
                setTimeout(function(){
                    window.location.href='index.php?page=MyApp/data_pengguna';
                }, 2600);
            } else {
                alert('Data pengguna berhasil ditambahkan');
                window.location.href='index.php?page=MyApp/data_pengguna';
            }
        })();
        </script>";
    }else{
        echo "<script>
        (function(){
            if(typeof Swal!=='undefined'){
                Swal.fire({
                    title:'Gagal!',
                    text:'Data pengguna gagal ditambahkan',
                    icon:'error',
                    confirmButtonText:'OK',
                    confirmButtonColor:'#d33'
                });
            } else {
                alert('Data pengguna gagal ditambahkan');
            }
        })();
        </script>";
    }
}

if (isset ($_POST['Ubah'])){
    $sql_ubah = "UPDATE tb_pengguna SET
        nama_pengguna='".$_POST['nama_pengguna']."',
        username='".$_POST['username']."',
        password='".$_POST['password']."',
        level='".$_POST['level']."'
        WHERE id_pengguna='".$_POST['id_pengguna']."'";
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
            logActivity($koneksi, 'UPDATE', 'tb_pengguna', 'Mengubah pengguna: ' . $_POST['nama_pengguna'] . ' (Username: ' . $_POST['username'] . ', Level: ' . $_POST['level'] . ')', $_POST['id_pengguna'] ?? null);
        }

        echo "<script>
        (function(){
            if(typeof Swal!=='undefined'){
                Swal.fire({
                    title:'Berhasil!',
                    text:'Data pengguna berhasil diubah',
                    icon:'success',
                    confirmButtonText:'OK',
                    confirmButtonColor:'#28a745',
                    allowOutsideClick:false,
                    allowEscapeKey:false,
                    timer:2500,
                    timerProgressBar:true
                }).then(function(){
                    window.location.href='index.php?page=MyApp/data_pengguna';
                });
                
                setTimeout(function(){
                    window.location.href='index.php?page=MyApp/data_pengguna';
                }, 2600);
            } else {
                alert('Data pengguna berhasil diubah');
                window.location.href='index.php?page=MyApp/data_pengguna';
            }
        })();
        </script>";
    }else{
        echo "<script>
        (function(){
            if(typeof Swal!=='undefined'){
                Swal.fire({
                    title:'Gagal!',
                    text:'Data pengguna gagal diubah',
                    icon:'error',
                    confirmButtonText:'OK',
                    confirmButtonColor:'#d33'
                });
            } else {
                alert('Data pengguna gagal diubah');
            }
        })();
        </script>";
    }
}
?>


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

