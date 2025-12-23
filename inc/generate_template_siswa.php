<?php
// Script untuk generate template Excel import siswa
session_start();
if (!isset($_SESSION["ses_username"])) {
    header("location: ../login.php");
    exit;
}

require_once dirname(__DIR__) . '/vendor/autoload.php';
include __DIR__ . '/koneksi.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Create new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set title
$sheet->setTitle('Template Import Siswa');

// Header row dengan styling
$headers = ['NIS', 'Nama Siswa', 'Jenis Kelamin', 'Kelas', 'Tahun Masuk', 'Status'];
$sheet->fromArray($headers, NULL, 'A1');

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

$sheet->getStyle('A1:F1')->applyFromArray($headerStyle);

// Set column widths
$sheet->getColumnDimension('A')->setWidth(15);
$sheet->getColumnDimension('B')->setWidth(30);
$sheet->getColumnDimension('C')->setWidth(18);
$sheet->getColumnDimension('D')->setWidth(20);
$sheet->getColumnDimension('E')->setWidth(15);
$sheet->getColumnDimension('F')->setWidth(15);

// Ambil data kelas dari database untuk contoh
$query_kelas = "SELECT kelas FROM tb_kelas ORDER BY kelas";
$result_kelas = mysqli_query($koneksi, $query_kelas);
$kelas_list = [];
while ($row = mysqli_fetch_assoc($result_kelas)) {
    $kelas_list[] = $row['kelas'];
}

// Tambahkan contoh data (3 baris)
$examples = [
    ['123456789012', 'Ahmad Fauzi', 'LK', !empty($kelas_list) ? $kelas_list[0] : '1 ( Satu )', date('Y'), 'Aktif'],
    ['123456789013', 'Siti Nurhaliza', 'PR', !empty($kelas_list) ? $kelas_list[0] : '1 ( Satu )', date('Y'), 'Aktif'],
    ['123456789014', 'Budi Santoso', 'LK', !empty($kelas_list) ? $kelas_list[0] : '1 ( Satu )', date('Y'), 'Aktif'],
];

$row = 2;
foreach ($examples as $example) {
    $sheet->fromArray($example, NULL, 'A' . $row);
    
    // Style untuk data contoh
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
    
    $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray($dataStyle);
    
    // Set alignment untuk kolom tertentu
    $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
    $row++;
}

// Freeze header row
$sheet->freezePane('A2');

// Set header row height
$sheet->getRowDimension(1)->setRowHeight(25);

// Output file
$filename = 'Template_Import_Siswa_' . date('Ymd_His') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>

