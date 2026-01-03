<?php
session_start();

if (!isset($_SESSION["ses_username"])) {
    echo json_encode(['success' => false, 'message' => 'Session expired']);
    exit;
}

// Load vendor autoload
$vendor_path = dirname(dirname(__DIR__)) . '/vendor/autoload.php';
if (!file_exists($vendor_path)) {
    echo json_encode(['success' => false, 'message' => 'Vendor autoload tidak ditemukan: ' . $vendor_path]);
    exit;
}
require_once $vendor_path;

// Load koneksi database
$koneksi_path = dirname(dirname(__DIR__)) . '/inc/koneksi.php';
if (!file_exists($koneksi_path)) {
    echo json_encode(['success' => false, 'message' => 'File koneksi tidak ditemukan: ' . $koneksi_path]);
    exit;
}
include $koneksi_path;

// Check database connection
if (!isset($koneksi) || !$koneksi) {
    echo json_encode(['success' => false, 'message' => 'Koneksi database tidak tersedia']);
    exit;
}

use PhpOffice\PhpSpreadsheet\IOFactory;

// Set headers untuk CORS dan JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_FILES['file_excel'])) {
    echo json_encode(['success' => false, 'message' => 'File tidak ditemukan']);
    exit;
}

if ($_FILES['file_excel']['error'] !== UPLOAD_ERR_OK) {
    $error_messages = [
        UPLOAD_ERR_INI_SIZE => 'File terlalu besar (melebihi upload_max_filesize)',
        UPLOAD_ERR_FORM_SIZE => 'File terlalu besar (melebihi MAX_FILE_SIZE)',
        UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian',
        UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diupload',
        UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan',
        UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk',
        UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh extension PHP'
    ];
    $error_msg = isset($error_messages[$_FILES['file_excel']['error']]) 
        ? $error_messages[$_FILES['file_excel']['error']] 
        : 'Unknown error: ' . $_FILES['file_excel']['error'];
    echo json_encode(['success' => false, 'message' => 'File upload error: ' . $error_msg]);
    exit;
}

$file_tmp = $_FILES['file_excel']['tmp_name'];
$file_name = $_FILES['file_excel']['name'];
$file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

// Validasi ekstensi file
$allowed_ext = ['xls', 'xlsx', 'csv'];
if (!in_array($file_ext, $allowed_ext)) {
    echo json_encode(['success' => false, 'message' => 'Format file tidak valid. Hanya file Excel (.xls, .xlsx) atau CSV yang diperbolehkan']);
    exit;
}

try {
    // Load file Excel
    $spreadsheet = IOFactory::load($file_tmp);
    
    // Skip header row (baris pertama)
    $success_count = 0;
    $error_count = 0;
    $duplicate_count = 0;
    $error_messages = [];
    
    // Proses semua sheet (multi-tab support)
    $sheet_count = $spreadsheet->getSheetCount();
    
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
                    // Cek jika error karena duplicate
                    if (mysqli_errno($koneksi) == 1062) {
                        $duplicate_count++;
                    } else {
                        $error_count++;
                        $error_messages[] = "Sheet '$sheet_name' Baris " . ($i + 1) . ": Gagal insert data NIS $nis - " . mysqli_error($koneksi);
                    }
                }
            }
        }
    }
    
    // Response
    $response = [
        'success' => true,
        'message' => 'Import selesai',
        'data' => [
            'file_name' => $file_name,
            'file_size' => number_format($_FILES['file_excel']['size'] / 1024 / 1024, 2) . ' MB',
            'success' => $success_count,
            'error' => $error_count,
            'duplicate' => $duplicate_count,
            'errors' => array_slice($error_messages, 0, 10) // Limit error messages
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    $errorResponse = [
        'success' => false, 
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ];
    
    echo json_encode($errorResponse);
}
?>

