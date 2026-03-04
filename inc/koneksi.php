<?php
/**
 * File Koneksi Database (Single Config)
 * Otomatis mendeteksi lingkungan Local atau Hosting
 */

// 1. Deteksi Lingkungan
$is_local = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) || $_SERVER['HTTP_HOST'] == 'localhost';

// 2. Konfigurasi Error Reporting
if ($is_local) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// 3. Pengaturan Database
if ($is_local) {
    // --- KONFIGURASI LOCAL (LARAGON/XAMPP) ---
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'db_tabsis';
} else {
    // --- KONFIGURASI HOSTING (SESUAIKAN DI SINI) ---
    $db_host = 'localhost';
    $db_user = 'root';      // Ganti dengan username database hosting
    $db_pass = '';          // Ganti dengan password database hosting
    $db_name = 'db_tabsis'; // Ganti dengan nama database hosting
}

// 4. Inisialisasi Koneksi
$koneksi = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Cek Koneksi
if ($koneksi->connect_error) {
    if ($is_local) {
        die("Koneksi Lokal Gagal: " . $koneksi->connect_error);
    } else {
        error_log("Database Connection Error: " . $koneksi->connect_error);
        die("Terjadi kesalahan koneksi ke server database.");
    }
}

// 5. Pengaturan Tambahan
$koneksi->set_charset("utf8mb4");
date_default_timezone_set('Asia/Jakarta');
$koneksi->query("SET time_zone = '+07:00'");
?>
