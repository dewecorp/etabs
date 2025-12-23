@echo off
echo ========================================
echo Setup Git Repository untuk e-TABS
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

echo [1/5] Mengecek status Git...
if exist .git (
    echo Git repository sudah ada.
) else (
    echo [2/5] Inisialisasi Git repository...
    git init
    if %errorlevel% neq 0 (
        echo ERROR: Gagal menginisialisasi Git repository!
        pause
        exit /b 1
    )
    echo Git repository berhasil diinisialisasi.
)

REM Buat .gitignore jika belum ada
if not exist .gitignore (
    echo [3/5] Membuat file .gitignore...
    (
        echo # Vendor/Composer
        echo /vendor/
        echo composer.lock
        echo.
        echo # IDE
        echo .idea/
        echo .vscode/
        echo *.swp
        echo *.swo
        echo *~
        echo.
        echo # OS
        echo .DS_Store
        echo Thumbs.db
        echo.
        echo # Logs
        echo *.log
        echo.
        echo # Temporary files
        echo *.tmp
        echo *.temp
        echo.
        echo # Backup files
        echo *.bak
        echo *.backup
        echo.
        echo # Database (jika ada file database lokal)
        echo *.sqlite
        echo *.db
        echo.
        echo # Environment files
        echo .env
        echo .env.local
        echo.
        echo # Upload files (sesuaikan dengan kebutuhan)
        echo # uploads/
        echo # files/
    ) > .gitignore
    echo File .gitignore berhasil dibuat.
) else (
    echo [3/5] File .gitignore sudah ada.
)

echo.
echo [4/5] Menambahkan semua file ke staging area...
git add .
if %errorlevel% neq 0 (
    echo ERROR: Gagal menambahkan file!
    pause
    exit /b 1
)

echo.
echo [5/5] Membuat commit pertama...
set /p commit_message="Masukkan pesan commit (atau tekan Enter untuk menggunakan pesan default): "
if "%commit_message%"=="" (
    set commit_message="Initial commit - e-TABS Sistem Tabungan Siswa"
)
git commit -m %commit_message%
if %errorlevel% neq 0 (
    echo ERROR: Gagal membuat commit!
    pause
    exit /b 1
)

echo.
echo ========================================
echo Git repository berhasil disetup!
echo ========================================
echo.
echo Status repository:
git status
echo.
echo.
echo Untuk menambahkan remote repository, gunakan:
echo   git remote add origin [URL_REPOSITORY]
echo   git push -u origin main
echo.
echo Atau jika menggunakan branch master:
echo   git branch -M main
echo   git remote add origin [URL_REPOSITORY]
echo   git push -u origin main
echo.
pause

