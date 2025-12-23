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
        if (is_object($koneksi) && method_exists($koneksi, 'prepare')) {
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
        
        // Fallback: gunakan mysqli_query langsung
        $user_id = is_resource($koneksi) ? mysqli_real_escape_string($koneksi, $user_id) : (is_object($koneksi) ? $koneksi->real_escape_string($user_id) : $user_id);
        $user_name = is_resource($koneksi) ? mysqli_real_escape_string($koneksi, $user_name) : (is_object($koneksi) ? $koneksi->real_escape_string($user_name) : $user_name);
        $user_level = is_resource($koneksi) ? mysqli_real_escape_string($koneksi, $user_level) : (is_object($koneksi) ? $koneksi->real_escape_string($user_level) : $user_level);
        $action = is_resource($koneksi) ? mysqli_real_escape_string($koneksi, $action) : (is_object($koneksi) ? $koneksi->real_escape_string($action) : $action);
        $table = is_resource($koneksi) ? mysqli_real_escape_string($koneksi, $table) : (is_object($koneksi) ? $koneksi->real_escape_string($table) : $table);
        $record_id = $record_id ? (is_resource($koneksi) ? mysqli_real_escape_string($koneksi, $record_id) : (is_object($koneksi) ? $koneksi->real_escape_string($record_id) : $record_id)) : null;
        $description = is_resource($koneksi) ? mysqli_real_escape_string($koneksi, $description) : (is_object($koneksi) ? $koneksi->real_escape_string($description) : $description);
        $icon = is_resource($koneksi) ? mysqli_real_escape_string($koneksi, $icon) : (is_object($koneksi) ? $koneksi->real_escape_string($icon) : $icon);
        $color = is_resource($koneksi) ? mysqli_real_escape_string($koneksi, $color) : (is_object($koneksi) ? $koneksi->real_escape_string($color) : $color);
        
        $sql = "INSERT INTO tb_activity_log (user_id, user_name, user_level, action, table_name, record_id, description, icon, color, created_at) 
                VALUES ('$user_id', '$user_name', '$user_level', '$action', '$table', " . ($record_id ? "'$record_id'" : "NULL") . ", '$description', '$icon', '$color', NOW())";
        
        if (is_object($koneksi) && method_exists($koneksi, 'query')) {
            $koneksi->query($sql);
        } elseif (is_resource($koneksi)) {
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
        
        if (is_object($koneksi) && method_exists($koneksi, 'query')) {
            $koneksi->query($sql);
        } elseif (is_resource($koneksi)) {
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
        if (is_object($koneksi) && method_exists($koneksi, 'query')) {
            $koneksi->query($sql);
        } elseif (is_resource($koneksi)) {
            mysqli_query($koneksi, $sql);
        }
    } catch (Exception $e) {
        // Silent fail
    }
}
}

/**
 * Ambil jumlah aktivitas terbaru
 */
if (!function_exists('getActivityCount')) {
function getActivityCount($koneksi) {
    try {
        cleanupOldActivities($koneksi);
        if (is_object($koneksi) && method_exists($koneksi, 'query')) {
            $sql = "SELECT COUNT(*) as total FROM tb_activity_log";
            $result = $koneksi->query($sql);
            if ($result && $row = $result->fetch_assoc()) {
                return (int)$row['total'];
            }
        } elseif (is_resource($koneksi)) {
            $sql = "SELECT COUNT(*) as total FROM tb_activity_log";
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
        
        if (is_object($koneksi) && method_exists($koneksi, 'query')) {
            $sql = "SELECT * FROM tb_activity_log ORDER BY created_at DESC LIMIT $limit";
            $result = $koneksi->query($sql);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $activities[] = $row;
                }
            }
        } elseif (is_resource($koneksi)) {
            $sql = "SELECT * FROM tb_activity_log ORDER BY created_at DESC LIMIT $limit";
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
