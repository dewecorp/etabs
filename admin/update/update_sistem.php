<?php
session_start();
ini_set('display_errors', '0');
error_reporting(E_ALL);
@set_time_limit(300);
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['ses_username']) || ($_SESSION['ses_level'] ?? '') !== 'Administrator') {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Hanya Administrator yang dapat memperbarui sistem.']);
    exit;
}

$rootDir = dirname(dirname(__DIR__));
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Catat semua error PHP (termasuk fatal) ke file agar bisa diperiksa
if (!is_dir($rootDir . '/tmp')) {
    @mkdir($rootDir . '/tmp', 0755, true);
}
ini_set('log_errors', '1');
ini_set('error_log', $rootDir . '/tmp/update_error.log');

function updateJsonResponse($success, $message, $extra = [])
{
    while (ob_get_level() > 0) {
        @ob_end_clean();
    }
    echo json_encode(array_merge([
        'success' => (bool) $success,
        'message' => $message,
    ], $extra));
    exit;
}

// Diagnostik lingkungan server untuk update
if ($action === 'status') {
    $tmpDir = $rootDir . '/tmp';
    $env = [
        'php' => PHP_VERSION,
        'zip_archive' => class_exists('ZipArchive'),
        'curl' => function_exists('curl_init'),
        'allow_url_fopen' => (bool) ini_get('allow_url_fopen'),
        'tmp_writable' => is_writable($rootDir),
        'github_error' => null,
        'github_http_code' => null,
        'ssl_insecure_needed' => false,
    ];

    if (function_exists('curl_init')) {
        $ch = curl_init('https://codeload.github.com/dewecorp/etabs/zip/refs/heads/main');
        curl_setopt_array($ch, [
            CURLOPT_NOBODY => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'e-Tabs-Updater/1.0',
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => 30,
        ]);
        $ok = curl_exec($ch);
        $env['github_http_code'] = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if (!$ok && $err && stripos($err, 'ssl') !== false) {
            // Deteksi CA bundle bermasalah: coba tanpa verifikasi SSL
            $ch2 = curl_init('https://codeload.github.com/dewecorp/etabs/zip/refs/heads/main');
            curl_setopt_array($ch2, [
                CURLOPT_NOBODY => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_USERAGENT => 'e-Tabs-Updater/1.0',
                CURLOPT_CONNECTTIMEOUT => 15,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
            ]);
            $ok2 = curl_exec($ch2);
            curl_close($ch2);
            if ($ok2) {
                $env['ssl_insecure_needed'] = true;
                $env['github_error'] = 'CA bundle server bermasalah (' . $err . '). Updater akan pakai fallback tanpa verifikasi SSL.';
            } else {
                $env['github_error'] = $err;
            }
        } elseif (!$ok) {
            $env['github_error'] = $err ?: 'Tidak dapat menghubungi GitHub.';
        }
    }

    updateJsonResponse(true, 'Status lingkungan update.', ['env' => $env]);
}

if (!class_exists('ZipArchive')) {
    echo json_encode(['success' => false, 'message' => 'Ekstensi PHP ZipArchive tidak tersedia di server hosting.']);
    exit;
}

$repoOwner = 'dewecorp';
$repoName = 'etabs';
$branch = 'main';

$preservePaths = [
    'inc/koneksi.php',
    'backup',
    'uploads',
    'tmp',
    '.env',
    '.env.local',
];

$skipDirs = ['.git', 'node_modules', 'vendor', 'tmp'];

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

/**
 * Unduh file ke $dest via cURL dengan fallback SSL.
 * return: [ok(bool), error(string), warn(string)]
 */
function updateCurlToFile($url, $dest)
{
    $fp = fopen($dest, 'w+');
    if (!$fp) {
        return [false, 'Tidak dapat membuat file unduhan sementara.', ''];
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_FILE => $fp,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_TIMEOUT => 300,
        CURLOPT_USERAGENT => 'e-Tabs-Updater/1.0',
        CURLOPT_HTTPHEADER => ['Cache-Control: no-cache'],
    ]);

    $ok = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    fclose($fp);

    // Fallback: CA bundle server bermasalah -> ulang tanpa verifikasi SSL
    if ((!$ok || $httpCode !== 200) && $error && stripos($error, 'ssl') !== false) {
        $fp = fopen($dest, 'w+');
        if ($fp) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_FILE => $fp,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_CONNECTTIMEOUT => 30,
                CURLOPT_TIMEOUT => 300,
                CURLOPT_USERAGENT => 'e-Tabs-Updater/1.0',
            ]);
            $ok = curl_exec($ch);
            $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            fclose($fp);
            if ($ok && $httpCode === 200) {
                return [true, '', 'SSL diverifikasi tanpa CA bundle (fallback).'];
            }
        }
    }

    if (!$ok || $httpCode !== 200) {
        @unlink($dest);
        return [false, 'Gagal mengunduh (HTTP ' . $httpCode . ')' . ($error ? ': ' . $error : '') . '.', ''];
    }

    return [true, '', ''];
}

/**
 * Ambil SHA commit terakhir via API (bebas cache) agar ZIP selalu versi paling baru.
 */
function updateResolveDownloadUrl($owner, $repo, $branch)
{
    $sha = '';
    if (function_exists('curl_init')) {
        foreach ([true, false] as $verify) {
            $ch = curl_init("https://api.github.com/repos/{$owner}/{$repo}/commits/{$branch}");
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => $verify,
                CURLOPT_SSL_VERIFYHOST => $verify ? 2 : 0,
                CURLOPT_CONNECTTIMEOUT => 15,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_USERAGENT => 'e-Tabs-Updater/1.0',
                CURLOPT_HTTPHEADER => [
                    'Accept: application/vnd.github+json',
                    'Cache-Control: no-cache',
                ],
            ]);
            $body = curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($body && $code === 200) {
                $meta = json_decode($body, true);
                if (!empty($meta['sha'])) {
                    $sha = $meta['sha'];
                    break;
                }
            }
            if ($verify && $code !== 403) {
                break; // bukan masalah SSL, jangan ulangi insecure
            }
        }
    }

    if ($sha !== '') {
        // Archive per-SHA tidak pernah stale/cache lama
        return ["https://codeload.github.com/{$owner}/{$repo}/zip/{$sha}", $sha];
    }

    return ["https://github.com/{$owner}/{$repo}/archive/refs/heads/{$branch}.zip", ''];
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
    $parts = explode('/', str_replace('\\', '/', $relativePath));
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

    unset($_SESSION['update_zip'], $_SESSION['update_extract'], $_SESSION['update_source'], $_SESSION['update_sha'], $_SESSION['update_warn']);
}

try {
    switch ($action) {
    case 'download':        updateCleanupSession($rootDir);
        $tmpDir = updateEnsureTmpDir($rootDir);
        $zipPath = $tmpDir . '/etabs_update_' . date('Ymd_His') . '.zip';

        list($url, $sha) = updateResolveDownloadUrl($repoOwner, $repoName, $branch);
        list($ok, $err, $warn) = updateCurlToFile($url, $zipPath);
        if (!$ok) {
            updateJsonResponse(false, $err);
        }

        if (!file_exists($zipPath) || filesize($zipPath) < 1024) {
            @unlink($zipPath);
            updateJsonResponse(false, 'File update tidak valid atau terlalu kecil.');
        }

        $_SESSION['update_zip'] = $zipPath;
        $_SESSION['update_extract'] = $tmpDir . '/etabs_extract_' . date('Ymd_His');
        $_SESSION['update_sha'] = substr($sha, 0, 7);
        $_SESSION['update_warn'] = $warn;

        updateJsonResponse(true, 'Update berhasil diunduh dari GitHub.' . ($sha ? ' Commit: ' . substr($sha, 0, 7) . '.' : ''));
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
        $failed = [];

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
                    $failed[] = $relativePath . ' (gagal membuat folder)';
                }
                continue;
            }

            $targetDir = dirname($targetPath);
            if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true)) {
                $failed[] = $relativePath . ' (gagal membuat folder tujuan)';
                continue;
            }

            // File lama milik user FTP sering tidak bisa ditimpa oleh PHP -> coba chmod dulu
            if (file_exists($targetPath) && !is_writable($targetPath)) {
                @chmod($targetPath, 0644);
            }

            if (!@copy($item->getPathname(), $targetPath)) {
                // Coba sekali lagi setelah longgar permission
                @chmod($targetPath, 0664);
                if (!@copy($item->getPathname(), $targetPath)) {
                    $clear = @pathinfo($targetPath);
                    $why = is_file($targetPath) && !is_writable($targetPath) ? 'file tidak bisa ditulis (kepemilikan/permission)' : 'copy gagal';
                    $failed[] = $relativePath . ' (' . $why . ')';
                } else {
                    @chmod($targetPath, 0644);
                    $copied++;
                }
                continue;
            }

            @chmod($targetPath, 0644);
            $copied++;
        }

        if (!empty($failed)) {
            $contoh = implode(', ', array_slice($failed, 0, 5));
            $sisa = count($failed) > 5 ? ' +' . (count($failed) - 5) . ' file lain' : '';
            updateJsonResponse(false, count($failed) . ' file gagal diperbarui: ' . $contoh . $sisa . '. Ubah permission/kepemilikan file itu (atau hapus lewat File Manager) lalu ulangi update.', [
                'files_failed' => $failed,
                'files_updated' => $copied,
            ]);
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

        // Increment versi setiap kali update (reset tiap ganti tahun)
        $versionFile = $rootDir . '/inc/version.json';
        $currentVersion = ['v' => date('y') . (int)date('n') . '01'];
        if (is_file($versionFile)) {
            $vData = json_decode(file_get_contents($versionFile), true);
            if ($vData && isset($vData['v']) && preg_match('/^(\d{2})(\d{1,2})(\d{2})$/', $vData['v'], $vm)) {
                $vy = (int)$vm[1]; $vs = (int)$vm[3];
                $cy = (int)date('y'); $cm = (int)date('n');
                if ($vy === $cy) {
                    $currentVersion['v'] = $vy . $cm . str_pad($vs + 1, 2, '0', STR_PAD_LEFT);
                } else {
                    $currentVersion['v'] = $cy . $cm . '01';
                }
            } else {
                $currentVersion['v'] = date('y') . (int)date('n') . '01';
            }
        }
        @file_put_contents($versionFile, json_encode($currentVersion));

        $pesan = 'Update berhasil diterapkan (' . $copied . ' file diperbarui).';
        if (!empty($_SESSION['update_sha'])) {
            $pesan .= ' Commit: ' . $_SESSION['update_sha'] . '.';
        }
        if (!empty($_SESSION['update_warn'])) {
            $pesan .= ' Catatan: ' . $_SESSION['update_warn'];
        }

        updateJsonResponse(true, $pesan, [
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
} catch (Throwable $e) {
    updateJsonResponse(false, 'Error internal tahap "' . $action . '": ' . $e->getMessage() . ' (' . basename($e->getFile()) . ':' . $e->getLine() . ')');
}
