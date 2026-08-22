<?php
session_start();
include "inc/koneksi.php";

$sql = $koneksi->query("SELECT * from tb_profil");
$data= $sql->fetch_assoc();

$nama=$data['nama_sekolah'];
$alamat=$data['alamat'];
// Handle Logo & Background dynamic
$logo_path = (isset($data['logo_sekolah']) && !empty($data['logo_sekolah'])) ? 'uploads/logo/' . $data['logo_sekolah'] : 'images/logo.png';
$bg_path = (isset($data['bg_login']) && !empty($data['bg_login'])) ? 'uploads/bg/' . $data['bg_login'] : 'images/bg_sf.jpg';
?>


<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Login | e-Tabs</title>
    <link rel="icon" href="<?php echo $logo_path; ?>">
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        .auth-bg {
            background:
                radial-gradient(1200px 600px at 10% -10%, rgba(16, 185, 129, 0.20), transparent 60%),
                radial-gradient(1000px 500px at 110% 110%, rgba(5, 150, 105, 0.18), transparent 60%),
                linear-gradient(rgba(255, 255, 255, 0.82), rgba(248, 250, 252, 0.92)), url('<?php echo $bg_path; ?>');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        .auth-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 28px;
            box-shadow: 0 25px 60px -15px rgba(15, 23, 42, 0.15), 0 8px 20px -8px rgba(16, 185, 129, 0.15);
        }
        .auth-input {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #1e293b;
            padding: 12px 16px;
            border-radius: 14px;
            width: 100%;
            transition: all 0.2s ease;
        }
        .auth-input::placeholder {
            color: #94a3b8;
        }
        .auth-input:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
            background-color: #ffffff;
        }
        .btn-login {
            background-image: linear-gradient(to right, #047857, #10b981);
            color: #ffffff;
            border-radius: 14px;
            box-shadow: 0 10px 20px -8px rgba(5, 150, 105, 0.55);
            transition: all 0.2s ease;
        }
        .btn-login:hover {
            background-image: linear-gradient(to right, #065f46, #059669);
            transform: translateY(-1px);
            box-shadow: 0 14px 26px -8px rgba(5, 150, 105, 0.6);
        }
        html, body {
            height: 100%;
            margin: 0;
            overflow: hidden;
        }
        @media (max-height: 600px) {
            html, body {
                overflow-y: auto;
            }
        }
    </style>
</head>

<body class="hold-transition auth-bg h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="auth-card px-7 py-8">
            <div class="text-center mb-6">
                <img src="<?php echo $logo_path; ?>" alt="Logo" class="h-20 mx-auto object-contain mb-4 drop-shadow-sm" onerror="this.src='images/logo.png'">
                <h2 class="text-2xl font-bold text-slate-800 tracking-tight">E-Tabungan Siswa</h2>
                <p class="text-emerald-600 text-sm font-semibold mt-1 uppercase tracking-wide"><?= $nama ?></p>
            </div>


            <form action="#" method="post" class="space-y-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-slate-500 ml-1">Username</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                            <i class="fa-solid fa-user text-xs"></i>
                        </span>
                        <input type="text" class="auth-input pl-10" name="username" placeholder="Masukkan username" required autocomplete="username">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-slate-500 ml-1">Password</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                            <i class="fa-solid fa-lock text-xs"></i>
                        </span>
                        <input type="password" class="auth-input pl-10" name="password" placeholder="••••••••" required autocomplete="current-password">
                    </div>
                </div>

                <button type="submit" class="btn-login w-full mt-5 flex items-center justify-center gap-2 py-3 font-semibold" name="btnLogin">
                    <span>Masuk</span>
                    <i class="fa-solid fa-arrow-right-to-bracket text-xs"></i>
                </button>
            </form>
            
            <div class="mt-6 pt-4 border-t border-slate-200/80 text-center">
                <p class="text-[10px] text-slate-400 uppercase tracking-widest font-medium">
                    &copy; 2026 E-Tabungan Siswa • <?= $nama ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>

<?php 
if (isset($_POST['btnLogin'])) {  

	$username=mysqli_real_escape_string($koneksi,$_POST['username']);
	$password=mysqli_real_escape_string($koneksi,$_POST['password']);


	$sql_login = "SELECT * FROM tb_pengguna WHERE BINARY username='$username' AND password='$password'";
	$query_login = mysqli_query($koneksi, $sql_login);
	$data_login = mysqli_fetch_array($query_login,MYSQLI_BOTH);
	$jumlah_login = mysqli_num_rows($query_login);
	

	if ($jumlah_login == 1 ){
		$_SESSION["ses_id"]=$data_login["id_pengguna"];
		$_SESSION["ses_nama"]=$data_login["nama_pengguna"];
		$_SESSION["ses_username"]=$data_login["username"];
		$_SESSION["ses_password"]=$data_login["password"];
		$_SESSION["ses_level"]=$data_login["level"];
		
		// Log aktivitas login
		include_once "inc/activity_log.php";
		if (function_exists('logActivity')) {
			logActivity($koneksi, 'LOGIN', 'system', 'User login: ' . $data_login["nama_pengguna"] . ' (' . $data_login["username"] . ')');
		}
		
		echo "<script>
		Swal.fire({
			title: 'Selamat Datang!',
			html: '<h3><strong>" . htmlspecialchars($data_login["nama_pengguna"]) . "</strong></h3><p>Login berhasil. Anda akan diarahkan ke halaman utama.</p>',
			icon: 'success',
			showConfirmButton: false,
			timer: 2000,
			timerProgressBar: true,
			allowOutsideClick: false,
			allowEscapeKey: false
		}).then(() => {
			window.location = 'index.php';
		});
		</script>";
	}else{
		echo "<script>
		Swal.fire({title: 'Maaf Login Gagal',text: '',icon: 'error',confirmButtonText: 'OK'
		}).then((result) => {
			if (result.value) {
				window.location = 'login.php';
			}
		})</script>";
	}
}
