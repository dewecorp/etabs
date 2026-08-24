<?php
// FILE DIAGNOSTIK SEMENTARA - HAPUS SETELAH SELESAI
session_start();
if (!isset($_SESSION['ses_username'])) { die('Login dulu lewat aplikasi, lalu buka file ini lagi.'); }
include "inc/koneksi.php";

header('Content-Type: text/plain; charset=utf-8');
echo "PHP: " . PHP_VERSION . "\n";
echo str_repeat('-', 50) . "\n";

// 1. Data DB
$r = @$koneksi->query("SELECT COUNT(*) c FROM tb_siswa");
echo "Total tb_siswa: " . ($r ? $r->fetch_assoc()['c'] : 'ERR: ' . $koneksi->error) . "\n";
echo str_repeat('-', 50) . "\n";

// 2. Eksekusi langsung data_setor.php seperti yang dilakukan index.php
$f = __DIR__ . '/petugas/setor/data_setor.php';
echo "File ada: " . (file_exists($f) ? 'YA' : 'TIDAK') . "\n";
if (file_exists($f)) {
    echo "Ukuran : " . filesize($f) . " bytes\n";
    echo "MD5    : " . md5_file($f) . "\n";
    echo "MD5 versi lokal yang benar: f68e7cae4d174b8453f7335843065c04 (44787 bytes)\n";
    echo str_repeat('-', 50) . "\n";

    // Stub fungsi dari inc/rupiah.php agar include mandiri tidak fatal
    if (!function_exists('rupiah')) {
        function rupiah($n) { return 'Rp ' . number_format((float)$n, 0, ',', '.'); }
    }
    if (!function_exists('tgl_indo_standar')) {
        function tgl_indo_standar($t) { return $t; }
    }

    $_GET['page'] = 'data_setor';
    $html = '';
    try {
        ob_start();
        include $f;
        $html = ob_get_clean();
        echo "Eksekusi data_setor.php: OK, panjang output " . strlen($html) . " karakter\n";
        echo "Jumlah '<option' dirender: " . substr_count($html, '<option') . "\n";
        echo "Ada teks [DEBUG]: " . (strpos($html, '[DEBUG]') !== false ? 'YA -> ' . strip_tags(substr($html, strpos($html, '[DEBUG]'), 120)) : 'TIDAK') . "\n";
    } catch (Throwable $e) {
        ob_end_clean();
        echo "FATAL saat eksekusi: " . $e->getMessage() . " di " . basename($e->getFile()) . ":" . $e->getLine() . "\n";
    }
}

echo str_repeat('-', 50) . "\n";
// 3. Aset pendukung dropdown
$assets = [
    'plugins/select2/select2.min.css',
    'plugins/select2/select2.full.min.js',
];
foreach ($assets as $a) {
    echo "$a : " . (file_exists(__DIR__ . '/' . $a) ? 'ADA (' . filesize(__DIR__ . '/' . $a) . ' b)' : 'TIDAK ADA!') . "\n";
}
