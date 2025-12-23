-- Script untuk update tabel tb_riwayat yang sudah ada
-- Menambahkan kolom status dan mengubah struktur tabel

-- Tambahkan kolom status jika belum ada
ALTER TABLE `tb_riwayat` 
ADD COLUMN IF NOT EXISTS `status` enum('Aktif','Tidak Aktif') NOT NULL DEFAULT 'Tidak Aktif' AFTER `petugas`;

-- Ubah tgl_hapus menjadi nullable
ALTER TABLE `tb_riwayat` 
MODIFY `tgl_hapus` datetime DEFAULT NULL;

-- Ubah petugas_hapus menjadi nullable
ALTER TABLE `tb_riwayat` 
MODIFY `petugas_hapus` varchar(20) DEFAULT NULL;

-- Tambahkan index untuk status
ALTER TABLE `tb_riwayat` 
ADD INDEX IF NOT EXISTS `idx_status` (`status`);

-- Update semua data yang ada menjadi Tidak Aktif (karena ini migrasi, data lama dianggap sudah dihapus)
UPDATE `tb_riwayat` SET `status` = 'Tidak Aktif' WHERE `status` IS NULL OR `status` = '';

