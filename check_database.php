<?php
/**
 * Script untuk mengecek koneksi database dan struktur tabel
 * Akses melalui browser: http://yourdomain.com/check_database.php
 * 
 * HAPUS FILE INI SETELAH SELESAI DEBUGGING UNTUK KEAMANAN!
 */

// Aktifkan error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Connection Check</h2>";

// Cek file koneksi
if (file_exists('inc/koneksi.php')) {
    echo "<p style='color: green;'>✓ File inc/koneksi.php ditemukan</p>";
    include 'inc/koneksi.php';
    
    if (isset($koneksi) && $koneksi) {
        echo "<p style='color: green;'>✓ Koneksi database berhasil</p>";
        
        // Cek tabel yang diperlukan
        $required_tables = ['tb_profil', 'tb_siswa', 'tb_kelas', 'tb_pengguna', 'tb_tabungan', 'tb_activity_log'];
        
        echo "<h3>Status Tabel:</h3>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>Tabel</th><th>Status</th><th>Jumlah Data</th></tr>";
        
        foreach ($required_tables as $table) {
            $check = @$koneksi->query("SHOW TABLES LIKE '$table'");
            if ($check && $check->num_rows > 0) {
                $count = @$koneksi->query("SELECT COUNT(*) as total FROM $table");
                $row = $count ? $count->fetch_assoc() : ['total' => 0];
                echo "<tr><td>$table</td><td style='color: green;'>✓ Ada</td><td>{$row['total']}</td></tr>";
            } else {
                echo "<tr><td>$table</td><td style='color: red;'>✗ Tidak ada</td><td>-</td></tr>";
            }
        }
        
        echo "</table>";
        
        // Cek data profil
        echo "<h3>Data Profil:</h3>";
        $profil = @$koneksi->query("SELECT * FROM tb_profil LIMIT 1");
        if ($profil && $profil->num_rows > 0) {
            $data = $profil->fetch_assoc();
            echo "<pre>";
            print_r($data);
            echo "</pre>";
        } else {
            echo "<p style='color: red;'>✗ Tidak ada data profil</p>";
        }
        
        // Cek data siswa
        echo "<h3>Data Siswa:</h3>";
        $siswa = @$koneksi->query("SELECT COUNT(*) as total FROM tb_siswa");
        if ($siswa) {
            $row = $siswa->fetch_assoc();
            echo "<p>Total siswa: <strong>{$row['total']}</strong></p>";
            
            if ($row['total'] > 0) {
                $sample = @$koneksi->query("SELECT * FROM tb_siswa LIMIT 5");
                echo "<p>Sample data (5 pertama):</p>";
                echo "<pre>";
                while ($s = $sample->fetch_assoc()) {
                    print_r($s);
                }
                echo "</pre>";
            }
        }
        
        // Cek error terakhir
        if ($koneksi->error) {
            echo "<h3 style='color: red;'>Error Terakhir:</h3>";
            echo "<p style='color: red;'>{$koneksi->error}</p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ Koneksi database gagal</p>";
        if (isset($koneksi) && is_object($koneksi)) {
            echo "<p style='color: red;'>Error: " . $koneksi->connect_error . "</p>";
        }
    }
} else {
    echo "<p style='color: red;'>✗ File inc/koneksi.php tidak ditemukan</p>";
}

echo "<hr>";
echo "<p><strong>PENTING:</strong> Hapus file ini setelah selesai debugging untuk keamanan!</p>";
?>

