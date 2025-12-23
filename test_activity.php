<?php
// Test script untuk memastikan activity log berfungsi
session_start();
include "inc/koneksi.php";
include_once "inc/activity_log.php";

echo "<h2>Test Activity Log</h2>";

// Test 1: Buat tabel
echo "<p>1. Creating table... ";
if (function_exists('createActivityTable')) {
    createActivityTable($koneksi);
    echo "OK</p>";
} else {
    echo "FAILED - Function not found</p>";
}

// Test 2: Cek apakah tabel ada
echo "<p>2. Checking if table exists... ";
$sql = "SHOW TABLES LIKE 'tb_activity_log'";
$result = $koneksi->query($sql);
if ($result && $result->num_rows > 0) {
    echo "OK - Table exists</p>";
} else {
    echo "FAILED - Table not found</p>";
}

// Test 3: Log aktivitas test
echo "<p>3. Logging test activity... ";
if (function_exists('logActivity')) {
    $_SESSION['ses_id'] = 1;
    $_SESSION['ses_nama'] = 'Test User';
    $_SESSION['ses_level'] = 'Administrator';
    logActivity($koneksi, 'CREATE', 'test', 'Test activity log');
    echo "OK</p>";
} else {
    echo "FAILED - Function not found</p>";
}

// Test 4: Ambil aktivitas
echo "<p>4. Getting recent activities... ";
if (function_exists('getRecentActivities')) {
    $activities = getRecentActivities($koneksi, 10);
    echo "OK - Found " . count($activities) . " activities</p>";
    if (count($activities) > 0) {
        echo "<ul>";
        foreach ($activities as $activity) {
            echo "<li>" . htmlspecialchars($activity['description']) . " - " . $activity['created_at'] . "</li>";
        }
        echo "</ul>";
    }
} else {
    echo "FAILED - Function not found</p>";
}

echo "<p><strong>Test completed!</strong></p>";
?>

