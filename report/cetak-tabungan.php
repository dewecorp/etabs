<?php
// Load mPDF
require_once dirname(__DIR__) . '/vendor/autoload.php';

include "../inc/koneksi.php";
//FUNGSI RUPIAH
include "../inc/rupiah.php";

$nis = $_GET['nis'];

$sql = $koneksi->query("SELECT * from tb_profil");
$profil_data = $sql->fetch_assoc();

$nama = $profil_data['nama_sekolah'];
$alamat = $profil_data['alamat'];
$tahun_ajaran = isset($profil_data['tahun_ajaran']) ? $profil_data['tahun_ajaran'] : '';
$nama_bendahara = isset($profil_data['nama_bendahara']) ? $profil_data['nama_bendahara'] : '';

// Logo handling for mPDF
$logo_base64 = '';
if (!empty($profil_data['logo_sekolah'])) {
    $logo_file = dirname(__DIR__) . '/uploads/logo/' . $profil_data['logo_sekolah'];
    if (file_exists($logo_file)) {
        $image_data = file_get_contents($logo_file);
        $image_info = getimagesize($logo_file);
        if ($image_data && $image_info) {
            $logo_base64 = 'data:' . $image_info['mime'] . ';base64,' . base64_encode($image_data);
        }
    }
}
if (empty($logo_base64)) {
    $logo_file = dirname(__DIR__) . '/images/logo.png';
    if (file_exists($logo_file)) {
        $image_data = file_get_contents($logo_file);
        $image_info = getimagesize($logo_file);
        if ($image_data && $image_info) {
            $logo_base64 = 'data:' . $image_info['mime'] . ';base64,' . base64_encode($image_data);
        }
    }
}

// Ambil data siswa
$sql_tampil = "SELECT * FROM tb_siswa JOIN tb_kelas ON tb_siswa.id_kelas = tb_kelas.id_kelas WHERE nis ='$nis'";
$query_tampil = mysqli_query($koneksi, $sql_tampil);
$data_siswa = mysqli_fetch_array($query_tampil, MYSQLI_BOTH);

// Start buffering
ob_start();
?>

<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<title>Cetak Tabungan - <?php echo htmlspecialchars($data_siswa['nama_siswa']); ?></title>
	<style>
		body {
			font-family: 'Arial', sans-serif;
			font-size: 11pt;
			line-height: 1.5;
			color: #000 !important;
		}
        
        p, div, span, strong, td, th {
            color: #000 !important;
        }
        
        th {
            color: #fff !important;
        }
		
		.header {
			margin-bottom: 20px;
			border-bottom: 3px solid #000;
			padding-bottom: 15px;
		}
		
		.header-logo {
			float: left;
			width: 80px;
		}
		
		.header-logo img {
			max-width: 70px;
			max-height: 70px;
		}
		
		.header-text {
			text-align: center;
            margin-left: 90px;
		}
		
		.header-text h1 {
			margin: 0;
			font-size: 18pt;
			color: #000;
			font-weight: bold;
			margin-bottom: 5px;
		}
		
		.header-text p {
			margin: 3px 0;
			font-size: 10pt;
			color: #000;
		}
		
		.report-title {
			text-align: center;
			font-size: 14pt;
			font-weight: bold;
			margin: 15px 0;
			color: #000;
		}
		
		.student-info {
			margin: 20px 0;
			padding: 10px 0;
			background-color: #fff;
			border: none !important;
		}
		
		.student-info table {
			width: 100%;
			border-collapse: collapse;
		}
		
		.student-info td {
			padding: 3px 10px;
			border: none !important;
			color: #000 !important;
		}
		
		.student-info td:first-child {
			width: 120px;
			font-weight: bold;
		}
		
		.motto {
			text-align: center;
			font-style: italic;
			color: #000;
			margin: 10px 0;
			font-size: 10pt;
		}
		
		table {
			width: 100%;
			border-collapse: collapse;
			margin-top: 10px;
			font-size: 10pt;
		}
		
		thead {
			background-color: #000 !important;
			color: #fff !important;
		}
		
		th {
			padding: 10px 8px;
			text-align: center;
			font-weight: bold;
			border: 1px solid #000 !important;
			color: #fff !important;
			background-color: #000 !important;
			-webkit-print-color-adjust: exact;
			print-color-adjust: exact;
		}
		
		td {
			padding: 8px;
			border: 1px solid #000 !important;
			text-align: left;
			color: #000 !important;
		}
		
		tbody tr:nth-child(even) {
			background-color: #fff;
		}
		
		.text-right {
			text-align: right;
		}
		
		.text-center {
			text-align: center;
		}
		
		.summary-row {
			background-color: #fff !important;
			font-weight: bold;
		}
		
		.summary-row td {
			border-top: 2px solid #000 !important;
			color: #000 !important;
		}
		
		.footer {
			margin-top: 30px;
			text-align: center;
			font-size: 9pt;
			color: #000 !important;
			border-top: 1px solid #000 !important;
			padding-top: 10px;
		}
        
        /* Clearfix for header */
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
	</style>
</head>
<body>
	<div class="header clearfix">
		<div class="header-logo">
			<img src="<?php echo $logo_base64; ?>" alt="Logo Sekolah">
		</div>
		<div class="header-text">
			<h1><?php echo htmlspecialchars($nama); ?></h1>
			<?php if (!empty($alamat)): ?>
			<p><?php echo htmlspecialchars($alamat); ?></p>
			<?php endif; ?>
            <?php if (!empty($tahun_ajaran)): ?>
            <p>Tahun Ajaran: <?php echo htmlspecialchars($tahun_ajaran); ?></p>
            <?php endif; ?>
		</div>
	</div>
	
	<div class="report-title">Laporan Tabungan Siswa</div>
	
	<div class="motto">Rajin Pangkal Pandai. Hemat Pangkal Kaya</div>
	
	<div class="student-info">
		<table>
			<tr>
				<td>NIS</td>
				<td>:</td>
				<td><?php echo htmlspecialchars($data_siswa['nis']); ?></td>
			</tr>
			<tr>
				<td>Nama Siswa</td>
				<td>:</td>
				<td><?php echo htmlspecialchars($data_siswa['nama_siswa']); ?></td>
			</tr>
			<tr>
				<td>Kelas</td>
				<td>:</td>
				<td><?php echo htmlspecialchars($data_siswa['kelas']); ?></td>
			</tr>
		</table>
	</div>
	
	<table>
		<thead>
			<tr>
				<th style="width: 5%;">No.</th>
				<th style="width: 20%;">Tanggal</th>
				<th style="width: 25%;" class="text-right">Pemasukan</th>
				<th style="width: 25%;" class="text-right">Pengeluaran</th>
				<th style="width: 20%;">Petugas</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$sql_tampil = "SELECT s.nis, s.nama_siswa, t.id_tabungan, t.setor, t.tarik, t.tgl, t.petugas 
						   FROM tb_siswa s 
						   JOIN tb_tabungan t ON s.nis=t.nis 
						   WHERE s.nis ='$nis' 
						   ORDER BY tgl ASC";
			
			$query_tampil = mysqli_query($koneksi, $sql_tampil);
			$no = 1;
			while ($data = mysqli_fetch_array($query_tampil, MYSQLI_BOTH)) {
			?>
			<tr>
				<td class="text-center"><?php echo $no; ?></td>
				<td><?php echo date("d/M/Y", strtotime($data['tgl'])); ?></td>
				<td class="text-right"><?php echo rupiah($data['setor']); ?></td>
				<td class="text-right"><?php echo rupiah($data['tarik']); ?></td>
				<td><?php echo htmlspecialchars($data['petugas']); ?></td>
			</tr>
			<?php
				$no++;
			}
			
			// Hitung total
			$sql = $koneksi->query("SELECT SUM(setor) as Tsetor FROM tb_tabungan WHERE jenis='ST' AND nis='$nis'");
			$data_setor = $sql->fetch_assoc();
			$total_setor = $data_setor['Tsetor'];
			
			$sql = $koneksi->query("SELECT SUM(tarik) as Ttarik FROM tb_tabungan WHERE jenis='TR' AND nis='$nis'");
			$data_tarik = $sql->fetch_assoc();
			$total_tarik = $data_tarik['Ttarik'];
			
			$sql = $koneksi->query("SELECT SUM(setor)-SUM(tarik) as Total FROM tb_tabungan WHERE nis='$nis'");
			$data_total = $sql->fetch_assoc();
			$saldo = $data_total['Total'];
			?>
		</tbody>
		<tfoot>
			<tr class="summary-row">
				<td colspan="2" class="text-right"><strong>Total Setoran</strong></td>
				<td colspan="3" class="text-right"><strong><?php echo rupiah($total_setor); ?></strong></td>
			</tr>
			<tr class="summary-row">
				<td colspan="2" class="text-right"><strong>Total Penarikan</strong></td>
				<td colspan="3" class="text-right"><strong><?php echo rupiah($total_tarik); ?></strong></td>
			</tr>
			<tr class="summary-row">
				<td colspan="2" class="text-right"><strong>Saldo Tabungan</strong></td>
				<td colspan="3" class="text-right"><strong><?php echo rupiah($saldo); ?></strong></td>
			</tr>
		</tfoot>
	</table>
	
	<div class="footer">
		<p>Dicetak pada: <?php echo date("d/m/Y H:i:s"); ?></p>
		<?php if (!empty($nama_bendahara)): ?>
		<div style="margin-top: 20px; text-align: right; padding-right: 50px;">
			<div style="display: inline-block; text-align: center;">
				<p>Bendahara,</p>
				<div style="margin: 10px 0;">
					<img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?php echo urlencode($nama_bendahara); ?>" alt="QR Code Signature" style="width: 80px; height: 80px;">
				</div>
				<p><strong><?php echo htmlspecialchars($nama_bendahara); ?></strong></p>
			</div>
		</div>
		<?php endif; ?>
		<p style="margin-top: 10px;">e-TABS System</p>
	</div>
	<script>
		window.onload = function() {
			setTimeout(function() { window.print(); }, 300);
		};
	</script>
</body>
</html>

<?php
// Get content from buffer and output as HTML for direct browser printing
$html = ob_get_clean();
echo $html;
exit;
?>