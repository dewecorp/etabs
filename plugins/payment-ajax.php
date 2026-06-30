<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['ses_username'])) {
    echo json_encode(['success' => false, 'message' => 'Sesi habis. Silakan login kembali.']);
    exit;
}

include dirname(__DIR__) . '/inc/koneksi.php';
include dirname(__DIR__) . '/inc/payment_integration.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$nis = trim($_POST['nis'] ?? $_GET['nis'] ?? '');
$jenisId = trim($_POST['jenis_id'] ?? $_GET['jenis_id'] ?? '');

switch ($action) {
    case 'jenis_bayar':
        if ($nis === '') {
            echo json_encode(['success' => false, 'message' => 'NIS siswa wajib diisi.']);
            exit;
        }
        echo json_encode(paymentGetJenisBayar($nis));
        break;

    case 'jenis_detail':
        if ($nis === '' || $jenisId === '') {
            echo json_encode(['success' => false, 'message' => 'NIS dan jenis bayar wajib diisi.']);
            exit;
        }
        echo json_encode(paymentGetJenisDetail($nis, $jenisId));
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Aksi tidak dikenali.']);
}
