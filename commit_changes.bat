@echo off
echo ========================================
echo Commit Changes ke Git
echo ========================================
echo.

REM Cek apakah git sudah terinstall
git --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: Git belum terinstall!
    echo Silakan install Git terlebih dahulu dari https://git-scm.com/
    pause
    exit /b 1
)

REM Cek apakah sudah ada git repository
if not exist .git (
    echo ERROR: Git repository belum diinisialisasi!
    echo Jalankan setup_git.bat terlebih dahulu.
    pause
    exit /b 1
)

echo Status perubahan:
git status
echo.
echo.

set /p commit_message="Masukkan pesan commit: "
if "%commit_message%"=="" (
    echo ERROR: Pesan commit tidak boleh kosong!
    pause
    exit /b 1
)

echo.
echo Menambahkan semua perubahan...
git add .
if %errorlevel% neq 0 (
    echo ERROR: Gagal menambahkan file!
    pause
    exit /b 1
)

echo.
echo Membuat commit...
git commit -m "%commit_message%"
if %errorlevel% neq 0 (
    echo ERROR: Gagal membuat commit!
    pause
    exit /b 1
)

echo.
echo ========================================
echo Commit berhasil dibuat!
echo ========================================
echo.
echo Untuk push ke remote repository, gunakan:
echo   git push
echo.
pause

