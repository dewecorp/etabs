<?php
/**
 * Script untuk membuat tabel tb_riwayat secara otomatis
 * Jalankan file ini sekali untuk membuat tabel di database
 */

include "../inc/koneksi.php";

// Query untuk membuat tabel tb_riwayat
$sql = "CREATE TABLE IF NOT EXISTS `tb_riwayat` (
  `id_riwayat` int(11) NOT NULL AUTO_INCREMENT,
  `id_tabungan_asli` int(11) NOT NULL COMMENT 'ID dari tb_tabungan',
  `nis` char(12) NOT NULL,
  `setor` int(11) NOT NULL,
  `tarik` int(11) NOT NULL,
  `tgl` date NOT NULL,
  `jenis` enum('ST','TR') NOT NULL,
  `petugas` varchar(20) NOT NULL,
  `status` enum('Aktif','Tidak Aktif') NOT NULL DEFAULT 'Aktif' COMMENT 'Aktif jika masih ada di tb_tabungan, Tidak Aktif jika sudah dihapus',
  `tgl_hapus` datetime DEFAULT NULL COMMENT 'Tanggal dan waktu ketika data dihapus',
  `petugas_hapus` varchar(20) DEFAULT NULL COMMENT 'Petugas yang menghapus data',
  PRIMARY KEY (`id_riwayat`),
  KEY `id_tabungan_asli` (`id_tabungan_asli`),
  KEY `nis` (`nis`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1";

if (mysqli_query($koneksi, $sql)) {
    echo "<h2 style='color: green;'>✓ Tabel tb_riwayat berhasil dibuat!</h2>";
    echo "<p><a href='../index.php'>Kembali ke Aplikasi</a></p>";
} else {
    echo "<h2 style='color: red;'>✗ Error: " . mysqli_error($koneksi) . "</h2>";
}

mysqli_close($koneksi);
?>

