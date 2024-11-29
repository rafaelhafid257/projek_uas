<?php
require 'libs/dompdf/autoload.inc.php';

use Dompdf\Dompdf;

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "manajemen_keuangan";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['start_date']) && isset($_POST['end_date']) && isset($_POST['chart'])) {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $chart = $_POST['chart'];

    // Fetch data based on the date filter
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

    // Generate the HTML content
    $html = '<html><head><style>
            body { font-family: Arial, sans-serif; }
            h1, h2 { text-align: center; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            table, th, td { border: 1px solid black; }
            th, td { padding: 8px 12px; text-align: left; }
            </style></head><body>';
    $html .= '<h1>Laporan Keuangan</h1>';
    $html .= "<p style='text-align: center;'>Tanggal: $start_date hingga $end_date</p>";

    // Grafik Keuangan
    $html .= '<h2>Grafik Keuangan</h2>';
    $html .= "<img src=\"$chart\" style=\"width:100%;height:auto;\">";

    // Rekap Keuangan
    $html .= '<h2>Rekap Keuangan</h2>';
    $html .= '<table>';
    $html .= '<tr><th>Tanggal</th><th>Total Pemasukan</th><th>Total Pengeluaran</th></tr>';
    foreach ($dates as $date => $totals) {
        $html .= '<tr>';
        $html .= '<td>' . $date . '</td>';
        $html .= '<td>Rp' . number_format($totals['income'], 0, ',', '.') . '</td>';
        $html .= '<td>Rp' . number_format($totals['expense'], 0, ',', '.') . '</td>';
        $html .= '</tr>';
    }
    $html .= '</table>';

    // Total
    $html .= '<h2>Total</h2>';
    $html .= '<table>';
    $html .= '<tr><th>Jumlah Pendapatan</th><th>Jumlah Pengeluaran</th></tr>';
    $html .= '<tr>';
    $html .= '<td>Rp' . number_format($total_income, 0, ',', '.') . '</td>';
    $html .= '<td>Rp' . number_format($total_expense, 0, ',', '.') . '</td>';
    $html .= '</tr>';
    $html .= '</table>';

    $html .= '</body></html>';

    // Initialize Dompdf
    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);

    // Setup the paper size and orientation
    $dompdf->setPaper('A4', 'portrait');

    // Render the HTML as PDF
    $dompdf->render();

    // Output the generated PDF (force download)
    $dompdf->stream("laporan_keuangan.pdf", array("Attachment" => 1));
} else {
    echo "Tanggal mulai dan akhir diperlukan.";
}
?>