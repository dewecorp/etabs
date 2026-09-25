<?php
// Konfigurasi integrasi ETAB -> Sibayar/SPP hosting & SIMAD.
// Otomatis membaca dari database (Pengaturan Endpoint) jika tersedia.

if (file_exists(__DIR__ . '/koneksi.php')) {
    require_once __DIR__ . '/koneksi.php';
    if (isset($koneksi) && $koneksi && !$koneksi->connect_error) {
        $resEp = @$koneksi->query("SELECT kode_app, base_url, api_key, status FROM tb_endpoint_masuk");
        if ($resEp && $resEp->num_rows > 0) {
            while ($rowEp = $resEp->fetch_assoc()) {
                $kode = strtolower($rowEp['kode_app']);
                $urlVal = trim($rowEp['base_url']);
                
                if ($kode === 'sibayar') {
                    $apiKeyVal = trim($rowEp['api_key']);
                    if (strpos($urlVal, '?') !== false) {
                        $parsed = parse_url($urlVal);
                        if (isset($parsed['query'])) {
                            parse_str($parsed['query'], $queryParams);
                            if (empty($apiKeyVal) && !empty($queryParams['api_key'])) {
                                $apiKeyVal = $queryParams['api_key'];
                            }
                        }
                        $urlVal = strtok($urlVal, '?');
                    }
                    if (!empty($urlVal) && strpos($urlVal, '/api/') === false && substr($urlVal, -4) !== '.php') {
                        $urlVal = rtrim($urlVal, '/') . '/api/etab.php';
                    }
                    if (!defined('PAYMENT_API_BASE_URL')) define('PAYMENT_API_BASE_URL', $urlVal);
                    if (!defined('PAYMENT_API_KEY')) define('PAYMENT_API_KEY', $apiKeyVal);
                    if (!defined('PAYMENT_API_ENABLED')) define('PAYMENT_API_ENABLED', (bool)$rowEp['status']);
                } else if ($kode === 'simad') {
                    $apiKeyVal = trim($rowEp['api_key']);
                    if (strpos($urlVal, '?') !== false) {
                        $parsed = parse_url($urlVal);
                        if (isset($parsed['query'])) {
                            parse_str($parsed['query'], $queryParams);
                            if (empty($apiKeyVal) && !empty($queryParams['api_key'])) {
                                $apiKeyVal = $queryParams['api_key'];
                            }
                        }
                        $urlVal = strtok($urlVal, '?');
                    }
                    if (!empty($urlVal) && strpos($urlVal, '/api/') === false && substr($urlVal, -4) !== '.php') {
                        $urlVal = rtrim($urlVal, '/') . '/api/v1/students.php';
                    }
                    if (!defined('SIMAD_API_BASE_URL')) define('SIMAD_API_BASE_URL', $urlVal);
                    if (!defined('SIMAD_API_KEY')) define('SIMAD_API_KEY', $apiKeyVal);
                    if (!defined('SIMAD_API_ENABLED')) define('SIMAD_API_ENABLED', (bool)$rowEp['status']);
                }
            }
        }
    }
}

if (!defined('PAYMENT_API_BASE_URL')) define('PAYMENT_API_BASE_URL', 'https://sibayar.misultanfattah.sch.id/api/etab.php');
if (!defined('PAYMENT_API_KEY')) define('PAYMENT_API_KEY', 'SPP_SECRET_KEY_2026');
if (!defined('PAYMENT_API_KEY_HEADER')) define('PAYMENT_API_KEY_HEADER', 'X-API-KEY');
if (!defined('PAYMENT_API_ENABLED')) define('PAYMENT_API_ENABLED', true);
if (!defined('PAYMENT_API_SUBMIT_ENABLED')) define('PAYMENT_API_SUBMIT_ENABLED', true);
if (!defined('PAYMENT_API_SUBMIT_FORMAT')) define('PAYMENT_API_SUBMIT_FORMAT', 'form');
if (!defined('PAYMENT_API_VERIFY_SUBMIT')) define('PAYMENT_API_VERIFY_SUBMIT', true);
if (!defined('PAYMENT_API_SSL_VERIFY')) define('PAYMENT_API_SSL_VERIFY', false);
if (!defined('PAYMENT_API_DEFAULT_PETUGAS_ID')) define('PAYMENT_API_DEFAULT_PETUGAS_ID', 1);

// Konfigurasi integrasi SIMAD -> ETAB (API Tabungan)
if (!defined('SIMAD_API_BASE_URL')) define('SIMAD_API_BASE_URL', 'https://etabs.misultanfattah.sch.id/api/simad.php');
if (!defined('SIMAD_API_KEY')) define('SIMAD_API_KEY', 'SIMAD_SECRET_KEY_2026');
if (!defined('SIMAD_API_KEY_HEADER')) define('SIMAD_API_KEY_HEADER', 'X-API-KEY');
if (!defined('SIMAD_API_ENABLED')) define('SIMAD_API_ENABLED', true);
