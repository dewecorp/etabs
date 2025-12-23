<?php
/**
 * Handler untuk ekspor data ke Excel dan PDF
 */

// Turn off error reporting untuk menghindari output sebelum header
error_reporting(0);
ini_set('display_errors', 0);

// Clean output buffer untuk memastikan tidak ada output sebelum header
while (ob_get_level()) {
    ob_end_clean();
}

// Start output buffering
ob_start();

// Start session dengan error suppression untuk menghindari notice jika sudah started
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}
if (!isset($_SESSION["ses_username"])) {
    ob_end_clean();
    header("location: ../login.php");
    exit;
}

include dirname(__DIR__) . '/inc/koneksi.php';

// Clean buffer lagi setelah include
ob_end_clean();

// Include export functions - hanya include yang diperlukan
$type = isset($_GET['type']) ? $_GET['type'] : 'excel';
if ($type === 'excel') {
    require_once dirname(__DIR__) . '/inc/export_excel.php';
} else {
    require_once dirname(__DIR__) . '/inc/export_pdf.php';
}

// $type sudah didefinisikan di atas
$table = isset($_GET['table']) ? $_GET['table'] : '';

if (empty($table)) {
    die('Table parameter is required');
}

// Mapping table ke query dan headers
$table_config = [
    'siswa' => [
        'title' => 'Data Siswa',
        'query' => "SELECT s.nis, s.nama_siswa, s.jekel, k.kelas, s.status, s.th_masuk 
                    FROM tb_siswa s 
                    INNER JOIN tb_kelas k ON s.id_kelas=k.id_kelas 
                    ORDER BY kelas ASC, nis ASC",
        'headers' => ['No', 'NIS', 'Nama Siswa', 'Jenis Kelamin', 'Kelas', 'Status', 'Tahun Masuk']
    ],
    'kelas' => [
        'title' => 'Data Kelas',
        'query' => "SELECT * FROM tb_kelas ORDER BY kelas",
        'headers' => ['No', 'Kelas']
    ],
    'pengguna' => [
        'title' => 'Data Pengguna',
        'query' => "SELECT * FROM tb_pengguna ORDER BY nama_pengguna",
        'headers' => ['No', 'Nama', 'Username', 'Level']
    ],
    'profil' => [
        'title' => 'Data Profil Sekolah',
        'query' => "SELECT * FROM tb_profil",
        'headers' => ['No', 'Nama Sekolah', 'Alamat', 'Akreditasi']
    ],
    'setor' => [
        'title' => 'Data Setoran',
        'query' => "SELECT s.nis, s.nama_siswa, t.setor, t.tgl, t.petugas 
                    FROM tb_siswa s 
                    INNER JOIN tb_tabungan t ON s.nis=t.nis 
                    WHERE t.jenis='ST' 
                    ORDER BY t.tgl DESC, t.id_tabungan DESC",
        'headers' => ['No', 'NIS', 'Nama Siswa', 'Tanggal', 'Setoran', 'Petugas']
    ],
    'tarik' => [
        'title' => 'Data Tarikan',
        'query' => "SELECT s.nis, s.nama_siswa, t.tarik, t.tgl, t.petugas 
                    FROM tb_siswa s 
                    INNER JOIN tb_tabungan t ON s.nis=t.nis 
                    WHERE t.jenis='TR' 
                    ORDER BY t.tgl DESC, t.id_tabungan DESC",
        'headers' => ['No', 'NIS', 'Nama Siswa', 'Tanggal', 'Tarikan', 'Petugas']
    ]
];

if (!isset($table_config[$table])) {
    die('Invalid table name');
}

$config = $table_config[$table];
$result = mysqli_query($koneksi, $config['query']);

if (!$result) {
    die('Query error: ' . mysqli_error($koneksi));
}

// Prepare data
$data = [];
$no = 1;

while ($row = mysqli_fetch_assoc($result)) {
    $rowData = [$no++];
    
    // Handle different tables
    switch ($table) {
        case 'siswa':
            $rowData[] = $row['nis'];
            $rowData[] = $row['nama_siswa'];
            $rowData[] = $row['jekel'];
            $rowData[] = $row['kelas'];
            $rowData[] = $row['status'];
            $rowData[] = $row['th_masuk'];
            break;
            
        case 'kelas':
            $rowData[] = $row['kelas'];
            break;
            
        case 'pengguna':
            $rowData[] = $row['nama_pengguna'];
            $rowData[] = $row['username'];
            $rowData[] = $row['level'];
            break;
            
        case 'profil':
            $rowData[] = $row['nama_sekolah'];
            $rowData[] = $row['alamat'];
            $rowData[] = 'Akreditasi ' . $row['akreditasi'];
            break;
            
        case 'setor':
            $rowData[] = $row['nis'];
            $rowData[] = $row['nama_siswa'];
            $rowData[] = date('d/m/Y', strtotime($row['tgl']));
            $rowData[] = number_format($row['setor'], 0, ',', '.');
            $rowData[] = $row['petugas'];
            break;
            
        case 'tarik':
            $rowData[] = $row['nis'];
            $rowData[] = $row['nama_siswa'];
            $rowData[] = date('d/m/Y', strtotime($row['tgl']));
            $rowData[] = number_format($row['tarik'], 0, ',', '.');
            $rowData[] = $row['petugas'];
            break;
    }
    
    $data[] = $rowData;
}

// Generate filename
$filename = $config['title'] . '_' . date('Ymd_His') . ($type === 'excel' ? '.xlsx' : '.pdf');

// Ambil data profil sekolah untuk header PDF
$sql_profil = mysqli_query($koneksi, "SELECT * FROM tb_profil LIMIT 1");
$profil_data = mysqli_fetch_assoc($sql_profil);

// Export
if ($type === 'excel') {
    exportToExcel($config['title'], $config['headers'], $data, $filename);
} else {
    // Gunakan fungsi exportToPDF yang sudah ada dengan data profil
    exportToPDF($config['title'], $config['headers'], $data, $filename, $profil_data);
}

