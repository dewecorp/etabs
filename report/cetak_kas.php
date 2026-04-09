<?php
require_once dirname(__DIR__) . '/inc/config.php';
require_once dirname(__DIR__) . '/inc/koneksi.php';
require_once dirname(__DIR__) . '/inc/rupiah.php';

$sql_profil = "SELECT * from tb_profil";
$query_profil = mysqli_query($koneksi, $sql_profil);
$data_profil = mysqli_fetch_array($query_profil, MYSQLI_BOTH);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Saldo Kas</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #333;
        }
        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            margin-bottom: 30px;
            border-bottom: 3px solid #000;
            padding-bottom: 15px;
            position: relative;
            min-height: 80px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            text-align: center;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 20px;
            font-weight: normal;
            text-align: center;
        }
        .header h3 {
            margin: 5px 0;
            font-size: 14px;
            font-weight: normal;
            text-align: center;
        }
        .header p {
            margin: 0;
            font-size: 14px;
            text-align: center;
        }
        .header-logo {
            position: absolute;
            left: 0;
            top: 0;
            width: 80px;
            height: 80px;
        }
        .header-logo img {
            width: 80px;
            height: 80px;
        }
        .header-text {
            text-align: center;
            width: 100%;
        }
        .content {
            margin-top: 30px;
        }
        .content table {
            width: 100%;
            border-collapse: collapse;
        }
        .content th, .content td {
            border: 1px solid #ccc;
            padding: 12px;
            font-size: 16px;
        }
        .content th {
            background-color: #f2f2f2;
            text-align: left;
        }
        .content .total-row th, .content .total-row td {
            font-weight: bold;
            background-color: #e9ecef;
        }
        .footer {
            margin-top: 60px;
            width: 100%;
            display: table;
        }
        .signature {
            display: table-cell;
            width: 50%;
            text-align: center;
        }
        .signature p {
            margin-bottom: 70px;
        }
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .container {
                width: 95%;
                margin: 0 auto;
                padding: 0;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container">
        <div class="header">
            <div class="header-logo">
                <img src="../images/logo.png" alt="Logo Sekolah">
            </div>
            <div class="header-text">
                <h1>LAPORAN SALDO KAS TABUNGAN</h1>
                <h2><?php echo strtoupper($data_profil['nama_sekolah']); ?></h2>
                <p><?php echo $data_profil['alamat']; ?></p>
                <h3>TAHUN AJARAN <?php echo $data_profil['tahun_ajaran']; ?></h3>
            </div>
        </div>

        <div class="content">
            <?php
                $sql_setor = $koneksi->query("SELECT SUM(setor) as total_setor from tb_tabungan where jenis='ST'");
                $data_setor = $sql_setor->fetch_assoc();
                $total_setor = $data_setor['total_setor'] ?? 0;

                $sql_tarik = $koneksi->query("SELECT SUM(tarik) as total_tarik from tb_tabungan where jenis='TR'");
                $data_tarik = $sql_tarik->fetch_assoc();
                $total_tarik = $data_tarik['total_tarik'] ?? 0;

                $saldo = $total_setor - $total_tarik;
            ?>
            <table>
                <tbody>
                    <tr>
                        <th width="50%">Total Pemasukan (Setoran)</th>
                        <td><?php echo rupiah($total_setor); ?></td>
                    </tr>
                    <tr>
                        <th width="50%">Total Pengeluaran (Penarikan)</th>
                        <td><?php echo rupiah($total_tarik); ?></td>
                    </tr>
                    <tr class="total-row">
                        <th width="50%">Saldo Akhir Kas</th>
                        <td><?php echo rupiah($saldo); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="footer">
            <div class="signature">
                <!-- Empty for spacing -->
            </div>
            <div class="signature">
                <p>Jepara, <?php echo tgl_indo(date('Y-m-d')); ?></p>
                <p>Bendahara,</p>
                <div style="margin: 10px 0;">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?php echo urlencode($data_profil['nama_bendahara']); ?>" alt="QR Code" style="margin: 0 auto; display: block; width: 80px; height: 80px;">
                </div>
                <p><strong><?php echo $data_profil['nama_bendahara']; ?></strong></p>
            </div>
        </div>
    </div>
</body>
</html>
