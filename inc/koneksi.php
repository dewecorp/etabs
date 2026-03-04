<?php
/**
 * File Koneksi Database
 * 
 * Untuk hosting, gunakan file config_db.php atau edit langsung di sini
 */

// Konfigurasi Error Reporting
// Set ke true untuk development, false untuk production
if (!defined('DEBUG')) {
    define('DEBUG', true); // Ubah ke false saat deploy ke hosting
}

if (DEBUG) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// Cek apakah file config_db.php ada (untuk hosting)
if (file_exists(__DIR__ . '/config_db.php')) {
    require_once __DIR__ . '/config_db.php';
} else {
    // Konfigurasi default untuk development (local)
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'db_tabsis';
    
    // Coba ambil dari environment variable jika ada (untuk hosting)
    if (getenv('DB_HOST')) {
        $db_host = getenv('DB_HOST');
        $db_user = getenv('DB_USER') ?: 'root';
        $db_pass = getenv('DB_PASS') ?: '';
        $db_name = getenv('DB_NAME') ?: 'db_tabsis';
    }
    
    $koneksi = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    // Cek koneksi
    if ($koneksi->connect_error) {
        // Tampilkan error hanya di development, di production log ke file
        if (defined('DEBUG') && DEBUG) {
            die("Koneksi database gagal: " . $koneksi->connect_error);
        } else {
            error_log("Database connection failed: " . $koneksi->connect_error);
            die("Terjadi kesalahan sistem. Silakan hubungi administrator.");
        }
    }
    
    // Set charset untuk menghindari masalah encoding
    $koneksi->set_charset("utf8mb4");
    
    // Set timezone
    date_default_timezone_set('Asia/Jakarta');
    $koneksi->query("SET time_zone = '+07:00'");
}
