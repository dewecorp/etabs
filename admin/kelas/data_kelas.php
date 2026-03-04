<section class="content-header">
	<h1>
		Master Data
		<small>Kelas</small>
	</h1>
</section>
<!-- Main content -->
<section class="content">
	<div class="rounded-2xl bg-white shadow-sm">
		<div class="border-b border-slate-100 px-6 py-4">
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" class="btn btn-dashboard-primary inline-flex items-center gap-2 tw-modal-open" data-target="#addModal">
                    <i class="fa-solid fa-plus text-xs"></i><span>Tambah Data</span>
                </button>
                <div class="inline-flex gap-2">
                    <a href="admin/export_handler.php?type=excel&table=kelas" class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-600/20 hover:bg-emerald-100" title="Ekspor ke Excel">
                        <i class="fa-solid fa-file-excel text-xs"></i><span>Excel</span>
                    </a>
                    <a href="admin/export_handler.php?type=pdf&table=kelas" class="inline-flex items-center gap-1.5 rounded-xl bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 ring-1 ring-rose-600/20 hover:bg-rose-100" title="Ekspor ke PDF" target="_blank">
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
							<th class="text-center">Kelas</th>
							<th class="text-center">Aksi</th>
						</tr>
					</thead>
					<tbody id="datatableUsersBody">

				<?php
                  $no = 1;
                  $sql = $koneksi->query("SELECT * FROM tb_kelas");
                  while ($data= $sql->fetch_assoc()) {
                ?>

						<tr>
							<td class="text-center">
								<?php echo $no++; ?>
							</td>
							<td class="text-center font-medium">
								<?php echo $data['kelas']; ?>
							</td>
							<td class="text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" class="btn btn-sm btn-dashboard-success tw-modal-open" 
                                        data-target="#editModal"
                                        data-id="<?php echo $data['id_kelas']; ?>"
                                        data-kelas="<?php echo $data['kelas']; ?>"
                                        title="Ubah">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <a href="?page=MyApp/del_kelas&kode=<?php echo $data['id_kelas']; ?>" 
                                        onclick="return confirmHapus(event, 'Yakin hapus kelas <?php echo htmlspecialchars($data['kelas']); ?>?')"
                                        title="Hapus" class="btn btn-sm btn-dashboard-danger">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
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
<div class="fixed inset-0 z-[120] hidden items-center justify-center bg-black/50 backdrop-blur-sm modal" id="addModal">
    <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl transition-all">
        <!-- Header -->
        <div class="mb-5 flex items-center justify-between border-b border-slate-100 pb-4">
            <h3 class="text-lg font-semibold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-plus-circle text-indigo-500"></i>
                Tambah Kelas
            </h3>
            <button type="button" class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700 tw-modal-close transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        
        <!-- Body -->
        <form action="" method="post">
            <div class="space-y-4">
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-slate-700">Nama Kelas</label>
                    <input type="text" name="kelas" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20" placeholder="Contoh: X IPA 1" required>
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
                <i class="fa-solid fa-pen-to-square text-indigo-500"></i>
                Ubah Kelas
            </h3>
            <button type="button" class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700 tw-modal-close transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        
        <!-- Body -->
        <form action="" method="post">
            <div class="space-y-4">
                <input type="hidden" name="id_kelas" id="edit_id_kelas">
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-slate-700">Nama Kelas</label>
                    <input type="text" name="kelas" id="edit_kelas" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20" placeholder="Contoh: X IPA 1" required>
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
    // Handler untuk modal edit
    $(document).on('click', '.tw-modal-open', function (event) {
        event.preventDefault();
        var target = $(this).data('target');
        
        // Khusus untuk modal edit, ambil data dari tombol
        if (target === '#editModal') {
            var id = $(this).data('id');
            var kelas = $(this).data('kelas');
            
            $('#edit_id_kelas').val(id);
            $('#edit_kelas').val(kelas);
        }
        
        // Tampilkan modal dengan transisi
        $(target).removeClass('hidden').addClass('flex');
    });

    // Handler untuk menutup modal
    $(document).on('click', '.tw-modal-close', function () {
        $(this).closest('.modal').addClass('hidden').removeClass('flex');
    });

    // Klik di luar modal untuk menutup
    $(document).on('click', '.modal', function (e) {
        if ($(e.target).hasClass('modal')) { 
            $(this).addClass('hidden').removeClass('flex'); 
        }
    });
</script>

<?php
if (isset ($_POST['Simpan'])){
    $sql_simpan = "INSERT INTO tb_kelas (kelas) VALUES ('".$_POST['kelas']."')";
    $query_simpan = mysqli_query($koneksi, $sql_simpan);
    
    if ($query_simpan){
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
            logActivity($koneksi, 'CREATE', 'tb_kelas', 'Menambah kelas: ' . $_POST['kelas'], $_POST['id_kelas'] ?? null);
        }

        echo "<script>
        (function(){
            if(typeof Swal!=='undefined'){
                Swal.fire({
                    title:'Berhasil!',
                    text:'Data kelas berhasil ditambahkan',
                    icon:'success',
                    confirmButtonText:'OK',
                    confirmButtonColor:'#28a745',
                    allowOutsideClick:false,
                    allowEscapeKey:false,
                    timer:2500,
                    timerProgressBar:true
                }).then(function(){
                    window.location.href='index.php?page=MyApp/data_kelas';
                });
                
                setTimeout(function(){
                    window.location.href='index.php?page=MyApp/data_kelas';
                }, 2600);
            } else {
                alert('Data kelas berhasil ditambahkan');
                window.location.href='index.php?page=MyApp/data_kelas';
            }
        })();
        </script>";
    }else{
        echo "<script>
        (function(){
            if(typeof Swal!=='undefined'){
                Swal.fire({
                    title:'Gagal!',
                    text:'Data kelas gagal ditambahkan',
                    icon:'error',
                    confirmButtonText:'OK',
                    confirmButtonColor:'#d33'
                });
            } else {
                alert('Data kelas gagal ditambahkan');
            }
        })();
        </script>";
    }
}

if (isset ($_POST['Ubah'])){
    $sql_ubah = "UPDATE tb_kelas SET
        kelas='".$_POST['kelas']."'
        WHERE id_kelas='".$_POST['id_kelas']."'";
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
            logActivity($koneksi, 'UPDATE', 'tb_kelas', 'Mengubah kelas: ' . $_POST['kelas'], $_POST['id_kelas'] ?? null);
        }
        
        echo "<script>
        (function(){
            if(typeof Swal!=='undefined'){
                Swal.fire({
                    title:'Berhasil!',
                    text:'Data kelas berhasil diubah',
                    icon:'success',
                    confirmButtonText:'OK',
                    confirmButtonColor:'#28a745',
                    allowOutsideClick:false,
                    allowEscapeKey:false,
                    timer:2500,
                    timerProgressBar:true
                }).then(function(){
                    window.location.href='index.php?page=MyApp/data_kelas';
                });
                
                setTimeout(function(){
                    window.location.href='index.php?page=MyApp/data_kelas';
                }, 2600);
            } else {
                alert('Data kelas berhasil diubah');
                window.location.href='index.php?page=MyApp/data_kelas';
            }
        })();
        </script>";
    }else{
        echo "<script>
        (function(){
            if(typeof Swal!=='undefined'){
                Swal.fire({
                    title:'Gagal!',
                    text:'Data kelas gagal diubah',
                    icon:'error',
                    confirmButtonText:'OK',
                    confirmButtonColor:'#d33'
                });
            } else {
                alert('Data kelas gagal diubah');
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

