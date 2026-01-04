# Perbedaan Localhost vs Hosting dan Solusinya

## 🔍 Mengapa di Localhost Normal Tapi di Hosting Aneh?

### Perbedaan Utama:

#### 1. **Case Sensitivity**
- **Windows (Localhost):** Case-insensitive → `Data_Siswa.php` = `data_siswa.php`
- **Linux (Hosting):** Case-sensitive → `Data_Siswa.php` ≠ `data_siswa.php`

**Solusi:**
- Pastikan semua nama file dan folder menggunakan lowercase atau sesuai case yang benar
- Contoh: `admin/siswa/data_siswa.php` bukan `Admin/Siswa/Data_Siswa.php`

#### 2. **Path Separator**
- **Windows:** Menggunakan backslash `\`
- **Linux:** Menggunakan forward slash `/`
- **PHP:** Menggunakan `DIRECTORY_SEPARATOR` atau `/` (PHP otomatis convert)

**Solusi:**
- Gunakan forward slash `/` di semua path (PHP akan convert otomatis)
- Atau gunakan `DIRECTORY_SEPARATOR` untuk cross-platform
- Lebih baik lagi: gunakan `__DIR__` untuk path absolut

#### 3. **Include Path**
- **Localhost:** Path relatif biasanya bekerja dengan baik
- **Hosting:** Path relatif bisa bermasalah karena struktur folder berbeda

**Solusi:**
```php
// ❌ BURUK (path relatif)
include "../inc/koneksi.php";

// ✅ BAIK (path absolut dengan __DIR__)
include __DIR__ . "/../inc/koneksi.php";

// ✅ LEBIH BAIK (dengan pengecekan)
$path = __DIR__ . "/../inc/koneksi.php";
if (file_exists($path)) {
    include $path;
} else {
    include "../inc/koneksi.php"; // fallback
}
```

#### 4. **PHP Version**
- **Localhost:** Mungkin PHP 7.x atau 8.x
- **Hosting:** Bisa berbeda versi PHP

**Solusi:**
- Cek versi PHP di hosting dengan `phpinfo()`
- Pastikan versi PHP hosting mendukung semua fitur yang digunakan
- Test dengan script `diagnose_hosting.php`

#### 5. **Error Reporting**
- **Localhost:** Error biasanya ditampilkan
- **Hosting:** Error biasanya disembunyikan untuk keamanan

**Solusi:**
- Aktifkan error reporting sementara untuk debugging:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```
- **PENTING:** Matikan kembali setelah selesai debugging!

#### 6. **File Permissions**
- **Windows:** Permission tidak terlalu ketat
- **Linux:** Permission sangat penting

**Solusi:**
- Set permission folder `uploads/logo/` ke **755** atau **777**
- Gunakan File Manager di cPanel atau command:
```bash
chmod 755 uploads
chmod 755 uploads/logo
```

#### 7. **Database Configuration**
- **Localhost:** `localhost`, `root`, password kosong
- **Hosting:** Bisa berbeda (cek di cPanel)

**Solusi:**
- Edit `inc/config_db.php` dengan informasi database hosting
- Atau edit `inc/koneksi.php` langsung

## 🛠️ Langkah-Langkah Perbaikan

### Step 1: Gunakan Script Diagnosa
1. Upload `diagnose_hosting.php` ke hosting
2. Akses: `https://yourdomain.com/diagnose_hosting.php`
3. Lihat semua informasi dan error yang ditemukan

### Step 2: Perbaiki Path Include
File yang sudah diperbaiki:
- ✅ `home/admin.php` - menggunakan `__DIR__`
- ✅ `admin/aktivitas/aktivitas.php` - menggunakan path absolut

File lain yang mungkin perlu diperbaiki:
- Cek semua file yang menggunakan `include` atau `require` dengan path relatif
- Ganti dengan path absolut menggunakan `__DIR__`

### Step 3: Cek Case Sensitivity
1. Pastikan semua nama file lowercase
2. Cek apakah ada file dengan case yang salah
3. Rename jika perlu

### Step 4: Import Database
1. Export database dari local (phpMyAdmin)
2. Import ke hosting (phpMyAdmin/cPanel)
3. **PENTING:** Pastikan semua data ter-import, bukan hanya struktur tabel

### Step 5: Set Permission
```bash
# Via cPanel File Manager atau SSH
chmod 755 uploads
chmod 755 uploads/logo
chmod 644 *.php  # untuk file PHP
```

### Step 6: Konfigurasi Database
Edit `inc/config_db.php`:
```php
define('DB_HOST', 'localhost'); // atau IP server database
define('DB_USER', 'username_hosting');
define('DB_PASS', 'password_hosting');
define('DB_NAME', 'nama_database_hosting');
```

### Step 7: Test Query
Gunakan script `check_database.php` untuk test:
- Koneksi database
- Tabel yang ada
- Jumlah data di setiap tabel

## 🐛 Masalah Umum dan Solusinya

### Masalah 1: Data Siswa Tidak Muncul
**Penyebab:**
- Query menggunakan INNER JOIN, siswa tanpa kelas tidak muncul
- Data tidak ter-import dengan benar

**Solusi:**
- ✅ Sudah diperbaiki: Query menggunakan LEFT JOIN
- Pastikan semua data siswa ter-import

### Masalah 2: Profil Tidak Tampil
**Penyebab:**
- Query error tidak terlihat
- Path logo salah

**Solusi:**
- ✅ Sudah diperbaiki: Error handling ditambahkan
- Cek path logo di `home/admin.php`

### Masalah 3: Aktivitas Tidak Tampil
**Penyebab:**
- Tabel `tb_activity_log` belum dibuat
- Query error

**Solusi:**
- ✅ Sudah diperbaiki: Tabel dibuat otomatis
- Error handling ditambahkan

### Masalah 4: Menu Tidak Bisa Diklik
**Penyebab:**
- JavaScript AdminLTE tidak ter-load
- Konflik dengan script lain

**Solusi:**
- ✅ Sudah diperbaiki: Fallback JavaScript ditambahkan
- Pastikan file `dist/js/app.min.js` ter-load

## 📋 Checklist Sebelum Deploy

- [ ] Semua path include menggunakan `__DIR__` atau path absolut
- [ ] Nama file dan folder menggunakan lowercase
- [ ] Database sudah di-import (semua tabel + data)
- [ ] Konfigurasi database sudah diubah di `inc/config_db.php`
- [ ] Permission folder `uploads/logo/` sudah di-set ke 755
- [ ] Test dengan `diagnose_hosting.php`
- [ ] Test dengan `check_database.php`
- [ ] Error reporting dimatikan setelah debugging

## 🔒 Keamanan

**PENTING:** Setelah selesai debugging, HAPUS file-file berikut:
- ❌ `diagnose_hosting.php`
- ❌ `check_database.php`
- ❌ `fix_hosting_issues.php`
- ❌ Matikan error reporting di production

## 📞 Bantuan

Jika masih ada masalah:
1. Cek error log di cPanel
2. Gunakan `diagnose_hosting.php` untuk diagnosa detail
3. Kirimkan output dari script diagnosa untuk analisis lebih lanjut

