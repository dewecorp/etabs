<?php
/**
 * Helper untuk ekspor data ke Excel
 * Menggunakan PHPSpreadsheet
 * 
 * CATATAN: File ini TIDAK memanggil session_start() karena sudah dipanggil di export_handler.php
 */

// Load PHPSpreadsheet
if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

/**
 * Ekspor data ke Excel
 * @param string $title Judul laporan
 * @param array $headers Array header kolom
 * @param array $data Array data (array of arrays)
 * @param string $filename Nama file output
 * @param array $profil_data Data profil sekolah
 */
function exportToExcel($title, $headers, $data, $filename = null, $profil_data = null) {
    if ($filename === null) {
        $filename = 'Export_' . date('Ymd_His') . '.xlsx';
    }
    
    // Clean output buffer
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Create new Spreadsheet object
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Set title sheet
    $sheet->setTitle('Data Export');
    
    $currentRow = 1;
    $lastColLetter = chr(64 + count($headers));
    
    // Tampilkan Informasi Sekolah jika ada
    if ($profil_data) {
        // Nama Sekolah
        if (!empty($profil_data['nama_sekolah'])) {
            $sheet->setCellValue('A' . $currentRow, $profil_data['nama_sekolah']);
            $sheet->mergeCells('A' . $currentRow . ':' . $lastColLetter . $currentRow);
            $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $currentRow++;
        }
        
        // Alamat
        if (!empty($profil_data['alamat'])) {
            $sheet->setCellValue('A' . $currentRow, $profil_data['alamat']);
            $sheet->mergeCells('A' . $currentRow . ':' . $lastColLetter . $currentRow);
            $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $currentRow++;
        }
        
        // Tahun Ajaran
        if (!empty($profil_data['tahun_ajaran'])) {
            $sheet->setCellValue('A' . $currentRow, 'Tahun Ajaran: ' . $profil_data['tahun_ajaran']);
            $sheet->mergeCells('A' . $currentRow . ':' . $lastColLetter . $currentRow);
            $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
            $currentRow++;
        }
        
        // Spasi
        $currentRow++;
    }
    
    // Set judul laporan
    $sheet->setCellValue('A' . $currentRow, $title);
    $sheet->mergeCells('A' . $currentRow . ':' . $lastColLetter . $currentRow);
    $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getRowDimension($currentRow)->setRowHeight(30);
    
    // Header row
    $headerRow = $currentRow + 2;
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . $headerRow, $header);
        $col++;
    }
    
    // Style header
    $headerStyle = [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF'],
            'size' => 12,
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '4472C4'],
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
            ],
        ],
    ];
    
    $lastCol = chr(64 + count($headers));
    $sheet->getStyle('A' . $headerRow . ':' . $lastCol . $headerRow)->applyFromArray($headerStyle);
    $sheet->getRowDimension($headerRow)->setRowHeight(25);
    
    // Data rows
    $row = $headerRow + 1;
    foreach ($data as $rowData) {
        $col = 'A';
        foreach ($rowData as $cellData) {
            // Pastikan data adalah string atau number
            $cellData = is_null($cellData) ? '' : (string)$cellData;
            $sheet->setCellValue($col . $row, $cellData);
            $col++;
        }
        
        // Style data row
        $dataStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
        
        $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->applyFromArray($dataStyle);
        $row++;
    }
    
    // Auto size columns
    foreach (range('A', $lastCol) as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Freeze header row
    $sheet->freezePane('A' . ($headerRow + 1));
    
    // Set print area
    $sheet->getPageSetup()->setPrintArea('A1:' . $lastCol . ($row - 1));
    
    // Set headers sebelum output
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Pragma: public');
    
    // Output file
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}
