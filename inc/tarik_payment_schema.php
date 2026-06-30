<?php
/**
 * Migrasi kolom integrasi pembayaran pada tb_tabungan
 */
function ensureTarikPaymentColumns($koneksi)
{
    $columns = [
        'tujuan_tarik' => "VARCHAR(20) NOT NULL DEFAULT 'lainnya'",
        'jenis_bayar' => "VARCHAR(150) DEFAULT NULL",
        'jenis_bayar_id' => "VARCHAR(50) DEFAULT NULL",
        'keterangan_tarik' => "TEXT DEFAULT NULL",
        'payment_ref' => "VARCHAR(100) DEFAULT NULL",
        'payment_sync' => "VARCHAR(20) NOT NULL DEFAULT 'none'",
        'payment_detail' => "TEXT DEFAULT NULL",
    ];

    foreach ($columns as $col => $definition) {
        $check = @$koneksi->query("SHOW COLUMNS FROM tb_tabungan LIKE '$col'");
        if ($check && $check->num_rows === 0) {
            @$koneksi->query("ALTER TABLE tb_tabungan ADD COLUMN `$col` $definition");
        }
    }
}
