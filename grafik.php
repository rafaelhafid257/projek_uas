<?php
session_start();
$title = "Manajemen Keuangan";
$page = "Dashboard";

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "manajemen_keuangan";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to fetch user data
function fetchUserData($conn) {
    $sql = "SELECT * FROM user";
    $result = $conn->query($sql);
    return $result;
}

// Function to fetch income data
function fetchIncomeData($conn, $start_date, $end_date) {
    $sql = "SELECT tanggal, SUM(pemasukan) AS total_income FROM pemasukan WHERE tanggal >= '$start_date' AND tanggal <= '$end_date' GROUP BY tanggal";
    $result = $conn->query($sql);
    return $result;
}

// Function to fetch expense data
function fetchExpenseData($conn, $start_date, $end_date) {
    $sql = "SELECT tanggal, SUM(pengeluaran) AS total_expense FROM pengeluaran WHERE tanggal >= '$start_date' AND tanggal <= '$end_date' GROUP BY tanggal";
    $result = $conn->query($sql);
    return $result;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Manajemen Keuangan</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php include "sidebar.php";?>
    <div class="container my-5">
        <h2 class="mb-4">Dashboard Manajemen Keuangan</h2>
        <form method="GET" action="" class="row g-3 mb-5">
            <div class="col-md-6">
                <label for="start_date" class="form-label">Tanggal Mulai</label>
                <input type="date" id="start_date" name="start_date" class="form-control">
            </div>
            <div class="col-md-6">
                <label for="end_date" class="form-label">Tanggal Akhir</label>
                <input type="date" id="end_date" name="end_date" class="form-control">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Tampilkan</button>
                <button type="button" class="btn btn-secondary" onclick="window.location.href='index.php'">Kembali</button>
            </div>
        </form>
        
        <?php
        $start_date = $_GET['start_date'] ?? '2022-01-01'; // Default start date
        $end_date = $_GET['end_date'] ?? date('Y-m-d'); // Default end date (today's date)

        $income_result = fetchIncomeData($conn, $start_date, $end_date);
        $expense_result = fetchExpenseData($conn, $start_date, $end_date);

        $income_dates = [];
        $income_totals = [];
        $total_income = 0;

        while ($row = $income_result->fetch_assoc()) {
            $income_dates[$row['tanggal']] = $row['total_income'];
            $total_income += $row['total_income'];
        }

        $expense_dates = [];
        $expense_totals = [];
        $total_expense = 0;

        while ($row = $expense_result->fetch_assoc()) {
            $expense_dates[$row['tanggal']] = $row['total_expense'];
            $total_expense += $row['total_expense'];
        }

        $all_dates = array_unique(array_merge(array_keys($income_dates), array_keys($expense_dates)));
        sort($all_dates);

        $income_totals = array_map(function($date) use ($income_dates) {
            return $income_dates[$date] ?? 0;
        }, $all_dates);

        $expense_totals = array_map(function($date) use ($expense_dates) {
            return $expense_dates[$date] ?? 0;
        }, $all_dates);

        if (!empty($all_dates)) {
            echo "<div class='table-responsive'>";
            echo "<h3>Grafik Pemasukan dan Pengeluaran</h3>";
            echo "<canvas id='financialChart' class='mb-4'></canvas>";

            echo "<h3>Rekap Pemasukan dan Pengeluaran</h3>";
            echo "<table class='table table-bordered table-hover'>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Total Pemasukan</th>
                            <th>Total Pengeluaran</th>
                        </tr>
                    </thead>
                    <tbody>";

            foreach ($all_dates as $date) {
                $income = $income_dates[$date] ?? 0;
                $expense = $expense_dates[$date] ?? 0;
                echo "<tr>";
                echo "<td>" . $date . "</td>";
                echo "<td>Rp" . number_format($income, 0, ',', '.') . "</td>";
                echo "<td>Rp" . number_format($expense, 0, ',', '.') . "</td>";
                echo "</tr>";
            }

            echo "</tbody>
                  </table>";

            echo "<table class='table table-bordered table-hover'>
                <thead>
                    <tr>
                        <th>Total Pemasukan</th>
                        <th>Total Pengeluaran</th>
                    </tr>
                </thead>
                <tbody>";
            echo "<h3>Total</h3>";
            echo "<tr>";
            echo "<td>Rp" . number_format($total_income, 0, ',', '.') . "</td>";
            echo "<td>Rp" . number_format($total_expense, 0, ',', '.') . "</td>";
            echo "</tr>";
            echo "</tbody>";
            echo "</table>";
            echo "</div>";

            echo "<div class='d-flex justify-content-end gap-2 mt-4'>";
            echo "<button class='btn btn-primary' id='printButton'>Cetak</button>";
            echo "<button class='btn btn-success' onclick=\"window.location.href='generate_excel.php?start_date=$start_date&end_date=$end_date'\">Excel</button>";
            echo "</div>";

            // Pass the data to JavaScript
            echo "<script>
                const allDates = " . json_encode($all_dates) . ";
                const incomeData = " . json_encode($income_totals) . ";
                const expenseData = " . json_encode($expense_totals) . ";
            </script>";
        } else {
            echo "<p>Tidak ada data untuk rentang tanggal yang dipilih.</p>";
        }
        ?>

    <script>
    const labels = allDates;
    const data = {
        labels: labels,
        datasets: [
            {
                label: 'Total Pemasukan Harian',
                data: incomeData,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            },
            {
                label: 'Total Pengeluaran Harian',
                data: expenseData,
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }
        ]
    };

    const config = {
        type: 'bar',
        data: data,
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp' + value.toLocaleString('id-ID').replace(/,/g, '.');
                        }
                    }
                }
            }
        }
    };

    const financialChart = new Chart(
        document.getElementById('financialChart'),
        config
    );

    // Convert canvas to base64 and submit as POST
    document.getElementById('printButton').addEventListener('click', function() {
        const canvas = document.getElementById('financialChart');
        const base64Image = canvas.toDataURL('image/png');
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'generate_pdf.php';

        // Create input elements
        const inputStartDate = document.createElement('input');
        inputStartDate.type = 'hidden';
        inputStartDate.name = 'start_date';
        inputStartDate.value = '<?php echo $start_date; ?>';

        const inputEndDate = document.createElement('input');
        inputEndDate.type = 'hidden';
        inputEndDate.name = 'end_date';
        inputEndDate.value = '<?php echo $end_date; ?>';

        const inputChart = document.createElement('input');
        inputChart.type = 'hidden';
        inputChart.name = 'chart';
        inputChart.value = base64Image;

        // Append inputs to form
        form.appendChild(inputStartDate);
        form.appendChild(inputEndDate);
        form.appendChild(inputChart);

        // Append form to body and submit
        document.body.appendChild(form);
        form.submit();
});
</script>
<?php include "footer.php";?>
</body>
</html>