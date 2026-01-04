<?php
/**
 * Script Diagnosa Masalah Hosting
 * Akses: https://yourdomain.com/diagnose_hosting.php
 * 
 * HAPUS FILE INI SETELAH SELESAI DEBUGGING!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Diagnosa Hosting</title>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;background:#f5f5f5;}";
echo ".section{background:white;padding:20px;margin:10px 0;border-radius:5px;box-shadow:0 2px 5px rgba(0,0,0,0.1);}";
echo ".success{color:green;font-weight:bold;}";
echo ".error{color:red;font-weight:bold;}";
echo ".warning{color:orange;font-weight:bold;}";
echo "table{border-collapse:collapse;width:100%;margin:10px 0;}";
echo "th,td{padding:8px;text-align:left;border:1px solid #ddd;}";
echo "th{background:#4CAF50;color:white;}";
echo ".info{background:#e7f3ff;padding:10px;border-left:4px solid #2196F3;margin:10px 0;}";
echo "</style></head><body>";
echo "<h1>🔍 Diagnosa Masalah Hosting</h1>";

// 1. Cek PHP Version
echo "<div class='section'>";
echo "<h2>1. Informasi PHP</h2>";
echo "<table>";
echo "<tr><th>Item</th><th>Nilai</th></tr>";
echo "<tr><td>PHP Version</td><td>" . phpversion() . "</td></tr>";
echo "<tr><td>Server Software</td><td>" . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</td></tr>";
echo "<tr><td>Operating System</td><td>" . PHP_OS . "</td></tr>";
echo "<tr><td>Case Sensitivity</td><td>" . (strtoupper('test') === 'TEST' ? 'Case Insensitive' : 'Case Sensitive') . "</td></tr>";
echo "</table>";
echo "</div>";

// 2. Cek File dan Path
echo "<div class='section'>";
echo "<h2>2. Cek File dan Path</h2>";
$files_to_check = [
    'inc/koneksi.php',
    'inc/config_db.php',
    'inc/activity_log.php',
    'index.php',
    'home/admin.php',
    'admin/siswa/data_siswa.php',
    'admin/aktivitas/aktivitas.php'
];

echo "<table>";
echo "<tr><th>File</th><th>Status</th><th>Path</th></tr>";
foreach ($files_to_check as $file) {
    $exists = file_exists($file);
    $real_path = $exists ? realpath($file) : 'NOT FOUND';
    $status = $exists ? "<span class='success'>✓ Ada</span>" : "<span class='error'>✗ Tidak Ada</span>";
    echo "<tr><td>$file</td><td>$status</td><td>$real_path</td></tr>";
}
echo "</table>";
echo "</div>";

// 3. Cek Koneksi Database
echo "<div class='section'>";
echo "<h2>3. Koneksi Database</h2>";
if (file_exists('inc/koneksi.php')) {
    include 'inc/koneksi.php';
    
    if (isset($koneksi) && $koneksi) {
        if (is_object($koneksi) && method_exists($koneksi, 'connect_error')) {
            if ($koneksi->connect_error) {
                echo "<p class='error'>✗ Error Koneksi: " . $koneksi->connect_error . "</p>";
            } else {
                echo "<p class='success'>✓ Koneksi Database Berhasil</p>";
                
                // Cek tabel
                echo "<h3>Tabel Database:</h3>";
                $tables = ['tb_profil', 'tb_siswa', 'tb_kelas', 'tb_pengguna', 'tb_tabungan', 'tb_activity_log'];
                echo "<table>";
                echo "<tr><th>Tabel</th><th>Status</th><th>Jumlah Data</th></tr>";
                
                foreach ($tables as $table) {
                    $check = @$koneksi->query("SHOW TABLES LIKE '$table'");
                    if ($check && $check->num_rows > 0) {
                        $count = @$koneksi->query("SELECT COUNT(*) as total FROM $table");
                        $row = $count ? $count->fetch_assoc() : ['total' => 0];
                        echo "<tr><td>$table</td><td><span class='success'>✓ Ada</span></td><td>{$row['total']}</td></tr>";
                    } else {
                        echo "<tr><td>$table</td><td><span class='error'>✗ Tidak Ada</span></td><td>-</td></tr>";
                    }
                }
                echo "</table>";
                
                // Cek data siswa detail
                echo "<h3>Detail Data Siswa:</h3>";
                $sql_siswa = @$koneksi->query("SELECT COUNT(*) as total FROM tb_siswa");
                if ($sql_siswa) {
                    $row_siswa = $sql_siswa->fetch_assoc();
                    echo "<p>Total siswa di database: <strong>{$row_siswa['total']}</strong></p>";
                    
                    // Cek siswa dengan kelas
                    $sql_with_class = @$koneksi->query("SELECT COUNT(*) as total FROM tb_siswa s INNER JOIN tb_kelas k ON s.id_kelas=k.id_kelas");
                    if ($sql_with_class) {
                        $row_with = $sql_with_class->fetch_assoc();
                        echo "<p>Siswa dengan kelas valid: <strong>{$row_with['total']}</strong></p>";
                    }
                    
                    // Cek siswa tanpa kelas
                    $sql_no_class = @$koneksi->query("SELECT COUNT(*) as total FROM tb_siswa s LEFT JOIN tb_kelas k ON s.id_kelas=k.id_kelas WHERE k.id_kelas IS NULL");
                    if ($sql_no_class) {
                        $row_no = $sql_no_class->fetch_assoc();
                        if ($row_no['total'] > 0) {
                            echo "<p class='warning'>⚠ Siswa tanpa kelas: <strong>{$row_no['total']}</strong></p>";
                        }
                    }
                }
                
                // Cek profil
                echo "<h3>Data Profil:</h3>";
                $sql_profil = @$koneksi->query("SELECT * FROM tb_profil LIMIT 1");
                if ($sql_profil && $sql_profil->num_rows > 0) {
                    $profil = $sql_profil->fetch_assoc();
                    echo "<p class='success'>✓ Data profil ditemukan</p>";
                    echo "<pre>" . print_r($profil, true) . "</pre>";
                } else {
                    echo "<p class='error'>✗ Data profil tidak ditemukan</p>";
                }
            }
        } else {
            echo "<p class='error'>✗ Koneksi tidak valid</p>";
        }
    } else {
        echo "<p class='error'>✗ Variabel \$koneksi tidak tersedia</p>";
    }
} else {
    echo "<p class='error'>✗ File inc/koneksi.php tidak ditemukan</p>";
}
echo "</div>";

// 4. Cek Include Path
echo "<div class='section'>";
echo "<h2>4. Cek Include Path</h2>";
echo "<table>";
echo "<tr><th>Konstanta</th><th>Nilai</th></tr>";
echo "<tr><td>__DIR__</td><td>" . __DIR__ . "</td></tr>";
echo "<tr><td>getcwd()</td><td>" . getcwd() . "</td></tr>";
echo "<tr><td>__FILE__</td><td>" . __FILE__ . "</td></tr>";
echo "<tr><td>include_path</td><td>" . get_include_path() . "</td></tr>";
echo "</table>";
echo "</div>";

// 5. Cek Permission
echo "<div class='section'>";
echo "<h2>5. Cek Permission File</h2>";
$dirs_to_check = ['uploads/logo', 'uploads'];
echo "<table>";
echo "<tr><th>Direktori</th><th>Status</th><th>Permission</th></tr>";
foreach ($dirs_to_check as $dir) {
    if (file_exists($dir)) {
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        $writable = is_writable($dir) ? "<span class='success'>✓ Writable</span>" : "<span class='error'>✗ Not Writable</span>";
        echo "<tr><td>$dir</td><td>$writable</td><td>$perms</td></tr>";
    } else {
        echo "<tr><td>$dir</td><td><span class='error'>✗ Tidak Ada</span></td><td>-</td></tr>";
    }
}
echo "</table>";
echo "</div>";

// 6. Cek Error Log
echo "<div class='section'>";
echo "<h2>6. Error Log</h2>";
$error_log_paths = [
    'error_log',
    '../error_log',
    ini_get('error_log'),
    sys_get_temp_dir() . '/php_errors.log'
];

echo "<p>Mencari error log di:</p><ul>";
foreach ($error_log_paths as $log_path) {
    if ($log_path && file_exists($log_path)) {
        echo "<li><span class='success'>✓ Ditemukan: $log_path</span></li>";
        $log_content = file_get_contents($log_path);
        if (strlen($log_content) > 0) {
            echo "<pre style='background:#f5f5f5;padding:10px;max-height:200px;overflow:auto;'>" . htmlspecialchars(substr($log_content, -2000)) . "</pre>";
        }
    } else {
        echo "<li><span class='warning'>- Tidak ditemukan: " . ($log_path ?: 'N/A') . "</span></li>";
    }
}
echo "</ul>";
echo "</div>";

// 7. Test Query Siswa
echo "<div class='section'>";
echo "<h2>7. Test Query Siswa</h2>";
if (isset($koneksi) && $koneksi) {
    echo "<h3>Query dengan INNER JOIN (Lama):</h3>";
    $sql_old = @$koneksi->query("SELECT COUNT(*) as total FROM tb_siswa s INNER JOIN tb_kelas k ON s.id_kelas=k.id_kelas");
    if ($sql_old) {
        $row_old = $sql_old->fetch_assoc();
        echo "<p>Hasil: <strong>{$row_old['total']}</strong> siswa</p>";
    } else {
        echo "<p class='error'>Error: " . $koneksi->error . "</p>";
    }
    
    echo "<h3>Query dengan LEFT JOIN (Baru):</h3>";
    $sql_new = @$koneksi->query("SELECT COUNT(*) as total FROM tb_siswa s LEFT JOIN tb_kelas k ON s.id_kelas=k.id_kelas");
    if ($sql_new) {
        $row_new = $sql_new->fetch_assoc();
        echo "<p>Hasil: <strong>{$row_new['total']}</strong> siswa</p>";
    } else {
        echo "<p class='error'>Error: " . $koneksi->error . "</p>";
    }
}
echo "</div>";

// 8. Rekomendasi
echo "<div class='section'>";
echo "<h2>8. Rekomendasi</h2>";
echo "<div class='info'>";
echo "<h3>Jika data siswa tidak muncul:</h3>";
echo "<ul>";
echo "<li>Pastikan semua data sudah di-import ke database hosting</li>";
echo "<li>Cek apakah id_kelas siswa sesuai dengan id_kelas di tabel tb_kelas</li>";
echo "<li>Gunakan query dengan LEFT JOIN (sudah diperbaiki di data_siswa.php)</li>";
echo "</ul>";

echo "<h3>Jika profil tidak muncul:</h3>";
echo "<ul>";
echo "<li>Pastikan tabel tb_profil ada dan berisi data</li>";
echo "<li>Cek path logo: uploads/logo/</li>";
echo "</ul>";

echo "<h3>Jika menu tidak bisa diklik:</h3>";
echo "<ul>";
echo "<li>Pastikan file dist/js/app.min.js ter-load dengan benar</li>";
echo "<li>Cek console browser untuk error JavaScript</li>";
echo "</ul>";

echo "<h3>Jika aktivitas tidak muncul:</h3>";
echo "<ul>";
echo "<li>Pastikan tabel tb_activity_log sudah dibuat (akan dibuat otomatis)</li>";
echo "<li>Cek apakah ada aktivitas yang tercatat</li>";
echo "</ul>";
echo "</div>";
echo "</div>";

echo "<hr>";
echo "<p><strong>⚠ PENTING:</strong> Hapus file ini setelah selesai debugging untuk keamanan!</p>";
echo "</body></html>";
?>

