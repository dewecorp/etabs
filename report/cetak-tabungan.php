<?php
include "../inc/koneksi.php";
//FUNGSI RUPIAH
include "../inc/rupiah.php";

$nis = $_GET['nis'];

$sql = $koneksi->query("SELECT * from tb_profil");
$profil_data = $sql->fetch_assoc();

$nama = $profil_data['nama_sekolah'];
$alamat = $profil_data['alamat'];
$logo_path = !empty($profil_data['logo_sekolah']) ? '../uploads/logo/' . $profil_data['logo_sekolah'] : '../images/logo.png';
$nama_bendahara = isset($profil_data['nama_bendahara']) ? $profil_data['nama_bendahara'] : '';

// Ambil data siswa
$sql_tampil = "SELECT * FROM tb_siswa JOIN tb_kelas ON tb_siswa.id_kelas = tb_kelas.id_kelas WHERE nis ='$nis'";
$query_tampil = mysqli_query($koneksi, $sql_tampil);
$data_siswa = mysqli_fetch_array($query_tampil, MYSQLI_BOTH);
?>

<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Cetak Tabungan Siswa</title>
	<style>
		@page {
			margin: 1.5cm;
			size: A4;
		}
		
		@media print {
			body { margin: 0; }
			.no-print { display: none; }
		}
		
		* {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
		}
		
		body {
			font-family: 'Arial', sans-serif;
			font-size: 11pt;
			line-height: 1.5;
			color: #333;
		}
		
		.header {
			margin-bottom: 20px;
			border-bottom: 3px solid #4472C4;
			padding-bottom: 15px;
		}
		
		.header-content {
			display: table;
			width: 100%;
			margin-bottom: 10px;
		}
		
		.header-logo {
			display: table-cell;
			vertical-align: middle;
			width: 80px;
		}
		
		.header-logo img {
			max-width: 70px;
			max-height: 70px;
			object-fit: contain;
		}
		
		.header-text {
			display: table-cell;
			vertical-align: middle;
			text-align: center;
		}
		
		.header-text h1 {
			margin: 0;
			font-size: 18pt;
			color: #333;
			font-weight: bold;
			margin-bottom: 5px;
		}
		
		.header-text p {
			margin: 3px 0;
			font-size: 10pt;
			color: #666;
		}
		
		.report-title {
			text-align: center;
			font-size: 14pt;
			font-weight: bold;
			margin: 15px 0;
			color: #333;
		}
		
		.student-info {
			margin: 20px 0;
			padding: 15px;
			background-color: #f9f9f9;
			border: 1px solid #ddd;
			border-radius: 5px;
		}
		
		.student-info table {
			width: 100%;
			border-collapse: collapse;
		}
		
		.student-info td {
			padding: 5px 10px;
			border: none;
		}
		
		.student-info td:first-child {
			width: 120px;
			font-weight: bold;
		}
		
		.motto {
			text-align: center;
			font-style: italic;
			color: #666;
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
			background-color: #4472C4;
			color: white;
		}
		
		th {
			padding: 10px 8px;
			text-align: center;
			font-weight: bold;
			border: 1px solid #2d5aa0;
		}
		
		td {
			padding: 8px;
			border: 1px solid #ddd;
			text-align: left;
		}
		
		tbody tr:nth-child(even) {
			background-color: #f9f9f9;
		}
		
		tbody tr:hover {
			background-color: #f0f0f0;
		}
		
		.text-right {
			text-align: right;
		}
		
		.text-center {
			text-align: center;
		}
		
		.summary-row {
			background-color: #e8f4f8;
			font-weight: bold;
		}
		
		.summary-row td {
			border-top: 2px solid #4472C4;
		}
		
		.footer {
			margin-top: 30px;
			text-align: center;
			font-size: 9pt;
			color: #666;
			border-top: 1px solid #ddd;
			padding-top: 10px;
		}
	</style>
</head>
<body>
	<div class="header">
		<div class="header-content">
			<div class="header-logo">
				<img src="<?php echo htmlspecialchars($logo_path); ?>" alt="Logo Sekolah" onerror="this.src='../images/logo.png'">
			</div>
			<div class="header-text">
				<h1><?php echo htmlspecialchars($nama); ?></h1>
				<?php if (!empty($alamat)): ?>
				<p><?php echo htmlspecialchars($alamat); ?></p>
				<?php endif; ?>
			</div>
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
		<div style="margin-top: 30px; text-align: right; padding-right: 50px;">
			<p>Bendahara,</p>
			<p style="margin-top: 50px;">
				<strong><?php echo htmlspecialchars($nama_bendahara); ?></strong>
			</p>
		</div>
		<?php endif; ?>
		<p style="margin-top: 10px;">e-TABS System</p>
	</div>
	
	<script>
		window.onload = function() {
			window.print();
		};
	</script>
</body>
</html>
