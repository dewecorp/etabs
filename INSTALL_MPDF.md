# Cara Install mPDF untuk Ekspor PDF

Karena Composer tidak tersedia di PATH, berikut beberapa cara untuk install mPDF:

## Cara 1: Menggunakan Laragon Terminal

1. Buka Laragon
2. Klik kanan pada project "etabs" di Laragon
3. Pilih "Open Terminal Here" atau "Terminal"
4. Di terminal Laragon, jalankan:
   ```
   composer require mpdf/mpdf
   ```

## Cara 2: Menggunakan Command Prompt Windows

1. Buka Command Prompt (bukan PowerShell)
2. Masuk ke folder project:
   ```
   cd F:\laragon\www\etabs
   ```
3. Jalankan composer dari Laragon:
   ```
   C:\laragon\bin\composer\composer.bat require mpdf/mpdf
   ```
   (Sesuaikan path jika Laragon di lokasi lain)

## Cara 3: Download Manual

1. Download mPDF dari: https://github.com/mpdf/mpdf/releases
2. Extract ke folder `vendor/mpdf/mpdf`
3. Pastikan struktur folder: `vendor/mpdf/mpdf/src/Mpdf.php`

## Catatan

Setelah mPDF terinstall, ekspor PDF akan menghasilkan file PDF langsung tanpa perlu print to PDF di browser.

