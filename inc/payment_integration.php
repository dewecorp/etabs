<?php
/**
 * Integrasi Sistem Pembayaran Siswa
 * Mode mock aktif sampai endpoint dari pengembang sistem bayar disediakan.
 */

// --- Konfigurasi API (isi nanti dari pengembang sistem bayar) ---
define('PAYMENT_API_ENABLED', false);
define('PAYMENT_API_BASE_URL', ''); // contoh: https://bayar.sekolah.sch.id/api
define('PAYMENT_API_KEY', '');

/**
 * Daftar jenis pembayaran siswa (mock / API)
 */
function paymentGetJenisBayar($nis)
{
    if (PAYMENT_API_ENABLED && PAYMENT_API_BASE_URL !== '') {
        return paymentApiRequest('GET', '/siswa/' . urlencode($nis) . '/jenis-bayar');
    }

    return paymentMockJenisBayar($nis);
}

/**
 * Detail field dinamis per jenis bayar (mock / API)
 */
function paymentGetJenisDetail($nis, $jenisId)
{
    if (PAYMENT_API_ENABLED && PAYMENT_API_BASE_URL !== '') {
        return paymentApiRequest('GET', '/siswa/' . urlencode($nis) . '/jenis-bayar/' . urlencode($jenisId));
    }

    return paymentMockJenisDetail($nis, $jenisId);
}

/**
 * Kirim transaksi pembayaran ke sistem bayar (mock / API)
 */
function paymentSubmitTransaksi(array $payload)
{
    if (PAYMENT_API_ENABLED && PAYMENT_API_BASE_URL !== '') {
        return paymentApiRequest('POST', '/transaksi', $payload);
    }

    return paymentMockSubmit($payload);
}

function paymentApiRequest($method, $path, $body = null)
{
    $url = rtrim(PAYMENT_API_BASE_URL, '/') . $path;

    $ch = curl_init($url);
    $headers = [
        'Accept: application/json',
        'Content-Type: application/json',
    ];
    if (PAYMENT_API_KEY !== '') {
        $headers[] = 'Authorization: Bearer ' . PAYMENT_API_KEY;
    }

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }

    $response = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($response === false || $httpCode < 200 || $httpCode >= 300) {
        return [
            'success' => false,
            'message' => 'Gagal menghubungi sistem pembayaran' . ($error ? ': ' . $error : ''),
        ];
    }

    $data = json_decode($response, true);
    if (!is_array($data)) {
        return ['success' => false, 'message' => 'Respons sistem pembayaran tidak valid.'];
    }

    return $data;
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
