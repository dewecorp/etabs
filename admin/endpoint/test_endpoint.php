<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION["ses_username"]) || $_SESSION["ses_level"] !== "Administrator") {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../inc/koneksi.php';

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$baseUrl = isset($_POST['base_url']) ? trim($_POST['base_url']) : '';
$apiKey = isset($_POST['api_key']) ? trim($_POST['api_key']) : '';

if ($id > 0) {
    $stmt = $koneksi->prepare("SELECT id, kode_app, base_url, api_key FROM tb_endpoint_masuk WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $baseUrl = $row['base_url'];
        $apiKey = $row['api_key'];
    }
    $stmt->close();
}

if (empty($baseUrl)) {
    $statusText = "GAGAL Base URL kosong.";
    if ($id > 0) {
        $now = date('Y-m-d H:i:s');
        $up = $koneksi->prepare("UPDATE tb_endpoint_masuk SET last_test_status = ?, last_test_time = ? WHERE id = ?");
        $up->bind_param("ssi", $statusText, $now, $id);
        $up->execute();
        $up->close();
    }
    echo json_encode([
        'success' => false,
        'status' => 'GAGAL',
        'message' => 'Base URL kosong.',
        'formatted' => $statusText,
        'last_test_time' => date('Y-m-d H:i:s')
    ]);
    exit;
}

// Perform cURL request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

if (!empty($apiKey)) {
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-API-KEY: ' . $apiKey,
        'Authorization: Bearer ' . $apiKey
    ]);
}

$startTime = microtime(true);
$response = curl_exec($ch);
$endTime = microtime(true);

$latency = round(($endTime - $startTime) * 1000);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr = curl_error($ch);
curl_close($ch);

$nowFormatted = date('Y-m-d H:i:s');
$isSuccess = false;
$statusText = '';

if ($curlErr) {
    $statusText = "GAGAL (" . substr($curlErr, 0, 40) . ")";
} else if ($httpCode >= 200 && $httpCode < 400) {
    $isSuccess = true;
    $statusText = "OK HTTP " . $httpCode . " OK " . $latency . "ms";
} else {
    $statusText = "GAGAL HTTP " . $httpCode . " (" . $latency . "ms)";
}

if ($id > 0) {
    $up = $koneksi->prepare("UPDATE tb_endpoint_masuk SET last_test_status = ?, last_test_time = ? WHERE id = ?");
    $up->bind_param("ssi", $statusText, $nowFormatted, $id);
    $up->execute();
    $up->close();
}

echo json_encode([
    'success' => $isSuccess,
    'status' => $isSuccess ? 'OK' : 'GAGAL',
    'http_code' => $httpCode,
    'latency_ms' => $latency,
    'formatted' => $statusText,
    'last_test_time' => $nowFormatted
]);
