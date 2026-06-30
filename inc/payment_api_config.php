<?php
// Konfigurasi integrasi ETAB -> Sibayar/SPP hosting.
// Semua sinkron pembayaran harus masuk ke aplikasi Sibayar produksi.

if (!defined('PAYMENT_API_BASE_URL')) define('PAYMENT_API_BASE_URL', 'https://sibayar.misultanfattah.sch.id/api/etab');
if (!defined('PAYMENT_API_KEY')) define('PAYMENT_API_KEY', 'SPP_SECRET_KEY_2026');
if (!defined('PAYMENT_API_KEY_HEADER')) define('PAYMENT_API_KEY_HEADER', 'X-API-KEY');
if (!defined('PAYMENT_API_ENABLED')) define('PAYMENT_API_ENABLED', true);
if (!defined('PAYMENT_API_SUBMIT_ENABLED')) define('PAYMENT_API_SUBMIT_ENABLED', true);
if (!defined('PAYMENT_API_SUBMIT_FORMAT')) define('PAYMENT_API_SUBMIT_FORMAT', 'form');
if (!defined('PAYMENT_API_VERIFY_SUBMIT')) define('PAYMENT_API_VERIFY_SUBMIT', true);
if (!defined('PAYMENT_API_SSL_VERIFY')) define('PAYMENT_API_SSL_VERIFY', false);
if (!defined('PAYMENT_API_DEFAULT_PETUGAS_ID')) define('PAYMENT_API_DEFAULT_PETUGAS_ID', 1);
