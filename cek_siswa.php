<?php
// FILE DIAGNOSTIK SEMENTARA - HAPUS SETELAH SELESAI
session_start();
if (!isset($_SESSION['ses_username'])) { die('Login dulu lewat aplikasi, lalu buka file ini lagi.'); }
include "inc/koneksi.php";

header('Content-Type: text/plain; charset=utf-8');
echo "PHP: " . PHP_VERSION . "\n";
echo "DB: " . $koneksi->server_info . "\n";
echo "Database: " . $db_name . "\n";
echo str_repeat('-', 50) . "\n";

$r = @$koneksi->query("SELECT COUNT(*) c FROM tb_siswa");
echo "Total tb_siswa: " . ($r ? $r->fetch_assoc()['c'] : 'ERR: ' . $koneksi->error) . "\n";

$r = @$koneksi->query("SHOW COLUMNS FROM tb_siswa LIKE 'status'");
echo "Kolom status ada: " . ($r && $r->num_rows ? 'YA' : 'TIDAK') . "\n";

if ($r && $r->num_rows) {
    $r = @$koneksi->query("SELECT status, COUNT(*) c FROM tb_siswa GROUP BY status");
    while ($row = $r->fetch_assoc()) { echo "  status='{$row['status']}': {$row['c']}\n"; }
}

$r = @$koneksi->query("SELECT COUNT(*) c FROM tb_kelas");
echo "Total tb_kelas: " . ($r ? $r->fetch_assoc()['c'] : 'ERR: ' . $koneksi->error) . "\n";
echo str_repeat('-', 50) . "\n";

$q = "select s.*, k.kelas from tb_siswa s left join tb_kelas k on s.id_kelas=k.id_kelas ORDER BY k.kelas ASC, s.nama_siswa ASC";
$hasil = @mysqli_query($koneksi, $q);
echo "Query dropdown gagal: " . ($hasil ? 'TIDAK' : 'YA -> ' . mysqli_error($koneksi)) . "\n";
echo "Jumlah baris hasil: " . ($hasil ? mysqli_num_rows($hasil) : 0) . "\n";
echo str_repeat('-', 50) . "\n";

if ($hasil && mysqli_num_rows($hasil)) {
    echo "5 siswa pertama:\n";
    $i = 0;
    while ($row = mysqli_fetch_array($hasil)) {
        echo "  {$row['nis']} | {$row['nama_siswa']} | {$row['kelas']}\n";
        if (++$i >= 5) break;
    }
}

// Cek file yang sedang dipakai
$f = __DIR__ . '/petugas/setor/data_setor.php';
echo str_repeat('-', 50) . "\n";
echo "data_setor.php ada: " . (file_exists($f) ? 'YA' : 'TIDAK') . "\n";
echo "Ukuran: " . (file_exists($f) ? filesize($f) . ' bytes' : '-') . "\n";
echo "Terakhir diubah: " . (file_exists($f) ? date('Y-m-d H:i:s', filemtime($f)) : '-') . " (waktu server)\n";
echo "Ada filter status di file: ";
$src = file_exists($f) ? file_get_contents($f) : '';
echo (strpos($src, "status='Aktif'") !== false) ? "MASIH ADA (file lama!)" : "sudah dihapus (file baru)";
echo "\n";
