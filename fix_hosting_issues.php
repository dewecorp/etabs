<?php
/**
 * Script untuk Memperbaiki Masalah Hosting
 * 
 * Script ini akan:
 * 1. Memperbaiki path include yang mungkin salah
 * 2. Membuat file helper untuk path yang konsisten
 * 3. Mengecek dan memperbaiki masalah umum
 * 
 * Akses: https://yourdomain.com/fix_hosting_issues.php
 * HAPUS FILE INI SETELAH SELESAI!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Fix Hosting Issues</title>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;background:#f5f5f5;}";
echo ".section{background:white;padding:20px;margin:10px 0;border-radius:5px;box-shadow:0 2px 5px rgba(0,0,0,0.1);}";
echo ".success{color:green;font-weight:bold;}";
echo ".error{color:red;font-weight:bold;}";
echo ".info{background:#e7f3ff;padding:15px;border-left:4px solid #2196F3;margin:10px 0;}";
echo "</style></head><body>";
echo "<h1>🔧 Memperbaiki Masalah Hosting</h1>";

// 1. Buat file helper untuk path
echo "<div class='section'>";
echo "<h2>1. Membuat File Helper Path</h2>";

$helper_content = '<?php
/**
 * Helper untuk Path yang Konsisten
 * Menggunakan __DIR__ untuk path absolut yang aman di semua OS
 */

// Pastikan fungsi helper belum didefinisikan
if (!function_exists("getBasePath")) {
    /**
     * Mendapatkan base path aplikasi
     */
    function getBasePath() {
        // Cari root folder dengan mencari file index.php
        $current_dir = __DIR__;
        $max_depth = 10;
        $depth = 0;
        
        while ($depth < $max_depth) {
            if (file_exists($current_dir . DIRECTORY_SEPARATOR . "index.php")) {
                return $current_dir;
            }
            $parent = dirname($current_dir);
            if ($parent === $current_dir) {
                break; // Sudah di root filesystem
            }
            $current_dir = $parent;
            $depth++;
        }
        
        // Fallback ke __DIR__
        return __DIR__;
    }
    
    /**
     * Include file dengan path yang benar
     */
    function safeInclude($file_path) {
        $base_path = getBasePath();
        $full_path = $base_path . DIRECTORY_SEPARATOR . str_replace("/", DIRECTORY_SEPARATOR, $file_path);
        
        if (file_exists($full_path)) {
            return include $full_path;
        }
        
        // Coba dengan path relatif dari current file
        $relative_path = dirname(__FILE__) . DIRECTORY_SEPARATOR . str_replace("/", DIRECTORY_SEPARATOR, $file_path);
        if (file_exists($relative_path)) {
            return include $relative_path;
        }
        
        return false;
    }
    
    /**
     * Require file dengan path yang benar
     */
    function safeRequire($file_path) {
        $base_path = getBasePath();
        $full_path = $base_path . DIRECTORY_SEPARATOR . str_replace("/", DIRECTORY_SEPARATOR, $file_path);
        
        if (file_exists($full_path)) {
            return require $full_path;
        }
        
        // Coba dengan path relatif dari current file
        $relative_path = dirname(__FILE__) . DIRECTORY_SEPARATOR . str_replace("/", DIRECTORY_SEPARATOR, $file_path);
        if (file_exists($relative_path)) {
            return require $relative_path;
        }
        
        throw new Exception("File not found: " . $file_path);
    }
}
';

$helper_file = 'inc/path_helper.php';
if (file_put_contents($helper_file, $helper_content)) {
    echo "<p class='success'>✓ File helper dibuat: $helper_file</p>";
} else {
    echo "<p class='error'>✗ Gagal membuat file helper</p>";
}
echo "</div>";

// 2. Cek dan perbaiki include path di file-file penting
echo "<div class='section'>";
echo "<h2>2. Cek Include Path</h2>";

$files_to_check = [
    'home/admin.php' => [
        'line' => 4,
        'old' => 'include_once "../inc/activity_log.php";',
        'new' => 'include_once __DIR__ . "/../inc/activity_log.php";'
    ]
];

foreach ($files_to_check as $file => $info) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        if (strpos($content, $info['old']) !== false) {
            echo "<p class='info'>File $file menggunakan path relatif yang mungkin bermasalah</p>";
            echo "<p>Rekomendasi: Gunakan path absolut dengan __DIR__</p>";
        } else {
            echo "<p class='success'>✓ $file sudah menggunakan path yang benar</p>";
        }
    } else {
        echo "<p class='error'>✗ File tidak ditemukan: $file</p>";
    }
}
echo "</div>";

// 3. Cek case sensitivity
echo "<div class='section'>";
echo "<h2>3. Cek Case Sensitivity</h2>";

$case_sensitive_files = [
    'admin/siswa/data_siswa.php',
    'Admin/Siswa/Data_Siswa.php', // Wrong case
    'ADMIN/SISWA/DATA_SISWA.PHP', // Wrong case
];

foreach ($case_sensitive_files as $file) {
    if (file_exists($file)) {
        echo "<p class='success'>✓ Ditemukan: $file</p>";
    } else {
        // Cek dengan case yang benar
        $correct_case = strtolower($file);
        if (file_exists($correct_case)) {
            echo "<p class='error'>✗ File dengan case salah ditemukan: $file</p>";
            echo "<p>Gunakan: $correct_case</p>";
        }
    }
}
echo "</div>";

// 4. Cek permission
echo "<div class='section'>";
echo "<h2>4. Cek Permission File</h2>";

$dirs = ['uploads', 'uploads/logo'];
foreach ($dirs as $dir) {
    if (file_exists($dir)) {
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        $writable = is_writable($dir);
        $status = $writable ? "<span class='success'>✓ Writable</span>" : "<span class='error'>✗ Not Writable</span>";
        echo "<p>$dir: $status (Permission: $perms)</p>";
        
        if (!$writable) {
            echo "<p class='info'>Rekomendasi: Set permission ke 755 atau 777</p>";
        }
    } else {
        echo "<p class='error'>✗ Direktori tidak ditemukan: $dir</p>";
        echo "<p class='info'>Rekomendasi: Buat direktori dengan permission 755</p>";
    }
}
echo "</div>";

// 5. Test koneksi database
echo "<div class='section'>";
echo "<h2>5. Test Koneksi Database</h2>";

if (file_exists('inc/koneksi.php')) {
    include 'inc/koneksi.php';
    
    if (isset($koneksi) && $koneksi) {
        if (is_object($koneksi) && method_exists($koneksi, 'connect_error')) {
            if ($koneksi->connect_error) {
                echo "<p class='error'>✗ Error Koneksi: " . $koneksi->connect_error . "</p>";
                echo "<p class='info'>Periksa konfigurasi di inc/koneksi.php atau inc/config_db.php</p>";
            } else {
                echo "<p class='success'>✓ Koneksi Database Berhasil</p>";
                
                // Test query
                $test_query = @$koneksi->query("SELECT COUNT(*) as total FROM tb_siswa");
                if ($test_query) {
                    $row = $test_query->fetch_assoc();
                    echo "<p>Total siswa: <strong>{$row['total']}</strong></p>";
                } else {
                    echo "<p class='error'>✗ Error Query: " . $koneksi->error . "</p>";
                }
            }
        }
    }
} else {
    echo "<p class='error'>✗ File inc/koneksi.php tidak ditemukan</p>";
}
echo "</div>";

// 6. Rekomendasi
echo "<div class='section'>";
echo "<h2>6. Rekomendasi Perbaikan</h2>";
echo "<div class='info'>";
echo "<h3>Masalah Umum dan Solusinya:</h3>";
echo "<ol>";
echo "<li><strong>Case Sensitivity:</strong> Linux case-sensitive, pastikan nama file/folder sesuai case</li>";
echo "<li><strong>Path Include:</strong> Gunakan __DIR__ untuk path absolut yang aman</li>";
echo "<li><strong>Database:</strong> Pastikan konfigurasi database benar di inc/config_db.php</li>";
echo "<li><strong>Permission:</strong> Folder uploads harus writable (755 atau 777)</li>";
echo "<li><strong>Data Import:</strong> Pastikan semua data sudah di-import dengan benar</li>";
echo "</ol>";

echo "<h3>Langkah Perbaikan:</h3>";
echo "<ol>";
echo "<li>Edit inc/config_db.php dengan informasi database hosting</li>";
echo "<li>Import database dari local ke hosting (semua tabel dan data)</li>";
echo "<li>Set permission folder uploads ke 755</li>";
echo "<li>Cek error log di cPanel untuk melihat error yang terjadi</li>";
echo "<li>Gunakan diagnose_hosting.php untuk diagnosa lebih detail</li>";
echo "</ol>";
echo "</div>";
echo "</div>";

echo "<hr>";
echo "<p><strong>⚠ PENTING:</strong> Hapus file ini setelah selesai!</p>";
echo "</body></html>";
?>

