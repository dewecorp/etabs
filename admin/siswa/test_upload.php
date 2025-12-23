<?php
// Test endpoint untuk memverifikasi path upload
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

$response = [
    'success' => true,
    'message' => 'Test endpoint berhasil diakses',
    'path' => __FILE__,
    'real_path' => realpath(__FILE__),
    'session' => isset($_SESSION["ses_username"]) ? 'Active' : 'Expired',
    'method' => $_SERVER['REQUEST_METHOD'],
    'request_uri' => $_SERVER['REQUEST_URI'],
    'script_name' => $_SERVER['SCRIPT_NAME'],
    'document_root' => $_SERVER['DOCUMENT_ROOT'],
    'timestamp' => date('Y-m-d H:i:s')
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file_excel'])) {
    $response['file_received'] = true;
    $response['file_name'] = $_FILES['file_excel']['name'];
    $response['file_size'] = $_FILES['file_excel']['size'];
    $response['file_error'] = $_FILES['file_excel']['error'];
} else {
    $response['file_received'] = false;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $response['post_data'] = $_POST;
        $response['files_data'] = $_FILES;
    }
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>

