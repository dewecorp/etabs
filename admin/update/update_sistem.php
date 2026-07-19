<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['ses_username']) || ($_SESSION['ses_level'] ?? '') !== 'Administrator') {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Hanya Administrator yang dapat memperbarui sistem.']);
    exit;
}

if (!class_exists('ZipArchive')) {
    echo json_encode(['success' => false, 'message' => 'Ekstensi PHP ZipArchive tidak tersedia di server hosting.']);
    exit;
}

$rootDir = dirname(dirname(__DIR__));
$action = $_POST['action'] ?? '';

$githubZipUrl = 'https://github.com/dewecorp/etabs/archive/refs/heads/main.zip';

$preservePaths = [
    'inc/koneksi.php',
    'backup',
    'uploads',
    'tmp',
    '.env',
    '.env.local',
];

$skipDirs = ['.git', 'node_modules', 'vendor', 'tmp'];

function updateJsonResponse($success, $message, $extra = [])
{
    echo json_encode(array_merge([
        'success' => (bool) $success,
        'message' => $message,
    ], $extra));
    exit;
}

function updateEnsureTmpDir($rootDir)
{
    $tmpDir = $rootDir . '/tmp';
    if (!is_dir($tmpDir) && !mkdir($tmpDir, 0755, true)) {
        updateJsonResponse(false, 'Folder tmp tidak dapat dibuat. Periksa permission folder aplikasi.');
    }
    if (!is_writable($tmpDir)) {
        updateJsonResponse(false, 'Folder tmp tidak dapat ditulis. Periksa permission folder aplikasi.');
    }
    return $tmpDir;
}

function updateDownloadZip($url, $dest)
{
    if (function_exists('curl_init')) {
        $fp = fopen($dest, 'w+');
        if (!$fp) {
            return 'Tidak dapat membuat file unduhan sementara.';
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_FILE => $fp,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_USERAGENT => 'e-Tabs-Updater/1.0',
        ]);

        $ok = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        fclose($fp);

        if (!$ok || $httpCode !== 200) {
            @unlink($dest);
            return 'Gagal mengunduh update dari GitHub' . ($error ? ': ' . $error : '') . '.';
        }
    } elseif (ini_get('allow_url_fopen')) {
        $context = stream_context_create([
            'http' => [
                'timeout' => 300,
                'user_agent' => 'e-Tabs-Updater/1.0',
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);

        $data = @file_get_contents($url, false, $context);
        if ($data === false || $data === '') {
            @unlink($dest);
            return 'Gagal mengunduh update dari GitHub.';
        }

        if (@file_put_contents($dest, $data) === false) {
            @unlink($dest);
            return 'Gagal menyimpan file unduhan update.';
        }
    } else {
        return 'Server tidak mendukung cURL maupun allow_url_fopen untuk mengunduh update.';
    }

    if (!file_exists($dest) || filesize($dest) < 1024) {
        @unlink($dest);
        return 'File update tidak valid atau terlalu kecil.';
    }

    return true;
}

function updateShouldPreserve($relativePath, $preservePaths)
{
    $relativePath = str_replace('\\', '/', $relativePath);

    foreach ($preservePaths as $preserve) {
        $preserve = str_replace('\\', '/', $preserve);
        if ($relativePath === $preserve || strpos($relativePath, $preserve . '/') === 0) {
            return true;
        }
    }

    return false;
}

function updateShouldSkipSource($relativePath, $skipDirs)
{
    $relativePath = str_replace('\\', '/', $relativePath);
    $parts = explode('/', $relativePath);

    return in_array($parts[0], $skipDirs, true);
}

function updateRemoveDirectory($dir)
{
    if (!is_dir($dir)) {
        return;
    }

    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($items as $item) {
        if ($item->isDir()) {
            @rmdir($item->getPathname());
        } else {
            @unlink($item->getPathname());
        }
    }

    @rmdir($dir);
}

function updateCleanupSession($rootDir)
{
    if (!empty($_SESSION['update_zip']) && file_exists($_SESSION['update_zip'])) {
        @unlink($_SESSION['update_zip']);
    }
    if (!empty($_SESSION['update_extract']) && is_dir($_SESSION['update_extract'])) {
        updateRemoveDirectory($_SESSION['update_extract']);
    }

    unset($_SESSION['update_zip'], $_SESSION['update_extract'], $_SESSION['update_source']);
}

switch ($action) {
    case 'download':
        updateCleanupSession($rootDir);
        $tmpDir = updateEnsureTmpDir($rootDir);
        $zipPath = $tmpDir . '/etabs_update_' . date('Ymd_His') . '.zip';

        $result = updateDownloadZip($githubZipUrl, $zipPath);
        if ($result !== true) {
            updateJsonResponse(false, $result);
        }

        $_SESSION['update_zip'] = $zipPath;
        $_SESSION['update_extract'] = $tmpDir . '/etabs_extract_' . date('Ymd_His');

        updateJsonResponse(true, 'Update berhasil diunduh dari GitHub.');
        break;

    case 'extract':
        $zipPath = $_SESSION['update_zip'] ?? '';
        $extractDir = $_SESSION['update_extract'] ?? '';

        if ($zipPath === '' || !file_exists($zipPath)) {
            updateJsonResponse(false, 'File update tidak ditemukan. Silakan ulangi proses update.');
        }
        if ($extractDir === '') {
            updateJsonResponse(false, 'Folder ekstraksi tidak ditemukan.');
        }

        if (!is_dir($extractDir) && !mkdir($extractDir, 0755, true)) {
            updateJsonResponse(false, 'Folder ekstraksi tidak dapat dibuat.');
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            updateJsonResponse(false, 'File ZIP update tidak dapat dibuka.');
        }

        if (!$zip->extractTo($extractDir)) {
            $zip->close();
            updateJsonResponse(false, 'Gagal mengekstrak file update.');
        }
        $zip->close();

        $sourceDirs = glob($extractDir . '/etabs-*', GLOB_ONLYDIR);
        if (empty($sourceDirs)) {
            updateJsonResponse(false, 'Struktur folder update tidak dikenali.');
        }

        $_SESSION['update_source'] = $sourceDirs[0];
        updateJsonResponse(true, 'File update berhasil diekstrak.');
        break;

    case 'apply':
        $sourceDir = $_SESSION['update_source'] ?? '';
        if ($sourceDir === '' || !is_dir($sourceDir)) {
            updateJsonResponse(false, 'Sumber file update tidak ditemukan.');
        }

        $sourceDir = rtrim(str_replace('\\', '/', $sourceDir), '/');
        $rootDirNormalized = rtrim(str_replace('\\', '/', $rootDir), '/');
        $copied = 0;

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relativePath = substr(str_replace('\\', '/', $item->getPathname()), strlen($sourceDir) + 1);
            if ($relativePath === false || $relativePath === '') {
                continue;
            }

            if (updateShouldSkipSource($relativePath, $skipDirs)) {
                continue;
            }
            if (updateShouldPreserve($relativePath, $preservePaths)) {
                continue;
            }

            $targetPath = $rootDirNormalized . '/' . $relativePath;

            if ($item->isDir()) {
                if (!is_dir($targetPath) && !mkdir($targetPath, 0755, true)) {
                    updateJsonResponse(false, 'Gagal membuat folder: ' . $relativePath);
                }
                continue;
            }

            $targetDir = dirname($targetPath);
            if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true)) {
                updateJsonResponse(false, 'Gagal membuat folder tujuan: ' . $relativePath);
            }

            if (!@copy($item->getPathname(), $targetPath)) {
                updateJsonResponse(false, 'Gagal menyalin file: ' . $relativePath);
            }

            $copied++;
        }

        $koneksiPath = $rootDir . '/inc/koneksi.php';
        if (file_exists($koneksiPath)) {
            include_once $koneksiPath;
            $activityPath = $rootDir . '/inc/activity_log.php';
            if (isset($koneksi) && file_exists($activityPath)) {
                include_once $activityPath;
                if (function_exists('logActivity')) {
                    $userName = $_SESSION['ses_nama'] ?? 'Administrator';
                    logActivity($koneksi, 'UPDATE', 'system', 'Memperbarui sistem dari GitHub (' . $copied . ' file)');
                }
            }
        }

        // Increment versi setiap kali update
        $versionFile = $rootDir . '/inc/version.json';
        $currentVersion = ['v' => date('y') . (int)date('n') . '01'];
        if (is_file($versionFile)) {
            $vData = json_decode(file_get_contents($versionFile), true);
            if ($vData && isset($vData['v']) && preg_match('/^(\d{2})(\d{1,2})(\d{2})$/', $vData['v'], $vm)) {
                $vy = (int)$vm[1]; $vmM = (int)$vm[2]; $vs = (int)$vm[3];
                $cy = (int)date('y'); $cm = (int)date('n');
                if ($vy === $cy && $vmM === $cm) {
                    $currentVersion['v'] = $vy . $cm . str_pad($vs + 1, 2, '0', STR_PAD_LEFT);
                } else {
                    $currentVersion['v'] = $cy . $cm . '01';
                }
            } else {
                $currentVersion['v'] = date('y') . (int)date('n') . '01';
            }
        }
        @file_put_contents($versionFile, json_encode($currentVersion));

        updateJsonResponse(true, 'Update berhasil diterapkan (' . $copied . ' file diperbarui).', [
            'files_updated' => $copied,
        ]);
        break;

    case 'cleanup':
        updateCleanupSession($rootDir);
        updateJsonResponse(true, 'File sementara berhasil dibersihkan.');
        break;

    default:
        updateJsonResponse(false, 'Aksi update tidak valid.');
}
