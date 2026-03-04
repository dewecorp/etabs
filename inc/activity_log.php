<?php
/**
 * Activity Log System
 * Mencatat semua aktivitas CRUD dan auto-delete setelah 24 jam
 */

/**
 * Fungsi untuk mencatat aktivitas
 */
if (!function_exists('logActivity')) {
function logActivity($koneksi, $action, $table, $description, $record_id = null) {
    // Pastikan tabel ada
    createActivityTable($koneksi);
    
    // Hapus aktivitas yang lebih dari 24 jam
    cleanupOldActivities($koneksi);
    
    // Ambil informasi user dari session
    $user_id = isset($_SESSION['ses_id']) ? $_SESSION['ses_id'] : 0;
    $user_name = isset($_SESSION['ses_nama']) ? $_SESSION['ses_nama'] : 'System';
    $user_level = isset($_SESSION['ses_level']) ? $_SESSION['ses_level'] : 'Unknown';
    
    // Icon berdasarkan action
    $icons = [
        'CREATE' => 'fa-plus-circle',
        'READ' => 'fa-eye',
        'UPDATE' => 'fa-edit',
        'DELETE' => 'fa-trash',
        'LOGIN' => 'fa-sign-in',
        'LOGOUT' => 'fa-sign-out',
        'EXPORT' => 'fa-download',
        'IMPORT' => 'fa-upload',
        'BACKUP' => 'fa-database',
        'RESTORE' => 'fa-undo'
    ];
    
    $icon = isset($icons[$action]) ? $icons[$action] : 'fa-circle';
    
    // Warna berdasarkan action
    $colors = [
        'CREATE' => 'success',
        'READ' => 'info',
        'UPDATE' => 'warning',
        'DELETE' => 'danger',
        'LOGIN' => 'success',
        'LOGOUT' => 'default',
        'EXPORT' => 'primary',
        'IMPORT' => 'primary',
        'BACKUP' => 'info',
        'RESTORE' => 'warning'
    ];
    
    $color = isset($colors[$action]) ? $colors[$action] : 'default';
    
    // Insert aktivitas
    try {
        // Pastikan session sudah dimulai
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        
        // Gunakan prepared statement jika koneksi adalah object mysqli
        if ($koneksi instanceof mysqli) {
            $sql = "INSERT INTO tb_activity_log (user_id, user_name, user_level, action, table_name, record_id, description, icon, color, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $koneksi->prepare($sql);
            if ($stmt) {
                // Pastikan record_id adalah string atau null
                $record_id_str = $record_id ? (string)$record_id : null;
                $stmt->bind_param("issssssss", 
                    $user_id, 
                    $user_name, 
                    $user_level, 
                    $action, 
                    $table, 
                    $record_id_str, 
                    $description, 
                    $icon, 
                    $color
                );
                $stmt->execute();
                $stmt->close();
                return;
            }
        }
        
        // Fallback: gunakan mysqli_query langsung jika bukan mysqli object
        if ($koneksi instanceof mysqli) {
            $u_id = $koneksi->real_escape_string($user_id);
            $u_name = $koneksi->real_escape_string($user_name);
            $u_level = $koneksi->real_escape_string($user_level);
            $act = $koneksi->real_escape_string($action);
            $tbl = $koneksi->real_escape_string($table);
            $rec_id = $record_id ? "'" . $koneksi->real_escape_string($record_id) . "'" : "NULL";
            $desc = $koneksi->real_escape_string($description);
            $icn = $koneksi->real_escape_string($icon);
            $clr = $koneksi->real_escape_string($color);
        } else {
            // Procedural fallback (only if absolutely necessary, but inc/koneksi.php uses mysqli)
            $u_id = mysqli_real_escape_string($koneksi, $user_id);
            $u_name = mysqli_real_escape_string($koneksi, $user_name);
            $u_level = mysqli_real_escape_string($koneksi, $user_level);
            $act = mysqli_real_escape_string($koneksi, $action);
            $tbl = mysqli_real_escape_string($koneksi, $table);
            $rec_id = $record_id ? "'" . mysqli_real_escape_string($koneksi, $record_id) . "'" : "NULL";
            $desc = mysqli_real_escape_string($koneksi, $description);
            $icn = mysqli_real_escape_string($koneksi, $icon);
            $clr = mysqli_real_escape_string($koneksi, $color);
        }
        
        $sql = "INSERT INTO tb_activity_log (user_id, user_name, user_level, action, table_name, record_id, description, icon, color, created_at) 
                VALUES ('$u_id', '$u_name', '$u_level', '$act', '$tbl', $rec_id, '$desc', '$icn', '$clr', NOW())";
        
        if ($koneksi instanceof mysqli) {
            $koneksi->query($sql);
        } else {
            mysqli_query($koneksi, $sql);
        }
    } catch (Exception $e) {
        // Silent fail - jangan tampilkan error
    }
}
}

/**
 * Membuat tabel activity log jika belum ada
 */
if (!function_exists('createActivityTable')) {
function createActivityTable($koneksi) {
    try {
        $sql = "CREATE TABLE IF NOT EXISTS tb_activity_log (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            user_id INT(11) DEFAULT NULL,
            user_name VARCHAR(100) NOT NULL,
            user_level VARCHAR(50) DEFAULT NULL,
            action VARCHAR(20) NOT NULL,
            table_name VARCHAR(50) NOT NULL,
            record_id VARCHAR(50) DEFAULT NULL,
            description TEXT NOT NULL,
            icon VARCHAR(50) DEFAULT 'fa-circle',
            color VARCHAR(20) DEFAULT 'default',
            created_at DATETIME NOT NULL,
            INDEX idx_created_at (created_at),
            INDEX idx_user_id (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        if ($koneksi instanceof mysqli) {
            $koneksi->query($sql);
        } else {
            mysqli_query($koneksi, $sql);
        }
    } catch (Exception $e) {
        // Silent fail
    }
}
}

/**
 * Hapus aktivitas yang lebih dari 24 jam
 */
if (!function_exists('cleanupOldActivities')) {
function cleanupOldActivities($koneksi) {
    try {
        $sql = "DELETE FROM tb_activity_log WHERE created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        if ($koneksi instanceof mysqli) {
            $koneksi->query($sql);
        } else {
            mysqli_query($koneksi, $sql);
        }
    } catch (Exception $e) {
        // Silent fail
    }
}
}

/**
 * Alias untuk cleanupOldActivities agar kompatibel dengan kode lama
 */
if (!function_exists('deleteOldActivities')) {
function deleteOldActivities($koneksi) {
    cleanupOldActivities($koneksi);
}
}

/**
 * Ambil jumlah aktivitas terbaru
 */
if (!function_exists('getActivityCount')) {
function getActivityCount($koneksi) {
    try {
        cleanupOldActivities($koneksi);
        $sql = "SELECT COUNT(*) as total FROM tb_activity_log";
        if ($koneksi instanceof mysqli) {
            $result = $koneksi->query($sql);
            if ($result && $row = $result->fetch_assoc()) {
                return (int)$row['total'];
            }
        } else {
            $result = mysqli_query($koneksi, $sql);
            if ($result && $row = mysqli_fetch_assoc($result)) {
                return (int)$row['total'];
            }
        }
    } catch (Exception $e) {
        // Silent fail
    }
    return 0;
}
}

/**
 * Ambil aktivitas terbaru
 */
if (!function_exists('getRecentActivities')) {
function getRecentActivities($koneksi, $limit = 50) {
    try {
        cleanupOldActivities($koneksi);
        $activities = [];
        $limit = (int)$limit;
        $sql = "SELECT * FROM tb_activity_log ORDER BY created_at DESC LIMIT $limit";
        
        if ($koneksi instanceof mysqli) {
            $result = $koneksi->query($sql);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $activities[] = $row;
                }
            }
        } else {
            $result = mysqli_query($koneksi, $sql);
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $activities[] = $row;
                }
            }
        }
    } catch (Exception $e) {
        // Silent fail
    }
    return $activities;
}
}

/**
 * Fungsi untuk mendapatkan badge color berdasarkan action
 */
if (!function_exists('getActivityBadge')) {
function getActivityBadge($action) {
    $colors = [
        'CREATE' => 'success',
        'UPDATE' => 'warning',
        'DELETE' => 'danger',
        'LOGIN' => 'success',
        'LOGOUT' => 'info',
        'BACKUP' => 'primary',
        'RESTORE' => 'warning'
    ];
    return isset($colors[$action]) ? $colors[$action] : 'info';
}
}

/**
 * Fungsi untuk mendapatkan icon berdasarkan action
 */
if (!function_exists('getActivityIcon')) {
function getActivityIcon($action) {
    $icons = [
        'CREATE' => 'fa-plus-circle',
        'UPDATE' => 'fa-edit',
        'DELETE' => 'fa-trash',
        'LOGIN' => 'fa-sign-in',
        'LOGOUT' => 'fa-sign-out',
        'BACKUP' => 'fa-database',
        'RESTORE' => 'fa-undo'
    ];
    return isset($icons[$action]) ? $icons[$action] : 'fa-circle';
}
}

/**
 * Fungsi untuk mendapatkan format waktu "yang lalu"
 */
if (!function_exists('getTimeAgo')) {
function getTimeAgo($datetime) {
    if (empty($datetime)) return 'Baru saja';
    
    // Pastikan timezone sudah Asia/Jakarta
    if (date_default_timezone_get() != 'Asia/Jakarta') {
        date_default_timezone_set('Asia/Jakarta');
    }
    
    $timestamp = false;
    try { 
        $dt = new DateTime($datetime); 
        $timestamp = $dt->getTimestamp(); 
    } catch (Exception $e) { 
        $timestamp = @strtotime($datetime); 
    }
    
    if (!$timestamp || $timestamp <= 0) { 
        $timestamp = @strtotime(str_replace('/', '-', $datetime)); 
        if (!$timestamp || $timestamp <= 0) { 
            return 'Baru saja'; 
        } 
    }
    
    $now = time();
    $diff = $now - $timestamp; 
    
    if ($diff < 0) return 'Baru saja';
    if ($diff < 60) return 'Baru saja';
    if ($diff < 3600) return floor($diff/60) . ' menit yang lalu';
    if ($diff < 86400) return floor($diff/3600) . ' jam yang lalu';
    if ($diff < 604800) return floor($diff/86400) . ' hari yang lalu';
    
    return date('d M Y', $timestamp);
}
}
