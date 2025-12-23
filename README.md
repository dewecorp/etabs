# e-TABS - Sistem Tabungan Siswa

Sistem manajemen tabungan siswa berbasis web dengan fitur lengkap untuk mengelola data siswa, transaksi setoran dan tarikan, serta laporan keuangan.

## Fitur Utama

- **Master Data**
  - Data Siswa (dengan import Excel)
  - Data Kelas
  - Data Pengguna (Administrator & Petugas)
  - Profil Sekolah

- **Transaksi**
  - Setoran Tabungan
  - Tarikan Tabungan
  - View Tabungan per Siswa

- **Laporan**
  - Laporan Keuangan
  - Riwayat Transaksi (dengan status Aktif/Tidak Aktif)

- **Fitur Tambahan**
  - Multiple Edit & Delete dengan checkbox
  - Export ke Excel dan PDF
  - Import Data Siswa dari Excel
  - Activity Log
  - Backup & Restore Database

## Teknologi

- PHP (Native)
- MySQL
- Bootstrap 3
- jQuery
- DataTables
- SweetAlert2
- PhpSpreadsheet (untuk import/export Excel)

## Instalasi

1. Clone repository ini atau extract ke folder web server (XAMPP/Laragon/WAMP)
2. Import database dari file `db/db_tabsis.sql`
3. Konfigurasi koneksi database di file `inc/koneksi.php`
4. Akses aplikasi melalui browser

## Struktur Folder

```
etabs/
├── admin/          # Halaman admin (siswa, kelas, pengguna, profil)
├── petugas/        # Halaman petugas (setoran, tarikan, tabungan, laporan, riwayat)
├── inc/            # File include (koneksi, fungsi helper)
├── db/             # File database SQL
├── plugins/        # Library pihak ketiga
├── dist/           # File distribusi (CSS, JS)
└── index.php       # File utama
```

## Penggunaan Git

### Setup Awal
Jalankan `setup_git.bat` untuk inisialisasi Git repository dan commit pertama.

### Commit Perubahan
Jalankan `commit_changes.bat` untuk commit perubahan yang telah dibuat.

### Push ke Remote
```bash
git remote add origin [URL_REPOSITORY]
git push -u origin main
```

## Catatan

- Pastikan file `.env` atau konfigurasi database tidak di-commit ke repository
- File upload (jika ada) sebaiknya tidak di-commit
- Backup database sebaiknya disimpan terpisah

## Lisensi

Proyek ini untuk keperluan internal sekolah.

