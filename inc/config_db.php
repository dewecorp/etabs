<?php
/**
 * Konfigurasi Database
 * 
 * Untuk penggunaan di hosting, edit file ini dengan informasi database hosting Anda
 * Jangan lupa untuk mengubah kembali ke konfigurasi lokal saat development
 */

// Konfigurasi Database
// Untuk Development (Local)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_tabsis');

// Untuk Production (Hosting) - Uncomment dan isi dengan data hosting Anda
// define('DB_HOST', 'localhost');
// define('DB_USER', 'username_hosting');
// define('DB_PASS', 'password_hosting');
// define('DB_NAME', 'nama_database_hosting');

// Koneksi Database
$koneksi = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if ($koneksi->connect_error) {
    die("Koneksi database gagal: " . $koneksi->connect_error);
}

// Set charset untuk menghindari masalah encoding
$koneksi->set_charset("utf8mb4");

// Set timezone
date_default_timezone_set('Asia/Jakarta');

