<!-- Content Header (Page header) -->
<?php 
$data_nama = $_SESSION["ses_nama"];

date_default_timezone_set("Asia/Jakarta"); 
$tanggal = date("Y-m-d");

include_once __DIR__ . '/../../inc/tarik_payment_schema.php';
include_once __DIR__ . '/../../inc/payment_integration.php';
ensureTarikPaymentColumns($koneksi);

if (isset($_POST['Simpan'])) {
    $tarik = $_POST['tarik'];
    $tarik_hasil = preg_replace("/[^0-9]/", "", $tarik);
    $tujuan_tarik = ($_POST['tujuan_tarik'] ?? 'lainnya') === 'pembayaran' ? 'pembayaran' : 'lainnya';
    $tgl_transaksi = !empty($_POST['tgl_bayar']) ? $_POST['tgl_bayar'] : $tanggal;
    $jenis_bayar_id = mysqli_real_escape_string($koneksi, $_POST['jenis_bayar_id'] ?? '');
    $jenis_bayar = mysqli_real_escape_string($koneksi, $_POST['jenis_bayar'] ?? '');
    $keterangan_tarik = mysqli_real_escape_string($koneksi, $_POST['keterangan_tarik'] ?? '');
    $payment_detail_raw = $_POST['payment_detail'] ?? '';
    $payment_detail = mysqli_real_escape_string($koneksi, $payment_detail_raw);
    $bulan_dipilih = json_decode($payment_detail_raw, true);
    $bulan_list = is_array($bulan_dipilih) && isset($bulan_dipilih['bulan']) ? $bulan_dipilih['bulan'] : [];
    $payment_items = is_array($bulan_dipilih) && isset($bulan_dipilih['items']) && is_array($bulan_dipilih['items']) ? $bulan_dipilih['items'] : [];

    if ($tujuan_tarik === 'pembayaran' && ($jenis_bayar_id === '' || $tarik_hasil <= 0)) {
        echo "<script>Swal.fire({title:'Gagal!',text:'Lengkapi jenis pembayaran dan nominal.',icon:'error',confirmButtonText:'OK'});</script>";
    } else {
    
    // Calculate saldo from DB directly for security
    $sql_saldo_db = "SELECT sum(setor)-sum(tarik) as total FROM tb_tabungan WHERE nis='".$_POST['nis']."'";
    $q_saldo_db = mysqli_query($koneksi, $sql_saldo_db);
    $d_saldo_db = mysqli_fetch_array($q_saldo_db);
    $saldo_db = $d_saldo_db['total'];

    if ($saldo_db >= $tarik_hasil) {
        $payment_ref = '';
        $payment_sync = 'none';

        if ($tujuan_tarik === 'pembayaran') {
            $paymentPayload = [
                'nisn' => $_POST['nis'],
                'tgl_bayar' => $tgl_transaksi,
                'jenis_bayar_id' => $jenis_bayar_id,
                'jenis_bayar' => $jenis_bayar,
                'nominal' => (int) $tarik_hasil,
                'bulan' => $bulan_list,
                'items' => $payment_items,
                'petugas' => $data_nama,
                'sumber' => 'etabs_tarikan',
            ];
            $paymentResult = paymentSubmitTransaksi($paymentPayload);

            if (empty($paymentResult['success'])) {
                $msg = addslashes($paymentResult['message'] ?? 'Gagal sinkron ke sistem pembayaran.');
                echo "<script>Swal.fire({title:'Gagal!',text:'".$msg."',icon:'error',confirmButtonText:'OK'});</script>";
            } else {
                $payment_ref = mysqli_real_escape_string($koneksi, $paymentResult['ref'] ?? $paymentResult['data']['ref_transaksi'] ?? '');
                $payment_sync = !empty($paymentResult['mock']) ? 'mock' : 'success';
            }
        }

        if ($tujuan_tarik === 'lainnya' || ($payment_sync === 'mock' || $payment_sync === 'success')) {
        $sql_simpan = "INSERT INTO tb_tabungan (nis,setor,tarik,tgl,jenis,petugas,tujuan_tarik,jenis_bayar,jenis_bayar_id,keterangan_tarik,payment_ref,payment_sync,payment_detail) VALUES (
            '".$_POST['nis']."',
            '0',
            '".$tarik_hasil."',
            '".$tgl_transaksi."',
            'TR',
            '".mysqli_real_escape_string($koneksi, $data_nama)."',
            '".$tujuan_tarik."',
            ".($jenis_bayar !== '' ? "'".$jenis_bayar."'" : "NULL").",
            ".($jenis_bayar_id !== '' ? "'".$jenis_bayar_id."'" : "NULL").",
            ".($keterangan_tarik !== '' ? "'".$keterangan_tarik."'" : "NULL").",
            ".($payment_ref !== '' ? "'".$payment_ref."'" : "NULL").",
            '".$payment_sync."',
            ".($payment_detail_raw !== '' ? "'".$payment_detail."'" : "NULL")."
        )";
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
                                '".$tgl_transaksi."',
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

            $successText = $tujuan_tarik === 'pembayaran'
                ? 'Penarikan & pembayaran berhasil dicatat'
                : 'Penarikan berhasil ditambahkan';

            echo "<script>
            (function(){
                if(typeof Swal!=='undefined'){
                    Swal.fire({
                        title:'Berhasil!',
                        text:'".$successText."',
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
								<th class="px-4 py-3 font-medium">Tujuan</th>
								<th class="px-4 py-3 font-medium">Jenis / Keterangan</th>
								<th class="px-4 py-3 font-medium">Tarikan</th>
								<th class="px-4 py-3 font-medium">Sync</th>
								<th class="px-4 py-3 font-medium">Petugas</th>
								<th class="px-4 py-3 font-medium text-center">Aksi</th>
							</tr>
						</thead>
						<tbody id="datatableUsersBody" class="divide-y divide-slate-200  bg-white">
							<?php

                  $no = 1;
				  $sql = $koneksi->query("select s.nis, s.nama_siswa, t.id_tabungan, t.tarik, t.tgl, t.petugas,
				  t.tujuan_tarik, t.jenis_bayar, t.keterangan_tarik, t.payment_sync, t.payment_ref from 
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
                                    <?php  $tgl = $data['tgl']; echo tgl_indo_standar($tgl)?>
                                </span>
							</td>
							<td class="px-4 py-3">
                                <?php if (($data['tujuan_tarik'] ?? 'lainnya') === 'pembayaran') { ?>
                                <span class="inline-flex rounded-full bg-indigo-100 px-2 py-0.5 text-[10px] font-semibold text-indigo-700">Pembayaran</span>
                                <?php } else { ?>
                                <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600">Lainnya</span>
                                <?php } ?>
							</td>
							<td class="px-4 py-3 text-slate-600">
                                <?php
                                    if (!empty($data['jenis_bayar'])) {
                                        echo htmlspecialchars($data['jenis_bayar']);
                                    } elseif (!empty($data['keterangan_tarik'])) {
                                        echo htmlspecialchars($data['keterangan_tarik']);
                                    } else {
                                        echo '<span class="text-slate-400">—</span>';
                                    }
                                ?>
							</td>
							<td class="px-4 py-3 text-left font-medium text-rose-600">
								<span class="whitespace-nowrap"><?php echo rupiah($data['tarik']); ?></span>
							</td>
							<td class="px-4 py-3 text-center">
                                <?php
                                    $sync = $data['payment_sync'] ?? 'none';
                                    if ($sync === 'success') {
                                        echo '<i class="fa-solid fa-circle-check text-emerald-500" title="Tersinkron"></i>';
                                    } elseif ($sync === 'mock') {
                                        echo '<button type="button" class="js-payment-resync inline-flex h-9 w-9 items-center justify-center rounded-xl border border-amber-200 bg-amber-50 text-amber-600 hover:bg-amber-100" data-id="'.(int)$data['id_tabungan'].'" title="Sinkron ulang ke Sibayar"><i class="fa-solid fa-rotate"></i></button>';
                                    } elseif ($sync === 'failed') {
                                        echo '<button type="button" class="js-payment-resync inline-flex h-9 w-9 items-center justify-center rounded-xl border border-rose-200 bg-rose-50 text-rose-600 hover:bg-rose-100" data-id="'.(int)$data['id_tabungan'].'" title="Coba sinkron ulang"><i class="fa-solid fa-rotate"></i></button>';
                                    } else {
                                        echo '<span class="text-slate-300">—</span>';
                                    }
                                ?>
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
    <div class="tarik-add-dialog relative flex max-h-[calc(100vh-3rem)] w-full max-w-xl flex-col overflow-hidden rounded-2xl bg-white shadow-xl transition-all">
        <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-6 py-5">
            <h3 class="text-lg font-semibold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-circle-minus text-indigo-500"></i>
                Tambah Penarikan
            </h3>
            <button type="button" class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700 tw-modal-close transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="" method="post" id="formTarikAdd" class="tarik-add-form flex flex-col overflow-hidden">
            <input type="hidden" name="tujuan_tarik" id="tujuan_tarik" value="pembayaran">
            <input type="hidden" name="jenis_bayar_id" id="jenis_bayar_id" value="">
            <input type="hidden" name="jenis_bayar" id="jenis_bayar" value="">
            <input type="hidden" name="payment_detail" id="payment_detail" value="">

            <div id="addModalBody" class="tarik-add-body max-h-[calc(100vh-13rem)] space-y-4 overflow-y-auto px-6 py-5 custom-scrollbar">
                <!-- Tujuan penarikan -->
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700">Tujuan Penarikan</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" class="tujuan-tab flex flex-col items-center gap-1 rounded-xl border-2 border-indigo-500 bg-indigo-50 px-3 py-2.5 text-center" data-tujuan="pembayaran">
                            <i class="fa-solid fa-receipt text-indigo-500"></i>
                            <span class="text-xs font-semibold text-indigo-700">Pembayaran</span>
                        </button>
                        <button type="button" class="tujuan-tab flex flex-col items-center gap-1 rounded-xl border-2 border-slate-200 px-3 py-2.5 text-center" data-tujuan="lainnya">
                            <i class="fa-solid fa-wallet text-slate-400"></i>
                            <span class="text-xs font-semibold text-slate-600">Lainnya</span>
                        </button>
                    </div>
                </div>

                <div class="space-y-1.5" id="wrap-tgl-bayar">
                    <label class="text-sm font-medium text-slate-700">Tanggal Bayar</label>
                    <input type="date" name="tgl_bayar" id="tgl_bayar" class="auth-input" value="<?php echo $tanggal; ?>">
                </div>

                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-slate-700">Nama Siswa</label>
                    <div class="relative">
                        <select name="nis" id="nis_add" class="auth-input appearance-none pr-9 select2" required>
                            <option value="">-- Pilih --</option>
                            <?php
                            $query = "select * from tb_siswa where status='Aktif'";
                            $hasil = mysqli_query($koneksi, $query);
                            while ($row = mysqli_fetch_array($hasil)) {
                            ?>
                            <option value="<?php echo $row['nis'] ?>">
                                <?php echo $row['nama_siswa'] ?> - <?php echo $row['nis'] ?>
                            </option>
                            <?php } ?>
                        </select>
                        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </span>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-slate-700">Saldo Tabungan</label>
                    <input type="text" name="saldo" id="saldo_add" class="auth-input bg-slate-50 text-slate-600" placeholder="Saldo saat ini" readonly>
                </div>

                <!-- Panel Pembayaran (mirip sistem bayar) -->
                <div id="panel-pembayaran" class="space-y-3 rounded-xl border border-slate-200 bg-slate-50/80 p-4">
                    <p class="text-xs font-semibold text-indigo-700 flex items-center gap-1.5">
                        <i class="fa-solid fa-link"></i> Sistem Pembayaran
                        <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-medium text-emerald-700">SPP API</span>
                    </p>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between gap-2">
                            <label class="text-sm font-medium text-slate-700">Tagihan SPP</label>
                            <span id="tagihan-count" class="text-[11px] font-medium text-slate-400"></span>
                        </div>
                        <div id="jenis_bayar_list" class="space-y-2 rounded-xl border border-slate-200 bg-white p-2">
                            <div class="rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-500">Pilih siswa terlebih dahulu</div>
                        </div>
                    </div>

                    <div id="panel-detail-bayar" class="hidden space-y-3 rounded-xl border border-slate-200 bg-white p-4">
                        <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-2">
                            <div>
                                <p id="detail-judul" class="text-sm font-semibold text-slate-800">Rincian Tagihan</p>
                                <p id="detail-subjudul" class="mt-0.5 text-xs text-slate-500"></p>
                            </div>
                            <span id="detail-total" class="shrink-0 rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">Rp 0</span>
                        </div>

                        <div id="rincian-tagihan-list" class="space-y-2"></div>

                        <div id="wrap-bulan-bayar" class="hidden space-y-2">
                            <label class="text-sm font-medium text-slate-700" id="label-bulan-bayar">Bayar Bulan</label>
                            <div id="bulan-bayar-list" class="flex flex-wrap gap-2"></div>
                        </div>

                        <div id="wrap-info-sekali" class="hidden rounded-lg bg-slate-50 p-3 text-xs text-slate-600 space-y-1">
                            <div class="flex justify-between"><span>Sisa tagihan</span><span id="info-sisa" class="font-semibold text-slate-900"></span></div>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-slate-700">Nominal Bayar</label>
                        <input type="text" name="tarik" id="tarik_add" class="auth-input font-medium" placeholder="Rp 0">
                    </div>
                </div>

                <!-- Panel Lainnya -->
                <div id="panel-lainnya" class="hidden space-y-3">
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-slate-700">Keterangan</label>
                        <input type="text" name="keterangan_tarik" id="keterangan_tarik" class="auth-input" placeholder="Contoh: Penarikan tunai">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-slate-700">Nominal Penarikan</label>
                        <input type="text" id="tarik_lainnya" class="auth-input" placeholder="Rp 0">
                    </div>
                </div>
            </div>

            <div class="flex shrink-0 justify-end gap-3 border-t border-slate-100 bg-white px-6 py-4">
                <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 tw-modal-close transition-all">
                    Batal
                </button>
                <button type="submit" name="Simpan" id="btn_simpan_tarik" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-700 transition-all">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span id="label_simpan_tarik">Simpan &amp; Bayar</span>
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
                    <select name="nis" id="nis_edit" class="auth-input appearance-none pr-9 select2" required>
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
                        $select.select2({
                            dropdownParent: $modalParent,
                            width: '100%'
                        });
                    } else {
                        $select.select2({
                            width: '100%'
                        });
                    }
                });

                // AJAX for Saldo in Add Modal
                var addModalScrollTopBeforeSelect = 0;
                $('#nis_add').on('select2:open', function() {
                    addModalScrollTopBeforeSelect = $('#addModalBody').scrollTop();
                }).on('select2:close', function() {
                    $('#addModalBody').scrollTop(addModalScrollTopBeforeSelect);
                });

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
                    if ($('#tujuan_tarik').val() === 'pembayaran') {
                        loadJenisBayar(nis);
                    }
                });

                // --- Integrasi pembayaran ---
                var paymentDetailCache = null;
                var paymentJenisCache = [];
                var selectedPayments = {};

                function escapeHtml(value) {
                    return String(value == null ? '' : value)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;');
                }

                function formatRupiahNum(num) {
                    var n = parseInt(String(num).replace(/[^0-9]/g, ''), 10) || 0;
                    return 'Rp ' + n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                }

                function setTujuanTarik(tujuan) {
                    $('#tujuan_tarik').val(tujuan);
                    $('.tujuan-tab').each(function() {
                        var active = $(this).data('tujuan') === tujuan;
                        $(this).toggleClass('border-indigo-500 bg-indigo-50', active);
                        $(this).toggleClass('border-slate-200 bg-white', !active);
                    });
                    $('#panel-pembayaran').toggleClass('hidden', tujuan !== 'pembayaran');
                    $('#panel-lainnya').toggleClass('hidden', tujuan !== 'lainnya');
                    $('#wrap-tgl-bayar').toggleClass('hidden', tujuan !== 'pembayaran');
                    $('#label_simpan_tarik').text(tujuan === 'pembayaran' ? 'Simpan & Bayar' : 'Simpan');
                    $('#tarik_add').prop('required', tujuan === 'pembayaran');
                    $('#tarik_lainnya').prop('required', tujuan === 'lainnya');
                }

                $('.tujuan-tab').on('click', function() {
                    setTujuanTarik($(this).data('tujuan'));
                });

                function loadJenisBayar(nis) {
                    var $list = $('#jenis_bayar_list');
                    $('#tagihan-count').text('');
                    $list.html('<div class="rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-500">Memuat tagihan...</div>');
                    $('#panel-detail-bayar').addClass('hidden');
                    $('#jenis_bayar_id, #jenis_bayar, #payment_detail').val('');
                    $('#tarik_add').val('');
                    paymentJenisCache = [];
                    selectedPayments = {};
                    paymentDetailCache = null;

                    if (!nis) {
                        $list.html('<div class="rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-500">Pilih siswa terlebih dahulu</div>');
                        return;
                    }

                    $.post('plugins/payment-ajax.php', { action: 'jenis_bayar', nis: nis }, function(res) {
                        if (window.console && (!res.data || !res.data.length)) {
                            console.warn('SPP jenis_bayar kosong untuk NIS', nis, res);
                        }
                        if (window.console && res.fallback) {
                            console.info('SPP tagihan memakai fallback raw extractor', res);
                        }
                        if (!res.success) {
                            var message = res.message || 'Gagal memuat data';
                            $list.html('<div class="rounded-lg bg-rose-50 px-3 py-2 text-sm text-rose-600">' + escapeHtml(message) + '</div>');
                            return;
                        }
                        if (!res.data || !res.data.length) {
                            $list.html('<div class="rounded-lg bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700">Tidak ada tagihan</div>');
                            return;
                        }
                        paymentJenisCache = res.data || [];
                        renderJenisBayarList();
                    }, 'json').fail(function(xhr) {
                        var message = 'Gagal menghubungi SPP';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        $list.html('<div class="rounded-lg bg-rose-50 px-3 py-2 text-sm text-rose-600">' + escapeHtml(message) + '</div>');
                    });
                }

                function renderJenisBayarList() {
                    var html = '';
                    var payableCount = 0;

                    paymentJenisCache.forEach(function(item) {
                        var id = String(item.id || item.nama || '');
                        var disabled = !!item.disabled || !!item.lunas || !item.has_tagihan || !(parseInt(item.sisa || 0, 10) > 0);
                        var selected = !!selectedPayments[id];
                        if (!disabled) payableCount++;

                        var cardClass = disabled
                            ? 'border-slate-200 bg-slate-50 opacity-75'
                            : (selected ? 'border-indigo-500 bg-indigo-50 ring-2 ring-indigo-500/20' : 'border-slate-200 bg-white hover:border-indigo-300');
                        var checkClass = selected ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-white border-slate-300';
                        var badge = item.lunas
                            ? '<span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">Lunas</span>'
                            : (disabled ? '<span class="rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-semibold text-slate-500">Tidak tersedia</span>' : '<span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-700">Ada tagihan</span>');
                        var meta = [];
                        if (item.tipe) meta.push(item.tipe === 'bulanan' ? 'Bulanan' : (item.tipe === 'cicilan' ? 'Cicilan' : 'Sekali bayar'));
                        if (item.kelas) meta.push('Kelas ' + item.kelas);
                        if (item.fallback_master) meta.push('Estimasi dari master SPP');

                        html += '<button type="button" class="payment-option w-full rounded-xl border p-3 text-left transition-all ' + cardClass + '" data-id="' + escapeHtml(id) + '" ' + (disabled ? 'disabled' : '') + '>' +
                            '<div class="flex items-start gap-3">' +
                                '<span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-md border text-[10px] ' + checkClass + '"><i class="fa-solid fa-check"></i></span>' +
                                '<span class="min-w-0 flex-1">' +
                                    '<span class="flex flex-wrap items-center gap-2 text-sm font-semibold text-slate-800">' + escapeHtml(item.nama || 'Pembayaran') + badge + '</span>' +
                                    '<span class="mt-1 block text-xs text-slate-500">' + escapeHtml(meta.join(' · ') || (item.disabled_reason || 'Tagihan pembayaran')) + '</span>' +
                                    (disabled ? '<span class="mt-1 block text-xs font-medium text-slate-500">' + escapeHtml(item.disabled_reason || 'Tidak ada tagihan') + '</span>' : '') +
                                '</span>' +
                                '<span class="shrink-0 text-sm font-bold ' + (disabled ? 'text-slate-400' : 'text-indigo-700') + '">' + formatRupiahNum(item.sisa || item.nominal || 0) + '</span>' +
                            '</div>' +
                        '</button>';
                    });

                    $('#tagihan-count').text(payableCount ? payableCount + ' bisa dibayar' : 'Tidak ada tagihan');
                    $('#jenis_bayar_list').html(html || '<div class="rounded-lg bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700">Tidak ada tagihan</div>');
                    updatePaymentSummary();
                }

                function renderBulanTags(bulanList) {
                    var html = '';
                    (bulanList || []).forEach(function(b) {
                        if (b.lunas) return;
                        html += '<button type="button" class="bulan-tag inline-flex cursor-pointer items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-800 transition-colors" data-id="' + b.id + '" data-nama="' + b.nama + '" data-selected="0">' +
                            '<span>' + b.nama + '</span></button>';
                    });
                    $('#bulan-bayar-list').html(html || '<span class="text-xs text-slate-500">Tidak ada bulan tersedia</span>');
                }

                function hitungNominalBayar() {
                    updatePaymentSummary();
                }

                function updatePaymentSummary() {
                    var selectedItems = Object.keys(selectedPayments).map(function(id) { return selectedPayments[id]; });
                    var nominal = 0;
                    var ids = [];
                    var names = [];
                    var detailHtml = '';

                    selectedItems.forEach(function(item) {
                        var itemNominal = parseInt(item.sisa || item.nominal || 0, 10) || 0;
                        nominal += itemNominal;
                        ids.push(item.id);
                        names.push(item.nama);

                        detailHtml += '<div class="rounded-xl border border-slate-200 bg-slate-50 p-3">' +
                            '<div class="flex items-start justify-between gap-3">' +
                                '<div>' +
                                    '<p class="text-sm font-semibold text-slate-800">' + escapeHtml(item.nama || 'Pembayaran') + '</p>' +
                                    '<p class="mt-0.5 text-xs text-slate-500">' + escapeHtml((item.tipe || 'tagihan') + (item.kelas ? ' · Kelas ' + item.kelas : '')) + '</p>' +
                                '</div>' +
                                '<span class="text-sm font-bold text-indigo-700">' + formatRupiahNum(itemNominal) + '</span>' +
                            '</div>';

                        if (item.rincian && item.rincian.length) {
                            detailHtml += '<div class="mt-3 space-y-1.5">';
                            item.rincian.forEach(function(row) {
                                var rowSisa = parseInt(row.sisa || 0, 10) || 0;
                                detailHtml += '<div class="flex items-center justify-between gap-3 rounded-lg bg-white px-3 py-2 text-xs">' +
                                    '<span class="min-w-0 text-slate-600">' + escapeHtml(row.label || 'Rincian') + (row.lunas ? ' <span class="font-semibold text-emerald-600">(lunas)</span>' : '') + '</span>' +
                                    '<span class="shrink-0 font-semibold ' + (row.lunas ? 'text-slate-400' : 'text-slate-800') + '">' + formatRupiahNum(rowSisa) + '</span>' +
                                '</div>';
                            });
                            detailHtml += '</div>';
                        } else {
                            detailHtml += '<p class="mt-2 text-xs text-slate-500">Rincian tagihan tidak tersedia dari SPP.</p>';
                        }

                        detailHtml += '</div>';
                    });

                    $('#tarik_add').val(nominal > 0 ? formatRupiahNum(nominal) : '');
                    $('#jenis_bayar_id').val(ids.join(','));
                    $('#jenis_bayar').val(names.join(', '));
                    $('#payment_detail').val(selectedItems.length ? JSON.stringify({ items: selectedItems }) : '');
                    $('#detail-total').text(formatRupiahNum(nominal));

                    if (selectedItems.length) {
                        $('#detail-judul').text('Rincian Tagihan Dipilih');
                        $('#detail-subjudul').text(selectedItems.length + ' jenis pembayaran');
                        $('#rincian-tagihan-list').html(detailHtml);
                        $('#panel-detail-bayar').removeClass('hidden');
                    } else {
                        var payable = paymentJenisCache.some(function(item) { return item.has_tagihan && !item.disabled && !item.lunas; });
                        $('#detail-judul').text(payable ? 'Rincian Tagihan' : 'Tidak ada tagihan');
                        $('#detail-subjudul').text(payable ? 'Centang satu atau beberapa tagihan untuk dibayar.' : 'Semua tagihan siswa sudah lunas atau tidak tersedia untuk kelas siswa ini.');
                        if (payable) {
                            var previewHtml = '';
                            paymentJenisCache.forEach(function(item) {
                                if (!item.has_tagihan || item.disabled || item.lunas) return;
                                previewHtml += '<div class="rounded-xl border border-slate-200 bg-slate-50 p-3">' +
                                    '<div class="flex items-start justify-between gap-3">' +
                                        '<div>' +
                                            '<p class="text-sm font-semibold text-slate-800">' + escapeHtml(item.nama || 'Pembayaran') + '</p>' +
                                            '<p class="mt-0.5 text-xs text-slate-500">' + escapeHtml((item.tipe || 'tagihan') + (item.kelas ? ' · Kelas ' + item.kelas : '')) + '</p>' +
                                        '</div>' +
                                        '<span class="text-sm font-bold text-indigo-700">' + formatRupiahNum(item.sisa || item.nominal || 0) + '</span>' +
                                    '</div>' +
                                '</div>';
                            });
                            $('#rincian-tagihan-list').html(previewHtml || '<div class="rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-500">Belum ada tagihan dipilih</div>');
                        } else {
                            $('#rincian-tagihan-list').html('<div class="rounded-lg bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700">Tidak ada tagihan</div>');
                        }
                        $('#panel-detail-bayar').removeClass('hidden');
                    }
                }

                $(document).on('click', '.payment-option', function() {
                    if (this.disabled) return;
                    var id = String($(this).data('id') || '');
                    var item = paymentJenisCache.find(function(row) { return String(row.id || row.nama || '') === id; });
                    if (!item) return;

                    if (selectedPayments[id]) {
                        delete selectedPayments[id];
                    } else {
                        selectedPayments[id] = item;
                    }

                    renderJenisBayarList();
                });

                $(document).on('click', '.bulan-tag', function(event) {
                    event.preventDefault();
                    var $body = $('#addModalBody');
                    var scrollTop = $body.scrollTop();
                    var selected = $(this).attr('data-selected') === '1';
                    $(this).attr('data-selected', selected ? '0' : '1');
                    $(this).toggleClass('bg-emerald-600 text-white border-emerald-600', !selected);
                    $(this).toggleClass('bg-emerald-50 text-emerald-800 border-emerald-200', selected);
                    hitungNominalBayar();
                    $body.scrollTop(scrollTop);
                    $(this).blur();
                });

                $('#formTarikAdd').on('submit', function() {
                    if ($('#tujuan_tarik').val() === 'lainnya') {
                        $('#tarik_add').val($('#tarik_lainnya').val());
                        $('#jenis_bayar_id, #jenis_bayar, #payment_detail').val('');
                    }
                });

                $(document).on('click', '.js-payment-resync', function() {
                    var id = $(this).data('id');
                    var $btn = $(this);
                    var runSync = function() {
                        $btn.prop('disabled', true).addClass('opacity-60');
                        $.post('plugins/payment-ajax.php', { action: 'resync_tarik', id_tabungan: id }, function(res) {
                            if (res && res.success) {
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: res.message || 'Transaksi berhasil disinkronkan ke Sibayar.',
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then(function() { window.location.reload(); });
                                return;
                            }

                            Swal.fire({
                                title: 'Gagal!',
                                text: (res && res.message) ? res.message : 'Sinkron ulang ke Sibayar gagal.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                            $btn.prop('disabled', false).removeClass('opacity-60');
                        }, 'json').fail(function(xhr) {
                            var message = 'Gagal menghubungi server sinkronisasi.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }
                            Swal.fire({ title: 'Gagal!', text: message, icon: 'error', confirmButtonText: 'OK' });
                            $btn.prop('disabled', false).removeClass('opacity-60');
                        });
                    };

                    Swal.fire({
                        title: 'Sinkron ulang?',
                        text: 'Transaksi pembayaran lama akan dikirim ke Sibayar tanpa membuat penarikan baru.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Sinkronkan',
                        cancelButtonText: 'Batal'
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            runSync();
                        }
                    });
                });

                setTujuanTarik('pembayaran');

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
                    if (target === '#addModal') {
                        $('#addModalBody').scrollTop(0);
                    }
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
                var tarik_lainnya = document.getElementById('tarik_lainnya');
                if(tarik_lainnya){
                    tarik_lainnya.addEventListener('keyup', function(e) {
                        tarik_lainnya.value = formatRupiah(this.value, 'Rp ');
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



