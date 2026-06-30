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
$idTabungan = trim($_POST['id_tabungan'] ?? $_GET['id_tabungan'] ?? '');
$tanggalMulai = trim($_POST['tanggal_mulai'] ?? $_GET['tanggal_mulai'] ?? '');
$tanggalSampai = trim($_POST['tanggal_sampai'] ?? $_GET['tanggal_sampai'] ?? '');

function paymentAjaxGetKelasSiswa($koneksi, $nis)
{
    $nis = mysqli_real_escape_string($koneksi, $nis);
    $sql = "SELECT COALESCE(k.kelas, s.id_kelas, '') AS kelas FROM tb_siswa s LEFT JOIN tb_kelas k ON s.id_kelas = k.id_kelas WHERE s.nis = '$nis' LIMIT 1";
    $query = mysqli_query($koneksi, $sql);
    if ($query && ($row = mysqli_fetch_assoc($query))) {
        return (string) ($row['kelas'] ?? '');
    }

    return '';
}

switch ($action) {
    case 'jenis_bayar':
        if ($nis === '') {
            echo json_encode(['success' => false, 'message' => 'NIS siswa wajib diisi.']);
            exit;
        }
        $result = paymentGetJenisBayar($nis, paymentAjaxGetKelasSiswa($koneksi, $nis));
        if (!isset($result['data']) || count($result['data']) === 0) {
            $result['debug_file'] = 'tmp/spp_debug_' . preg_replace('/[^0-9A-Za-z_-]/', '_', $nis) . '.json';
        }
        echo json_encode($result);
        break;

    case 'jenis_detail':
        if ($nis === '' || $jenisId === '') {
            echo json_encode(['success' => false, 'message' => 'NIS dan jenis bayar wajib diisi.']);
            exit;
        }
        echo json_encode(paymentGetJenisDetail($nis, $jenisId, paymentAjaxGetKelasSiswa($koneksi, $nis)));
        break;

    case 'transaksi':
        echo json_encode(paymentGetTransaksi([
            'nisn' => $nis,
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_sampai' => $tanggalSampai,
        ]));
        break;

    case 'resync_tarik':
        if ($idTabungan === '') {
            echo json_encode(['success' => false, 'message' => 'ID penarikan wajib diisi.']);
            exit;
        }
        echo json_encode(paymentResyncTarik($koneksi, $idTabungan, $_SESSION['ses_nama'] ?? $_SESSION['ses_username'] ?? ''));
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Aksi tidak dikenali.']);
}
