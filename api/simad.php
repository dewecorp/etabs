<?php
/**
 * API Endpoint for SIMAD Integration
 * Endpoint: /api/simad.php
 * 
 * Authentication: API Key via X-API-KEY header
 * 
 * Available Endpoints:
 * 1. GET /api/simad.php?action=tabungan&nis=12345 - Get student savings summary
 * 2. GET /api/simad.php?action=riwayat&nis=12345 - Get student transaction history
 */

// Set JSON response header
header('Content-Type: application/json; charset=utf-8');

// Load configuration and database connection
require_once __DIR__ . '/../inc/koneksi.php';
require_once __DIR__ . '/../inc/payment_api_config.php';

// Check if SIMAD API is enabled
if (!defined('SIMAD_API_ENABLED') || !SIMAD_API_ENABLED) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(503);
    echo json_encode([
        'success' => false,
        'message' => 'SIMAD API is disabled'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// API Configuration from constants
$SIMAD_API_KEY = SIMAD_API_KEY;
$SIMAD_API_KEY_HEADER = SIMAD_API_KEY_HEADER;

// --- Utility Functions ---

/**
 * Send JSON response
 */
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Get error response
 */
function sendErrorResponse($message, $statusCode = 400) {
    sendJsonResponse([
        'success' => false,
        'message' => $message
    ], $statusCode);
}

// --- Authentication ---

// Get API Key from headers
$headers = getallheaders();
$apiKey = '';

// Check for API key in various header formats (case-insensitive)
foreach ($headers as $key => $value) {
    if (strtolower($key) === strtolower($SIMAD_API_KEY_HEADER)) {
        $apiKey = $value;
        break;
    }
}

// Also check GET parameter as fallback
if (empty($apiKey) && isset($_GET['api_key'])) {
    $apiKey = $_GET['api_key'];
}

// Validate API Key
if (empty($apiKey) || $apiKey !== $SIMAD_API_KEY) {
    sendErrorResponse('Invalid API Key', 401);
}

// --- Request Handling ---

$action = $_GET['action'] ?? '';
$nis = $_GET['nis'] ?? '';

if (empty($action)) {
    sendErrorResponse('Action parameter is required');
}

if (empty($nis)) {
    sendErrorResponse('NIS parameter is required');
}

// Sanitize inputs
$nis = mysqli_real_escape_string($koneksi, $nis);

// --- Endpoint 1: Get Tabungan Summary ---
if ($action === 'tabungan') {
    // Get student info
    $sql_siswa = "SELECT nis, nama_siswa FROM tb_siswa WHERE nis='$nis'";
    $result_siswa = $koneksi->query($sql_siswa);
    
    if (!$result_siswa || $result_siswa->num_rows === 0) {
        sendErrorResponse('Student not found');
    }
    
    $siswa = $result_siswa->fetch_assoc();
    
    // Get total deposits (setoran)
    $sql_setor = "SELECT SUM(setor) as total_setor FROM tb_tabungan WHERE nis='$nis' AND jenis='ST'";
    $result_setor = $koneksi->query($sql_setor);
    $data_setor = $result_setor->fetch_assoc();
    $total_setor = (int)$data_setor['total_setor'];
    
    // Get total withdrawals (tarikan)
    $sql_tarik = "SELECT SUM(tarik) as total_tarik FROM tb_tabungan WHERE nis='$nis' AND jenis='TR'";
    $result_tarik = $koneksi->query($sql_tarik);
    $data_tarik = $result_tarik->fetch_assoc();
    $total_tarik = (int)$data_tarik['total_tarik'];
    
    // Calculate balance (saldo)
    $saldo = $total_setor - $total_tarik;
    
    sendJsonResponse([
        'success' => true,
        'data' => [
            'nis' => $siswa['nis'],
            'nama_siswa' => $siswa['nama_siswa'],
            'total_setor' => $total_setor,
            'total_tarik' => $total_tarik,
            'saldo' => $saldo
        ]
    ]);
}

// --- Endpoint 2: Get Riwayat Transaksi ---
elseif ($action === 'riwayat') {
    // Get student info
    $sql_siswa = "SELECT nis, nama_siswa FROM tb_siswa WHERE nis='$nis'";
    $result_siswa = $koneksi->query($sql_siswa);
    
    if (!$result_siswa || $result_siswa->num_rows === 0) {
        sendErrorResponse('Student not found');
    }
    
    $siswa = $result_siswa->fetch_assoc();
    
    // Optional pagination
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    $offset = ($page - 1) * $limit;
    
    // Get transaction history
    $sql_riwayat = "SELECT 
        id_tabungan, 
        tgl, 
        setor, 
        tarik, 
        jenis, 
        petugas 
    FROM tb_tabungan 
    WHERE nis='$nis' 
    ORDER BY tgl DESC, id_tabungan DESC 
    LIMIT $limit OFFSET $offset";
    
    $result_riwayat = $koneksi->query($sql_riwayat);
    
    $transactions = [];
    while ($row = $result_riwayat->fetch_assoc()) {
        $transactions[] = [
            'id' => (int)$row['id_tabungan'],
            'tanggal' => $row['tgl'],
            'jenis' => $row['jenis'] === 'ST' ? 'setoran' : 'tarikan',
            'nominal' => (int)($row['jenis'] === 'ST' ? $row['setor'] : $row['tarik']),
            'petugas' => $row['petugas']
        ];
    }
    
    // Get total count for pagination
    $sql_count = "SELECT COUNT(*) as total FROM tb_tabungan WHERE nis='$nis'";
    $result_count = $koneksi->query($sql_count);
    $count_data = $result_count->fetch_assoc();
    $total_transactions = (int)$count_data['total'];
    $total_pages = ceil($total_transactions / $limit);
    
    sendJsonResponse([
        'success' => true,
        'data' => [
            'nis' => $siswa['nis'],
            'nama_siswa' => $siswa['nama_siswa'],
            'transaksi' => $transactions,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total_transaksi' => $total_transactions,
                'total_halaman' => $total_pages
            ]
        ]
    ]);
}

// --- Invalid Action ---
else {
    sendErrorResponse('Invalid action. Available actions: tabungan, riwayat');
}
