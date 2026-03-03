<?php
session_start();
include "inc/koneksi.php";

$sql = $koneksi->query("SELECT * from tb_profil");
$data= $sql->fetch_assoc();

$nama=$data['nama_sekolah'];
$alamat=$data['alamat'];
// Handle Logo & Background dynamic
$logo_path = !empty($data['logo_sekolah']) ? 'uploads/logo/' . $data['logo_sekolah'] : 'images/logo.png';
$bg_path = (isset($data['bg_login']) && !empty($data['bg_login'])) ? 'uploads/bg/' . $data['bg_login'] : 'images/bg_sf.jpg';
?>


<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Login | e-TABS</title>
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
            background: linear-gradient(rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.8)), url('<?php echo $bg_path; ?>');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        .auth-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            box-shadow: 0 10px 24px -6px rgba(2, 8, 23, 0.08);
        }
        .auth-input {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            color: #1f2937;
            padding: 12px 16px;
            border-radius: 12px;
            width: 100%;
            transition: all 0.2s ease;
        }
        .auth-input:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
            background: #ffffff;
        }
    </style>
</head>

<body class="hold-transition auth-bg min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="auth-card p-6">
            <div class="text-center mb-6">
                <div class="inline-flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 via-sky-500 to-emerald-400 shadow-lg shadow-indigo-500/40 mb-4">
                    <img src="<?php echo $logo_path; ?>" alt="Logo" class="h-14 w-14 object-contain">
                </div>
                <h2 class="text-2xl font-bold text-white tracking-tight">E-Tabungan Siswa</h2>
                <p class="text-slate-400 text-sm mt-1"><?= $nama ?></p>
            </div>



            <form action="#" method="post" class="space-y-3">
                <div class="space-y-1">
                    <label class="text-xs font-medium text-slate-300 ml-1">Username</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500">
                            <i class="fa-solid fa-user text-xs"></i>
                        </span>
                        <input type="text" class="auth-input pl-10" name="username" placeholder="Masukkan username" required>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-medium text-slate-300 ml-1">Password</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500">
                            <i class="fa-solid fa-lock text-xs"></i>
                        </span>
                        <input type="password" class="auth-input pl-10" name="password" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <div class="flex items-center">
                        <input type="checkbox" id="remember" class="rounded border-slate-700 bg-slate-800 text-indigo-500 focus:ring-indigo-500">
                        <label for="remember" class="ml-2 text-xs text-slate-400">Ingat saya</label>
                    </div>
                    <a href="#" class="text-xs text-indigo-400 hover:text-indigo-300">Lupa password?</a>
                </div>

                <button type="submit" class="w-full btn-dashboard-primary mt-3 flex items-center justify-center gap-2 py-3" name="btnLogin">
                    <span>Masuk</span>
                    <i class="fa-solid fa-arrow-right-to-bracket text-xs"></i>
                </button>
            </form>
            
            <div class="mt-6 pt-6 border-t border-slate-800 text-center">
                <p class="text-[10px] text-slate-500 uppercase tracking-widest font-medium">
                    &copy; 2026 e-TABS • <?= $nama ?>
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
