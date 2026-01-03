<?php
// Load PhpSpreadsheet library
require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

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
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title" style="margin-right: 15px;">
                <i class="fa fa-users"></i> 
                <span id="infoJumlahSiswa">
                    <?php
                    // Hitung total siswa
                    $query_total = "SELECT COUNT(*) as total FROM tb_siswa";
                    $result_total = mysqli_query($koneksi, $query_total);
                    $data_total = mysqli_fetch_assoc($result_total);
                    $total_siswa = $data_total['total'];
                    echo "Total: <strong>" . $total_siswa . "</strong> Siswa";
                    ?>
                </span>
            </h3>
            <a href="?page=MyApp/add_siswa" title="Tambah Data" class="btn btn-primary">
                <i class="glyphicon glyphicon-plus"></i> Tambah Data</a>
            <button type="button" id="btnEditTerpilih" class="btn btn-success" onclick="editTerpilih()" disabled>
                <i class="glyphicon glyphicon-edit"></i> Edit Terpilih
            </button>
            <button type="button" id="btnHapusTerpilih" class="btn btn-danger" onclick="hapusTerpilih()" disabled>
                <i class="glyphicon glyphicon-trash"></i> Hapus Terpilih
            </button>
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal-impor">
                <i class="glyphicon glyphicon-upload"></i> Impor Data Siswa
            </button>
            <div class="btn-group">
                <a href="admin/export_handler.php?type=excel&table=siswa" class="btn btn-info" title="Ekspor ke Excel">
                    <i class="fa fa-file-excel-o"></i> Excel
                </a>
                <a href="admin/export_handler.php?type=pdf&table=siswa" class="btn btn-danger" title="Ekspor ke PDF" target="_blank">
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
            <!-- Filter Form -->
            <div class="row" style="margin-bottom: 15px;">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading" style="background-color: #f4f4f4; padding: 10px;">
                            <h4 class="panel-title" style="margin: 0;">
                                <i class="fa fa-filter"></i> Filter Data
                            </h4>
                        </div>
                        <div class="panel-body" style="padding: 15px;">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Filter NIS:</label>
                                        <input type="text" id="filterNIS" class="form-control" placeholder="Cari berdasarkan NIS...">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Filter Nama:</label>
                                        <input type="text" id="filterNama" class="form-control" placeholder="Cari berdasarkan Nama...">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Filter Tahun Masuk:</label>
                                        <select id="filterThMasuk" class="form-control">
                                            <option value="">-- Semua Tahun --</option>
                                            <?php
                                            // Ambil daftar tahun masuk yang unik dari database
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
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="button" id="btnCariFilter" class="btn btn-primary" onclick="executeFilterSiswa(); return false;">
                                        <i class="fa fa-search"></i> Cari
                                    </button>
                                    <button type="button" id="btnResetFilter" class="btn btn-warning" onclick="resetFilterSiswa(); return false;">
                                        <i class="fa fa-refresh"></i> Reset Filter
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <form id="formSiswa" method="post" action="">
                    <table id="example1" class="table table-bordered table-striped">
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
                        <tbody>

                            <?php
                      $no = 1;
                      $sql = $koneksi->query("SELECT s.nis, s.nama_siswa, s.jekel, s.status, s.th_masuk, s.id_kelas, k.kelas 
                      from tb_siswa s inner join tb_kelas k on s.id_kelas=k.id_kelas 
                      order by kelas asc, nis asc");
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
                                <span class="label label-primary">Aktif</span>
                                <?php } elseif ($warna == 'Lulus') { ?>
                                <span class="label label-success">Lulus</span>
                                <?php } elseif ($warna == 'Pindah') { ?>
                                <span class="label label-danger">Pindah</span>
                            </td>
                            <?php } ?>

                            <td>
                                <?php echo $data['th_masuk']; ?>
                            </td>

                            <td>
                                <a href="?page=MyApp/edit_siswa&kode=<?php echo $data['nis']; ?>" title="Ubah"
                                    class="btn btn-success">
                                    <i class="glyphicon glyphicon-edit"></i>
                                </a>
                                <a href="?page=MyApp/del_siswa&kode=<?php echo $data['nis']; ?>"
                                    onclick="return confirmHapus(event, 'Yakin hapus data siswa <?php echo htmlspecialchars($data['nama_siswa']); ?>?')" title="Hapus"
                                    class="btn btn-danger">
                                    <i class="glyphicon glyphicon-trash"></i>
                                    <a />
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
                    <i class="glyphicon glyphicon-edit"></i> Edit Multiple Siswa
                    <span id="countData" style="font-size: 14px; font-weight: normal;"></span>
                </h4>
            </div>
            <form id="formEditMultiple" method="post" action="?page=MyApp/edit_siswa_multiple">
                <div class="modal-body" style="max-height: 500px; overflow-y: auto;">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>NIS</th>
                                    <th>Nama</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Kelas</th>
                                    <th>Tahun Masuk</th>
                                    <th>Status</th>
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
    var filterThMasuk = $('#filterThMasuk').val() ? $('#filterThMasuk').val().trim() : '';
    
    // Jika menggunakan DataTable, gunakan DataTable API untuk filter
    if (typeof $.fn.DataTable !== 'undefined' && $.fn.DataTable.isDataTable('#example1')) {
        var table = $('#example1').DataTable();
        
        // Apply filter ke setiap kolom menggunakan DataTable API
        // Kolom index: 0=checkbox, 1=No, 2=NIS, 3=Nama, 4=Laki-laki, 5=Perempuan, 6=Kelas, 7=Status, 8=Th Masuk, 9=Aksi
        
        // Filter NIS dan Nama menggunakan search biasa (partial match)
        table.column(2).search(filterNIS);
        table.column(3).search(filterNama);
        
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
                    class: 'form-control',
                    value: nis,
                    readonly: true
                })))
                .append($('<td>').append($('<input>', {
                    type: 'text',
                    class: 'form-control',
                    name: 'nama_siswa[]',
                    value: nama,
                    required: true
                })))
                .append($('<td>').append($('<select>', {
                    class: 'form-control',
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
                .append($('<td>').append(selectKelas))
                .append($('<td>').append($('<input>', {
                    type: 'number',
                    class: 'form-control',
                    name: 'th_masuk[]',
                    value: th_masuk,
                    required: true,
                    min: '1900',
                    max: '2099'
                })))
                .append($('<td>').append($('<select>', {
                    class: 'form-control',
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
    
    $('#modalEditMultiple').modal('show');
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
            confirmButtonColor: '#f39c12'
        });
        return;
    }
    
    Swal.fire({
        title: '<i class="fa fa-exclamation-triangle" style="color: #f39c12; font-size: 48px;"></i>',
        html: '<div style="text-align: center; padding: 10px;">' +
              '<h3 style="color: #d33; margin-bottom: 20px; font-weight: bold;">Konfirmasi Hapus</h3>' +
              '<p style="font-size: 16px; margin-bottom: 20px; color: #495057;">Yakin hapus ' + checked + ' siswa terpilih?</p>' +
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

<div class="modal fade" id="modal-impor">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Upload Data Siswa</h4>
            </div>
            <form method="POST" enctype="multipart/form-data" action="">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Pilih File Excel</label>
                        <a href="../../inc/generate_template_siswa.php" class="btn btn-success btn-sm pull-right" target="_blank">
                            <i class="glyphicon glyphicon-download"></i> Download Template
                        </a>
                        <input type="file" name="file_excel" class="form-control" accept=".xls,.xlsx,.csv" required>
                        <small class="help-block">Format: Excel (.xls, .xlsx) atau CSV</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" name="simpan" class="btn btn-primary">
                        <i class="glyphicon glyphicon-upload"></i> Upload
                    </button>
                </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

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


