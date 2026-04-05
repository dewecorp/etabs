<?php
session_start();

if (!isset($_SESSION["ses_username"])) {
    echo json_encode(['success' => false, 'message' => 'Session expired']);
    exit;
}

// Load koneksi database
$root_path = dirname(dirname(__DIR__));
$koneksi_path = $root_path . '/inc/koneksi.php';
$log_path = $root_path . '/inc/activity_log.php';

if (!file_exists($koneksi_path)) {
    echo json_encode(['success' => false, 'message' => 'File koneksi tidak ditemukan: ' . $koneksi_path]);
    exit;
}
include $koneksi_path;

if (file_exists($log_path)) {
    include_once $log_path;
}

// Check database connection
if (!isset($koneksi) || !$koneksi) {
    echo json_encode(['success' => false, 'message' => 'Koneksi database tidak tersedia']);
    exit;
}

// Set headers untuk JSON
header('Content-Type: application/json; charset=utf-8');

// API Endpoint Simad
$apiUrl = "https://simad.misultanfattah.sch.id/api/v1/students?api_key=SIS_CENTRAL_HUB_SECRET_2026";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$response = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if ($err) {
    echo json_encode(['success' => false, 'message' => 'Gagal menghubungi API Simad: ' . $err]);
    exit;
}

$result = json_decode($response, true);

if (!isset($result['status']) || $result['status'] !== 'success') {
    echo json_encode(['success' => false, 'message' => 'API Simad mengembalikan status gagal atau format tidak valid.']);
    exit;
}

$dataSiswa = $result['data'];
$success_count = 0;
$update_count = 0;
$error_count = 0;
$error_messages = [];

// Cache kelas untuk mempercepat pencarian
$kelas_map = [];
$res_kelas = mysqli_query($koneksi, "SELECT id_kelas, kelas FROM tb_kelas");
while ($row_kelas = mysqli_fetch_assoc($res_kelas)) {
    $clean_kelas = strtolower(trim($row_kelas['kelas']));
    $kelas_map[$clean_kelas] = $row_kelas['id_kelas'];
    
    // Support mapping "Kelas 1" -> "1" atau "I"
    $only_name = str_replace('kelas', '', $clean_kelas);
    $only_name = trim($only_name);
    if (!empty($only_name)) {
        $kelas_map[$only_name] = $row_kelas['id_kelas'];
    }
}

// Tambahan mapping angka ke romawi jika diperlukan
$roman_map = [
    '1' => 'i', '2' => 'ii', '3' => 'iii', '4' => 'iv', '5' => 'v', '6' => 'vi',
    'i' => '1', 'ii' => '2', 'iii' => '3', 'iv' => '4', 'v' => '5', 'vi' => '6'
];

foreach ($dataSiswa as $siswa) {
    $nama = mysqli_real_escape_string($koneksi, trim($siswa['nama_siswa']));
    $nis = mysqli_real_escape_string($koneksi, trim($siswa['nisn'])); // Menggunakan NISN sebagai NIS
    $jekel_input = strtoupper(trim($siswa['jenis_kelamin']));
    $nama_kelas_ori = strtolower(trim($siswa['nama_kelas']));
    
    // Normalisasi nama kelas dari Simad
    $nama_kelas = str_replace('kelas', '', $nama_kelas_ori);
    $nama_kelas = trim($nama_kelas);
    
    // Default values if missing from API
    $status = 'Aktif';
    $th_masuk = date('Y');

    // Map Jenis Kelamin
    if (in_array($jekel_input, ['L', 'LK', 'LAKI-LAKI'])) {
        $jekel = 'LK';
    } elseif (in_array($jekel_input, ['P', 'PR', 'PEREMPUAN'])) {
        $jekel = 'PR';
    } else {
        $jekel = 'LK'; 
    }

    // Cari id_kelas dengan berbagai variasi
    $id_kelas = null;
    if (isset($kelas_map[$nama_kelas])) {
        $id_kelas = $kelas_map[$nama_kelas];
    } elseif (isset($kelas_map[$nama_kelas_ori])) {
        $id_kelas = $kelas_map[$nama_kelas_ori];
    } elseif (isset($roman_map[$nama_kelas]) && isset($kelas_map[$roman_map[$nama_kelas]])) {
        $id_kelas = $kelas_map[$roman_map[$nama_kelas]];
    }

    if (!$id_kelas) {
        $error_count++;
        $error_messages[] = "Siswa $nama (NIS: $nis): Kelas '$nama_kelas_ori' tidak ditemukan di database lokal.";
        continue;
    }

    // Cek apakah NIS sudah ada
    $cek_nis = "SELECT nis FROM tb_siswa WHERE nis = '$nis'";
    $result_cek = mysqli_query($koneksi, $cek_nis);

    if (mysqli_num_rows($result_cek) > 0) {
        // Update data jika sudah ada
        $sql_update = "UPDATE tb_siswa SET 
            nama_siswa = '$nama',
            jekel = '$jekel',
            id_kelas = '$id_kelas',
            status = '$status'
            WHERE nis = '$nis'";
        $query_update = mysqli_query($koneksi, $sql_update);
        
        if ($query_update) {
            $update_count++;
            $success_count++;
        } else {
            $error_count++;
            $error_messages[] = "Gagal update data NIS $nis: " . mysqli_error($koneksi);
        }
    } else {
        // Insert data baru
        $sql_insert = "INSERT INTO tb_siswa (nis, nama_siswa, jekel, id_kelas, status, th_masuk) 
            VALUES ('$nis', '$nama', '$jekel', '$id_kelas', '$status', '$th_masuk')";
        $query_insert = mysqli_query($koneksi, $sql_insert);
        
        if ($query_insert) {
            $success_count++;
        } else {
            $error_count++;
            $error_messages[] = "Gagal insert data NIS $nis: " . mysqli_error($koneksi);
        }
    }
}

// Log aktivitas jika memungkinkan
if ($success_count > 0 && function_exists('logActivity')) {
    logActivity($koneksi, 'SYNC', 'tb_siswa', "Sinkronisasi Simad: $success_count berhasil ($update_count update), $error_count gagal", null);
}

$msg = "Sinkronisasi selesai. $success_count data berhasil disinkronkan ($update_count data diperbarui).";
if ($error_count > 0) {
    $msg .= " Terdapat $error_count data yang dilewati karena kelas tidak cocok.";
}

echo json_encode([
    'success' => $success_count > 0 || $error_count == 0,
    'message' => $msg,
    'details' => [
        'success' => $success_count,
        'update' => $update_count,
        'error' => $error_count,
        'errors' => array_slice($error_messages, 0, 20)
    ]
]);
