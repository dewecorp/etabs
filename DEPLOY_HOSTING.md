# Panduan Deploy ke Hosting

## Masalah yang Sering Terjadi Setelah Deploy

### 1. Profil Tidak Tampil
### 2. Data Aktivitas Tidak Tampil  
### 3. Data Siswa Tidak Ada

## Solusi

### Langkah 1: Konfigurasi Database

Edit file `inc/config_db.php` atau `inc/koneksi.php` dengan informasi database hosting Anda:

```php
// Untuk Hosting (Production)
define('DB_HOST', 'localhost'); // atau IP server database
define('DB_USER', 'username_database_hosting');
define('DB_PASS', 'password_database_hosting');
define('DB_NAME', 'nama_database_hosting');
```

### Langkah 2: Import Database

1. Export database dari local (phpMyAdmin)
2. Import ke database hosting melalui phpMyAdmin atau cPanel
3. Pastikan semua tabel ter-import dengan benar:
   - `tb_profil`
   - `tb_siswa`
   - `tb_kelas`
   - `tb_pengguna`
   - `tb_tabungan`
   - `tb_activity_log` (akan dibuat otomatis jika belum ada)

### Langkah 3: Cek File Permission

Pastikan folder `uploads/logo/` memiliki permission yang benar (755 atau 777)

### Langkah 4: Cek Error Log

Cek error log di hosting untuk melihat error yang terjadi:
- cPanel → Error Log
- Atau cek file `error_log` di root folder

### Langkah 5: Aktifkan Error Reporting (Sementara)

Tambahkan di awal file `index.php` untuk debugging:

```php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

**PENTING:** Matikan kembali setelah selesai debugging untuk keamanan!

### Langkah 6: Cek Case Sensitivity

Linux hosting case-sensitive. Pastikan:
- Nama file sesuai case: `data_siswa.php` bukan `Data_Siswa.php`
- Nama folder sesuai case: `admin/siswa/` bukan `Admin/Siswa/`

### Langkah 7: Cek Include Path

Pastikan semua include path benar:
- `include "inc/koneksi.php"` (relative path)
- Atau gunakan `include __DIR__ . "/inc/koneksi.php"` (absolute path)

## Checklist Sebelum Deploy

- [ ] Database sudah di-import ke hosting
- [ ] Konfigurasi database sudah diubah di `inc/config_db.php`
- [ ] File permission sudah benar (folder uploads)
- [ ] Semua file sudah di-upload ke hosting
- [ ] Case sensitivity sudah diperhatikan
- [ ] Error log sudah dicek

## Troubleshooting

### Jika Profil Tidak Tampil:
1. Cek apakah tabel `tb_profil` ada di database hosting
2. Cek apakah ada data di tabel `tb_profil`
3. Cek error log untuk melihat error yang terjadi

### Jika Data Siswa Tidak Ada:
1. Cek apakah tabel `tb_siswa` ada di database hosting
2. Cek apakah data sudah di-import dengan benar
3. Cek query di `admin/siswa/data_siswa.php` apakah ada error

### Jika Data Aktivitas Tidak Tampil:
1. Cek apakah tabel `tb_activity_log` ada (akan dibuat otomatis)
2. Cek file `inc/activity_log.php` apakah ter-include dengan benar
3. Cek error log untuk melihat error yang terjadi

