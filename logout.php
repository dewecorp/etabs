<?php
    session_start();
    
    // Log aktivitas logout sebelum session dihancurkan
    include "inc/koneksi.php";
    include_once "inc/activity_log.php";
    if (function_exists('logActivity') && isset($koneksi)) {
        logActivity($koneksi, 'LOGOUT', 'system', 'User logout: ' . (isset($_SESSION['ses_nama']) ? $_SESSION['ses_nama'] : 'Unknown'));
    }
    
    session_destroy();
    echo "<script>location='login.php'</script>";
?>