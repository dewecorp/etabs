<!-- Content Header (Page header) -->
<?php 
$data_nama = $_SESSION["ses_nama"];

date_default_timezone_set("Asia/Jakarta"); 
$tanggal = date("Y-m-d");

if (isset($_POST['Simpan'])) {
    //menangkap post
    $tarik = $_POST['tarik'];
    //membuang Rp dan Titik
    $tarik_hasil = preg_replace("/[^0-9]/", "", $tarik);
    
    // Calculate saldo from DB directly for security
    $sql_saldo_db = "SELECT sum(setor)-sum(tarik) as total FROM tb_tabungan WHERE nis='".$_POST['nis']."'";
    $q_saldo_db = mysqli_query($koneksi, $sql_saldo_db);
    $d_saldo_db = mysqli_fetch_array($q_saldo_db);
    $saldo_db = $d_saldo_db['total'];

    if ($saldo_db >= $tarik_hasil) {
        $sql_simpan = "INSERT INTO tb_tabungan (nis,setor,tarik,tgl,jenis,petugas) VALUES (
            '".$_POST['nis']."',
            '0',
            '".$tarik_hasil."',
            '".$tanggal."',
            'TR',
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
                                '0',
                                '".$tarik_hasil."',
                                '".$tanggal."',
                                'TR',
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
                logActivity($koneksi, 'CREATE', 'tb_tabungan', 'Menambah penarikan untuk ' . $nama_siswa . ' sebesar Rp ' . number_format($tarik_hasil, 0, ',', '.'), $_POST['nis']);
            }

            echo "<script>
            (function(){
                if(typeof Swal!=='undefined'){
                    Swal.fire({
                        title:'Berhasil!',
                        text:'Penarikan berhasil ditambahkan',
                        icon:'success',
                        confirmButtonText:'OK',
                        confirmButtonColor:'#28a745',
                        allowOutsideClick:false,
                        allowEscapeKey:false,
                        timer:2500,
                        timerProgressBar:true
                    }).then(function(){
                        window.location.href='index.php?page=data_tarik';
                    });
                    
                    setTimeout(function(){
                        window.location.href='index.php?page=data_tarik';
                    }, 2500);
                } else {
                    alert('Penarikan berhasil ditambahkan');
                    window.location.href='index.php?page=data_tarik';
                }
            })();
            </script>";
        } else {
            echo "<script>
            (function(){
                if(typeof Swal!=='undefined'){
                    Swal.fire({
                        title:'Gagal!',
                        text:'Penarikan gagal ditambahkan',
                        icon:'error',
                        confirmButtonText:'OK',
                        confirmButtonColor:'#d33'
                    });
                } else {
                    alert('Penarikan gagal ditambahkan');
                }
            })();
            </script>";
        }
    } else {
        echo "<script>
        (function(){
            if(typeof Swal!=='undefined'){
                Swal.fire({
                    title:'Gagal!',
                    text:'Saldo tidak mencukupi',
                    icon:'error',
                    confirmButtonText:'OK',
                    confirmButtonColor:'#d33'
                });
            } else {
                alert('Saldo tidak mencukupi');
            }
        })();
        </script>";
    }
}

if (isset($_POST['Ubah'])) {
    //menangkap post tarik
    $tarik = $_POST['tarik'];
    //membuang Rp dan Titik
    $tarik_hasil = preg_replace("/[^0-9]/", "", $tarik);

    $id_tabungan = $_POST['id_tabungan'];
    $nis_baru = $_POST['nis'];
    
    // Get existing data to know the old amount and old NIS
    $sql_cek = "SELECT * FROM tb_tabungan WHERE id_tabungan='$id_tabungan'";
    $query_cek = mysqli_query($koneksi, $sql_cek);
    $data_cek = mysqli_fetch_array($query_cek);
    $nis_lama = $data_cek['nis'];
    $tarik_lama = $data_cek['tarik'];
    
    // Calculate max withdrawable
    if ($nis_baru == $nis_lama) {
        // Same student: Current Balance + Old Amount
        $sql_saldo = "SELECT sum(setor)-sum(tarik) as total FROM tb_tabungan WHERE nis='$nis_baru'";
        $q_saldo = mysqli_query($koneksi, $sql_saldo);
        $d_saldo = mysqli_fetch_array($q_saldo);
        $saldo_saat_ini = $d_saldo['total'];
        $batas = $saldo_saat_ini + $tarik_lama;
    } else {
        // Different student: Just Current Balance of new student
        $sql_saldo = "SELECT sum(setor)-sum(tarik) as total FROM tb_tabungan WHERE nis='$nis_baru'";
        $q_saldo = mysqli_query($koneksi, $sql_saldo);
        $d_saldo = mysqli_fetch_array($q_saldo);
        $batas = $d_saldo['total'];
    }

    if ($batas >= $tarik_hasil) {
        $sql_ubah = "UPDATE tb_tabungan SET
            nis='".$_POST['nis']."',
            tarik='".$tarik_hasil."',
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
                logActivity($koneksi, 'UPDATE', 'tb_tabungan', 'Mengubah penarikan untuk ' . $nama_siswa . ' menjadi Rp ' . number_format($tarik_hasil, 0, ',', '.'), $_POST['nis']);
            }
            
            echo "<script>
            (function(){
                if(typeof Swal!=='undefined'){
                    Swal.fire({
                        title:'Berhasil!',
                        text:'Penarikan berhasil diubah',
                        icon:'success',
                        confirmButtonText:'OK',
                        confirmButtonColor:'#28a745',
                        allowOutsideClick:false,
                        allowEscapeKey:false,
                        timer:2500,
                        timerProgressBar:true
                    }).then(function(){
                        window.location.href='index.php?page=data_tarik';
                    });
                    
                    setTimeout(function(){
                        window.location.href='index.php?page=data_tarik';
                    }, 2500);
                } else {
                    alert('Penarikan berhasil diubah');
                    window.location.href='index.php?page=data_tarik';
                }
            })();
            </script>";
        } else {
            echo "<script>
            (function(){
                if(typeof Swal!=='undefined'){
                    Swal.fire({
                        title:'Gagal!',
                        text:'Penarikan gagal diubah',
                        icon:'error',
                        confirmButtonText:'OK',
                        confirmButtonColor:'#d33'
                    });
                } else {
                    alert('Penarikan gagal diubah');
                }
            })();
            </script>";
        }
    } else {
        echo "<script>
        (function(){
            if(typeof Swal!=='undefined'){
                Swal.fire({
                    title:'Gagal!',
                    text:'Saldo tidak mencukupi untuk perubahan ini',
                    icon:'error',
                    confirmButtonText:'OK',
                    confirmButtonColor:'#d33'
                });
            } else {
                alert('Saldo tidak mencukupi untuk perubahan ini');
            }
        })();
        </script>";
    }
}
?>

<section class="content-header">
	<h1>
		Transaksi Penarikan
	</h1>
</section>
<!-- Main content -->

<section class="content">

	<!-- Info Banner -->
	<div class="mb-6 rounded-2xl bg-gradient-to-r from-rose-500 to-pink-600 p-6 text-white shadow-lg relative overflow-hidden">
        <div class="absolute right-0 top-0 h-full w-1/3 bg-white/10 skew-x-12 transform"></div>
        <div class="relative z-10 flex items-center justify-between gap-6">
            <div>
                <h4 class="text-lg font-semibold flex items-center gap-2 mb-2">
                    <i class="fa-solid fa-money-bill-transfer"></i> Total Penarikan
                </h4>
                <div class="text-3xl font-bold tracking-tight">
                    <?php
                        $sql = $koneksi->query("SELECT SUM(tarik) as total  from tb_tabungan where jenis='TR'");
                        while ($data= $sql->fetch_assoc()) {
                            echo rupiah($data['total']); 
                        }
                    ?>
                </div>
            </div>
            <div class="hidden md:block">
                <i class="fa-solid fa-hand-holding-dollar text-6xl text-white/20"></i>
            </div>
        </div>
	</div>

	<div class="rounded-2xl bg-white shadow-sm">
		<div class="flex flex-col md:flex-row items-center justify-between gap-4 border-b border-slate-100 px-6 py-4">
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" class="btn btn-dashboard-primary inline-flex items-center gap-2 tw-modal-open" data-target="#addModal">
                    <i class="fa-solid fa-plus text-xs"></i><span>Tambah Data</span>
                </button>
                <button type="button" id="btnEditTerpilih" class="btn btn-dashboard-soft inline-flex items-center gap-2 disabled:opacity-50" onclick="editTerpilih()" disabled>
                    <i class="fa-solid fa-pen-to-square text-xs"></i><span>Edit Terpilih</span>
                </button>
                <button type="button" id="btnHapusTerpilih" class="btn btn-dash-danger inline-flex items-center gap-2 disabled:opacity-50" onclick="hapusTerpilih()" disabled>
                    <i class="fa-solid fa-trash text-xs"></i><span>Hapus Terpilih</span>
                </button>
            </div>
            
			<div class="flex items-center gap-2">
				<a href="admin/export_handler.php?type=excel&table=tarik" class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-600/20 hover:bg-emerald-100" title="Ekspor ke Excel">
					<i class="fa-solid fa-file-excel"></i><span>Excel</span>
				</a>
				<a href="admin/export_handler.php?type=pdf&table=tarik" class="inline-flex items-center gap-1.5 rounded-xl bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 ring-1 ring-rose-600/20 hover:bg-rose-100" title="Ekspor ke PDF" target="_blank">
					<i class="fa-solid fa-file-pdf"></i><span>PDF</span>
				</a>
			</div>
		</div>
		<!-- /.box-header -->
		<div class="p-6">
			<div class="table-responsive">
				<form id="formTarik" method="post" action="">
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
									<input type="checkbox" id="checkAll" title="Pilih Semua" onchange="handleCheckAllClick(this)">
								</th>
								<th class="px-4 py-3 font-medium text-center" width="40px">No</th>
								<th class="px-4 py-3 font-medium">NIS</th>
								<th class="px-4 py-3 font-medium">Nama</th>
								<th class="px-4 py-3 font-medium">Tanggal</th>
								<th class="px-4 py-3 font-medium">Tarikan</th>
								<th class="px-4 py-3 font-medium">Petugas</th>
								<th class="px-4 py-3 font-medium text-center">Aksi</th>
							</tr>
						</thead>
						<tbody id="datatableUsersBody" class="divide-y divide-slate-200  bg-white">
							<?php

                  $no = 1;
				  $sql = $koneksi->query("select s.nis, s.nama_siswa, t.id_tabungan, t.tarik, t.tgl, t.petugas from 
				  tb_siswa s join tb_tabungan t on s.nis=t.nis 
				  where jenis ='TR' order by tgl desc, id_tabungan desc");
                  while ($data= $sql->fetch_assoc()) {
                ?>

							<tr class="hover:bg-slate-50  transition-colors" data-id="<?php echo $data['id_tabungan']; ?>" data-nis="<?php echo $data['nis']; ?>" data-nama="<?php echo htmlspecialchars($data['nama_siswa']); ?>" data-tarik="<?php echo $data['tarik']; ?>" data-tgl="<?php echo $data['tgl']; ?>">
								<td class="px-4 py-3 text-center">
									<input type="checkbox" name="id_tabungan[]" class="checkItem rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" value="<?php echo $data['id_tabungan']; ?>">
								</td>
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
                                <span class="badge-pill badge-pill-secondary inline-flex items-center gap-2">
                                    <i class="fa-regular fa-calendar"></i>
                                    <?php  $tgl = $data['tgl']; echo date("d M Y", strtotime($tgl))?>
                                </span>
							</td>
							<td class="px-4 py-3 text-left font-medium text-rose-600">
								<span class="whitespace-nowrap"><?php echo rupiah($data['tarik']); ?></span>
							</td>
							<td class="px-4 py-3">
                                <span class="badge-pill badge-pill-primary">
                                    <?php echo $data['petugas']; ?>
                                </span>
							</td>
							<td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" class="inline-flex items-center rounded-xl px-3 py-2 text-xs bg-emerald-600 text-white hover:bg-emerald-500 tw-modal-open" 
                                        data-target="#editModal"
                                        data-id="<?php echo $data['id_tabungan']; ?>"
                                        data-nis="<?php echo $data['nis']; ?>"
                                        data-nama="<?php echo htmlspecialchars($data['nama_siswa']); ?>"
                                        data-tarik="<?php echo $data['tarik']; ?>"
                                        title="Ubah">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </button>
                                    <a href="?page=del_tarik&kode=<?php echo $data['id_tabungan']; ?>" 
                                        onclick="return confirmHapusTarik(event, '<?php echo htmlspecialchars($data['nis']); ?>', '<?php echo htmlspecialchars($data['nama_siswa']); ?>', '<?php echo rupiah($data['tarik']); ?>')"
                                        title="Hapus" class="inline-flex items-center rounded-xl px-3 py-2 text-xs bg-rose-600 text-white hover:bg-rose-500">
                                        <i class="fa-solid fa-trash text-xs"></i>
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
				</form>
			</div>
		</div>
	</div>
</section>

<!-- Modal Tambah -->
<div class="fixed inset-0 z-[120] hidden items-center justify-center bg-black/50 backdrop-blur-sm modal" id="addModal">
    <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl  transition-all">
        <!-- Header -->
        <div class="mb-5 flex items-center justify-between border-b border-slate-100 pb-4">
            <h3 class="text-lg font-semibold text-slate-900  flex items-center gap-2">
                <i class="fa-solid fa-circle-minus text-indigo-500"></i>
                Tambah Penarikan
            </h3>
            <button type="button" class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700    tw-modal-close transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        
        <!-- Body -->
        <form action="" method="post" enctype="multipart/form-data">
            <div class="space-y-4">
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-slate-700">Pilih Siswa</label>
                    <div class="relative">
                    <select name="nis" id="nis_add" class="auth-input appearance-none pr-9" required>
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
                    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </span>
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-slate-700  Tabungan</label>">
                    <input type="text" name="saldo" id="saldo_add" class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-500 focus:outline-none    placeholder="Saldo saat ini" readonly>
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-slate-700  Penarikan</label>">
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <span class="text-slate-500">
                        </div>
                        <input type="text" name="tarik" id="tarik_add" class="block w-full rounded-xl border border-slate-200 bg-white pl-10 pr-4 py-2.5 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20     placeholder="0" required>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="mt-8 flex justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-200/50      tw-modal-close transition-all">
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

<!-- Modal Edit -->
<div class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm modal" id="editModal">
    <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl  transition-all">
        <!-- Header -->
        <div class="mb-5 flex items-center justify-between border-b border-slate-100 pb-4">
            <h3 class="text-lg font-semibold text-slate-900  flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-indigo-500"></i>
                Ubah Penarikan
            </h3>
            <button type="button" class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700    tw-modal-close transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        
        <!-- Body -->
        <form action="" method="post" enctype="multipart/form-data">
            <div class="space-y-4">
                <input type="hidden" name="id_tabungan" id="id_tabungan_edit">
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-slate-700">Pilih Siswa</label>
                    <div class="relative">
                    <select name="nis" id="nis_edit" class="auth-input appearance-none pr-9" required>
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
                    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </span>
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-slate-700  Penarikan</label>">
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <span class="text-slate-500">
                        </div>
                        <input type="text" name="tarik" id="tarik_edit" class="block w-full rounded-xl border border-slate-200 bg-white pl-10 pr-4 py-2.5 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20     required>">
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="mt-8 flex justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-200/50      tw-modal-close transition-all">
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

<!-- Modal Edit Multiple -->
<div class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm modal" id="modalEditMultiple">
    <div class="relative w-full max-w-6xl rounded-2xl bg-white p-6 shadow-xl  overflow-hidden flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div class="mb-5 flex items-center justify-between border-b border-slate-100 pb-4  shrink-0">
            <h3 class="text-lg font-semibold text-slate-900  flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-indigo-500"></i>
                Edit Multiple Tarikan
                <span id="countData" class="ml-2 rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-600">
            </h3>
            <button type="button" class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700    tw-modal-close transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        
        <!-- Body -->
        <form id="formEditMultiple" method="post" action="?page=edit_tarik_multiple" class="flex flex-col flex-1 overflow-hidden">
            <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar">
                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-xs uppercase text-slate-500   sticky top-0 z-10">
                            <tr>
                                <th class="px-4 py-3 font-medium">NIS</th>
                                <th class="px-4 py-3 font-medium">Nama</th>
                                <th class="px-4 py-3 font-medium">Tarikan</th>
                                <th class="px-4 py-3 font-medium">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyEditMultiple" class="divide-y divide-slate-200  bg-white">
                            <!-- Data akan diisi via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="mt-5 flex justify-end gap-3 pt-4 border-t border-slate-100  shrink-0">
                <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-200/50      tw-modal-close transition-all">
                    Batal
                </button>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Simpan Semua</span>
                </button>
            </div>
        </form>
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
(function() {
    var waitForJQuery = setInterval(function() {
        if (typeof $ !== 'undefined') {
            clearInterval(waitForJQuery);

            $(document).ready(function() {
                // Initialize Select2
                $('.select2').each(function () {
                    var $select = $(this);
                    var $modalParent = $select.closest('.modal');
                    if ($modalParent.length) {
                        $select.select2({ dropdownParent: $modalParent });
                    } else {
                        $select.select2();
                    }
                });

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

                $(document).on('click', '.tw-modal-open', function (event) {
                    event.preventDefault();
                    var target = $(this).data('target');
                    if (!target) return;

                    if (target === '#editModal') {
                        var id = $(this).data('id');
                        var nis = $(this).data('nis');
                        var tarik = $(this).data('tarik');

                        $('#id_tabungan_edit').val(id);
                        $('#nis_edit').val(nis).trigger('change');
                        $('#tarik_edit').val(formatRupiah(String(tarik == null ? '' : tarik), 'Rp '));
                    }

                    $(target).removeClass('hidden').addClass('flex');
                });

                $(document).on('click', '.tw-modal-close', function () {
                    $(this).closest('.modal').addClass('hidden').removeClass('flex');
                });

                $(document).on('click', '.modal', function (e) {
                    if ($(e.target).hasClass('modal')) {
                        $(this).addClass('hidden').removeClass('flex');
                    }
                });

                // Format Rupiah for Add Modal
                var tarik_add = document.getElementById('tarik_add');
                if(tarik_add){
                    tarik_add.addEventListener('keyup', function(e) {
                        tarik_add.value = formatRupiah(this.value, 'Rp ');
                    });
                }

                // Format Rupiah for Edit Modal
                var tarik_edit = document.getElementById('tarik_edit');
                if(tarik_edit){
                    tarik_edit.addEventListener('keyup', function(e) {
                        tarik_edit.value = formatRupiah(this.value, 'Rp ');
                    });
                }

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
        }
    }, 100);
})();

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
            customClass: {
                popup: 'rounded-3xl border border-slate-200 shadow-xl',
                title: 'text-lg font-semibold text-slate-900',
                confirmButton: 'rounded-xl px-4 py-2 text-sm font-medium',
            }
		});
		return;
	}
	
	var checkedItems = [];
	$('.checkItem:checked').each(function() {
		checkedItems.push($(this).val());
	});
	
	// Jika hanya 1 data, buka modal edit dan isi data
	if (checked == 1) {
        var id = checkedItems[0];
        var row = $('tr[data-id="' + id + '"]');
        if (row.length > 0) {
            var nis = row.attr('data-nis');
            var tarik = row.attr('data-tarik');
            $('#id_tabungan_edit').val(id);
            $('#nis_edit').val(nis).trigger('change');
            $('#tarik_edit').val(formatNumber(String(tarik)));
            $('#tarik_edit').val((function(v){ var s=v.replace(/[^,\\d]/g,''); var a=s.split(','); var si=a[0].length%3; var r=a[0].substr(0,si); var rb=a[0].substr(si).match(/\\d{3}/gi); if(rb){ var sep=si?'.':''; r+=sep+rb.join('.'); } r=a[1]!=undefined? r+','+a[1] : r; return 'Rp '+r; })(String(tarik)));
            $('#editModal').removeClass('hidden').addClass('flex');
        }
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
	
	$('#modalEditMultiple').removeClass('hidden').addClass('flex');
	
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
		title: 'Konfirmasi Hapus',
		html: '<div style="text-align:left;">' +
			  '<div style="margin-bottom:10px; font-size:14px; color:#334155;">Yakin hapus <strong>' + checked + '</strong> penarikan terpilih?</div>' +
			  '<div style="background-color:#fff3cd; border:1px solid #ffc107; border-radius:10px; padding:10px;">' +
			  '<div style="display:flex; gap:10px; align-items:flex-start; color:#856404; font-size:13px; font-weight:600;">' +
			  '<i class="fa fa-warning" style="margin-top:2px;"></i>' +
			  '<div>Data yang dihapus tidak dapat dikembalikan.</div>' +
			  '</div>' +
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
		width: '420px',
		padding: '1rem',
		heightAuto: false
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
                title: 'Konfirmasi Hapus Tarikan',
                html: '<div style="text-align:left;">' +
                      '<div style="margin-bottom:10px; font-size:14px; color:#334155;">Yakin hapus tarikan berikut?</div>' +
                      '<div style="background-color:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:12px; margin: 10px 0;">' +
                      '<div style="margin:4px 0; font-size:13px;"><strong>NIS:</strong> ' + nis + '</div>' +
                      '<div style="margin:4px 0; font-size:13px;"><strong>Nama:</strong> ' + nama + '</div>' +
                      '<div style="margin:4px 0; font-size:13px;"><strong>Tarikan:</strong> ' + tarik + '</div>' +
                      '</div>' +
                      '<div style="background-color:#fff3cd; border:1px solid #ffc107; border-radius:10px; padding:10px;">' +
                      '<div style="display:flex; gap:10px; align-items:flex-start; color:#856404; font-size:13px; font-weight:600;">' +
                      '<i class="fa fa-warning" style="margin-top:2px;"></i>' +
                      '<div>Data yang dihapus tidak dapat dikembalikan.</div>' +
                      '</div>' +
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
                width: '420px',
                padding: '1rem',
                heightAuto: false
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



