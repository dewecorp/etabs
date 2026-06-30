<?php
/**
 * Integrasi Sistem Pembayaran Siswa / SPP.
 *
 * Konfigurasi bisa diisi lewat environment/.env atau file lokal
 * inc/payment_api_config.php. Jika base URL atau API key kosong, sistem
 * otomatis kembali ke mock agar halaman tetap bisa dipakai saat development.
 */

$paymentLocalConfig = __DIR__ . '/payment_api_config.php';
if (is_file($paymentLocalConfig)) {
    include_once $paymentLocalConfig;
}

function paymentEnv($key, $default = '')
{
    $value = getenv($key);
    if ($value !== false) {
        return $value;
    }

    if (isset($_ENV[$key])) {
        return $_ENV[$key];
    }

    if (isset($_SERVER[$key])) {
        return $_SERVER[$key];
    }

    return $default;
}

function paymentBool($value)
{
    return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
}

function paymentDefine($name, $value)
{
    if (!defined($name)) {
        define($name, $value);
    }
}

paymentDefine('PAYMENT_API_BASE_URL', rtrim((string) paymentEnv('SPP_API_BASE_URL', paymentEnv('PAYMENT_API_BASE_URL', 'https://sibayar.misultanfattah.sch.id/api/etab')), '/'));
paymentDefine('PAYMENT_API_KEY', (string) paymentEnv('SPP_API_KEY', paymentEnv('PAYMENT_API_KEY', 'SPP_SECRET_KEY_2026')));
paymentDefine('PAYMENT_API_ENABLED', paymentBool(paymentEnv('SPP_API_ENABLED', paymentEnv('PAYMENT_API_ENABLED', PAYMENT_API_BASE_URL !== '' && PAYMENT_API_KEY !== '' ? 'true' : 'false'))));
paymentDefine('PAYMENT_API_KEY_HEADER', (string) paymentEnv('SPP_API_KEY_HEADER', 'X-API-KEY'));
paymentDefine('PAYMENT_API_AUTH_SCHEME', (string) paymentEnv('SPP_API_AUTH_SCHEME', 'Bearer'));
paymentDefine('PAYMENT_API_SUBMIT_ENABLED', paymentBool(paymentEnv('SPP_API_SUBMIT_ENABLED', paymentEnv('PAYMENT_API_SUBMIT_ENABLED', 'true'))));
paymentDefine('PAYMENT_API_KEY_QUERY_ENABLED', paymentBool(paymentEnv('SPP_API_KEY_QUERY_ENABLED', 'false')));
paymentDefine('PAYMENT_API_SSL_VERIFY', paymentBool(paymentEnv('SPP_API_SSL_VERIFY', paymentEnv('PAYMENT_API_SSL_VERIFY', 'false'))));
paymentDefine('PAYMENT_API_SUBMIT_FORMAT', strtolower((string) paymentEnv('SPP_API_SUBMIT_FORMAT', 'form')));
paymentDefine('PAYMENT_API_VERIFY_SUBMIT', paymentBool(paymentEnv('SPP_API_VERIFY_SUBMIT', 'true')));
paymentDefine('PAYMENT_API_ACTION_JENIS', (string) paymentEnv('SPP_API_ACTION_JENIS', 'jenis_bayar'));
paymentDefine('PAYMENT_API_ACTION_TAGIHAN', (string) paymentEnv('SPP_API_ACTION_TAGIHAN', 'tagihan'));
paymentDefine('PAYMENT_API_ACTION_TRANSAKSI', (string) paymentEnv('SPP_API_ACTION_TRANSAKSI', 'transaksi'));
paymentDefine('PAYMENT_API_ACTION_BAYAR', (string) paymentEnv('SPP_API_ACTION_BAYAR', 'simpan_pembayaran'));
paymentDefine('PAYMENT_API_ACTION_BAYAR_FALLBACK', (string) paymentEnv('SPP_API_ACTION_BAYAR_FALLBACK', 'simpan_pembayaran'));
paymentDefine('PAYMENT_API_ACTION_BAYAR_FALLBACK_2', (string) paymentEnv('SPP_API_ACTION_BAYAR_FALLBACK_2', 'potongan_tabungan'));

/**
 * Daftar jenis pembayaran siswa (mock / API)
 */
function paymentGetJenisBayar($nis, $kelasSiswa = '')
{
    if (PAYMENT_API_ENABLED && PAYMENT_API_BASE_URL !== '') {
        $response = paymentSppRequest(PAYMENT_API_ACTION_JENIS);
        $jenis = paymentNormalizeJenisBayar($response, ['source' => 'master']);

        if ($nis !== '') {
            $tagihan = paymentSppRequest(PAYMENT_API_ACTION_TAGIHAN, ['nisn' => $nis, 'nis' => $nis]);
            $jenisFromTagihan = paymentNormalizeJenisBayar($tagihan, ['source' => 'tagihan']);
            if (!empty($jenisFromTagihan['success']) && !empty($jenisFromTagihan['data'])) {
                if (!paymentJenisBayarNeedsTagihanFallback($jenis)) {
                    return paymentFilterJenisByKelas(paymentMergeJenisWithTagihan($jenis, $jenisFromTagihan), $kelasSiswa);
                }
                return paymentFilterJenisByKelas($jenisFromTagihan, $kelasSiswa);
            }

            $fallbackTagihan = paymentFallbackTagihanFromRaw($tagihan);
            if (!empty($fallbackTagihan['success']) && !empty($fallbackTagihan['data'])) {
                return $fallbackTagihan;
            }

            paymentDebugWrite($nis, [
                'kelas_siswa' => $kelasSiswa,
                'jenis_response' => $jenis,
                'tagihan_response' => $tagihan,
                'jenis_from_tagihan' => $jenisFromTagihan,
                'fallback_tagihan' => $fallbackTagihan,
            ]);

            return [
                'success' => true,
                'data' => [],
                'message' => 'Tidak ada tagihan dari SPP untuk siswa ini.',
                'raw' => $tagihan,
            ];
        }

        return paymentFilterJenisByKelas($jenis, $kelasSiswa);
    }

    return paymentMockJenisBayar($nis);
}

function paymentDebugWrite($nis, array $payload, $prefix = 'spp_debug_')
{
    if (!PAYMENT_API_ENABLED) {
        return;
    }

    $dir = dirname(__DIR__) . '/tmp';
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }

    if (!is_dir($dir) || !is_writable($dir)) {
        return;
    }

    $safeNis = preg_replace('/[^0-9A-Za-z_-]/', '_', (string) $nis);
    $payload['debug_time'] = date('Y-m-d H:i:s');
    @file_put_contents($dir . '/' . $prefix . $safeNis . '.json', json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

/**
 * Detail field dinamis per jenis bayar (mock / API)
 */
function paymentGetJenisDetail($nis, $jenisId, $kelasSiswa = '')
{
    if (PAYMENT_API_ENABLED && PAYMENT_API_BASE_URL !== '') {
        $response = paymentSppRequest(PAYMENT_API_ACTION_TAGIHAN, ['nisn' => $nis, 'nis' => $nis]);
        return paymentNormalizeJenisDetail($response, $jenisId, $kelasSiswa);
    }

    return paymentMockJenisDetail($nis, $jenisId);
}

/**
 * Kirim transaksi pembayaran ke sistem bayar (mock / API)
 */
function paymentSubmitTransaksi(array $payload)
{
    if (PAYMENT_API_ENABLED && PAYMENT_API_BASE_URL !== '') {
        if (!PAYMENT_API_SUBMIT_ENABLED) {
            return [
                'success' => true,
                'mock' => true,
                'message' => 'Data tagihan terhubung ke SPP. Endpoint simpan pembayaran SPP belum diaktifkan.',
                'ref' => 'SPP-READONLY-' . date('YmdHis') . '-' . substr(md5(json_encode($payload)), 0, 6),
                'data' => [
                    'status' => 'readonly',
                ],
            ];
        }

        return paymentSubmitPayloads($payload, paymentBuildSubmitPayloads($payload));
    }

    return paymentMockSubmit($payload);
}

function paymentSubmitPayloads(array $originalPayload, array $submitPayloads)
{
    if ($submitPayloads === []) {
        return ['success' => false, 'message' => 'Detail pembayaran kosong.'];
    }

    $refs = [];
    $results = [];
    $lastResult = null;

    foreach ($submitPayloads as $submitPayload) {
        $beforeSnapshot = PAYMENT_API_VERIFY_SUBMIT ? paymentGetSubmitTransactionSnapshot($submitPayload) : ['count' => 0, 'sum' => 0, 'refs' => []];
        $actions = array_values(array_unique(array_filter([
            PAYMENT_API_ACTION_BAYAR,
            PAYMENT_API_ACTION_BAYAR_FALLBACK,
            PAYMENT_API_ACTION_BAYAR_FALLBACK_2,
        ])));
        $attempts = [];

        foreach ($actions as $action) {
            $result = paymentSppRequest($action, [], 'POST', $submitPayload);
            $attempts[] = [
                'action' => $action,
                'success' => !empty($result['success']),
                'message' => (string) ($result['message'] ?? ''),
            ];
            if (!empty($result['success'])) {
                $normalized = paymentNormalizeSubmitResponse($result, $submitPayload, $action);
                $looksSuccessful = paymentSubmitResultLooksSuccessful($result);
                $verified = PAYMENT_API_VERIFY_SUBMIT ? paymentVerifySubmitStored($submitPayload, $normalized, $beforeSnapshot) : true;
                if (($looksSuccessful && !PAYMENT_API_VERIFY_SUBMIT) || $verified) {
                    $refs[] = $normalized['ref'] ?? '';
                    $results[] = $normalized;
                    $lastResult = $normalized;
                    continue 2;
                }

                $result = [
                    'success' => false,
                    'message' => $looksSuccessful
                        ? 'Sibayar menerima request, tetapi transaksi belum ditemukan di data transaksi Sibayar.'
                        : 'Sibayar belum mengembalikan konfirmasi simpan pembayaran yang valid.',
                    'raw' => $normalized,
                ];
            }

            $lastResult = $result;
            if (!paymentSubmitShouldTryFallback($result)) {
                break;
            }
        }

        paymentDebugWrite($originalPayload['nis'] ?? '', [
            'submit_payload' => paymentRedactSubmitPayload($submitPayload),
            'submit_response' => $lastResult,
            'attempts' => $attempts,
        ], 'spp_submit_');

        return $lastResult ?: ['success' => false, 'message' => 'Gagal sinkron transaksi ke SPP.'];
    }

    $refs = array_values(array_filter(array_unique($refs)));
    $success = [
        'success' => true,
        'mock' => false,
        'ref' => implode(',', $refs),
        'data' => [
            'ref_transaksi' => implode(',', $refs),
            'items' => $results,
        ],
    ];

    paymentDebugWrite($originalPayload['nis'] ?? '', [
        'submit_payloads' => array_map('paymentRedactSubmitPayload', $submitPayloads),
        'submit_response' => $success,
        'verified' => true,
    ], 'spp_submit_success_');

    return $success;
}

function paymentBuildSubmitPayloads(array $payload)
{
    $items = isset($payload['items']) && is_array($payload['items']) ? $payload['items'] : [];
    if (count($items) <= 1) {
        return [paymentBuildSubmitPayload($payload)];
    }

    $payloads = [];
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }

        $itemId = (string) paymentFirstValue($item, ['id_jenis_bayar', 'jenis_bayar_id', 'id', 'kode'], '');
        $itemName = (string) paymentFirstValue($item, ['nama_pembayaran', 'jenis_bayar', 'nama'], 'Pembayaran');
        $itemAmount = paymentMoneyValue($item, ['sisa', 'nominal', 'jumlah', 'nominal_bayar'], 0);
        $splitPayload = $payload;
        $splitPayload['jenis_bayar_id'] = $itemId;
        $splitPayload['jenis_bayar'] = $itemName;
        $splitPayload['nominal'] = $itemAmount;
        $splitPayload['items'] = [$item];
        $payloads[] = paymentBuildSubmitPayload($splitPayload);
    }

    return $payloads;
}

function paymentBuildSubmitPayload(array $payload)
{
    $nisn = trim((string) paymentFirstValue($payload, ['nisn', 'nis'], ''));
    $tanggal = trim((string) paymentFirstValue($payload, ['tgl_bayar', 'tanggal', 'tgl'], date('Y-m-d')));
    $items = isset($payload['items']) && is_array($payload['items']) ? $payload['items'] : [];
    $detail = paymentBuildSubmitItems($items);
    $nominal = paymentMoneyValue($payload, ['nominal', 'jumlah', 'total'], 0);
    if ($nominal <= 0 && $detail !== []) {
        $nominal = array_sum(array_map(function ($item) {
            return (int) ($item['nominal'] ?? 0);
        }, $detail));
    }

    $ref = 'ETAB-' . date('YmdHis') . '-' . substr(md5($nisn . $tanggal . json_encode($detail)), 0, 8);
    $jenisBayarId = (string) paymentFirstValue($payload, ['jenis_bayar_id', 'id_jenis_bayar'], '');
    $jenisBayar = (string) paymentFirstValue($payload, ['jenis_bayar', 'nama_jenis_bayar'], '');

    return [
        'ref' => $ref,
        'ref_etab' => $ref,
        'kode_transaksi' => $ref,
        'nisn' => $nisn,
        'tanggal' => $tanggal,
        'tgl' => $tanggal,
        'tgl_bayar' => $tanggal,
        'tanggal_bayar' => $tanggal,
        'jenis_bayar_id' => $jenisBayarId,
        'id_jenis_bayar' => $jenisBayarId,
        'kode_jenis_bayar' => $jenisBayarId,
        'jenis_bayar' => $jenisBayar,
        'nama_pembayaran' => $jenisBayar,
        'nominal' => $nominal,
        'jumlah' => $nominal,
        'total' => $nominal,
        'nominal_bayar' => $nominal,
        'jumlah_bayar' => $nominal,
        'total_bayar' => $nominal,
        'metode' => 'potong_tabungan',
        'metode_bayar' => 'potong_tabungan',
        'cara_bayar' => 'potong_tabungan',
        'sumber' => 'etabs_tarikan',
        'petugas' => (string) paymentFirstValue($payload, ['petugas', 'operator'], ''),
        'keterangan' => 'Pembayaran dari potongan tabungan ETAB',
        'items' => $detail,
        'detail' => $detail,
        'rincian' => $detail,
        'items_json' => json_encode($detail, JSON_UNESCAPED_UNICODE),
        'detail_json' => json_encode($detail, JSON_UNESCAPED_UNICODE),
        'rincian_json' => json_encode($detail, JSON_UNESCAPED_UNICODE),
        'bulan' => paymentSubmitFlattenMonths($detail),
        'bulan_json' => json_encode(paymentSubmitFlattenMonths($detail), JSON_UNESCAPED_UNICODE),
    ];
}

function paymentSubmitFlattenMonths(array $items)
{
    $months = [];
    foreach ($items as $item) {
        foreach (($item['rincian'] ?? []) as $row) {
            $month = trim((string) ($row['bulan'] ?? $row['label'] ?? ''));
            if ($month !== '') {
                $months[] = $month;
            }
        }
    }

    return array_values(array_unique($months));
}

function paymentBuildSubmitItems(array $items)
{
    $result = [];

    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }

        $rincian = isset($item['rincian']) && is_array($item['rincian']) ? $item['rincian'] : [];
        $unpaidRows = [];
        foreach ($rincian as $row) {
            if (!is_array($row)) {
                continue;
            }
            $rowNominal = paymentMoneyValue($row, ['sisa', 'nominal', 'jumlah', 'tagihan'], 0);
            if (!empty($row['lunas']) || $rowNominal <= 0) {
                continue;
            }
            $unpaidRows[] = [
                'id' => (string) paymentFirstValue($row, ['id', 'id_tagihan', 'id_detail', 'kode'], ''),
                'id_tagihan' => (string) paymentFirstValue($row, ['id_tagihan', 'id', 'id_detail', 'kode'], ''),
                'label' => (string) paymentFirstValue($row, ['label', 'bulan', 'periode', 'nama'], ''),
                'bulan' => (string) paymentFirstValue($row, ['bulan', 'periode', 'label'], ''),
                'nominal' => $rowNominal,
                'jumlah' => $rowNominal,
                'sisa' => $rowNominal,
            ];
        }

        $itemNominal = paymentMoneyValue($item, ['sisa', 'nominal', 'jumlah', 'tagihan'], 0);
        if ($unpaidRows !== []) {
            $itemNominal = array_sum(array_map(function ($row) {
                return (int) ($row['nominal'] ?? 0);
            }, $unpaidRows));
        }

        if ($itemNominal <= 0) {
            continue;
        }

        $result[] = [
            'id' => (string) paymentFirstValue($item, ['id', 'id_jenis_bayar', 'kode'], ''),
            'id_jenis_bayar' => (string) paymentFirstValue($item, ['id_jenis_bayar', 'id', 'kode'], ''),
            'kode_jenis_bayar' => (string) paymentFirstValue($item, ['kode_jenis_bayar', 'id', 'kode'], ''),
            'jenis_bayar_id' => (string) paymentFirstValue($item, ['jenis_bayar_id', 'id', 'kode'], ''),
            'nama' => (string) paymentFirstValue($item, ['nama', 'jenis_bayar', 'nama_pembayaran'], 'Pembayaran'),
            'jenis_bayar' => (string) paymentFirstValue($item, ['jenis_bayar', 'nama', 'nama_pembayaran'], 'Pembayaran'),
            'nama_pembayaran' => (string) paymentFirstValue($item, ['nama_pembayaran', 'nama', 'jenis_bayar'], 'Pembayaran'),
            'tipe' => (string) paymentFirstValue($item, ['tipe', 'tipe_bayar'], ''),
            'tipe_bayar' => (string) paymentFirstValue($item, ['tipe_bayar', 'tipe'], ''),
            'kelas' => (string) paymentFirstValue($item, ['kelas', 'kelas_siswa'], ''),
            'nominal' => $itemNominal,
            'jumlah' => $itemNominal,
            'nominal_bayar' => $itemNominal,
            'jumlah_bayar' => $itemNominal,
            'rincian' => $unpaidRows,
            'rincian_json' => json_encode($unpaidRows, JSON_UNESCAPED_UNICODE),
            'bulan' => array_values(array_filter(array_map(function ($row) {
                return trim((string) ($row['bulan'] ?? $row['label'] ?? ''));
            }, $unpaidRows))),
        ];
    }

    return $result;
}

function paymentNormalizeSubmitResponse(array $response, array $payload, $action)
{
    $data = isset($response['data']) && is_array($response['data']) ? $response['data'] : $response;
    $ref = (string) paymentFirstValue($data, ['ref', 'ref_transaksi', 'kode_transaksi', 'id_transaksi', 'id'], '');
    if ($ref === '') {
        $ref = (string) paymentFirstValue($payload, ['ref', 'ref_etab', 'kode_transaksi'], '');
    }

    $response['success'] = true;
    $response['mock'] = false;
    $response['ref'] = $ref;
    $response['action'] = $action;
    if (!isset($response['data']) || !is_array($response['data'])) {
        $response['data'] = [];
    }
    $response['data']['ref_transaksi'] = $ref;
    $response['data']['action'] = $action;

    return $response;
}

function paymentSubmitResultLooksSuccessful(array $result)
{
    if (empty($result['raw_html'])) {
        $message = strtolower((string) ($result['message'] ?? $result['pesan'] ?? ''));
        if ($message !== '') {
            return strpos($message, 'gagal') === false && strpos($message, 'error') === false && strpos($message, 'invalid') === false;
        }

        $data = $result['data'] ?? null;
        if (is_array($data) && !paymentIsList($data)) {
            foreach (['ref', 'ref_transaksi', 'kode_transaksi', 'id_transaksi', 'id', 'status'] as $key) {
                if (isset($data[$key]) && $data[$key] !== '') {
                    return true;
                }
            }
        }

        return false;
    }

    $text = strtolower(paymentHtmlToText((string) $result['raw_html']));
    if ($text === '') {
        return false;
    }

    $hasSuccessWord = strpos($text, 'sukses') !== false || strpos($text, 'berhasil') !== false || strpos($text, 'success') !== false;
    $hasFailureWord = strpos($text, 'gagal') !== false || strpos($text, 'error') !== false || strpos($text, 'invalid') !== false || strpos($text, 'login') !== false;

    return $hasSuccessWord && !$hasFailureWord;
}

function paymentGetSubmitTransactionSnapshot(array $payload)
{
    $nisn = (string) paymentFirstValue($payload, ['nisn', 'nis'], '');
    $tanggal = (string) paymentFirstValue($payload, ['tanggal', 'tgl_bayar', 'tgl'], date('Y-m-d'));
    if ($nisn === '') {
        return ['count' => 0, 'sum' => 0, 'refs' => []];
    }

    $response = paymentSppRequest(PAYMENT_API_ACTION_TRANSAKSI, [
        'nisn' => $nisn,
        'tanggal_mulai' => $tanggal,
        'tanggal_sampai' => $tanggal,
    ]);
    $transaksi = paymentNormalizeTransaksi($response);
    if (empty($transaksi['success']) || empty($transaksi['data'])) {
        return ['count' => 0, 'sum' => 0, 'refs' => []];
    }

    $snapshot = ['count' => 0, 'sum' => 0, 'refs' => []];
    foreach ($transaksi['data'] as $row) {
        if (!is_array($row)) {
            continue;
        }
        $rowRef = (string) paymentFirstValue($row, ['ref', 'id', 'kode_transaksi', 'ref_transaksi'], '');
        $rowTanggal = substr((string) ($row['tanggal'] ?? ''), 0, 10);
        $rowNominal = paymentMoneyValue($row, ['nominal', 'jumlah', 'bayar', 'total'], 0);
        if (($rowTanggal === '' || $rowTanggal === $tanggal) && $rowNominal > 0) {
            $snapshot['count']++;
            $snapshot['sum'] += $rowNominal;
            if ($rowRef !== '') {
                $snapshot['refs'][] = $rowRef;
            }
        }
    }

    return $snapshot;
}

function paymentVerifySubmitStored(array $payload, array $submitResponse, array $beforeSnapshot = ['count' => 0, 'sum' => 0, 'refs' => []])
{
    $nominal = paymentMoneyValue($payload, ['nominal', 'jumlah', 'total', 'nominal_bayar'], 0);
    if ($nominal <= 0) {
        return false;
    }

    $afterSnapshot = paymentGetSubmitTransactionSnapshot($payload);
    $expectedRef = (string) paymentFirstValue($submitResponse, ['ref', 'ref_transaksi'], paymentFirstValue($payload, ['ref', 'ref_etab'], ''));

    if ($expectedRef !== '' && in_array($expectedRef, $afterSnapshot['refs'], true) && !in_array($expectedRef, $beforeSnapshot['refs'] ?? [], true)) {
        return true;
    }

    $sumIncrease = (int) ($afterSnapshot['sum'] ?? 0) - (int) ($beforeSnapshot['sum'] ?? 0);
    $countIncrease = (int) ($afterSnapshot['count'] ?? 0) - (int) ($beforeSnapshot['count'] ?? 0);

    return $sumIncrease >= $nominal || ($countIncrease > 0 && $sumIncrease > 0);
}

function paymentSubmitShouldTryFallback($result)
{
    $message = strtolower((string) ($result['message'] ?? ''));
    foreach (['action', 'aksi', 'route', 'not found', 'tidak dikenali', 'method not allowed', 'unknown column', 's.nis', 'gagal menyiapkan query', '500', '404', '405'] as $needle) {
        if (strpos($message, $needle) !== false) {
            return true;
        }
    }

    return false;
}

function paymentRedactSubmitPayload(array $payload)
{
    if (isset($payload['api_key'])) {
        $payload['api_key'] = '***';
    }
    return $payload;
}

function paymentResyncTarik($koneksi, $idTabungan, $petugas = '')
{
    $idTabungan = (int) $idTabungan;
    if ($idTabungan <= 0) {
        return ['success' => false, 'message' => 'ID penarikan tidak valid.'];
    }

    $sql = "SELECT id_tabungan, nis, tarik, tgl, tujuan_tarik, jenis_bayar, jenis_bayar_id, payment_detail FROM tb_tabungan WHERE id_tabungan = " . $idTabungan . " AND jenis = 'TR' LIMIT 1";
    $query = mysqli_query($koneksi, $sql);
    if (!$query || !($row = mysqli_fetch_assoc($query))) {
        return ['success' => false, 'message' => 'Data penarikan tidak ditemukan.'];
    }

    if (($row['tujuan_tarik'] ?? '') !== 'pembayaran') {
        return ['success' => false, 'message' => 'Penarikan ini bukan transaksi pembayaran.'];
    }

    $detail = json_decode((string) ($row['payment_detail'] ?? ''), true);
    $items = is_array($detail) && isset($detail['items']) && is_array($detail['items']) ? $detail['items'] : [];
    if ($items === []) {
        return ['success' => false, 'message' => 'Detail pembayaran lama kosong, tidak bisa sinkron ulang.'];
    }

    $payload = [
        'nis' => $row['nis'],
        'tgl_bayar' => $row['tgl'],
        'jenis_bayar_id' => $row['jenis_bayar_id'],
        'jenis_bayar' => $row['jenis_bayar'],
        'nominal' => (int) $row['tarik'],
        'items' => $items,
        'petugas' => $petugas,
        'sumber' => 'etabs_resync',
    ];

    $result = paymentSubmitTransaksi($payload);
    $sync = empty($result['success']) ? 'failed' : (!empty($result['mock']) ? 'mock' : 'success');
    $ref = mysqli_real_escape_string($koneksi, (string) ($result['ref'] ?? $result['data']['ref_transaksi'] ?? ''));
    $message = (string) ($result['message'] ?? '');

    $setRef = $ref !== '' ? "payment_ref = '" . $ref . "'" : "payment_ref = payment_ref";
    mysqli_query($koneksi, "UPDATE tb_tabungan SET payment_sync = '" . $sync . "', " . $setRef . " WHERE id_tabungan = " . $idTabungan);

    if ($sync !== 'success') {
        return [
            'success' => false,
            'message' => $message !== '' ? $message : 'Sinkron ulang ke Sibayar gagal.',
            'data' => $result,
        ];
    }

    return [
        'success' => true,
        'message' => 'Transaksi lama berhasil disinkronkan ke Sibayar.',
        'ref' => $ref,
        'data' => $result,
    ];
}

/**
 * Query transaksi pembayaran dari SPP.
 * Filter yang didukung API SPP: nisn, tanggal_mulai, tanggal_sampai.
 */
function paymentGetTransaksi(array $filters = [])
{
    $allowed = ['nisn', 'tanggal_mulai', 'tanggal_sampai'];
    $params = [];

    foreach ($allowed as $key) {
        if (isset($filters[$key]) && $filters[$key] !== '') {
            $params[$key] = $filters[$key];
        }
    }

    if (PAYMENT_API_ENABLED && PAYMENT_API_BASE_URL !== '') {
        $response = paymentSppRequest(PAYMENT_API_ACTION_TRANSAKSI, $params);
        return paymentNormalizeTransaksi($response);
    }

    return ['success' => true, 'mock' => true, 'data' => []];
}

function paymentSppRequest($action, array $params = [], $method = 'GET', $body = null)
{
    $params = array_merge(['action' => $action], $params);
    $actionLower = strtolower((string) $action);
    if ($actionLower === strtolower((string) PAYMENT_API_ACTION_TRANSAKSI)) {
        unset($params['nis']);
    }

    if (in_array($actionLower, [strtolower((string) PAYMENT_API_ACTION_BAYAR), strtolower((string) PAYMENT_API_ACTION_BAYAR_FALLBACK)], true) && is_array($body)) {
        unset($body['nis']);
    }

    if (PAYMENT_API_KEY_QUERY_ENABLED && PAYMENT_API_KEY !== '') {
        $params['api_key'] = PAYMENT_API_KEY;
    }
    $url = paymentBuildSppUrl($params);
    $result = paymentApiRequest($method, $url, $body, true);

    if (empty($result['success']) && !PAYMENT_API_KEY_QUERY_ENABLED && PAYMENT_API_KEY !== '') {
        $fallbackParams = array_merge($params, ['api_key' => PAYMENT_API_KEY]);
        $fallback = paymentApiRequest($method, paymentBuildSppUrl($fallbackParams), $body, true);
        if (!empty($fallback['success'])) {
            return $fallback;
        }
    }

    return $result;
}

function paymentBuildSppUrl(array $params)
{
    $separator = strpos(PAYMENT_API_BASE_URL, '?') === false ? '?' : '&';
    return PAYMENT_API_BASE_URL . $separator . http_build_query($params);
}

function paymentApiRequest($method, $path, $body = null, $absoluteUrl = false)
{
    $url = $absoluteUrl ? $path : rtrim(PAYMENT_API_BASE_URL, '/') . $path;
    $method = strtoupper((string) $method);
    $sendAsForm = $body !== null && $method !== 'GET' && PAYMENT_API_SUBMIT_FORMAT === 'form';

    $ch = curl_init($url);
    $headers = [
        'Accept: application/json',
    ];
    if ($body !== null) {
        $headers[] = $sendAsForm ? 'Content-Type: application/x-www-form-urlencoded' : 'Content-Type: application/json';
    }
    if (PAYMENT_API_KEY !== '') {
        $headers[] = PAYMENT_API_KEY_HEADER . ': ' . PAYMENT_API_KEY;
        if (!$absoluteUrl && PAYMENT_API_AUTH_SCHEME !== '') {
            $headers[] = 'Authorization: ' . PAYMENT_API_AUTH_SCHEME . ' ' . PAYMENT_API_KEY;
        }
    }

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_USERAGENT => 'ETABS-SPP-Integration/1.0',
        CURLOPT_SSL_VERIFYPEER => PAYMENT_API_SSL_VERIFY,
        CURLOPT_SSL_VERIFYHOST => PAYMENT_API_SSL_VERIFY ? 2 : 0,
    ]);

    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $sendAsForm ? http_build_query($body) : json_encode($body));
    }

    $response = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = (string) curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($response === false || $httpCode < 200 || $httpCode >= 300) {
        $detail = $error;
        if (!$detail && is_string($response) && trim($response) !== '') {
            $detail = trim(strip_tags($response));
            $detail = preg_replace('/\s+/', ' ', $detail);
            $detail = substr($detail, 0, 180);
        }

        return [
            'success' => false,
            'message' => 'Gagal menghubungi sistem pembayaran' . ($httpCode ? ' (HTTP ' . $httpCode . ')' : '') . ($detail ? ': ' . $detail : ''),
        ];
    }

    $data = json_decode($response, true);
    if (!is_array($data)) {
        $mixedJson = paymentDecodeMixedJsonResponse($response);
        if (is_array($mixedJson)) {
            return paymentNormalizeApiEnvelope($mixedJson);
        }

        $text = is_string($response) ? trim($response) : '';
        if ($text !== '') {
            return [
                'success' => true,
                'raw_html' => $response,
                'content_type' => $contentType,
            ];
        }

        return ['success' => false, 'message' => 'Respons sistem pembayaran tidak valid.'];
    }

    return paymentNormalizeApiEnvelope($data);
}

function paymentDecodeMixedJsonResponse($response)
{
    if (!is_string($response) || trim($response) === '') {
        return null;
    }

    $start = strpos($response, '{');
    $end = strrpos($response, '}');
    if ($start === false || $end === false || $end <= $start) {
        return null;
    }

    $json = substr($response, $start, $end - $start + 1);
    $data = json_decode($json, true);
    return is_array($data) ? $data : null;
}

function paymentNormalizeApiEnvelope($data)
{
    if (!is_array($data)) {
        return ['success' => false, 'message' => 'Respons sistem pembayaran tidak valid.'];
    }

    $status = strtolower((string) ($data['status'] ?? ''));
    $hasSuccessFlag = array_key_exists('success', $data);
    $success = $hasSuccessFlag ? (bool) $data['success'] : !in_array($status, ['error', 'failed', 'gagal'], true);

    if (!$success) {
        return [
            'success' => false,
            'message' => $data['message'] ?? $data['pesan'] ?? 'Sistem pembayaran mengembalikan status gagal.',
            'raw' => $data,
        ];
    }

    $data['success'] = true;
    return $data;
}

function paymentExtractDataList(array $response)
{
    $priority = ['tagihan', 'tagihans', 'data_tagihan', 'pembayaran', 'jenis_bayar', 'jenis', 'items', 'detail', 'details', 'result', 'results', 'data'];
    $list = paymentFindListByKeys($response, $priority);
    if ($list !== []) {
        return $list;
    }

    return paymentArrayToList($response);
}

function paymentFindListByKeys(array $value, array $keys)
{
    foreach ($keys as $key) {
        if (isset($value[$key]) && is_array($value[$key])) {
            if (!paymentIsList($value[$key]) && !paymentLooksLikeRow($value[$key])) {
                $nested = paymentFindListByKeys($value[$key], $keys);
                if ($nested !== []) {
                    return $nested;
                }
            }

            $list = paymentArrayToList($value[$key]);
            if ($list !== [] && !paymentListLooksLikeStudentOnly($list)) {
                return $list;
            }
        }
    }

    foreach ($value as $key => $child) {
        if (!is_array($child) || in_array(strtolower((string) $key), ['siswa', 'student', 'santri', 'profil', 'profile'], true)) {
            continue;
        }

        $nested = paymentFindListByKeys($child, $keys);
        if ($nested !== []) {
            return $nested;
        }
    }

    return [];
}

function paymentListLooksLikeStudentOnly(array $list)
{
    if (count($list) !== 1 || !is_array($list[0])) {
        return false;
    }

    return paymentRowLooksLikeStudentOnly($list[0]);
}

function paymentRowLooksLikeStudentOnly(array $row)
{
    $hasStudent = isset($row['nisn']) || isset($row['nis']) || isset($row['nama_siswa']) || isset($row['kelas']);
    $hasPayment = isset($row['nominal']) || isset($row['tagihan']) || isset($row['sisa']) || isset($row['nama_pembayaran']) || isset($row['jenis_bayar']);
    return $hasStudent && !$hasPayment;
}

function paymentArrayToList(array $value)
{
    if (paymentIsList($value)) {
        return $value;
    }

    if (paymentLooksLikeRow($value)) {
        return [$value];
    }

    $rows = [];
    foreach ($value as $child) {
        if (!is_array($child)) {
            continue;
        }

        if (paymentIsList($child)) {
            foreach ($child as $row) {
                if (is_array($row) && paymentRowLooksLikeStudentOnly($row)) {
                    continue;
                }
                $rows[] = $row;
            }
        } elseif (paymentLooksLikeRow($child) && !paymentRowLooksLikeStudentOnly($child)) {
            $rows[] = $child;
        }
    }

    return $rows;
}

function paymentIsList(array $value)
{
    if ($value === []) {
        return true;
    }

    return array_keys($value) === range(0, count($value) - 1);
}

function paymentLooksLikeRow(array $value)
{
    foreach (['id', 'kode', 'nama', 'nama_bayar', 'nama_pembayaran', 'nama_tagihan', 'nama_biaya', 'nama_pos', 'jenis_bayar', 'jenis', 'nominal', 'nominal_bayar', 'nominal_pembayaran', 'nominal_tagihan', 'jumlah_tagihan', 'sisa', 'sisa_tagihan', 'bulan', 'periode', 'tanggal', 'tgl', 'ref', 'ref_transaksi'] as $key) {
        if (array_key_exists($key, $value)) {
            return true;
        }
    }

    return false;
}

function paymentFirstValue(array $row, array $keys, $default = '')
{
    foreach ($keys as $key) {
        if (isset($row[$key]) && $row[$key] !== '') {
            return $row[$key];
        }
    }

    return $default;
}

function paymentSlug($value)
{
    $value = strtolower(trim((string) $value));
    $value = preg_replace('/[^a-z0-9]+/i', '_', $value);
    return trim($value, '_') ?: 'tagihan';
}

function paymentNormalizeNameKey($value)
{
    $value = strtolower(trim((string) $value));
    $value = str_replace(['ekskul', 'extra kurikuler', 'ekstra kurikuler'], 'ekstrakurikuler', $value);
    $value = preg_replace('/\b(iuran|biaya|bayar|pembayaran|tagihan|uang)\b/i', ' ', $value);
    $value = preg_replace('/[^a-z0-9]+/i', ' ', $value);
    return trim(preg_replace('/\s+/', ' ', $value));
}

function paymentFallbackTagihanFromRaw(array $response)
{
    if (empty($response['success'])) {
        return $response;
    }

    if (!empty($response['raw_html'])) {
        return paymentParseTagihanHtml((string) $response['raw_html'], $response);
    }

    $raw = $response['raw'] ?? $response;
    $rows = [];
    paymentCollectPaymentRowsDeep($raw, $rows);

    $groups = [];
    foreach ($rows as $row) {
        if (!is_array($row) || paymentRowLooksLikeStudentOnly($row)) {
            continue;
        }

        $amount = paymentInferAmount($row);
        if ($amount <= 0) {
            continue;
        }

        $name = paymentInferPaymentName($row);
        $key = paymentNormalizeNameKey($name ?: json_encode(array_keys($row)));
        if ($key === '') {
            $key = 'tagihan_' . count($groups);
        }

        if (!isset($groups[$key])) {
            $groups[$key] = [
                'id' => paymentSlug($name ?: $key),
                'nama' => $name ?: 'Tagihan SPP',
                'tipe' => paymentResolveTipe($row),
                'kelas' => paymentResolveKelasText($row),
                'target_kelas' => paymentResolveTargetClasses($row),
                'keterangan' => '',
                'nominal' => 0,
                'sisa' => 0,
                'nominal_per_bulan' => 0,
                'has_tagihan' => true,
                'lunas' => false,
                'disabled' => false,
                'disabled_reason' => '',
                'rincian' => [],
            ];
        }

        $groups[$key]['nominal'] += $amount;
        $groups[$key]['sisa'] += $amount;
        $groups[$key]['nominal_per_bulan'] = $amount;
        $groups[$key]['rincian'][] = [
            'id' => (string) paymentFirstValue($row, ['id_tagihan', 'id_detail', 'id', 'kode', 'periode'], paymentSlug(paymentInferDetailLabel($row, count($groups[$key]['rincian']) + 1))),
            'label' => paymentInferDetailLabel($row, count($groups[$key]['rincian']) + 1),
            'nominal' => $amount,
            'sisa' => $amount,
            'lunas' => false,
            'tipe' => paymentResolveTipe($row),
            'kelas' => paymentResolveKelasText($row),
        ];
    }

    return [
        'success' => true,
        'data' => array_values($groups),
        'raw' => $response,
        'fallback' => true,
    ];
}

function paymentParseTagihanHtml($html, array $response = [])
{
    $text = paymentHtmlToText($html);
    $kelas = '';
    if (preg_match('/\bKelas\s*:\s*([0-9]+)/i', $text, $match)) {
        $kelas = trim($match[1]);
    }

    $items = [];
    if (preg_match_all('/Jumlah\s+Tagihan\s+([^:\r\n]+?)\s*:\s*Rp\s*([0-9.,]+)/i', $text, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $rawName = trim($match[1]);
            $nominal = paymentNumberValue($match[2]);
            if ($nominal <= 0) {
                continue;
            }

            $name = paymentResolveHtmlTagihanName($rawName);
            $tipe = paymentResolveHtmlTagihanTipe($name, $text);
            $rincian = paymentBuildHtmlTagihanRincian($name, $nominal, $tipe, $text, $kelas);

            $items[] = [
                'id' => paymentSlug($name),
                'nama' => $name,
                'tipe' => $tipe,
                'kelas' => $kelas,
                'target_kelas' => $kelas !== '' ? [$kelas] : [],
                'keterangan' => 'Tagihan dari laporan SPP',
                'nominal' => $nominal,
                'sisa' => $nominal,
                'nominal_per_bulan' => $tipe === 'bulanan' && count($rincian) ? (int) ceil($nominal / count($rincian)) : $nominal,
                'has_tagihan' => true,
                'lunas' => false,
                'disabled' => false,
                'disabled_reason' => '',
                'rincian' => $rincian,
                'source' => 'spp_laporan',
            ];
        }
    }

    return [
        'success' => true,
        'data' => $items,
        'raw' => $response,
        'source' => 'spp_laporan',
    ];
}

function paymentHtmlToText($html)
{
    $html = preg_replace('/<(br|\/tr|\/p|\/div|\/td|\/th)\b[^>]*>/i', "\n", (string) $html);
    $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace('/[ \t]+/', ' ', $text);
    $text = preg_replace('/\s*\n\s*/', "\n", $text);
    return trim($text);
}

function paymentResolveHtmlTagihanName($name)
{
    $lower = strtolower($name);
    if (strpos($lower, 'ekskul') !== false || strpos($lower, 'ekstra') !== false) {
        return 'Iuran Ekstrakurikuler';
    }
    if (strpos($lower, 'lks') !== false && strpos($lower, '4') !== false) {
        return 'LKS Kelas 4-6';
    }
    if (strpos($lower, 'lks') !== false && strpos($lower, '1') !== false) {
        return 'LKS Kelas 1-3';
    }
    if (strpos($lower, 'ujian') !== false) {
        return 'Biaya Ujian 2026';
    }
    if (strpos($lower, 'rekreasi') !== false) {
        return 'Iuran Rekreasi';
    }

    return trim($name) !== '' ? trim($name) : 'Tagihan SPP';
}

function paymentResolveHtmlTagihanTipe($name, $text)
{
    $lower = strtolower($name . ' ' . $text);
    if (strpos(strtolower($name), 'ekstrakurikuler') !== false) {
        return 'bulanan';
    }
    if (strpos($lower, 'cicilan') !== false || strpos(strtolower($name), 'lks') !== false || strpos(strtolower($name), 'ujian') !== false || strpos(strtolower($name), 'rekreasi') !== false) {
        return 'cicilan';
    }

    return 'sekali';
}

function paymentBuildHtmlTagihanRincian($name, $nominal, $tipe, $text, $kelas)
{
    $baseId = paymentSlug($name);
    if ($tipe === 'bulanan') {
        $months = paymentExtractUnpaidMonths($text);
        if ($months !== []) {
            $perMonth = (int) ceil($nominal / count($months));
            $rows = [];
            foreach ($months as $month) {
                $rows[] = [
                    'id' => $baseId . '_' . paymentSlug($month),
                    'label' => $month,
                    'bulan' => $month,
                    'nominal' => $perMonth,
                    'sisa' => $perMonth,
                    'lunas' => false,
                    'tipe' => 'bulanan',
                    'kelas' => $kelas,
                ];
            }
            return $rows;
        }
    }

    return [[
        'id' => $baseId,
        'label' => $tipe === 'cicilan' ? 'Sisa tagihan' : 'Tagihan',
        'nominal' => $nominal,
        'sisa' => $nominal,
        'lunas' => false,
        'tipe' => $tipe,
        'kelas' => $kelas,
    ]];
}

function paymentExtractUnpaidMonths($text)
{
    $monthPattern = 'Juli|Agustus|September|Oktober|November|Desember|Januari|Februari|Maret|April|Mei|Juni';
    $months = [];
    if (preg_match_all('/(?:x|X|×|✕|✗|\*)\s*(' . $monthPattern . ')/u', $text, $matches)) {
        foreach ($matches[1] as $month) {
            $months[] = $month;
        }
    }

    return array_values(array_unique($months));
}

function paymentCollectPaymentRowsDeep($value, array &$rows, $depth = 0)
{
    if ($depth > 8 || !is_array($value)) {
        return;
    }

    if (paymentLooksLikeRow($value) && !paymentRowLooksLikeStudentOnly($value)) {
        $rows[] = $value;
    }

    foreach ($value as $key => $child) {
        if (!is_array($child) || in_array(strtolower((string) $key), ['siswa', 'student', 'santri', 'profil', 'profile'], true)) {
            continue;
        }
        paymentCollectPaymentRowsDeep($child, $rows, $depth + 1);
    }
}

function paymentInferPaymentName(array $row)
{
    $name = paymentResolveJenisName($row);
    if (!paymentIsGenericPaymentName($name)) {
        return $name;
    }

    foreach ($row as $key => $value) {
        if (is_array($value) || is_numeric($value) || paymentIsGenericPaymentName($value)) {
            continue;
        }
        $keyLower = strtolower((string) $key);
        if (strpos($keyLower, 'nama') !== false || strpos($keyLower, 'ket') !== false || strpos($keyLower, 'uraian') !== false || strpos($keyLower, 'title') !== false || strpos($keyLower, 'label') !== false) {
            return (string) $value;
        }
    }

    return $name;
}

function paymentInferDetailLabel(array $row, $index)
{
    $label = paymentResolveDetailLabel($row, $index);
    return $label ?: 'Tagihan ' . $index;
}

function paymentInferAmount(array $row)
{
    $amount = paymentMoneyValue($row, ['sisa', 'sisa_tagihan', 'sisa_bayar', 'sisa_pembayaran', 'belum_bayar', 'kurang', 'kekurangan', 'tunggakan', 'saldo_tagihan', 'nominal_tagihan', 'nominal_bayar', 'nominal_pembayaran', 'jumlah_tagihan', 'jumlah_bayar', 'jumlah_pembayaran', 'total_tagihan', 'besar_tagihan', 'amount', 'bill_amount', 'nominal', 'jumlah', 'tagihan', 'total', 'biaya', 'harga', 'tarif'], 0);
    if ($amount > 0) {
        return $amount;
    }

    foreach ($row as $key => $value) {
        if (is_array($value)) {
            continue;
        }
        $keyLower = strtolower((string) $key);
        if ((strpos($keyLower, 'nominal') !== false || strpos($keyLower, 'jumlah') !== false || strpos($keyLower, 'tagihan') !== false || strpos($keyLower, 'total') !== false || strpos($keyLower, 'biaya') !== false) && paymentNumberValue($value) > 0) {
            return paymentNumberValue($value);
        }
    }

    return 0;
}

function paymentIsGenericPaymentName($value)
{
    $name = strtolower(trim((string) $value));
    return in_array($name, ['', 'pembayaran', 'tagihan', 'jenis bayar', 'jenis pembayaran'], true);
}

function paymentResolveJenisName(array $row)
{
    $preferredKeys = [
        'nama_jenis', 'jenis_pembayaran', 'nama_pembayaran', 'nama_tagihan',
        'nama_biaya', 'biaya', 'nama_pos', 'pos', 'kategori', 'kategori_bayar',
        'jenis_bayar', 'jenis', 'keterangan', 'deskripsi', 'description',
        'nama_bayar', 'name', 'title', 'nama'
    ];

    foreach ($preferredKeys as $key) {
        if (isset($row[$key]) && !paymentIsGenericPaymentName($row[$key])) {
            return (string) $row[$key];
        }
    }

    foreach ($row as $key => $value) {
        if (is_array($value) || paymentIsGenericPaymentName($value)) {
            continue;
        }

        $keyLower = strtolower((string) $key);
        if (strpos($keyLower, 'nama') !== false || strpos($keyLower, 'jenis') !== false || strpos($keyLower, 'tagihan') !== false || strpos($keyLower, 'bayar') !== false || strpos($keyLower, 'pos') !== false || strpos($keyLower, 'kategori') !== false) {
            return (string) $value;
        }
    }

    return (string) paymentFirstValue($row, ['nama', 'nama_bayar', 'jenis_bayar', 'jenis', 'name', 'title'], 'Pembayaran');
}

function paymentResolveJenisId(array $row, $nama)
{
    $id = paymentFirstValue($row, ['id_jenis_bayar', 'kode_jenis_bayar', 'id_jenis', 'jenis_id', 'kode_jenis', 'kode_bayar', 'kode_tagihan', 'kode', 'id'], '');
    return (string) ($id !== '' ? $id : paymentSlug($nama));
}

function paymentNumberValue($value)
{
    if (is_int($value) || is_float($value)) {
        return (int) $value;
    }

    $value = trim((string) $value);
    if (preg_match('/[.,]/', $value)) {
        return (int) preg_replace('/[^0-9]/', '', $value);
    }

    if (is_numeric($value)) {
        return (int) $value;
    }

    return (int) preg_replace('/[^0-9]/', '', (string) $value);
}

function paymentMoneyValue(array $row, array $keys, $default = 0)
{
    foreach ($keys as $key) {
        if (isset($row[$key]) && $row[$key] !== '') {
            return paymentNumberValue($row[$key]);
        }
    }

    if ($default === null) {
        return null;
    }

    return (int) $default;
}

function paymentIsPaidRow(array $row)
{
    $status = strtolower(trim((string) paymentFirstValue($row, ['status', 'status_bayar', 'status_tagihan', 'lunas', 'is_lunas'], '')));
    if (strpos($status, 'belum') !== false || strpos($status, 'kurang') !== false || strpos($status, 'tagihan') !== false) {
        return false;
    }
    if (in_array($status, ['1', 'true', 'lunas', 'paid', 'sudah lunas', 'selesai'], true)) {
        return true;
    }

    $sisa = paymentMoneyValue($row, ['sisa', 'sisa_tagihan', 'sisa_bayar', 'sisa_pembayaran', 'belum_bayar', 'kurang', 'kekurangan', 'tunggakan'], null);
    return $sisa === 0 && paymentMoneyValue($row, ['nominal', 'nominal_bayar', 'nominal_pembayaran', 'nominal_tagihan', 'jumlah_tagihan', 'jumlah_bayar', 'jumlah_pembayaran', 'total_tagihan', 'besar_tagihan', 'saldo_tagihan', 'amount', 'bill_amount', 'jumlah', 'tagihan', 'total', 'biaya', 'harga', 'tarif'], 0) > 0;
}

function paymentResolveTipe(array $row)
{
    $tipe = strtolower(trim((string) paymentFirstValue($row, ['tipe_bayar', 'tipe', 'type', 'jenis_tipe', 'model_bayar', 'sistem_bayar', 'waktu_bayar'], '')));
    if (strpos($tipe, 'bulan') !== false || paymentFirstValue($row, ['bulan', 'periode', 'nama_bulan'], '') !== '') {
        return 'bulanan';
    }
    if (strpos($tipe, 'cicil') !== false || paymentFirstValue($row, ['cicilan', 'angsuran', 'termin', 'ke'], '') !== '') {
        return 'cicilan';
    }

    return $tipe !== '' ? $tipe : 'sekali';
}

function paymentResolveKelasText(array $row)
{
    $raw = paymentFirstValue($row, ['tagihan_kelas', 'tagihan_kepada', 'kepada', 'kelas_tujuan', 'kelas_tagihan', 'target_kelas', 'untuk_kelas', 'kelas', 'nama_kelas', 'kelas_siswa'], '');
    if (is_array($raw)) {
        return implode(', ', array_map('strval', $raw));
    }

    return (string) $raw;
}

function paymentExtractClassNumbers($value)
{
    if (is_array($value)) {
        $numbers = [];
        foreach ($value as $item) {
            $numbers = array_merge($numbers, paymentExtractClassNumbers($item));
        }
        return array_values(array_unique($numbers));
    }

    $text = strtolower(trim((string) $value));
    if ($text === '' || in_array($text, ['semua', 'all', 'semua kelas'], true)) {
        return [];
    }

    // Label seperti "6 Kelas" berarti semua 6 kelas, bukan khusus kelas 6.
    if (preg_match('/^\s*\d+\s+kelas\s*$/i', $text)) {
        return [];
    }

    $numbers = [];
    if (preg_match_all('/(\d+)\s*[-–]\s*(\d+)/', $text, $ranges, PREG_SET_ORDER)) {
        foreach ($ranges as $range) {
            $start = (int) $range[1];
            $end = (int) $range[2];
            if ($start > $end) {
                [$start, $end] = [$end, $start];
            }
            for ($i = $start; $i <= $end; $i++) {
                $numbers[] = $i;
            }
        }
        $text = preg_replace('/\d+\s*[-–]\s*\d+/', ' ', $text);
    }

    preg_match_all('/\d+/', $text, $matches);
    $numbers = array_merge($numbers, array_map('intval', $matches[0] ?? []));

    $romanMap = [
        'i' => 1, 'ii' => 2, 'iii' => 3, 'iv' => 4, 'v' => 5, 'vi' => 6,
        'vii' => 7, 'viii' => 8, 'ix' => 9, 'x' => 10, 'xi' => 11, 'xii' => 12,
    ];
    foreach ($romanMap as $roman => $number) {
        if (preg_match('/\b' . preg_quote($roman, '/') . '\b/i', $text)) {
            $numbers[] = $number;
        }
    }

    return array_values(array_unique($numbers));
}

function paymentResolveTargetClasses(array $row)
{
    foreach (['tagihan_kelas', 'tagihan_kepada', 'kepada', 'kelas_tujuan', 'kelas_tagihan', 'target_kelas', 'untuk_kelas', 'kelas'] as $key) {
        if (isset($row[$key]) && $row[$key] !== '') {
            return paymentExtractClassNumbers($row[$key]);
        }
    }

    return [];
}

function paymentClassNumber($kelasSiswa)
{
    $numbers = paymentExtractClassNumbers($kelasSiswa);
    return $numbers[0] ?? null;
}

function paymentAppliesToKelas(array $item, $kelasSiswa)
{
    $kelas = paymentClassNumber($kelasSiswa);
    $targets = $item['target_kelas'] ?? [];
    if ($kelas === null || empty($targets)) {
        return true;
    }

    return in_array($kelas, array_map('intval', $targets), true);
}

function paymentResolveDetailLabel(array $row, $index)
{
    $label = paymentFirstValue($row, ['label', 'rincian', 'nama_detail', 'bulan', 'periode', 'nama_bulan', 'cicilan', 'angsuran', 'termin', 'ke'], '');
    if ($label !== '') {
        return (string) $label;
    }

    $tipe = paymentResolveTipe($row);
    return $tipe === 'cicilan' ? 'Cicilan ' . $index : ($tipe === 'bulanan' ? 'Bulan ' . $index : 'Tagihan ' . $index);
}

function paymentBuildJenisFromRows(array $rows, array $context = [])
{
    $source = $context['source'] ?? 'tagihan';
    $first = $rows[0];
    $nama = paymentResolveJenisName($first);
    $id = paymentResolveJenisId($first, $nama);
    $tipe = paymentResolveTipe($first);
    $kelas = paymentResolveKelasText($first);
    $targetKelas = paymentResolveTargetClasses($first);
    $rincian = [];
    $total = 0;
    $sisa = 0;
    $unpaid = 0;
    $paid = 0;

    foreach ($rows as $index => $row) {
        $unpaidItems = paymentFirstValue($row, ['item_belum_bayar', 'belum_bayar_items', 'bulan_belum_bayar'], []);
        if (is_array($unpaidItems) && $unpaidItems !== []) {
            $itemSisaTotal = paymentMoneyValue($row, ['sisa', 'sisa_tagihan', 'sisa_bayar', 'sisa_pembayaran', 'belum_bayar', 'kurang', 'kekurangan', 'tunggakan'], 0);
            $rowTipe = paymentResolveTipe($row);
            $perItem = paymentMoneyValue($row, ['nominal_per_bulan', 'nominal', 'tarif', 'biaya'], 0);

            if ($itemSisaTotal > 0 && ($rowTipe !== 'bulanan' || $perItem <= 0 || ($perItem * count($unpaidItems)) > $itemSisaTotal)) {
                $perItem = (int) ceil($itemSisaTotal / count($unpaidItems));
            }

            foreach ($unpaidItems as $detailIndex => $detailLabel) {
                $label = is_array($detailLabel)
                    ? (string) paymentFirstValue($detailLabel, ['label', 'bulan', 'periode', 'nama'], 'Tagihan ' . ($detailIndex + 1))
                    : (string) $detailLabel;

                $detailNominal = is_array($detailLabel)
                    ? paymentMoneyValue($detailLabel, ['sisa', 'nominal', 'jumlah', 'tagihan'], $perItem)
                    : $perItem;

                if ($detailNominal <= 0) {
                    continue;
                }

                $total += $detailNominal;
                $sisa += $detailNominal;
                $unpaid++;
                $rincian[] = [
                    'id' => (string) paymentFirstValue($row, ['id_tagihan', 'id_detail', 'id', 'kode', 'kode_jenis_bayar'], paymentSlug($nama)) . '_' . paymentSlug($label),
                    'label' => $label,
                    'bulan' => $label,
                    'nominal' => $detailNominal,
                    'sisa' => $detailNominal,
                    'lunas' => false,
                    'tipe' => $rowTipe,
                    'kelas' => paymentResolveKelasText($row),
                ];
            }

            continue;
        }

        $itemNominal = paymentMoneyValue($row, ['sisa', 'sisa_tagihan', 'sisa_bayar', 'sisa_pembayaran', 'belum_bayar', 'kurang', 'kekurangan', 'tunggakan', 'saldo_tagihan', 'nominal_tagihan', 'nominal_bayar', 'nominal_pembayaran', 'jumlah_tagihan', 'jumlah_bayar', 'jumlah_pembayaran', 'total_tagihan', 'besar_tagihan', 'amount', 'bill_amount', 'nominal', 'jumlah', 'tagihan', 'total', 'biaya', 'harga', 'tarif'], 0);
        $itemTotal = paymentMoneyValue($row, ['nominal_tagihan', 'nominal_bayar', 'nominal_pembayaran', 'jumlah_tagihan', 'jumlah_bayar', 'jumlah_pembayaran', 'total_tagihan', 'besar_tagihan', 'amount', 'bill_amount', 'nominal', 'jumlah', 'tagihan', 'total', 'biaya', 'harga', 'tarif'], $itemNominal);
        $isPaid = $source === 'master' ? false : paymentIsPaidRow($row);
        $itemSisa = $isPaid ? 0 : $itemNominal;

        $total += $itemTotal;
        $sisa += $itemSisa;
        $isPaid ? $paid++ : $unpaid++;

        $rincian[] = [
            'id' => (string) paymentFirstValue($row, ['id_tagihan', 'id_detail', 'id', 'kode', 'periode'], paymentSlug(paymentResolveDetailLabel($row, $index + 1))),
            'label' => paymentResolveDetailLabel($row, $index + 1),
            'nominal' => $itemTotal,
            'sisa' => $itemSisa,
            'lunas' => $isPaid,
            'tipe' => paymentResolveTipe($row),
            'kelas' => paymentResolveKelasText($row),
        ];
    }

    $hasTagihan = $source !== 'master' && $unpaid > 0 && $sisa > 0;

    return [
        'id' => $id,
        'nama' => $nama,
        'tipe' => $tipe,
        'kelas' => $kelas,
        'target_kelas' => $targetKelas,
        'keterangan' => (string) paymentFirstValue($first, ['keterangan', 'deskripsi', 'description'], ''),
        'kali_cicilan' => (int) paymentFirstValue($first, ['kali_cicilan', 'jumlah_cicilan', 'cicilan'], 0),
        'nominal' => $total,
        'sisa' => $source === 'master' ? 0 : $sisa,
        'nominal_per_bulan' => count($rincian) ? (int) ceil($sisa / max(1, $unpaid)) : $sisa,
        'has_tagihan' => $hasTagihan,
        'lunas' => $source !== 'master' && !$hasTagihan && ($paid > 0 || $sisa <= 0),
        'disabled' => !$hasTagihan,
        'disabled_reason' => $hasTagihan ? '' : ($source === 'master' ? 'Tidak ada tagihan' : ($paid > 0 ? 'Tidak ada tagihan' : 'Tidak tersedia untuk kelas siswa')),
        'rincian' => $rincian,
    ];
}

function paymentJenisBayarNeedsTagihanFallback(array $jenis)
{
    if (empty($jenis['success']) || empty($jenis['data']) || !is_array($jenis['data'])) {
        return true;
    }

    $names = [];
    foreach ($jenis['data'] as $item) {
        $name = trim((string) ($item['nama'] ?? ''));
        if ($name !== '') {
            $names[] = strtolower($name);
        }
    }

    return count(array_unique($names)) <= 1 && isset($names[0]) && paymentIsGenericPaymentName($names[0]);
}

function paymentMergeJenisWithTagihan(array $jenis, array $tagihan)
{
    $merged = [];
    $tagihanMap = [];

    foreach (($tagihan['data'] ?? []) as $item) {
        $key = paymentNormalizeNameKey($item['nama'] ?? $item['id'] ?? '');
        $tagihanMap[$key] = $item;
    }

    foreach (($jenis['data'] ?? []) as $item) {
        $key = paymentNormalizeNameKey($item['nama'] ?? $item['id'] ?? '');
        if (isset($tagihanMap[$key])) {
            $merged[] = array_merge($item, $tagihanMap[$key]);
            unset($tagihanMap[$key]);
            continue;
        }

        $item['has_tagihan'] = false;
        $item['lunas'] = false;
        $item['disabled'] = true;
        $item['disabled_reason'] = $item['disabled_reason'] ?? 'Tidak tersedia untuk kelas siswa';
        $item['sisa'] = 0;
        $item['rincian'] = [];
        $merged[] = $item;
    }

    foreach ($tagihanMap as $item) {
        $merged[] = $item;
    }

    return [
        'success' => true,
        'data' => $merged,
        'raw' => [
            'jenis' => $jenis['raw'] ?? $jenis,
            'tagihan' => $tagihan['raw'] ?? $tagihan,
        ],
    ];
}

function paymentFilterJenisByKelas(array $jenis, $kelasSiswa)
{
    if (empty($jenis['success']) || empty($jenis['data']) || !is_array($jenis['data'])) {
        return $jenis;
    }

    $filtered = [];
    foreach ($jenis['data'] as $item) {
        if (!is_array($item)) {
            continue;
        }

        if (!paymentAppliesToKelas($item, $kelasSiswa)) {
            continue;
        }

        // Yang tampil sebagai kartu hanya tagihan aktif yang bisa dibayar.
        // Jika lunas/tidak ada sisa, UI cukup menampilkan "Tidak ada tagihan".
        if (empty($item['has_tagihan'])) {
            continue;
        }

        $filtered[] = $item;
    }

    $jenis['data'] = $filtered;
    return $jenis;
}

function paymentMasterJenisAsTagihan(array $jenis)
{
    $jenis['data'] = [];
    $jenis['fallback_master'] = false;
    $jenis['message'] = 'Master jenis bayar tidak dipakai sebagai tagihan.';
    return $jenis;
}

function paymentFallbackMasterIsPaid(array $item)
{
    $name = strtolower((string) ($item['nama'] ?? ''));
    return strpos($name, 'ujian') !== false || strpos($name, 'rekreasi') !== false;
}

function paymentBuildFallbackMasterRincian(array $item, $nominal)
{
    $tipe = $item['tipe'] ?? 'sekali';
    $baseId = (string) ($item['id'] ?? paymentSlug($item['nama'] ?? 'tagihan'));

    if ($tipe === 'bulanan') {
        $months = ['Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni'];
        $paidFallback = (int) paymentEnv('SPP_FALLBACK_PAID_MONTHS', '2');
        $rows = [];
        foreach ($months as $index => $month) {
            $isPaid = $index < $paidFallback;
            $rows[] = [
                'id' => $baseId . '_' . paymentSlug($month),
                'label' => $month,
                'nominal' => $nominal,
                'sisa' => $isPaid ? 0 : $nominal,
                'lunas' => $isPaid,
                'tipe' => 'bulanan',
                'kelas' => $item['kelas'] ?? '',
            ];
        }
        return $rows;
    }

    if ($tipe === 'cicilan') {
        $count = max(1, (int) ($item['kali_cicilan'] ?? 1));
        $label = $count > 1 ? 'Kurang ' . $count . 'x cicilan' : 'Kurang';
        return [[
            'id' => $baseId . '_cicilan',
            'label' => $label,
            'nominal' => $nominal,
            'sisa' => $nominal,
            'lunas' => false,
            'tipe' => 'cicilan',
            'kelas' => $item['kelas'] ?? '',
        ]];
    }

    return [[
        'id' => $baseId,
        'label' => 'Kurang',
        'nominal' => $nominal,
        'sisa' => $nominal,
        'lunas' => false,
        'tipe' => $tipe,
        'kelas' => $item['kelas'] ?? '',
    ]];
}

function paymentNormalizeJenisBayar(array $response, array $context = [])
{
    if (empty($response['success'])) {
        return $response;
    }

    $items = paymentExtractDataList($response);
    $normalized = [];
    $groups = [];

    foreach ($items as $row) {
        if (!is_array($row)) {
            if ($row !== '') {
                $normalized[] = [
                    'id' => paymentSlug($row),
                    'nama' => (string) $row,
                    'tipe' => 'sekali',
                    'keterangan' => '',
                    'nominal_per_bulan' => 0,
                ];
            }
            continue;
        }

        $nama = paymentResolveJenisName($row);
        $id = paymentResolveJenisId($row, $nama);
        $dedupeKey = paymentIsGenericPaymentName($nama)
            ? strtolower(paymentSlug($id !== '' ? $id : $nama))
            : strtolower(paymentSlug($nama));

        if (!isset($groups[$dedupeKey])) {
            $groups[$dedupeKey] = [];
        }
        $groups[$dedupeKey][] = $row;
    }

    foreach ($groups as $rows) {
        $normalized[] = paymentBuildJenisFromRows($rows, $context);
    }

    return [
        'success' => true,
        'data' => $normalized,
        'raw' => $response,
    ];
}

function paymentNormalizeJenisDetail(array $response, $jenisId, $kelasSiswa = '')
{
    if (empty($response['success'])) {
        return $response;
    }

    $items = paymentExtractDataList($response);
    $matches = [];
    $fallback = [];

    foreach ($items as $row) {
        if (!is_array($row)) {
            continue;
        }

        $nama = paymentResolveJenisName($row);
        $id = paymentResolveJenisId($row, $nama);
        $isMatch = strtolower($id) === strtolower((string) $jenisId) || strtolower(paymentSlug($nama)) === strtolower((string) $jenisId);

        if ($isMatch) {
            $matches[] = $row;
        }

        if ($fallback === []) {
            $fallback = $row;
        }
    }

    if ($matches === [] && $fallback !== []) {
        $matches[] = $fallback;
    }

    if ($matches === []) {
        return ['success' => false, 'message' => 'Tagihan siswa tidak ditemukan di SPP.'];
    }

    $jenis = paymentBuildJenisFromRows($matches, ['source' => 'tagihan']);
    if (!paymentAppliesToKelas($jenis, $kelasSiswa)) {
        return ['success' => false, 'message' => 'Tagihan tidak tersedia untuk kelas siswa ini.'];
    }
    $first = $matches[0];
    $bulanRows = paymentFirstValue($first, ['bulan', 'bulan_list', 'periode', 'tagihan_bulan'], null);
    $bulan = [];

    if (is_array($bulanRows)) {
        foreach ($bulanRows as $bulanRow) {
            if (is_array($bulanRow)) {
                $bulanNama = (string) paymentFirstValue($bulanRow, ['nama', 'bulan', 'periode', 'label'], 'Bulan');
                $bulanId = (string) paymentFirstValue($bulanRow, ['id', 'kode', 'bulan', 'periode'], paymentSlug($bulanNama));
                $lunasRaw = paymentFirstValue($bulanRow, ['lunas', 'is_lunas', 'status'], false);
                $bulan[] = ['id' => $bulanId, 'nama' => $bulanNama, 'lunas' => in_array(strtolower((string) $lunasRaw), ['1', 'true', 'lunas', 'paid'], true)];
            } else {
                $bulan[] = ['id' => paymentSlug($bulanRow), 'nama' => (string) $bulanRow, 'lunas' => false];
            }
        }
    } elseif (count($matches) > 1 || paymentFirstValue($first, ['bulan', 'periode'], '') !== '') {
        foreach ($matches as $row) {
            $bulanNama = (string) paymentFirstValue($row, ['bulan', 'periode', 'nama_bulan'], 'Bulan');
            $bulanId = (string) paymentFirstValue($row, ['id_tagihan', 'id', 'kode', 'bulan', 'periode'], paymentSlug($bulanNama));
            $lunasRaw = paymentFirstValue($row, ['lunas', 'is_lunas', 'status'], false);
            $bulan[] = ['id' => $bulanId, 'nama' => $bulanNama, 'lunas' => in_array(strtolower((string) $lunasRaw), ['1', 'true', 'lunas', 'paid'], true)];
        }
    }

    $nominal = (int) ($jenis['nominal_per_bulan'] ?? paymentFirstValue($first, ['nominal_per_bulan', 'nominal', 'jumlah', 'tagihan', 'sisa'], 0));
    $hasBulan = $bulan !== [];

    return [
        'success' => true,
        'data' => array_merge($jenis, [
            'judul_panel' => $jenis['nama'],
            'field_bulan' => $hasBulan,
            'label_bulan' => 'Bayar Bulan',
            'nominal_per_bulan' => $nominal,
            'bulan' => $bulan,
        ]),
        'raw' => $response,
    ];
}

function paymentNormalizeTransaksi(array $response)
{
    if (empty($response['success'])) {
        return $response;
    }

    $items = paymentExtractDataList($response);
    $normalized = [];

    foreach ($items as $row) {
        if (!is_array($row)) {
            continue;
        }

        $normalized[] = [
            'id' => (string) paymentFirstValue($row, ['id', 'id_transaksi', 'ref', 'ref_transaksi'], ''),
            'ref' => (string) paymentFirstValue($row, ['ref', 'ref_transaksi', 'kode_transaksi', 'id_transaksi'], ''),
            'nisn' => (string) paymentFirstValue($row, ['nisn', 'nis'], ''),
            'nama' => (string) paymentFirstValue($row, ['nama', 'nama_siswa'], ''),
            'jenis_bayar' => (string) paymentFirstValue($row, ['jenis_bayar', 'nama_bayar', 'jenis', 'nama_jenis'], ''),
            'tanggal' => (string) paymentFirstValue($row, ['tanggal', 'tgl', 'tgl_bayar', 'created_at'], ''),
            'nominal' => (int) paymentFirstValue($row, ['nominal', 'jumlah', 'bayar', 'total'], 0),
            'status' => (string) paymentFirstValue($row, ['status', 'status_bayar'], ''),
            'raw' => $row,
        ];
    }

    return [
        'success' => true,
        'data' => $normalized,
        'raw' => $response,
    ];
}

function paymentMockJenisBayar($nis)
{
    return [
        'success' => true,
        'mock' => true,
        'data' => [
            [
                'id' => 'spp',
                'nama' => 'SPP',
                'tipe' => 'bulanan',
                'keterangan' => 'SPP Bulanan',
                'nominal_per_bulan' => 150000,
            ],
            [
                'id' => 'ekstra',
                'nama' => 'Iuran Ekstrakurikuler',
                'tipe' => 'bulanan',
                'keterangan' => 'Bulanan',
                'nominal_per_bulan' => 75000,
            ],
            [
                'id' => 'seragam',
                'nama' => 'Uang Seragam',
                'tipe' => 'sekali',
                'keterangan' => 'Sekali bayar',
                'nominal' => 350000,
                'sisa' => 350000,
            ],
            [
                'id' => 'kegiatan',
                'nama' => 'Kegiatan Sekolah',
                'tipe' => 'sekali',
                'keterangan' => 'Study tour',
                'nominal' => 500000,
                'sisa' => 200000,
            ],
        ],
    ];
}

function paymentMockJenisDetail($nis, $jenisId)
{
    $bulan = [
        ['id' => '01', 'nama' => 'Januari', 'lunas' => true],
        ['id' => '02', 'nama' => 'Februari', 'lunas' => true],
        ['id' => '03', 'nama' => 'Maret', 'lunas' => true],
        ['id' => '04', 'nama' => 'April', 'lunas' => true],
        ['id' => '05', 'nama' => 'Mei', 'lunas' => true],
        ['id' => '06', 'nama' => 'Juni', 'lunas' => false],
        ['id' => '07', 'nama' => 'Juli', 'lunas' => false],
        ['id' => '08', 'nama' => 'Agustus', 'lunas' => false],
        ['id' => '09', 'nama' => 'September', 'lunas' => false],
        ['id' => '10', 'nama' => 'Oktober', 'lunas' => false],
        ['id' => '11', 'nama' => 'November', 'lunas' => false],
        ['id' => '12', 'nama' => 'Desember', 'lunas' => false],
    ];

    $map = [
        'spp' => [
            'id' => 'spp',
            'nama' => 'SPP',
            'judul_panel' => 'SPP (Bulanan)',
            'tipe' => 'bulanan',
            'field_bulan' => true,
            'label_bulan' => 'Bayar Bulan',
            'nominal_per_bulan' => 150000,
            'bulan' => $bulan,
        ],
        'ekstra' => [
            'id' => 'ekstra',
            'nama' => 'Iuran Ekstrakurikuler',
            'judul_panel' => 'Iuran Ekstrakurikuler (Bulanan)',
            'tipe' => 'bulanan',
            'field_bulan' => true,
            'label_bulan' => 'Bayar Bulan',
            'nominal_per_bulan' => 75000,
            'bulan' => $bulan,
        ],
        'seragam' => [
            'id' => 'seragam',
            'nama' => 'Uang Seragam',
            'judul_panel' => 'Uang Seragam',
            'tipe' => 'sekali',
            'field_bulan' => false,
            'nominal' => 350000,
            'sisa' => 350000,
        ],
        'kegiatan' => [
            'id' => 'kegiatan',
            'nama' => 'Kegiatan Sekolah',
            'judul_panel' => 'Kegiatan Sekolah',
            'tipe' => 'sekali',
            'field_bulan' => false,
            'nominal' => 500000,
            'sisa' => 200000,
        ],
    ];

    if (!isset($map[$jenisId])) {
        return ['success' => false, 'message' => 'Jenis pembayaran tidak ditemukan.'];
    }

    return [
        'success' => true,
        'mock' => true,
        'data' => $map[$jenisId],
    ];
}

function paymentMockSubmit(array $payload)
{
    $ref = 'MOCK-' . date('YmdHis') . '-' . substr(md5(json_encode($payload)), 0, 6);

    return [
        'success' => true,
        'mock' => true,
        'message' => 'Transaksi pembayaran berhasil (mode mock).',
        'ref' => $ref,
        'data' => [
            'ref_transaksi' => $ref,
            'status' => 'lunas',
        ],
    ];
}

function paymentHitungNominal(array $detail, array $bulanDipilih = [])
{
    if (($detail['tipe'] ?? '') === 'bulanan') {
        $perBulan = (int) ($detail['nominal_per_bulan'] ?? 0);
        $jumlahBulan = count($bulanDipilih);
        return $perBulan * $jumlahBulan;
    }

    return (int) ($detail['sisa'] ?? $detail['nominal'] ?? 0);
}
