<?php
/**
 * API Endpoint for Sibayar / External Payment Integration
 * Endpoint: /api/payment.php
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, X-API-KEY, Authorization');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../inc/koneksi.php';
require_once __DIR__ . '/../inc/payment_api_config.php';

// API Key Validation
$headers = getallheaders();
$apiKey = '';

foreach ($headers as $key => $value) {
    if (strtolower($key) === 'x-api-key') {
        $apiKey = $value;
        break;
    }
}

if (empty($apiKey) && isset($_GET['api_key'])) {
    $apiKey = $_GET['api_key'];
}
if (empty($apiKey) && isset($_POST['api_key'])) {
    $apiKey = $_POST['api_key'];
}

$validKeys = [
    defined('SIMAD_API_KEY') ? SIMAD_API_KEY : 'SIMAD_SECRET_KEY_2026',
    defined('PAYMENT_API_KEY') ? PAYMENT_API_KEY : 'SPP_SECRET_KEY_2026',
    'SIMAD_SECRET_KEY_2026',
    'SPP_SECRET_KEY_2026'
];

$action = $_GET['action'] ?? $_POST['action'] ?? 'ping';

// Health Check / Ping Test Response (HTTP 200 OK)
if ($action === 'ping' || empty($action) || (empty($apiKey) && $_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['nis']))) {
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'status' => 'OK',
        'message' => 'ETABS Payment API Service is active',
        'app' => 'e-Tabs',
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Validate API Key for data operations
if (empty($apiKey) || !in_array($apiKey, $validKeys)) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid API Key'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

$nis = $_GET['nis'] ?? $_POST['nis'] ?? '';
$nis = mysqli_real_escape_string($koneksi, $nis);

// Action 1: Cek Saldo Tabungan
if ($action === 'tabungan' || $action === 'cek_saldo') {
    if (empty($nis)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Parameter NIS wajib diisi']);
        exit;
    }

    $sql_siswa = "SELECT nis, nama_siswa FROM tb_siswa WHERE nis='$nis'";
    $res_siswa = $koneksi->query($sql_siswa);

    if (!$res_siswa || $res_siswa->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Siswa tidak ditemukan']);
        exit;
    }

    $siswa = $res_siswa->fetch_assoc();

    $res_setor = $koneksi->query("SELECT SUM(setor) as total FROM tb_tabungan WHERE nis='$nis' AND jenis='ST'");
    $total_setor = (int)($res_setor->fetch_assoc()['total'] ?? 0);

    $res_tarik = $koneksi->query("SELECT SUM(tarik) as total FROM tb_tabungan WHERE nis='$nis' AND jenis='TR'");
    $total_tarik = (int)($res_tarik->fetch_assoc()['total'] ?? 0);

    $saldo = $total_setor - $total_tarik;

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => [
            'nis' => $siswa['nis'],
            'nama_siswa' => $siswa['nama_siswa'],
            'total_setor' => $total_setor,
            'total_tarik' => $total_tarik,
            'saldo' => $saldo
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Action 2: Potong Saldo Tabungan untuk Pembayaran (Sibayar)
if ($action === 'potong_tabungan' || $action === 'bayar' || $action === 'tarik') {
    $nominal = intval($_POST['nominal'] ?? $_GET['nominal'] ?? 0);
    $keterangan = trim($_POST['keterangan'] ?? $_GET['keterangan'] ?? 'Pembayaran Tagihan Sibayar');
    $petugas = trim($_POST['petugas'] ?? $_GET['petugas'] ?? 'API Sibayar');

    if (empty($nis) || $nominal <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Parameter nis dan nominal valid wajib diisi']);
        exit;
    }

    // Cek saldo
    $res_setor = $koneksi->query("SELECT SUM(setor) as total FROM tb_tabungan WHERE nis='$nis' AND jenis='ST'");
    $total_setor = (int)($res_setor->fetch_assoc()['total'] ?? 0);

    $res_tarik = $koneksi->query("SELECT SUM(tarik) as total FROM tb_tabungan WHERE nis='$nis' AND jenis='TR'");
    $total_tarik = (int)($res_tarik->fetch_assoc()['total'] ?? 0);

    $saldo = $total_setor - $total_tarik;

    if ($saldo < $nominal) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Saldo tabungan tidak mencukupi',
            'saldo' => $saldo,
            'nominal_dibutuhkan' => $nominal
        ]);
        exit;
    }

    // Process Tarikan
    $tgl = date('Y-m-d');
    $stmt = $koneksi->prepare("INSERT INTO tb_tabungan (nis, setor, tarik, tgl, jenis, petugas) VALUES (?, 0, ?, ?, 'TR', ?)");
    $stmt->bind_param("siss", $nis, $nominal, $tgl, $petugas);

    if ($stmt->execute()) {
        $id_tabungan = $stmt->insert_id;
        $sisa_saldo = $saldo - $nominal;

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Penarikan tabungan untuk pembayaran berhasil',
            'data' => [
                'id_tabungan' => $id_tabungan,
                'nis' => $nis,
                'nominal' => $nominal,
                'sisa_saldo' => $sisa_saldo,
                'tanggal' => $tgl,
                'petugas' => $petugas
            ]
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Gagal mencatat transaksi tabungan']);
    }
    $stmt->close();
    exit;
}

// Fallback response for ping or unknown actions
http_response_code(200);
echo json_encode([
    'success' => true,
    'status' => 'OK',
    'message' => 'API Sibayar ETABS aktif'
]);
