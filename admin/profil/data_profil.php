<section class="content-header">
	<h1>
		Pengaturan
	</h1>
</section>

<!-- Main content -->
<section class="content">
	<?php
	// 1. Cek & Buat Kolom Tambahan jika belum ada
	$cols_to_check = [
		'nama_bendahara' => "VARCHAR(100) NULL AFTER logo_sekolah",
		'tahun_ajaran' => "VARCHAR(20) NULL AFTER nama_bendahara",
		'bg_login' => "VARCHAR(255) NULL AFTER tahun_ajaran"
	];

	foreach ($cols_to_check as $col => $def) {
		$check = mysqli_query($koneksi, "SHOW COLUMNS FROM tb_profil LIKE '$col'");
		if (mysqli_num_rows($check) == 0) {
			mysqli_query($koneksi, "ALTER TABLE tb_profil ADD COLUMN $col $def");
		}
	}

	// 2. Handler Proses Simpan (POST)
	if (isset($_POST['Ubah'])) {
		$id_profil = $_POST['id_profil'];
		$nama_sekolah = $_POST['nama_sekolah'];
		$alamat = $_POST['alamat'];
		$akreditasi = $_POST['akreditasi'];
		$nama_bendahara = $_POST['nama_bendahara'];
		$tahun_ajaran = $_POST['tahun_ajaran'];

		// Upload Logo
		$sumber_logo = @$_FILES['logo_sekolah']['tmp_name'];
		$target_logo = 'uploads/logo/';
		$nama_logo = @$_FILES['logo_sekolah']['name'];
		
		// Upload Background Login
		$sumber_bg = @$_FILES['bg_login']['tmp_name'];
		$target_bg = 'uploads/bg/';
		$nama_bg = @$_FILES['bg_login']['name'];

		// Buat folder jika belum ada
		if (!file_exists($target_logo)) mkdir($target_logo, 0777, true);
		if (!file_exists($target_bg)) mkdir($target_bg, 0777, true);

		// Logika Update
		// A. Jika Logo dan BG tidak diubah
		if (empty($sumber_logo) && empty($sumber_bg)) {
			$sql_ubah = "UPDATE tb_profil SET 
				nama_sekolah='$nama_sekolah', 
				alamat='$alamat', 
				akreditasi='$akreditasi',
				nama_bendahara='$nama_bendahara',
				tahun_ajaran='$tahun_ajaran'
				WHERE id_profil='$id_profil'";
		}
		// B. Jika Logo diubah, BG tidak
		elseif (!empty($sumber_logo) && empty($sumber_bg)) {
			$nama_logo_baru = "logo_" . time() . "_" . $nama_logo;
			$pindah_logo = move_uploaded_file($sumber_logo, $target_logo . $nama_logo_baru);
			if ($pindah_logo) {
				$sql_ubah = "UPDATE tb_profil SET 
					nama_sekolah='$nama_sekolah', 
					alamat='$alamat', 
					akreditasi='$akreditasi',
					nama_bendahara='$nama_bendahara',
					tahun_ajaran='$tahun_ajaran',
					logo_sekolah='$nama_logo_baru'
					WHERE id_profil='$id_profil'";
			}
		}
		// C. Jika BG diubah, Logo tidak
		elseif (empty($sumber_logo) && !empty($sumber_bg)) {
			$nama_bg_baru = "bg_" . time() . "_" . $nama_bg;
			$pindah_bg = move_uploaded_file($sumber_bg, $target_bg . $nama_bg_baru);
			if ($pindah_bg) {
				$sql_ubah = "UPDATE tb_profil SET 
					nama_sekolah='$nama_sekolah', 
					alamat='$alamat', 
					akreditasi='$akreditasi',
					nama_bendahara='$nama_bendahara',
					tahun_ajaran='$tahun_ajaran',
					bg_login='$nama_bg_baru'
					WHERE id_profil='$id_profil'";
			}
		}
		// D. Jika Keduanya diubah
		else {
			$nama_logo_baru = "logo_" . time() . "_" . $nama_logo;
			$pindah_logo = move_uploaded_file($sumber_logo, $target_logo . $nama_logo_baru);
			
			$nama_bg_baru = "bg_" . time() . "_" . $nama_bg;
			$pindah_bg = move_uploaded_file($sumber_bg, $target_bg . $nama_bg_baru);

			if ($pindah_logo && $pindah_bg) {
				$sql_ubah = "UPDATE tb_profil SET 
					nama_sekolah='$nama_sekolah', 
					alamat='$alamat', 
					akreditasi='$akreditasi',
					nama_bendahara='$nama_bendahara',
					tahun_ajaran='$tahun_ajaran',
					logo_sekolah='$nama_logo_baru',
					bg_login='$nama_bg_baru'
					WHERE id_profil='$id_profil'";
			}
		}

		// Eksekusi Query
		$query_ubah = mysqli_query($koneksi, $sql_ubah);
		if ($query_ubah) {
			echo "<script>
				Swal.fire({
					title: 'Ubah Data Berhasil',
					text: 'Profil sekolah telah diperbarui',
					icon: 'success',
					showConfirmButton: false,
					timer: 1500,
					timerProgressBar: true
				}).then(() => {
					window.location = 'index.php?page=MyApp/data_profil';
				});
			</script>";
		} else {
			echo "<script>
				Swal.fire({title: 'Ubah Data Gagal', text: '', icon: 'error', confirmButtonText: 'OK'}).then((result) => {
					if (result.value) { window.location = 'index.php?page=MyApp/data_profil'; }
				});
			</script>";
		}
	}

	// 3. Ambil Data Profil
	$sql = $koneksi->query("SELECT * FROM tb_profil LIMIT 1");
	$profil = $sql->fetch_assoc();
	
	// Fix Logo Path: Gunakan path relatif dari index.php (root)
	$logo_path = !empty($profil['logo_sekolah']) ? 'uploads/logo/' . $profil['logo_sekolah'] : 'images/logo.png';
	$bg_path = !empty($profil['bg_login']) ? 'uploads/bg/' . $profil['bg_login'] : 'images/bg_sf.jpg';

    // 4. Handler Reset Tabungan
    if (isset($_POST['ResetTabungan'])) {
        $sql_reset_tabungan = "TRUNCATE TABLE tb_tabungan";
        $query_reset_tabungan = mysqli_query($koneksi, $sql_reset_tabungan);
        
        $sql_reset_riwayat = "TRUNCATE TABLE tb_riwayat";
        $query_reset_riwayat = mysqli_query($koneksi, $sql_reset_riwayat);

        if ($query_reset_tabungan && $query_reset_riwayat) {
            // Log Aktivitas Reset
            if (function_exists('logActivity')) {
                logActivity($koneksi, 'DELETE', 'tb_tabungan', 'Reset Semua Data Tabungan & Riwayat (Pergantian Tahun Ajaran)');
            }
            echo "<script>
                Swal.fire({
                    title: 'Reset Berhasil',
                    text: 'Semua data tabungan dan riwayat transaksi telah dihapus.',
                    icon: 'success',
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true
                }).then(() => {
                    window.location = 'index.php?page=MyApp/data_profil';
                });
            </script>";
        } else {
            echo "<script>
                Swal.fire({title: 'Reset Gagal', text: 'Terjadi kesalahan saat menghapus data.', icon: 'error', confirmButtonText: 'OK'});
            </script>";
        }
    }
	?>
	
	<div class="grid grid-cols-1 gap-6 md:grid-cols-3">
		<!-- Profil Card -->
		<div class="md:col-span-2">
			<div class="rounded-2xl bg-white shadow-sm  h-full flex flex-col">
				<div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 ">
					<h3 class="text-lg font-semibold text-slate-900  flex items-center gap-2">
						<i class="fa-solid fa-school text-indigo-500"></i>
						Informasi Profil
					</h3>
				</div>
				<div class="p-6 flex-1">
					<div class="space-y-6">
						<div class="group relative rounded-xl border border-slate-100 bg-slate-50/50 p-4 transition-all hover:border-indigo-100 hover:bg-white hover:shadow-sm  ">
							<div class="mb-1 text-xs font-medium uppercase tracking-wider text-slate-500  flex items-center gap-2">
                                <i class="fa-solid fa-building text-indigo-500"></i> Nama Sekolah
                            </div>
							<div class="text-base font-bold" style="color: #000000 !important;">
								<?php echo htmlspecialchars(!empty($profil['nama_sekolah']) ? $profil['nama_sekolah'] : '-'); ?>
							</div>
						</div>
						
						<div class="group relative rounded-xl border border-slate-100 bg-slate-50/50 p-4 transition-all hover:border-indigo-100 hover:bg-white hover:shadow-sm  ">
							<div class="mb-1 text-xs font-medium uppercase tracking-wider text-slate-500  flex items-center gap-2">
                                <i class="fa-solid fa-map-location-dot text-indigo-500"></i> Alamat
                            </div>
							<div class="text-base font-bold" style="color: #000000 !important;">
								<?php echo htmlspecialchars(!empty($profil['alamat']) ? $profil['alamat'] : '-'); ?>
							</div>
						</div>
						
						<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="group relative rounded-xl border border-slate-100 bg-slate-50/50 p-4 transition-all hover:border-indigo-100 hover:bg-white hover:shadow-sm  ">
                                <div class="mb-2 text-xs font-medium uppercase tracking-wider text-slate-500  flex items-center gap-2">
                                    <i class="fa-solid fa-certificate text-indigo-500"></i> Akreditasi
                                </div>
                                <div>
                                    <span class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-bold shadow-sm" style="background-color: #ffffff !important; color: #000000 !important; border-color: #e2e8f0 !important;">
                                        Terakreditasi <?php echo htmlspecialchars($profil['akreditasi']); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="group relative rounded-xl border border-slate-100 bg-slate-50/50 p-4 transition-all hover:border-indigo-100 hover:bg-white hover:shadow-sm  ">
                                <div class="mb-2 text-xs font-medium uppercase tracking-wider text-slate-500  flex items-center gap-2">
                                    <i class="fa-solid fa-calendar-check text-indigo-500"></i> Tahun Ajaran Aktif
                                </div>
                                <div>
                                    <span class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-bold shadow-sm" style="background-color: #ffffff !important; color: #000000 !important; border-color: #e2e8f0 !important;">
                                        <?php echo htmlspecialchars(!empty($profil['tahun_ajaran']) ? $profil['tahun_ajaran'] : '-'); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
						
						<div class="group relative rounded-xl border border-slate-100 bg-slate-50/50 p-4 transition-all hover:border-indigo-100 hover:bg-white hover:shadow-sm  ">
							<div class="mb-1 text-xs font-medium uppercase tracking-wider text-slate-500  flex items-center gap-2">
                                <i class="fa-solid fa-user-tie text-indigo-500"></i> Nama Bendahara
                            </div>
							<div class="text-base font-bold" style="color: #000000 !important;">
								<?php echo htmlspecialchars(!empty($profil['nama_bendahara']) ? $profil['nama_bendahara'] : '-'); ?>
							</div>
						</div>
					</div>
				</div>
				<div class="border-t border-slate-100 p-6  mt-auto">
					<button type="button" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-3 text-sm font-medium text-white transition-all hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 sm:w-auto tw-modal-open" data-target="#editProfilModal">
						<i class="fa-solid fa-pen-to-square"></i> Edit Profil Sekolah
					</button>
				</div>
			</div>
		</div>
		
		<!-- Logo & Background Card -->
		<div class="md:col-span-1 space-y-6">
            <!-- Logo -->
			<div class="rounded-2xl bg-white shadow-sm ">
				<div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 ">
					<h3 class="text-lg font-semibold text-slate-900  flex items-center gap-2">
						<i class="fa-solid fa-image text-emerald-500"></i>
						Logo Sekolah
					</h3>
				</div>
				<div class="p-6 flex flex-col items-center justify-center text-center">
					<div class="relative mb-6 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 p-8 shadow-lg transition-transform hover:scale-105">
						<img src="<?php echo $logo_path; ?>" 
						     alt="Logo Sekolah" 
						     class="h-auto max-h-[140px] w-auto max-w-full rounded-lg bg-white p-3 shadow-md" 
						     id="logoDisplay"
						     onerror="this.src='images/logo.png'">
					</div>
					<p class="text-xs text-slate-500 ">
						Tampil di: Login, Dashboard, Laporan
					</p>
				</div>
			</div>

            <!-- Background Login -->
			<div class="rounded-2xl bg-white shadow-sm ">
				<div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 ">
					<h3 class="text-lg font-semibold text-slate-900  flex items-center gap-2">
						<i class="fa-solid fa-desktop text-sky-500"></i>
						Background Login
					</h3>
				</div>
				<div class="p-6 flex flex-col items-center justify-center text-center">
					<div class="relative mb-4 w-full overflow-hidden rounded-xl border border-slate-200 ">
						<img src="<?php echo $bg_path; ?>" 
						     alt="Background Login" 
						     class="h-32 w-full object-cover" 
						     id="bgDisplay"
						     onerror="this.src='images/bg_sf.jpg'">
					</div>
					<p class="text-xs text-slate-500 ">
						Tampil di halaman Login
					</p>
				</div>
			</div>

            <!-- Maintenance Box -->
			<div class="rounded-2xl bg-white shadow-sm mt-6">
				<div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 ">
					<h3 class="text-lg font-semibold text-rose-600 flex items-center gap-2">
						<i class="fa-solid fa-triangle-exclamation"></i>
						Maintenance Area
					</h3>
				</div>
				<div class="p-6">
                    <div class="rounded-xl border border-rose-100 bg-rose-50 p-4 mb-4">
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-circle-exclamation text-rose-500 mt-0.5"></i>
                            <div>
                                <h4 class="text-sm font-semibold text-rose-800">Reset Data Tabungan (Pergantian Tahun Ajaran)</h4>
                                <p class="text-xs text-rose-600 mt-1">
                                    Fitur ini akan <strong>MENGHAPUS SEMUA DATA</strong> tabungan (setoran & penarikan) serta riwayat transaksi secara permanen. 
                                    Gunakan fitur ini hanya ketika memasuki tahun ajaran baru untuk memulai pencatatan dari awal (nol).
                                </p>
                            </div>
                        </div>
                    </div>

                    <form action="" method="post">
                        <input type="hidden" name="ResetTabungan" value="1">
                        <button type="button" onclick="konfirmasiReset()" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-rose-600 px-4 py-3 text-sm font-medium text-white transition-all hover:bg-rose-700 hover:shadow-lg hover:shadow-rose-500/30 focus:outline-none focus:ring-2 focus:ring-rose-500/50">
                            <i class="fa-solid fa-trash-can"></i> Reset Data Tabungan
                        </button>
                         <button type="submit" id="btnResetReal" class="hidden"></button>
                    </form>
				</div>
			</div>
		</div>
	</div>
</section>

<script>
function konfirmasiReset() {
    Swal.fire({
        title: 'PERINGATAN KERAS!',
        html: "Tindakan ini akan <strong>MENGHAPUS SEMUA DATA TABUNGAN & RIWAYAT TRANSAKSI</strong> secara permanen.<br><br>Data yang dihapus <strong>TIDAK DAPAT DIKEMBALIKAN</strong>.<br><br>Apakah Anda yakin ingin melanjutkan?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus Semua Data',
        cancelButtonText: 'Batal',
        focusCancel: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Konfirmasi kedua
            Swal.fire({
                title: 'Konfirmasi Terakhir',
                text: "Ketik 'SAYA YAKIN' untuk mengonfirmasi penghapusan data.",
                input: 'text',
                icon: 'warning',
                inputAttributes: {
                    autocapitalize: 'off'
                },
                showCancelButton: true,
                confirmButtonText: 'Proses Reset',
                confirmButtonColor: '#e11d48',
                showLoaderOnConfirm: true,
                preConfirm: (text) => {
                    if (text !== 'SAYA YAKIN') {
                        Swal.showValidationMessage('Konfirmasi salah. Silakan ketik SAYA YAKIN')
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('btnResetReal').click();
                }
            })
        }
    })
}

document.addEventListener("DOMContentLoaded", function() {
    const editBtn = document.querySelector('[data-target="#editProfilModal"]');
    const editModal = document.getElementById('editProfilModal');
    const closeBtns = editModal ? editModal.querySelectorAll('.tw-modal-close') : [];

    if (editBtn && editModal) {
        editBtn.addEventListener('click', function(e) {
            e.preventDefault();
            editModal.classList.remove('hidden');
            editModal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        });
    }

    if (closeBtns.length > 0) {
        closeBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                editModal.classList.add('hidden');
                editModal.classList.remove('flex');
                document.body.style.overflow = '';
            });
        });
    }

    if (editModal) {
        editModal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
                this.classList.remove('flex');
                document.body.style.overflow = '';
            }
        });
    }
});
</script>

<!-- Edit Modal -->
<div class="fixed inset-0 z-50 hidden justify-center bg-black/50 backdrop-blur-sm modal overflow-y-auto py-10" id="editProfilModal">
    <div class="relative w-full max-w-2xl rounded-2xl bg-white p-6 shadow-xl  transition-all my-auto">
        <!-- Header -->
        <div class="mb-5 flex items-center justify-between border-b border-slate-100 pb-4  sticky top-0 bg-white  z-10">
            <h3 class="text-lg font-semibold text-slate-900  flex items-center gap-2">
                <i class="fa-solid fa-edit text-indigo-500"></i>
                Edit Profil & Pengaturan
            </h3>
            <button type="button" class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700   tw-modal-close transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        
        <!-- Body -->
        <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id_profil" value="<?php echo $profil['id_profil']; ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1.5 md:col-span-2">
                    <label class="text-sm font-medium text-slate-700 ">Nama Sekolah</label>
                    <input type="text" name="nama_sekolah" value="<?php echo $profil['nama_sekolah']; ?>" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20   " required>
                </div>

                <div class="space-y-1.5 md:col-span-2">
                    <label class="text-sm font-medium text-slate-700 ">Alamat Sekolah</label>
                    <input type="text" name="alamat" value="<?php echo $profil['alamat']; ?>" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20   " required>
                </div>

                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-slate-700 ">Akreditasi</label>
                    <select name="akreditasi" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20   ">
                        <option value="A" <?php if($profil['akreditasi']=="A") echo "selected"; ?>>A</option>
                        <option value="B" <?php if($profil['akreditasi']=="B") echo "selected"; ?>>B</option>
                        <option value="C" <?php if($profil['akreditasi']=="C") echo "selected"; ?>>C</option>
                        <option value="Belum Terakreditasi" <?php if($profil['akreditasi']=="Belum Terakreditasi") echo "selected"; ?>>Belum Terakreditasi</option>
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-slate-700 ">Tahun Ajaran Aktif</label>
                    <input type="text" name="tahun_ajaran" value="<?php echo $profil['tahun_ajaran']; ?>" placeholder="Contoh: 2025/2026" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20   ">
                </div>

                <div class="space-y-1.5 md:col-span-2">
                    <label class="text-sm font-medium text-slate-700 ">Nama Bendahara</label>
                    <input type="text" name="nama_bendahara" value="<?php echo $profil['nama_bendahara']; ?>" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20   ">
                </div>

                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-slate-700 ">Logo Sekolah</label>
                    <input type="file" name="logo_sekolah" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100   ">
                    <p class="text-[10px] text-slate-500">*Kosongkan jika tidak diubah</p>
                </div>

                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-slate-700 ">Background Login</label>
                    <input type="file" name="bg_login" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100   ">
                    <p class="text-[10px] text-slate-500">*Kosongkan jika tidak diubah</p>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-slate-100 ">
                <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-200    tw-modal-close">Batal</button>
                <button type="submit" name="Ubah" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 shadow-lg shadow-indigo-500/30">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
