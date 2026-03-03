<section class="content-header">
	<h1>
		Pengguna Sistem
	</h1>
</section>

<!-- Main content -->
<section class="content">
	<div class="rounded-2xl bg-white shadow-sm">
		<div class="border-b border-slate-100 px-6 py-4">
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" class="btn btn-dashboard-primary inline-flex items-center gap-2 tw-modal-open" data-target="#addModal">
                    <i class="fa-solid fa-user-plus text-xs"></i><span>Tambah Data</span>
                </button>
                <div class="inline-flex gap-2">
                    <a href="admin/export_handler.php?type=excel&table=pengguna" class="btn btn-sm btn-dashboard-soft text-[11px]" title="Ekspor ke Excel">
                        <i class="fa-solid fa-file-excel text-xs"></i><span>Excel</span>
                    </a>
                    <a href="admin/export_handler.php?type=pdf&table=pengguna" class="btn btn-sm btn-dashboard-soft text-[11px]" title="Ekspor ke PDF" target="_blank">
                        <i class="fa-solid fa-file-pdf text-xs"></i><span>PDF</span>
                    </a>
                </div>
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
							<th class="text-center" width="30px">No</th>
							<th>Nama</th>
							<th>Username</th>
							<th>Level</th>
							<th class="text-center">Aksi</th>
						</tr>
					</thead>
					<tbody id="datatableUsersBody">

						<?php
                  $no = 1;
                  $sql = $koneksi->query("select * from tb_pengguna");
                  while ($data= $sql->fetch_assoc()) {
                ?>

						<tr>
							<td class="text-center">
								<?php echo $no++; ?>
							</td>
							<td class="font-medium">
								<?php echo $data['nama_pengguna']; ?>
							</td>
							<td>
								<code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs text-indigo-600"><?php echo $data['username']; ?></code>
							</td>
							<td class="px-4 py-3 text-center">
								<?php 
                                if ($data['level'] == 'Administrator') {
                                    echo '<span class="badge-pill badge-pill-primary">Administrator</span>';
                                } else {
                                    echo '<span class="badge-pill badge-pill-success">Petugas</span>';
                                }
                                ?>
							</td>
							<td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" 
                                        class="btn btn-sm btn-dashboard-success tw-modal-open" 
                                        data-target="#editModal" 
                                        data-id="<?php echo $data['id_pengguna']; ?>"
                                        data-nama="<?php echo $data['nama_pengguna']; ?>"
                                        data-username="<?php echo $data['username']; ?>"
                                        data-password="<?php echo $data['password']; ?>"
                                        data-level="<?php echo $data['level']; ?>"
                                        title="Ubah">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <?php if ($data['level'] != 'Administrator'): ?>
                                    <a href="?page=MyApp/del_pengguna&kode=<?php echo $data['id_pengguna']; ?>"
                                    onclick="return confirmHapus(event, 'Yakin hapus pengguna <?php echo htmlspecialchars($data['nama_pengguna']); ?>?')" title="Hapus" class="btn btn-sm btn-dashboard-danger">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
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

<!-- Add Modal -->
<div class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm modal" id="addModal">
    <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl transition-all">
        <!-- Header -->
        <div class="mb-5 flex items-center justify-between border-b border-slate-100 pb-4">
            <h3 class="text-lg font-semibold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-user-plus text-indigo-500"></i>
                Tambah Pengguna
            </h3>
            <button type="button" class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700 tw-modal-close transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        
        <!-- Body -->
        <form action="" method="post">
            <div class="space-y-4">
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-slate-700">Nama Pengguna</label>
                    <input type="text" name="nama_pengguna" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20" placeholder="Nama lengkap" required>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-slate-700">Username</label>
                        <input type="text" name="username" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20" placeholder="Username" required>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-slate-700">Level</label>
                        <select name="level" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20" required>
                            <option value="">-- Pilih Level --</option>
                            <option value="Administrator">Administrator</option>
                            <option value="Petugas">Petugas</option>
                        </select>
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-slate-700">Password</label>
                    <input type="password" name="password" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20" placeholder="Password" required>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="mt-8 flex justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-200/50 tw-modal-close transition-all">
                    Batal
                </button>
                <button type="submit" name="Simpan" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm modal" id="editModal">
    <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl transition-all">
        <!-- Header -->
        <div class="mb-5 flex items-center justify-between border-b border-slate-100 pb-4">
            <h3 class="text-lg font-semibold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-user-pen text-indigo-500"></i>
                Ubah Pengguna
            </h3>
            <button type="button" class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700 tw-modal-close transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        
        <!-- Body -->
        <form action="" method="post">
            <div class="space-y-4">
                <input type="hidden" name="id_pengguna" id="edit_id_pengguna">
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-slate-700">Nama Pengguna</label>
                    <input type="text" name="nama_pengguna" id="edit_nama_pengguna" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20" required>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-slate-700">Username</label>
                        <input type="text" name="username" id="edit_username" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20" required>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-slate-700">Level</label>
                        <select name="level" id="edit_level" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20" required>
                            <option value="">-- Pilih Level --</option>
                            <option value="Administrator">Administrator</option>
                            <option value="Petugas">Petugas</option>
                        </select>
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-slate-700">Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="edit_password" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20" required>
                        <button type="button" onclick="toggleEditPassword()" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <i id="eye_icon" class="fa-solid fa-eye text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="mt-8 flex justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-200/50 tw-modal-close transition-all">
                    Batal
                </button>
                <button type="submit" name="Ubah" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).on('click', '.tw-modal-open', function (event) {
        event.preventDefault();
        var target = $(this).data('target');
        
        if (target === '#editModal') {
            var id = $(this).data('id');
            var nama = $(this).data('nama');
            var username = $(this).data('username');
            var password = $(this).data('password');
            var level = $(this).data('level');
            
            $('#edit_id_pengguna').val(id);
            $('#edit_nama_pengguna').val(nama);
            $('#edit_username').val(username);
            $('#edit_password').val(password);
            $('#edit_level').val(level);
        }
        $(target).removeClass('hidden').addClass('flex');
    });

    $(document).on('click', '.tw-modal-close', function () {
        $(this).closest('.modal').addClass('hidden').removeClass('flex');
    });

    $(document).on('click', '.modal', function (e) {
        if ($(e.target).hasClass('modal')) { $(this).addClass('hidden').removeClass('flex'); }
    });

    function toggleEditPassword() {
        var x = document.getElementById("edit_password");
        var icon = document.getElementById("eye_icon");
        if (x.type === "password") {
            x.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        } else {
            x.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
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

