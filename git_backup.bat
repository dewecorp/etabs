@echo off
setlocal
title Git Commit, Push & Backup Utility

echo ========================================
echo    GIT COMMIT, PUSH & BACKUP UTILITY
echo ========================================
echo.

:: 1. Cek Git
where git >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] Git tidak ditemukan. Silakan install Git.
    pause
    exit /b
)

:: 2. Tampilkan Status
echo Status Perubahan:
git status -s
echo.

:: 3. Input Pesan
set /p "msg=Masukkan pesan commit: "
if "%msg%"=="" (
    echo [WARNING] Pesan commit tidak boleh kosong.
    pause
    exit /b
)

:: 4. Konfirmasi
echo.
echo ----------------------------------------
echo Konfirmasi Eksekusi:
echo Pesan Commit : %msg%
echo Aksi         : 1. Git Add ^& Commit
echo                2. Git Push
echo                3. Update Backup ZIP (etabs_backup.zip)
echo ----------------------------------------
echo.

set /p "confirm=Lanjutkan eksekusi? (y/n): "
if /i "%confirm%"=="ya" set confirm=y
if /i "%confirm%"=="yes" set confirm=y
if /i not "%confirm%"=="y" (
    echo Operasi dibatalkan.
    pause
    exit /b
)

:: 5. Eksekusi Git
echo.
echo [1/3] Menjalankan Git Add & Commit...
git add .
git commit -m "%msg%"

echo.
echo [2/3] Menjalankan Git Push...
git push -u origin main

:: 6. Eksekusi Backup (via PowerShell wrapper)
echo.
echo [3/3] Memproses Backup ZIP...

set "backupFile=etabs_backup.zip"
echo    Target: %backupFile%

:: Menggunakan PowerShell untuk compress/update zip
powershell -NoProfile -Command ^
    "$backupFile = '%backupFile%';" ^
    "$exclude = @($backupFile, '.git', '.vs', '.vscode');" ^
    "if (Test-Path $backupFile) { Write-Host '   Mengupdate file backup...' -ForegroundColor Yellow } else { Write-Host '   Membuat file backup baru...' -ForegroundColor Yellow };" ^
    "$items = Get-ChildItem -Path . -Exclude $exclude;" ^
    "try { Compress-Archive -Path $items -DestinationPath $backupFile -Update; Write-Host '   Backup berhasil!' -ForegroundColor Green } catch { Write-Error '   Gagal: ' + $_ }"

echo.
echo ========================================
echo    SEMUA PROSES SELESAI
echo ========================================
pause
