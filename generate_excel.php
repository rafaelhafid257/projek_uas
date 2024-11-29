<?php
require 'libs/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "manajemen_keuangan";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];

    $income_query = "SELECT tanggal, SUM(pemasukan) AS total_income FROM pemasukan WHERE tanggal >= '$start_date' AND tanggal <= '$end_date' GROUP BY tanggal";
    $expense_query = "SELECT tanggal, SUM(pengeluaran) AS total_expense FROM pengeluaran WHERE tanggal >= '$start_date' AND tanggal <= '$end_date' GROUP BY tanggal";

    $income_result = $conn->query($income_query);
    $expense_result = $conn->query($expense_query);

    $dates = [];
    $income_totals = [];
    $expense_totals = [];
    $total_income = 0;
    $total_expense = 0;

    while ($row = $income_result->fetch_assoc()) {
        $dates[$row['tanggal']] = [
            'income' => $row['total_income'],
            'expense' => 0
        ];
        $total_income += $row['total_income'];
    }

    while ($row = $expense_result->fetch_assoc()) {
        if (isset($dates[$row['tanggal']])) {
            $dates[$row['tanggal']]['expense'] = $row['total_expense'];
        } else {
            $dates[$row['tanggal']] = [
                'income' => 0,
                'expense' => $row['total_expense']
            ];
        }
        $total_expense += $row['total_expense'];
    }

    // Sort dates array by date
    ksort($dates);

    // Create new Spreadsheet object
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set sheet title
    $sheet->setTitle('Laporan Keuangan');

    // Header
    $sheet->setCellValue('A1', 'Rekap Laporan Keuangan');
    $sheet->mergeCells('A1:D1');
    $sheet->setCellValue('A2', "Tanggal: $start_date hingga $end_date");
    $sheet->mergeCells('A2:D2');

    // Column titles
    $sheet->setCellValue('A4', 'No');
    $sheet->setCellValue('B4', 'Tanggal');
    $sheet->setCellValue('C4', 'Total Pemasukan');
    $sheet->setCellValue('D4', 'Total Pengeluaran');

    // Data rows
    $rowNumber = 5;
    $index = 1;
    foreach ($dates as $date => $totals) {
        $sheet->setCellValue('A' . $rowNumber, $index++);
        $sheet->setCellValue('B' . $rowNumber, date('d-M-y', strtotime($date)));
        $sheet->setCellValue('C' . $rowNumber, 'Rp' . number_format($totals['income'], 0, ',', '.'));
        $sheet->setCellValue('D' . $rowNumber, 'Rp' . number_format($totals['expense'], 0, ',', '.'));
        $rowNumber++;
    }

    // Total row
    $sheet->setCellValue('A' . $rowNumber, 'Total');
    $sheet->setCellValue('C' . $rowNumber, 'Rp' . number_format($total_income, 0, ',', '.'));
    $sheet->setCellValue('D' . $rowNumber, 'Rp' . number_format($total_expense, 0, ',', '.'));

    // Format the sheet
    $sheet->getStyle('A1:D2')->getFont()->setBold(true);
    $sheet->getStyle('A4:D4')->getFont()->setBold(true);
    $sheet->getStyle('A4:D' . $rowNumber)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

    // Save Excel file with .xlsx extension
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="laporan_keuangan.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
} else {
    echo "Tanggal mulai dan akhir diperlukan.";
}
?>