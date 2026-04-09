<?php
/**
 * Helper untuk ekspor data ke PDF
 * Menggunakan mPDF (harus diinstall: composer require mpdf/mpdf)
 * 
 * CATATAN: File ini TIDAK memanggil session_start() karena sudah dipanggil di export_handler.php
 */

/**
 * Ekspor data ke PDF - fungsi utama
 */
function exportToPDF($title, $headers, $data, $filename = null, $profil_data = null, $force_print_html = false) {
    if ($filename === null) {
        $filename = 'Export_' . date('Ymd_His') . '.pdf';
    }
    
    // Clean output buffer
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Cek apakah mPDF tersedia (skip jika dipaksa ke HTML print)
    $vendorPath = dirname(__DIR__) . '/vendor/autoload.php';
    
    if (!$force_print_html && file_exists($vendorPath)) {
        require_once $vendorPath;
        
        // Cek mPDF
        if (class_exists('Mpdf\Mpdf')) {
            try {
                $mpdf = new \Mpdf\Mpdf([
                    'mode' => 'utf-8',
                    'format' => 'A4-L', // Landscape untuk tabel lebar
                    'margin_left' => 10,
                    'margin_right' => 10,
                    'margin_top' => 15,
                    'margin_bottom' => 15,
                    'orientation' => 'L'
                ]);
                
                // Set metadata
                $mpdf->SetTitle($title);
                $mpdf->SetAuthor('e-Tabs System');
                $mpdf->SetCreator('e-Tabs');
                
                // Ambil path logo dan convert ke base64 untuk mPDF
                $logo_base64 = '';
                if ($profil_data && !empty($profil_data['logo_sekolah'])) {
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
                
                // Build HTML content
                $html = '<style>
                    body { font-family: Arial, sans-serif; color: #000 !important; }
                    p, div, span, strong, td, th { color: #000 !important; }
                    th { color: #fff !important; background-color: #000 !important; }
                    .header { margin-bottom: 20px; border-bottom: 3px solid #000 !important; padding-bottom: 15px; }
                    .header-content { display: table; width: 100%; }
                    .header-logo { display: table-cell; vertical-align: middle; width: 80px; }
                    .header-logo img { max-width: 70px; max-height: 70px; }
                    .header-text { display: table-cell; vertical-align: middle; text-align: center; }
                    .header-text h2 { margin: 0; font-size: 16pt; font-weight: bold; }
                    .header-text p { margin: 5px 0; font-size: 10pt; }
                    .title { text-align: center; margin: 15px 0; font-size: 14pt; font-weight: bold; }
                    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                    th { padding: 8px; text-align: center; font-weight: bold; border: 1px solid #000 !important; }
                    td { padding: 6px; border: 1px solid #000 !important; text-align: left; }
                    tr:nth-child(even) { background-color: #fff; }
                    .footer { text-align: center; font-size: 8pt; margin-top: 20px; border-top: 1px solid #000 !important; padding-top: 10px; }
                </style>';
                
                // Header dengan logo dan nama sekolah
                $html .= '<div class="header">';
                $html .= '<div class="header-content">';
                if (!empty($logo_base64)) {
                    $html .= '<div class="header-logo">';
                    $html .= '<img src="' . $logo_base64 . '" alt="Logo Sekolah">';
                    $html .= '</div>';
                }
                $html .= '<div class="header-text">';
                if ($profil_data && !empty($profil_data['nama_sekolah'])) {
                    $html .= '<h2>' . htmlspecialchars($profil_data['nama_sekolah']) . '</h2>';
                    if (!empty($profil_data['alamat'])) {
                        $html .= '<p>' . htmlspecialchars($profil_data['alamat']) . '</p>';
                    }
                    if (!empty($profil_data['tahun_ajaran'])) {
                        $html .= '<p>Tahun Ajaran: ' . htmlspecialchars($profil_data['tahun_ajaran']) . '</p>';
                    }
                } else {
                    $html .= '<h2>e-Tabs System</h2>';
                }
                $html .= '</div>';
                $html .= '</div>';
                $html .= '</div>';
                
                // Judul tabel
                $html .= '<div class="title">' . htmlspecialchars($title) . '</div>';
                $html .= '<table>';
                $html .= '<thead><tr>';
                
                foreach ($headers as $header) {
                    $html .= '<th>' . htmlspecialchars($header) . '</th>';
                }
                
                $html .= '</tr></thead><tbody>';
                
                foreach ($data as $row) {
                    $html .= '<tr>';
                    foreach ($row as $cell) {
                        $html .= '<td>' . htmlspecialchars($cell) . '</td>';
                    }
                    $html .= '</tr>';
                }
                
                $html .= '</tbody></table>';
                $html .= '<div class="footer">';
                $html .= '<p>Dicetak pada: ' . date('d/m/Y H:i:s') . '</p>';
                if ($profil_data && !empty($profil_data['nama_bendahara'])) {
                    $html .= '<div style="margin-top: 20px; text-align: right; padding-right: 20px;">';
                    $html .= '<div style="display: inline-block; text-align: center;">';
                    $html .= '<p>Bendahara,</p>';
                    $html .= '<div style="margin: 10px 0;">';
                    $html .= '<barcode code="' . htmlspecialchars($profil_data['nama_bendahara']) . '" type="QR" class="barcode" size="0.8" error="M" />';
                    $html .= '</div>';
                    $html .= '<p><strong>' . htmlspecialchars($profil_data['nama_bendahara']) . '</strong></p>';
                    $html .= '</div>';
                    $html .= '</div>';
                }
                $html .= '<p style="margin-top: 10px;">e-Tabs System</p>';
                $html .= '</div>';
                
                // Set JS to trigger print dialog
                $mpdf->SetJS('this.print();');
                
                // Write HTML to PDF
                $mpdf->WriteHTML($html);

                // Output PDF Inline to browser (I) to open in new tab
                $mpdf->Output('', 'I');
                exit;
                
            } catch (Exception $e) {
                // Error handling
                die('Error generating PDF: ' . $e->getMessage() . '<br><br>Silakan install mPDF dengan menjalankan: <code>composer require mpdf/mpdf</code>');
            }
        }
    }
    
    // Jika mPDF tidak tersedia, tampilkan halaman HTML yang bisa di-print to PDF
    // CATATAN: Untuk mendapatkan file PDF langsung, install mPDF dengan:
    // - Buka terminal Laragon di folder project ini
    // - Jalankan: composer require mpdf/mpdf
    // - Atau lihat file INSTALL_MPDF.md untuk instruksi lengkap
    // Clean output buffer
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Ambil path logo untuk HTML fallback
    $logo_url = '';
    if ($profil_data && !empty($profil_data['logo_sekolah'])) {
        $logo_file = '../uploads/logo/' . $profil_data['logo_sekolah'];
        if (file_exists(dirname(__DIR__) . '/uploads/logo/' . $profil_data['logo_sekolah'])) {
            $logo_url = $logo_file;
        }
    }
    if (empty($logo_url)) {
        $logo_file = '../images/logo.png';
        if (file_exists(dirname(__DIR__) . '/images/logo.png')) {
            $logo_url = $logo_file;
        }
    }
    
    // Generate HTML untuk print to PDF
    $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($title) . '</title>
    <style>
        @media print {
            @page { 
                margin: 1cm; 
                size: A4 landscape;
            }
            body { margin: 0; }
            .print-btn { display: none; }
        }
        body { font-family: Arial, sans-serif; font-size: 9pt; margin: 20px; background-color: #ffffff; color: #000 !important; }
        p, div, span, strong, td, th { color: #000 !important; }
        th { color: #fff !important; background-color: #000 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .header {
            margin-bottom: 20px;
            border-bottom: 3px solid #000 !important;
            padding-bottom: 15px;
        }
        .header-logo { float: left; width: 80px; }
        .header-logo img { max-width: 70px; max-height: 70px; }
        .header-text { text-align: center; margin-left: 90px; }
        .clearfix::after { content: ""; clear: both; display: table; }
        .header-text h2 { margin: 0; font-size: 16pt; font-weight: bold; }
        .header-text p { margin: 5px 0; font-size: 10pt; }
        h1 { text-align: center; margin: 15px 0; font-size: 14pt; font-weight: bold; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background-color: white;
            box-shadow: none;
        }
        th {
            padding: 10px 8px;
            text-align: center;
            border: 1px solid #000 !important;
            font-weight: bold;
            font-size: 10pt;
        }
        td {
            padding: 8px 6px;
            border: 1px solid #000 !important;
            text-align: left;
            font-size: 9pt;
        }
        tr:nth-child(even) { background-color: #fff; }
        tr:hover { background-color: #fff; }
        .footer { margin-top: 30px; text-align: center; font-size: 8pt; padding-top: 10px; border-top: 1px solid #000 !important; }
        .print-btn {
            text-align: center;
            margin: 20px 0;
        }
        .print-btn button {
            padding: 12px 30px;
            font-size: 16px;
            background-color: #000 !important;
            color: white !important;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .print-btn button:hover {
            background-color: #333 !important;
        }
    </style>
</head>
<body>
    <div class="header clearfix">';
    
    if (!empty($logo_url)) {
        $html .= '<div class="header-logo">
            <img src="' . htmlspecialchars($logo_url) . '" alt="Logo Sekolah">
        </div>';
    }
    
    $html .= '<div class="header-text">';
    if ($profil_data && !empty($profil_data['nama_sekolah'])) {
        $html .= '<h2>' . htmlspecialchars($profil_data['nama_sekolah']) . '</h2>';
        if (!empty($profil_data['alamat'])) {
            $html .= '<p>' . htmlspecialchars($profil_data['alamat']) . '</p>';
        }
        if (!empty($profil_data['tahun_ajaran'])) {
            $html .= '<p>Tahun Ajaran: ' . htmlspecialchars($profil_data['tahun_ajaran']) . '</p>';
        }
    } else {
        $html .= '<h2>e-Tabs System</h2>';
    }
    $html .= '</div>
    </div>
    <h1>' . htmlspecialchars($title) . '</h1>
    <table>
        <thead>
            <tr>';
    
    foreach ($headers as $header) {
        $html .= '<th>' . htmlspecialchars($header) . '</th>';
    }
    
    $html .= '</tr>
        </thead>
        <tbody>';
    
    foreach ($data as $row) {
        $html .= '<tr>';
        foreach ($row as $cell) {
            $html .= '<td>' . htmlspecialchars($cell) . '</td>';
        }
        $html .= '</tr>';
    }
    
    $html .= '</tbody>
    </table>
    <div class="footer">
        <p>Dicetak pada: ' . date('d/m/Y H:i:s') . '</p>';
    
    if ($profil_data && !empty($profil_data['nama_bendahara'])) {
        $html .= '<div style="margin-top: 20px; text-align: right; padding-right: 50px;">
            <div style="display: inline-block; text-align: center;">
                <p>Bendahara,</p>
                <div style="margin: 10px 0;">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=' . urlencode($profil_data['nama_bendahara']) . '" alt="QR Code Signature" style="width: 80px; height: 80px;">
                </div>
                <p><strong>' . htmlspecialchars($profil_data['nama_bendahara']) . '</strong></p>
            </div>
        </div>';
    }
    
    $html .= '<p style="margin-top: 10px;">e-Tabs System</p>
    </div>
    <script>
        window.onload = function() {
            setTimeout(function() { window.print(); }, 300);
        };
    </script>
</body>
</html>';
    
    // Output HTML (user bisa print to PDF di browser)
    header('Content-Type: text/html; charset=UTF-8');
    echo $html;
    exit;
}
