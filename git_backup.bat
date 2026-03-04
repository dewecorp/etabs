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
echo [1/3] Menjalankan Git Add ^& Commit...
git add .
git commit -m "%msg%"

echo.
echo [2/3] Menjalankan Git Push (Paksa)...
git push -u origin main --force

:: 6. Eksekusi Backup (via PowerShell wrapper)
echo.
echo [3/3] Memproses Backup ZIP...

set "backupFile=etabs_backup.zip"
echo    Target: %backupFile%
echo    (Menunggu antrian proses file...)
timeout /t 2 /nobreak >nul

:: Menggunakan PowerShell dengan .NET ZipFile untuk hasil ZIP yang lebih standar dan bersih (Menghindari false positive virus)
powershell -NoProfile -Command ^
    "$backupFile = '%backupFile%';" ^
    "if (Test-Path $backupFile) { Remove-Item $backupFile -Force };" ^
    "Write-Host '   Membuat file backup standar (NET ZipFile)...' -ForegroundColor Yellow;" ^
    "Add-Type -AssemblyName 'System.IO.Compression.FileSystem';" ^
    "$exclude = @($backupFile, '.git', '.vs', '.vscode', '.gitignore', 'node_modules', '*.zip', '*.bat', 'DEPLOY_HOSTING.md');" ^
    "$zip = [System.IO.Compression.ZipFile]::Open($backupFile, 'Create');" ^
    "Get-ChildItem -Path . -Recurse | Where-Object { -not $_.PSIsContainer } | ForEach-Object {" ^
    "    $filePath = $_.FullName;" ^
    "    $relPath = Resolve-Path -Path $filePath -Relative;" ^
    "    $relPath = $relPath.Replace('.\', '').Replace('\', '/');" ^
    "    $skip = $false;" ^
    "    foreach ($ex in $exclude) {" ^
    "        if ($relPath -like $ex -or $relPath -like ($ex + '/*') -or $relPath.StartsWith('.git/')) { $skip = $true; break }" ^
    "    }" ^
    "    if (-not $skip) {" ^
    "        [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($zip, $filePath, $relPath, 'Optimal')" ^
    "    }" ^
    "};" ^
    "$zip.Dispose();" ^
    "Write-Host '   Backup berhasil! (etabs_backup.zip)' -ForegroundColor Green"

echo.
echo ========================================
echo    SEMUA PROSES SELESAI
echo ========================================
pause
