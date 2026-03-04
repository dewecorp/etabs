<?php
// Load PhpSpreadsheet library
require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_POST['Simpan'])) {
    $sql_simpan = "INSERT INTO tb_siswa (nis,nama_siswa,jekel,id_kelas,status,th_masuk) VALUES (
        '".$_POST['nis']."',
        '".$_POST['nama_siswa']."',
        '".$_POST['jekel']."',
        '".$_POST['id_kelas']."',
        'Aktif',
        '".$_POST['th_masuk']."')";
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
            logActivity($koneksi, 'CREATE', 'tb_siswa', 'Menambah siswa: ' . $_POST['nama_siswa'] . ' (NIS: ' . $_POST['nis'] . ')', $_POST['nis'] ?? null);
        }

        echo "<script>
        (function(){
            if(typeof Swal!=='undefined'){
                Swal.fire({
                    title:'Berhasil!',
                    text:'Data siswa berhasil ditambahkan',
                    icon:'success',
                    confirmButtonText:'OK',
                    confirmButtonColor:'#28a745',
                    allowOutsideClick:false,
                    allowEscapeKey:false,
                    timer:2500,
                    timerProgressBar:true
                }).then(function(){
                    window.location.href='index.php?page=MyApp/data_siswa';
                });
                
                setTimeout(function(){
                    window.location.href='index.php?page=MyApp/data_siswa';
                }, 2600);
            } else {
                alert('Data siswa berhasil ditambahkan');
                window.location.href='index.php?page=MyApp/data_siswa';
            }
        })();
        </script>";
    }else{
        echo "<script>
        (function(){
            if(typeof Swal!=='undefined'){
                Swal.fire({
                    title:'Gagal!',
                    text:'Data siswa gagal ditambahkan',
                    icon:'error',
                    confirmButtonText:'OK',
                    confirmButtonColor:'#d33'
                });
            } else {
                alert('Data siswa gagal ditambahkan');
            }
        })();
        </script>";
    }
}

if (isset($_POST['Ubah'])) {
    $sql_ubah = "UPDATE tb_siswa SET
        nama_siswa='".$_POST['nama_siswa']."',
        jekel='".$_POST['jekel']."',
        id_kelas='".$_POST['id_kelas']."',
        th_masuk='".$_POST['th_masuk']."'
        WHERE nis='".$_POST['nis']."'";
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
            logActivity($koneksi, 'UPDATE', 'tb_siswa', 'Mengubah siswa: ' . $_POST['nama_siswa'] . ' (NIS: ' . $_POST['nis'] . ')', $_POST['nis'] ?? null);
        }

        echo "<script>
        (function(){
            if(typeof Swal!=='undefined'){
                Swal.fire({
                    title:'Berhasil!',
                    text:'Data siswa berhasil diubah',
                    icon:'success',
                    confirmButtonText:'OK',
                    confirmButtonColor:'#28a745',
                    allowOutsideClick:false,
                    allowEscapeKey:false,
                    timer:2500,
                    timerProgressBar:true
                }).then(function(){
                    window.location.href='index.php?page=MyApp/data_siswa';
                });
                
                setTimeout(function(){
                    window.location.href='index.php?page=MyApp/data_siswa';
                }, 2600);
            } else {
                alert('Data siswa berhasil diubah');
                window.location.href='index.php?page=MyApp/data_siswa';
            }
        })();
        </script>";
    }else{
        echo "<script>
        (function(){
            if(typeof Swal!=='undefined'){
                Swal.fire({
                    title:'Gagal!',
                    text:'Data siswa gagal diubah',
                    icon:'error',
                    confirmButtonText:'OK',
                    confirmButtonColor:'#d33'
                });
            } else {
                alert('Data siswa gagal diubah');
            }
        })();
        </script>";
    }
}

// Proses Upload Excel
if (isset($_POST['simpan'])) {
    if (isset($_FILES['file_excel']) && $_FILES['file_excel']['error'] == 0) {
        $file_tmp = $_FILES['file_excel']['tmp_name'];
        $file_name = $_FILES['file_excel']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Validasi ekstensi file
        $allowed_ext = ['xls', 'xlsx', 'csv'];
        if (!in_array($file_ext, $allowed_ext)) {
            echo "<script>
            Swal.fire({title: 'Format File Tidak Valid',text: 'Hanya file Excel (.xls, .xlsx) atau CSV yang diperbolehkan',icon: 'error',confirmButtonText: 'OK'
            }).then((result) => {
                if (result.value) {
                    window.location = 'index.php?page=MyApp/data_siswa';
                }
            })</script>";
        } else {
            try {
                // Load file Excel
                $spreadsheet = IOFactory::load($file_tmp);
                
                // Skip header row (baris pertama)
                $success_count = 0;
                $error_count = 0;
                $error_messages = [];
                
                // Proses semua sheet (multi-tab support)
                $sheet_count = $spreadsheet->getSheetCount();
                $current_row_offset = 0;
                
                for ($sheet_index = 0; $sheet_index < $sheet_count; $sheet_index++) {
                    $worksheet = $spreadsheet->getSheet($sheet_index);
                    $rows = $worksheet->toArray();
                    $sheet_name = $worksheet->getTitle();
                    
                    // Skip header row (baris pertama) untuk setiap sheet
                    for ($i = 1; $i < count($rows); $i++) {
                    $row = $rows[$i];
                    
                    // Skip baris kosong
                    if (empty($row[0]) || empty($row[1])) {
                        continue;
                    }
                    
                    $nis = mysqli_real_escape_string($koneksi, trim($row[0]));
                    $nama_siswa = mysqli_real_escape_string($koneksi, trim($row[1]));
                    $jekel_input = mysqli_real_escape_string($koneksi, strtoupper(trim($row[2])));
                    $kelas = mysqli_real_escape_string($koneksi, trim($row[3]));
                    $th_masuk = mysqli_real_escape_string($koneksi, trim($row[4]));
                    $status = isset($row[5]) ? mysqli_real_escape_string($koneksi, trim($row[5])) : 'Aktif';
                    
                    // Validasi data
                    if (empty($nis) || empty($nama_siswa) || empty($jekel_input) || empty($kelas)) {
                        $error_count++;
                        $error_messages[] = "Sheet '$sheet_name' Baris " . ($i + 1) . ": Data tidak lengkap";
                        continue;
                    }
                    
                    // Validasi dan konversi jekel (support L/P dan LK/PR)
                    if (in_array($jekel_input, ['L', 'LK', 'LAKI-LAKI'])) {
                        $jekel = 'LK';
                    } elseif (in_array($jekel_input, ['P', 'PR', 'PEREMPUAN'])) {
                        $jekel = 'PR';
                    } else {
                        $error_count++;
                        $error_messages[] = "Sheet '$sheet_name' Baris " . ($i + 1) . ": Jenis kelamin harus L/LK (Laki-laki) atau P/PR (Perempuan)";
                        continue;
                    }
                    
                    // Cari id_kelas berdasarkan nama kelas
                    $query_kelas = "SELECT id_kelas FROM tb_kelas WHERE kelas = '$kelas' LIMIT 1";
                    $result_kelas = mysqli_query($koneksi, $query_kelas);
                    
                    if (mysqli_num_rows($result_kelas) == 0) {
                        $error_count++;
                        $error_messages[] = "Sheet '$sheet_name' Baris " . ($i + 1) . ": Kelas '$kelas' tidak ditemukan";
                        continue;
                    }
                    
                    $data_kelas = mysqli_fetch_assoc($result_kelas);
                    $id_kelas = $data_kelas['id_kelas'];
                    
                    // Cek apakah NIS sudah ada
                    $cek_nis = "SELECT nis FROM tb_siswa WHERE nis = '$nis'";
                    $result_cek = mysqli_query($koneksi, $cek_nis);
                    
                    if (mysqli_num_rows($result_cek) > 0) {
                        // Update data jika sudah ada
                        $sql_update = "UPDATE tb_siswa SET 
                            nama_siswa = '$nama_siswa',
                            jekel = '$jekel',
                            id_kelas = '$id_kelas',
                            th_masuk = '$th_masuk',
                            status = '$status'
                            WHERE nis = '$nis'";
                        $query_update = mysqli_query($koneksi, $sql_update);
                        
                        if ($query_update) {
                            $success_count++;
                        } else {
                            $error_count++;
                            $error_messages[] = "Sheet '$sheet_name' Baris " . ($i + 1) . ": Gagal update data NIS $nis";
                        }
                    } else {
                        // Insert data baru
                        $sql_insert = "INSERT INTO tb_siswa (nis, nama_siswa, jekel, id_kelas, status, th_masuk) 
                            VALUES ('$nis', '$nama_siswa', '$jekel', '$id_kelas', '$status', '$th_masuk')";
                        $query_insert = mysqli_query($koneksi, $sql_insert);
                        
                        if ($query_insert) {
                            $success_count++;
                        } else {
                            $error_count++;
                            $error_messages[] = "Sheet '$sheet_name' Baris " . ($i + 1) . ": Gagal insert data NIS $nis - " . mysqli_error($koneksi);
                        }
                    }
                    }
                }
                
                // Tampilkan hasil
                $message = "Berhasil: $success_count data, Gagal: $error_count data";
                if ($error_count > 0 && count($error_messages) > 0) {
                    $message .= "<br>Detail error:<br>" . implode("<br>", array_slice($error_messages, 0, 10));
                    if (count($error_messages) > 10) {
                        $message .= "<br>... dan " . (count($error_messages) - 10) . " error lainnya";
                    }
                }
                
                $icon = ($success_count > 0) ? 'success' : 'error';
                $title = ($success_count > 0) ? 'Impor Data Berhasil' : 'Impor Data Gagal';
                
                echo "<script>
                Swal.fire({title: '$title',html: '$message',icon: '$icon',confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.value) {
                        window.location = 'index.php?page=MyApp/data_siswa';
                    }
                })</script>";
                
            } catch (Exception $e) {
                echo "<script>
                Swal.fire({title: 'Error',text: 'Terjadi kesalahan: " . addslashes($e->getMessage()) . "',icon: 'error',confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.value) {
                        window.location = 'index.php?page=MyApp/data_siswa';
                    }
                })</script>";
            }
        }
    } else {
        echo "<script>
        Swal.fire({title: 'File Tidak Ditemukan',text: 'Silakan pilih file Excel untuk diupload',icon: 'error',confirmButtonText: 'OK'
        }).then((result) => {
            if (result.value) {
                window.location = 'index.php?page=MyApp/data_siswa';
            }
        })</script>";
    }
}
?>

<section class="content-header">
    <h1>
        Master Data
        <small>Siswa</small>
    </h1>
</section>
<!-- Main content -->
<section class="content">
    <div class="rounded-2xl bg-white shadow-sm">
        <div class="border-b border-slate-100 px-6 py-4">
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-900">Master Data Siswa</h3>
                    <div class="flex items-center gap-2">
                        <a href="admin/export_handler.php?type=excel&table=siswa" class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-600/20 hover:bg-emerald-100 transition-colors" title="Ekspor ke Excel">
                            <i class="fa-solid fa-file-excel"></i><span>Excel</span>
                        </a>
                        <a href="admin/export_handler.php?type=pdf&table=siswa" class="inline-flex items-center gap-1.5 rounded-xl bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 ring-1 ring-rose-600/20 hover:bg-rose-100 transition-colors" title="Ekspor ke PDF" target="_blank">
                            <i class="fa-solid fa-file-pdf"></i><span>PDF</span>
                        </a>
                    </div>
                </div>
                
                <div class="flex flex-wrap items-center justify-between gap-3 bg-slate-50/50 rounded-2xl p-3 border border-slate-100">
                    <div class="flex items-center gap-2 text-sm font-medium text-slate-600">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                            <i class="fa-solid fa-users text-xs"></i>
                        </div>
                        <span id="infoJumlahSiswa">
                            <?php
                            $query_total = "SELECT COUNT(*) as total FROM tb_siswa";
                            $result_total = mysqli_query($koneksi, $query_total);
                            $data_total = mysqli_fetch_assoc($result_total);
                            echo "Total: <strong>" . $data_total['total'] . "</strong> Siswa";
                            ?>
                        </span>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" class="btn btn-dashboard-primary tw-modal-open" data-target="#addModal">
                            <i class="fa-solid fa-plus text-xs"></i><span>Tambah Data</span>
                        </button>
                        <button type="button" id="btnEditTerpilih" class="btn btn-dashboard-soft disabled:opacity-50" onclick="editTerpilih()" disabled>
                            <i class="fa-solid fa-pen-to-square text-xs"></i><span>Edit</span>
                        </button>
                        <button type="button" id="btnHapusTerpilih" class="btn btn-dash-danger disabled:opacity-50" onclick="hapusTerpilih()" disabled>
                            <i class="fa-solid fa-trash text-xs"></i><span>Hapus</span>
                        </button>
                        <div class="h-8 w-px bg-slate-200 mx-1 hidden sm:block"></div>
                        <button type="button" class="btn btn-dashboard-soft tw-modal-open" data-target="#modal-impor">
                            <i class="fa-solid fa-file-import text-xs"></i><span>Impor Data</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.box-header -->
        <div class="p-6">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 mb-4">
                <h4 class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-sky-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M22 3H2l8 9v7l4 2v-9l8-9z"/>
                    </svg>
                    <span>Filter Data</span>
                </h4>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="text-xs text-slate-500">Filter NIS:</label>
                        <input type="text" id="filterNIS" class="auth-input" placeholder="Cari berdasarkan NIS..." onkeyup="executeFilterSiswa()">
                    </div>
                    <div>
                        <label class="text-xs text-slate-500">Filter Nama:</label>
                        <input type="text" id="filterNama" class="auth-input" placeholder="Cari berdasarkan Nama..." onkeyup="executeFilterSiswa()">
                    </div>
                    <div>
                        <label class="text-xs text-slate-500">Filter Kelas:</label>
                        <select id="filterKelas" class="auth-input" onchange="executeFilterSiswa()">
                            <option value="">-- Semua Kelas --</option>
                            <?php
                            $query_kelas_filter = "SELECT DISTINCT kelas FROM tb_kelas ORDER BY kelas ASC";
                            $result_kelas_filter = mysqli_query($koneksi, $query_kelas_filter);
                            while ($row_kelas = mysqli_fetch_assoc($result_kelas_filter)) {
                                echo '<option value="' . htmlspecialchars($row_kelas['kelas']) . '">' . htmlspecialchars($row_kelas['kelas']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-slate-500">Filter Tahun Masuk:</label>
                        <select id="filterThMasuk" class="auth-input" onchange="executeFilterSiswa()">
                            <option value="">-- Semua Tahun --</option>
                            <?php
                            $query_tahun = "SELECT DISTINCT th_masuk FROM tb_siswa ORDER BY th_masuk DESC";
                            $result_tahun = mysqli_query($koneksi, $query_tahun);
                            while ($row_tahun = mysqli_fetch_assoc($result_tahun)) {
                                echo '<option value="' . $row_tahun['th_masuk'] . '">' . $row_tahun['th_masuk'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <form id="formSiswa" method="post" action="">
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
                                <th width="30">
                                    <input type="checkbox" id="checkAll" title="Pilih Semua" onchange="handleCheckAllClick(this)">
                                </th>
                                <th>No</th>
                                <th>NIS</th>
                                <th>Nama</th>
                                <th>Laki-laki</th>
                                <th>Perempuan</th>
                                <th>Kelas</th>
                                <th>Status</th>
                                <th>Th Masuk</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="datatableUsersBody">

                            <?php
                      $no = 1;
                      // Cek koneksi dan query dengan error handling
                      if (!isset($koneksi) || !$koneksi) {
                          echo '<tr><td colspan="10" class="text-center text-danger">Error: Koneksi database tidak tersedia</td></tr>';
                      } else {
                          // Debug: Hitung total siswa di database
                          $count_all = @$koneksi->query("SELECT COUNT(*) as total FROM tb_siswa");
                          $total_siswa = 0;
                          if ($count_all) {
                              $row_count = $count_all->fetch_assoc();
                              $total_siswa = $row_count['total'];
                          }
                          
                          // Gunakan LEFT JOIN untuk menampilkan semua siswa meskipun kelas tidak ada
                          $sql = @$koneksi->query("SELECT s.nis, s.nama_siswa, s.jekel, s.status, s.th_masuk, s.id_kelas, 
                          COALESCE(k.kelas, 'Tidak Ada Kelas') as kelas 
                          from tb_siswa s 
                          LEFT JOIN tb_kelas k on s.id_kelas=k.id_kelas 
                          order by COALESCE(k.kelas, 'ZZZ') asc, s.nis asc");
                          
                          // Debug: Tampilkan error jika ada
                          if ($sql === false) {
                              echo '<tr><td colspan="10" class="text-center text-danger">Error Query: ' . htmlspecialchars($koneksi->error) . '</td></tr>';
                          } elseif ($sql && $sql->num_rows > 0) {
                              // Debug info (akan dihapus setelah fix)
                              if ($total_siswa > $sql->num_rows) {
                                  echo '<tr><td colspan="10" class="text-center text-warning"><small>Total siswa di database: ' . $total_siswa . ', yang ditampilkan: ' . $sql->num_rows . '</small></td></tr>';
                              }
                              while ($data= $sql->fetch_assoc()) {
                      ?>

                            <tr data-nis="<?php echo $data['nis']; ?>" data-nama="<?php echo htmlspecialchars($data['nama_siswa']); ?>" data-jekel="<?php echo $data['jekel']; ?>" data-id_kelas="<?php echo $data['id_kelas']; ?>" data-kelas="<?php echo htmlspecialchars($data['kelas']); ?>" data-status="<?php echo $data['status']; ?>" data-th_masuk="<?php echo $data['th_masuk']; ?>">
                                <td>
                                    <input type="checkbox" name="nis[]" class="checkItem" value="<?php echo $data['nis']; ?>" onchange="toggleButtonsSiswa()">
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
                            <td class="text-center">
                                <?php if ($data['jekel'] == 'LK') { ?>
                                    <i class="fa fa-check text-success"></i>
                                <?php } else { ?>
                                    <span class="text-muted">-</span>
                                <?php } ?>
                            </td>
                            <td class="text-center">
                                <?php if ($data['jekel'] == 'PR') { ?>
                                    <i class="fa fa-check text-success"></i>
                                <?php } else { ?>
                                    <span class="text-muted">-</span>
                                <?php } ?>
                            </td>
                            <td>
                                <?php echo $data['kelas']; ?>
                            </td>

                            <?php $warna = $data['status']  ?>
                            <td>
                                <?php if ($warna == 'Aktif') { ?>
                                <span class="badge-pill badge-pill-primary">Aktif</span>
                                <?php } elseif ($warna == 'Lulus') { ?>
                                <span class="badge-pill badge-pill-success">Lulus</span>
                                <?php } elseif ($warna == 'Pindah') { ?>
                                <span class="badge-pill badge-pill-danger">Pindah</span>
                            </td>
                            <?php } ?>

                            <td>
                                <?php echo $data['th_masuk']; ?>
                            </td>

                            <td>
                                <button type="button" class="btn btn-sm btn-dashboard-success tw-modal-open" data-target="#editModal"
                                    data-nis="<?php echo $data['nis']; ?>"
                                    data-nama="<?php echo htmlspecialchars($data['nama_siswa']); ?>"
                                    data-jekel="<?php echo $data['jekel']; ?>"
                                    data-id_kelas="<?php echo $data['id_kelas']; ?>"
                                    data-th_masuk="<?php echo $data['th_masuk']; ?>"
                                    title="Ubah">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <a href="?page=MyApp/del_siswa&kode=<?php echo $data['nis']; ?>"
                                    onclick="return confirmHapus(event, 'Yakin hapus data siswa <?php echo htmlspecialchars($data['nama_siswa']); ?>?')" title="Hapus"
                                    class="btn btn-sm btn-dashboard-danger">
                                    <i class="fa-solid fa-trash"></i>
                                    </a>
                            </td>
                        </tr>
                        <?php
                              }
                          } else {
                              // Tampilkan pesan jika tidak ada data
                              echo '<tr><td colspan="10" class="text-center">';
                              if ($sql === false) {

                                  echo '<span class="text-danger">Error: ' . htmlspecialchars($koneksi->error) . '</span>';
                              } else {
                                  echo '<span class="text-muted">Tidak ada data siswa. Silakan tambah data siswa terlebih dahulu.</span>';
                              }
                              echo '</td></tr>';
                          }
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

<!-- Modal Edit Multiple -->
<div class="fixed inset-0 z-[120] hidden items-center justify-center bg-black/50 backdrop-blur-sm modal" id="modalEditMultiple">
    <div class="relative w-full max-w-6xl rounded-2xl bg-white p-6 shadow-xl overflow-hidden flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div class="mb-5 flex items-center justify-between border-b border-slate-100 pb-4 shrink-0">
            <h3 class="text-lg font-semibold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-indigo-500"></i>
                Edit Multiple Siswa
                <span id="countData" class="ml-2 rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-600"></span>
            </h3>
            <button type="button" class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700 tw-modal-close transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        
        <!-- Body -->
        <form id="formEditMultiple" method="post" action="?page=MyApp/edit_siswa_multiple" class="flex flex-col flex-1 overflow-hidden">
            <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar">
                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-xs uppercase text-slate-500 sticky top-0 z-10">
                            <tr>
                                <th class="px-4 py-3 font-medium">NIS</th>
                                <th class="px-4 py-3 font-medium">Nama</th>
                                <th class="px-4 py-3 font-medium">Jenis Kelamin</th>
                                <th class="px-4 py-3 font-medium">Kelas</th>
                                <th class="px-4 py-3 font-medium">Tahun Masuk</th>
                                <th class="px-4 py-3 font-medium">Status</th>
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
// Definisikan fungsi filter di awal agar bisa diakses dari onclick
// Fungsi untuk eksekusi filter - dibuat global, menggunakan filter manual jQuery
window.executeFilterSiswa = function() {
    // Tunggu jQuery ready
    if (typeof jQuery === 'undefined') {
        alert('jQuery belum dimuat. Silakan refresh halaman.');
        return;
    }
    
    // Ambil nilai dari setiap filter (independen)
    var filterNIS = $('#filterNIS').val() ? $('#filterNIS').val().trim().toLowerCase() : '';
    var filterNama = $('#filterNama').val() ? $('#filterNama').val().trim().toLowerCase() : '';
    var filterKelas = $('#filterKelas').val() ? $('#filterKelas').val().trim() : '';
    var filterThMasuk = $('#filterThMasuk').val() ? $('#filterThMasuk').val().trim() : '';
    
    // Jika menggunakan DataTable, gunakan DataTable API untuk filter
    if (typeof $.fn.DataTable !== 'undefined' && $.fn.DataTable.isDataTable('#example1')) {
        var table = $('#example1').DataTable();
        
        // Apply filter ke setiap kolom menggunakan DataTable API
        // Kolom index: 0=checkbox, 1=No, 2=NIS, 3=Nama, 4=Laki-laki, 5=Perempuan, 6=Kelas, 7=Status, 8=Th Masuk, 9=Aksi
        
        // Filter NIS dan Nama menggunakan search biasa (partial match)
        table.column(2).search(filterNIS);
        table.column(3).search(filterNama);
        
        // Filter Kelas - gunakan exact match
        if (filterKelas !== '') {
            var regexKelas = '^' + filterKelas.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '$';
            table.column(6).search(regexKelas, true, false);
        } else {
            table.column(6).search('');
        }
        
        // Filter Tahun Masuk - gunakan exact match dengan regex
        // Escape special characters dan gunakan ^ dan $ untuk exact match
        if (filterThMasuk !== '') {
            // Gunakan regex untuk exact match: ^2024$ akan match hanya "2024"
            var regexPattern = '^' + filterThMasuk.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '$';
            table.column(8).search(regexPattern, true, false);
        } else {
            // Jika tidak ada filter tahun, clear search
            table.column(8).search('');
        }
        
        // Draw tabel dengan filter yang sudah diterapkan
        table.draw();
        
        // Update info jumlah siswa setelah filter
        setTimeout(function() {
            updateInfoJumlahSiswa();
        }, 100);
    } else {
        // Fallback: filter manual dengan jQuery jika DataTable belum tersedia
        var visibleCount = 0;
        
        $('#example1 tbody tr').each(function() {
            var $row = $(this);
            
            // Ambil nilai dari setiap kolom
            // Kolom: 0=checkbox, 1=No, 2=NIS, 3=Nama, 4=Laki-laki, 5=Perempuan, 6=Kelas, 7=Status, 8=Th Masuk, 9=Aksi
            var nis = $row.find('td').eq(2).text().trim().toLowerCase();
            var nama = $row.find('td').eq(3).text().trim().toLowerCase();
            var kelas = $row.find('td').eq(6).text().trim();
            var thMasuk = $row.find('td').eq(8).text().trim();
            
            var showRow = true;
            
            // Filter NIS - hanya aktif jika ada nilai dan cocok
            if (filterNIS !== '' && nis.indexOf(filterNIS) === -1) {
                showRow = false;
            }
            
            // Filter Nama - hanya aktif jika ada nilai dan cocok
            if (filterNama !== '' && nama.indexOf(filterNama) === -1) {
                showRow = false;
            }
            
            // Filter Kelas - hanya aktif jika ada nilai dan cocok (exact match)
            if (filterKelas !== '' && kelas !== filterKelas) {
                showRow = false;
            }
            
            // Filter Tahun Masuk - hanya aktif jika ada nilai dan cocok (exact match)
            if (filterThMasuk !== '') {
                // Normalisasi tahun untuk perbandingan (hapus whitespace, convert ke string)
                var thMasukNormalized = String(thMasuk).trim();
                var filterThMasukNormalized = String(filterThMasuk).trim();
                
                if (thMasukNormalized !== filterThMasukNormalized) {
                    showRow = false;
                }
            }
            
            // Tampilkan atau sembunyikan baris
            if (showRow) {
                $row.show();
                visibleCount++;
            } else {
                $row.hide();
            }
        });
        
        // Update info jumlah siswa
        updateInfoJumlahSiswaManual(visibleCount);
    }
    
    // Update tombol setelah filter
    if (typeof toggleButtonsSiswa === 'function') {
        setTimeout(function() {
            toggleButtonsSiswa();
        }, 100);
    }
};

// Fungsi untuk update info jumlah siswa (DataTable)
function updateInfoJumlahSiswa() {
    if (typeof $.fn.DataTable !== 'undefined' && $.fn.DataTable.isDataTable('#example1')) {
        var table = $('#example1').DataTable();
        var info = table.page.info();
        var totalRecords = info.recordsTotal;
        var filteredRecords = info.recordsDisplay;
        
        if (filteredRecords < totalRecords) {
            $('#infoJumlahSiswa').html('<i class="fa fa-filter"></i> Menampilkan: <strong>' + filteredRecords + '</strong> dari <strong>' + totalRecords + '</strong> Siswa');
        } else {
            $('#infoJumlahSiswa').html('<i class="fa fa-users"></i> Total: <strong>' + totalRecords + '</strong> Siswa');
        }
    }
}

// Fungsi untuk update info jumlah siswa (Manual)
function updateInfoJumlahSiswaManual(visibleCount) {
    var totalRows = $('#example1 tbody tr').length;
    
    if (visibleCount < totalRows) {
        $('#infoJumlahSiswa').html('<i class="fa fa-filter"></i> Menampilkan: <strong>' + visibleCount + '</strong> dari <strong>' + totalRows + '</strong> Siswa');
    } else {
        $('#infoJumlahSiswa').html('<i class="fa fa-users"></i> Total: <strong>' + totalRows + '</strong> Siswa');
    }
}

// Fungsi untuk reset filter
window.resetFilterSiswa = function() {
    // Reset semua input filter
    $('#filterNIS').val('');
    $('#filterNama').val('');
    $('#filterKelas').val('');
    $('#filterThMasuk').val('');
    
    // Tampilkan semua baris
    $('#example1 tbody tr').show();
    
    // Reset filter di DataTable jika tersedia
    if (typeof $.fn.DataTable !== 'undefined' && $.fn.DataTable.isDataTable('#example1')) {
        var table = $('#example1').DataTable();
        table.search('').columns().search('').draw();
        
        // Pastikan semua baris ditampilkan setelah draw
        setTimeout(function() {
            $('#example1 tbody tr').show();
            updateInfoJumlahSiswa();
        }, 100);
    } else {
        updateInfoJumlahSiswaManual($('#example1 tbody tr').length);
    }
    
    if (typeof toggleButtonsSiswa === 'function') {
        setTimeout(function() {
            toggleButtonsSiswa();
        }, 100);
    }
};

// Fungsi global untuk toggle tombol
function toggleButtonsSiswa() {
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
    toggleButtonsSiswa();
}

// Checkbox untuk pilih semua
$(document).ready(function() {
    // Fungsi untuk toggle tombol (local untuk kompatibilitas)
    function toggleButtons() {
        toggleButtonsSiswa();
    }
    
    // Handler untuk checkAll menggunakan event delegation
    $(document).on('click change', '#checkAll', function(e) {
        e.stopPropagation();
        handleCheckAllClick(this);
    });
    
    // Pastikan checkbox all sync dengan checkbox individual (menggunakan event delegation)
    $(document).on('change click', '.checkItem', function(e) {
        // Jangan stop propagation agar inline handler juga bisa berjalan
        var totalCheckbox = $('.checkItem').length;
        var checkedCount = $('.checkItem:checked').length;
        $('#checkAll').prop('checked', (totalCheckbox > 0 && checkedCount === totalCheckbox));
        // Panggil fungsi global untuk toggle tombol
        toggleButtonsSiswa();
    });
    
    // Tambahkan juga handler langsung sebagai backup (setelah delay untuk memastikan elemen sudah ada)
    setTimeout(function() {
        $('.checkItem').on('change click', function() {
            toggleButtonsSiswa();
        });
    }, 300);
    
    // Setup handler langsung pada elemen setelah DOM ready sebagai backup
    setTimeout(function() {
        // Handler untuk checkAll
        var checkAllEl = $('#checkAll');
        if (checkAllEl.length > 0) {
            checkAllEl.off('click change').on('click change', function(e) {
                e.stopPropagation();
                handleCheckAllClick(this);
            });
        }
        
        // Handler untuk checkbox individual sebagai backup (tanpa .off untuk menghindari konflik)
        $('.checkItem').on('change.checkItem click.checkItem', function() {
            var totalCheckbox = $('.checkItem').length;
            var checkedCount = $('.checkItem:checked').length;
            $('#checkAll').prop('checked', (totalCheckbox > 0 && checkedCount === totalCheckbox));
            toggleButtonsSiswa();
        });
    }, 200);
    
    // Handle checkbox all di DataTable (jika menggunakan DataTable)
    $('#example1').on('draw.dt', function() {
        // Reset checkAll setelah draw
        var totalCheckbox = $('.checkItem').length;
        var checkedCount = $('.checkItem:checked').length;
        $('#checkAll').prop('checked', (totalCheckbox > 0 && checkedCount === totalCheckbox));
        
        // Re-setup handler langsung setelah draw
        var checkAllEl = $('#checkAll');
        if (checkAllEl.length > 0) {
            checkAllEl.off('click change').on('click change', function(e) {
                e.stopPropagation();
                handleCheckAllClick(this);
            });
            // Juga set onchange attribute
            checkAllEl.attr('onchange', 'handleCheckAllClick(this)');
        }
        
        // Re-setup handler untuk checkbox individual (menggunakan namespace)
        $('.checkItem').off('change.checkItem click.checkItem').on('change.checkItem click.checkItem', function() {
            var totalCheckbox = $('.checkItem').length;
            var checkedCount = $('.checkItem:checked').length;
            $('#checkAll').prop('checked', (totalCheckbox > 0 && checkedCount === totalCheckbox));
            toggleButtonsSiswa();
        });
        
        // Re-attach inline onchange handler untuk checkbox individual
        $('.checkItem').attr('onchange', 'toggleButtonsSiswa()');
        
        toggleButtonsSiswa();
    });
    
    // Setup handler setelah delay untuk memastikan DataTable sudah init
    setTimeout(function() {
        var checkAllEl = $('#checkAll');
        if (checkAllEl.length > 0) {
            checkAllEl.off('click change').on('click change', function(e) {
                e.stopPropagation();
                handleCheckAllClick(this);
            });
            checkAllEl.attr('onchange', 'handleCheckAllClick(this)');
        }
    }, 500);
    
    // Inisialisasi: disable tombol di awal
    toggleButtonsSiswa();
});


// Setup event handler untuk Enter key dan change event
$(document).ready(function() {
    // Enter key pada input field
    $(document).on('keypress', '#filterNIS, #filterNama', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            executeFilterSiswa();
        }
    });
    
    // Change event untuk dropdown tahun masuk
    $(document).on('change', '#filterThMasuk', function(e) {
        executeFilterSiswa();
    });
    
    // Update info jumlah siswa saat DataTable draw
    if (typeof $.fn.DataTable !== 'undefined' && $.fn.DataTable.isDataTable('#example1')) {
        var table = $('#example1').DataTable();
        table.on('draw', function() {
            updateInfoJumlahSiswa();
        });
        
        // Update info saat pertama kali load
        setTimeout(function() {
            updateInfoJumlahSiswa();
        }, 500);
    }
});

function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function loadDataForEdit(nisArray) {
    var tbody = $('#tbodyEditMultiple');
    tbody.empty();
    $('#formEditMultiple').find('input[name="nis[]"]').remove();
    $('#formEditMultiple').find('input[name="nama_siswa[]"]').remove();
    $('#formEditMultiple').find('select[name="jekel[]"]').remove();
    $('#formEditMultiple').find('select[name="id_kelas[]"]').remove();
    $('#formEditMultiple').find('input[name="th_masuk[]"]').remove();
    $('#formEditMultiple').find('select[name="status[]"]').remove();
    
    var count = 0;
    
    nisArray.forEach(function(nis) {
        var row = $('tr[data-nis="' + nis + '"]');
        if (row.length > 0) {
            var nama = row.attr('data-nama');
            var jekel = row.attr('data-jekel');
            var id_kelas = row.attr('data-id_kelas');
            var status = row.attr('data-status');
            var th_masuk = row.attr('data-th_masuk');
            
            // Buat select kelas dengan option yang sudah di-set selected
            var selectKelas = $('<select>', {
                class: 'form-control',
                name: 'id_kelas[]',
                required: true
            });
            
            // Tambahkan semua option kelas
            <?php
            $query_kelas_for_js = "SELECT * FROM tb_kelas ORDER BY kelas";
            $hasil_kelas_for_js = mysqli_query($koneksi, $query_kelas_for_js);
            while ($row_kelas_for_js = mysqli_fetch_array($hasil_kelas_for_js)) {
                echo "selectKelas.append($('<option>', {value: '" . $row_kelas_for_js['id_kelas'] . "', text: '" . htmlspecialchars($row_kelas_for_js['kelas'], ENT_QUOTES) . "'}));";
            }
            ?>
            
            // Set selected berdasarkan id_kelas
            selectKelas.val(id_kelas);
            
            var tr = $('<tr>')
                .append($('<td>').append($('<input>', {
                    type: 'text',
                    class: 'block w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500',
                    value: nis,
                    readonly: true
                })))
                .append($('<td>').append($('<input>', {
                    type: 'text',
                    class: 'block w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500',
                    name: 'nama_siswa[]',
                    value: nama,
                    required: true
                })))
                .append($('<td>').append($('<select>', {
                    class: 'block w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500',
                    name: 'jekel[]',
                    required: true
                }).append($('<option>', {
                    value: 'LK',
                    selected: jekel == 'LK',
                    text: 'LK'
                })).append($('<option>', {
                    value: 'PR',
                    selected: jekel == 'PR',
                    text: 'PR'
                }))))
                .append($('<td>').append(selectKelas.addClass('block w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500')))
                .append($('<td>').append($('<input>', {
                    type: 'number',
                    class: 'block w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500',
                    name: 'th_masuk[]',
                    value: th_masuk,
                    required: true,
                    min: '1900',
                    max: '2099'
                })))
                .append($('<td>').append($('<select>', {
                    class: 'block w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500',
                    name: 'status[]',
                    required: true
                }).append($('<option>', {
                    value: 'Aktif',
                    selected: status == 'Aktif',
                    text: 'Aktif'
                })).append($('<option>', {
                    value: 'Lulus',
                    selected: status == 'Lulus',
                    text: 'Lulus'
                })).append($('<option>', {
                    value: 'Pindah',
                    selected: status == 'Pindah',
                    text: 'Pindah'
                }))));
            
            tbody.append(tr);
            
            // Tambahkan hidden input untuk nis
            $('#formEditMultiple').append($('<input>', {
                type: 'hidden',
                name: 'nis[]',
                value: nis
            }));
            count++;
        }
    });
    
    $('#countData').text(count + ' siswa');
    
    if (typeof $ !== 'undefined') { $('#modalEditMultiple').removeClass('hidden').addClass('flex'); }
}

function editTerpilih() {
    var checked = $('.checkItem:checked').length;
    if (checked === 0) {
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
    if (checked === 1) {
        window.location.href = '?page=MyApp/edit_siswa&kode=' + checkedItems[0];
        return;
    }
    
    // Jika lebih dari 1, load data dan buka modal
    if (typeof loadDataForEdit === 'function') {
        loadDataForEdit(checkedItems);
    } else {
        setTimeout(function() {
            if (typeof loadDataForEdit === 'function') {
                loadDataForEdit(checkedItems);
            }
        }, 100);
    }
}

function hapusTerpilih() {
    var checked = $('.checkItem:checked').length;
    if (checked === 0) {
        Swal.fire({
            title: 'Peringatan!',
            text: 'Pilih data yang akan dihapus!',
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
    
    Swal.fire({
        title: 'Konfirmasi Hapus',
        html: `Yakin hapus <strong>${checked}</strong> siswa terpilih?<br><small class='text-red-500'>Data yang dihapus tidak dapat dikembalikan!</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48', // rose-600
        cancelButtonColor: '#64748b', // slate-500
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-3xl border border-slate-200 shadow-xl',
            title: 'text-lg font-semibold text-slate-900',
            confirmButton: 'rounded-xl',
            cancelButton: 'rounded-xl'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $('#formSiswa').attr('action', '?page=MyApp/del_siswa_multiple').submit();
        }
    });
}

// Expose fungsi ke global scope
window.editTerpilih = editTerpilih;
window.hapusTerpilih = hapusTerpilih;
window.handleCheckAllClick = handleCheckAllClick;
window.toggleButtonsSiswa = toggleButtonsSiswa;
</script>

<!-- Modal Impor -->
<div class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm modal" id="modal-impor">
    <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl  transition-all">
        <!-- Header -->
        <div class="mb-5 flex items-center justify-between border-b border-slate-100 pb-4">
            <h3 class="text-lg font-semibold text-slate-900  flex items-center gap-2">
                <i class="fa-solid fa-file-import text-emerald-500"></i>
                Upload Data Siswa
            </h3>
            <button type="button" class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700    tw-modal-close transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        
        <!-- Body -->
        <form method="POST" enctype="multipart/form-data" action="">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <label class="text-sm font-medium text-slate-700  File Excel</label>"><a href="../../inc/generate_template_siswa.php" class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-600 hover:text-emerald-700   target="_blank">
                        <i class="fa-solid fa-download"></i>
                        Download Template
                    </a>
                </div>
                
                <div class="relative">
                    <input type="file" name="file_excel" class="block w-full cursor-pointer rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20     accept=".xls,.xlsx,.csv" required>
                </div>
                
                <p class="text-[11px] text-slate-500">
                    <i class="fa-solid fa-circle-info mr-1"></i>
                    Format yang didukung: Excel (.xls, .xlsx) atau CSV
                </p>
            </div>
            
            <!-- Footer -->
            <div class="mt-8 flex justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-200/50      tw-modal-close transition-all">
                    Batal
                </button>
                <button type="submit" name="simpan" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-emerald-700 hover:shadow-lg hover:shadow-emerald-500/30 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 transition-all">
                    <i class="fa-solid fa-upload"></i>
                    <span>Upload</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Add Modal -->
<div class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm modal" id="addModal">
    <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl  transition-all">
        <!-- Header -->
        <div class="mb-5 flex items-center justify-between border-b border-slate-100 pb-4">
            <h3 class="text-lg font-semibold text-slate-900  flex items-center gap-2">
                <i class="fa-solid fa-user-plus text-indigo-500"></i>
                Tambah Siswa
            </h3>
            <button type="button" class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700    tw-modal-close transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        
        <!-- Body -->
        <form action="" method="post">
            <div class="space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-slate-700">
                        <input type="text" name="nis" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20     placeholder="NIS" required>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-slate-700  Masuk</label>"><input type="number" name="th_masuk" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20     placeholder="Tahun Masuk" required>
                    </div>
                </div>
                
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-slate-700  Siswa</label>"><input type="text" name="nama_siswa" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20     placeholder="Nama Lengkap" required>
                </div>
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-slate-700  Kelamin</label>"><select name="jekel" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20     required>"><option value="">-- Pilih --</option>
                            <option value="LK">Laki-laki</option>
                            <option value="PR">Perempuan</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-slate-700">
                        <select name="id_kelas" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20     required>"><option value="">-- Pilih --</option>
                            <?php
                            $query_kelas = "select * from tb_kelas";
                            $hasil_kelas = mysqli_query($koneksi, $query_kelas);
                            while ($row_kelas = mysqli_fetch_array($hasil_kelas)) {
                                echo '<option value="'.$row_kelas['id_kelas'].'">'.$row_kelas['kelas'].'</option>';
                            }
                            ?>
                        </select>
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

<!-- Edit Modal -->
<div class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm modal" id="editModal">
    <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl  transition-all">
        <!-- Header -->
        <div class="mb-5 flex items-center justify-between border-b border-slate-100 pb-4">
            <h3 class="text-lg font-semibold text-slate-900  flex items-center gap-2">
                <i class="fa-solid fa-user-pen text-indigo-500"></i>
                Ubah Siswa
            </h3>
            <button type="button" class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700    tw-modal-close transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        
        <!-- Body -->
        <form action="" method="post">
            <div class="space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-slate-700">
                        <input type="text" name="nis" id="edit_nis" class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-500 focus:outline-none    readonly>"></div>
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-slate-700  Masuk</label>"><input type="number" name="th_masuk" id="edit_th_masuk" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20     required>"></div>
                </div>">
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-slate-700  Siswa</label>"><input type="text" name="nama_siswa" id="edit_nama_siswa" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20     required>"></div>">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-slate-700  Kelamin</label>"><select name="jekel" id="edit_jekel" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20     required>"><option value="">-- Pilih --</option>
                            <option value="LK">Laki-laki</option>
                            <option value="PR">Perempuan</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-slate-700">
                        <select name="id_kelas" id="edit_id_kelas" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20     required>"><option value="">-- Pilih --</option>
                            <?php
                            $hasil_kelas_edit = mysqli_query($koneksi, "select * from tb_kelas");
                            while ($row_kelas = mysqli_fetch_array($hasil_kelas_edit)) {
                                echo '<option value="'.$row_kelas['id_kelas'].'">'.$row_kelas['kelas'].'</option>';
                            }
                            ?>
                        </select>
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

<script>
    // Tunggu sampai jQuery dimuat
    $(document).on('click', '.tw-modal-open', function (event) {
        event.preventDefault();
        var target = $(this).data('target');
        if (target === '#editModal') {
            var nis = $(this).data('nis');
            var nama = $(this).data('nama');
            var jekel = $(this).data('jekel');
            var id_kelas = $(this).data('id_kelas');
            var th_masuk = $(this).data('th_masuk');
            $('#edit_nis').val(nis);
            $('#edit_nama_siswa').val(nama);
            $('#edit_jekel').val(jekel);
            $('#edit_id_kelas').val(id_kelas);
            $('#edit_th_masuk').val(th_masuk);
        }
        $(target).removeClass('hidden').addClass('flex');
    });
    $(document).on('click', '.tw-modal-close', function () {
        $(this).closest('.modal').addClass('hidden').removeClass('flex');
    });
    $(document).on('click', '.modal', function (e) {
        if ($(e.target).hasClass('modal')) { $(this).addClass('hidden').removeClass('flex'); }
    });
</script>

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

