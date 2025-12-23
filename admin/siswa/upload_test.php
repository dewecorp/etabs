<?php
// Simple test handler untuk upload
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$response = [
    'success' => true,
    'message' => 'Test handler berhasil diakses',
    'method' => $_SERVER['REQUEST_METHOD'],
    'has_file' => isset($_FILES['file_excel']),
    'file_info' => isset($_FILES['file_excel']) ? [
        'name' => $_FILES['file_excel']['name'],
        'size' => $_FILES['file_excel']['size'],
        'error' => $_FILES['file_excel']['error'],
        'type' => $_FILES['file_excel']['type']
    ] : null,
    'timestamp' => date('Y-m-d H:i:s'),
    'path' => __FILE__
];

echo json_encode($response, JSON_PRETTY_PRINT);
?>

