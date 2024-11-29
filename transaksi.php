<?php
session_start();

// Pastikan session sudah memiliki informasi user
if (!isset($_SESSION['username'])) {
    die("Anda harus login terlebih dahulu!");
}

// Ambil user_id dari session
$user_id = $_SESSION['username'];

$conn = new mysqli("localhost", "root", "", "manajemen_keuangan");

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Proses menyimpan data pengeluaran
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pengeluaran'])) {
    $pengeluaran = $_POST['pengeluaran'];
    $kategori = $_POST['kategori_pengeluaran'];
    $tanggal = $_POST['tanggal_pengeluaran'];

    $stmt = $conn->prepare("INSERT INTO pengeluaran (user_id, pengeluaran, kategori, tanggal) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("idss", $user_id, $pengeluaran, $kategori, $tanggal);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('Pengeluaran berhasil ditambahkan!');
    window.location.href = 'transaksi.php';</script>";
    exit();
}

// Proses menyimpan data pendapatan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pendapatan'])) {
    $pendapatan = $_POST['pendapatan'];
    $kategori = $_POST['kategori_pendapatan'];
    $tanggal = $_POST['tanggal_pendapatan'];

    $stmt = $conn->prepare("INSERT INTO pemasukan (user_id, pemasukan, sumber, tanggal) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("idss", $user_id, $pendapatan, $kategori, $tanggal);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('Pendapatan berhasil ditambahkan!'); 
    window.location.href = 'transaksi.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pengeluaran dan Pendapatan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <link rel="stylesheet" href="styles.css">
    <style>
        .hidden { display: none; }
        .btn-toggle {
            width: 100%;
            margin: 10px 0;
        }
    </style>
</head>
<body>
<?php include "sidebar.php"; ?>
<div class="container mt-5">
    <h2 class="mb-4 text-center">Tambah Pengeluaran dan Pendapatan</h2>

    <!-- Button toggle -->
    <button class="btn btn-primary btn-toggle" id="btn-pengeluaran">Tambah Pengeluaran</button>
    <button class="btn btn-success btn-toggle" id="btn-pendapatan">Tambah Pendapatan</button>

    <!-- Form Pengeluaran -->
    <form method="POST" id="form-pengeluaran" class="hidden" action='transaksi.php'>
        <div class="mb-3">
            <label for="pengeluaran" class="form-label">Nominal Pengeluaran (Rp)</label>
            <input type="number" step="0.01" name="pengeluaran" id="pengeluaran" class="form-control" placeholder="Masukkan jumlah pengeluaran" required>
        </div>
        <div class="mb-3">
            <label for="kategori_pengeluaran" class="form-label">Kategori</label>
            <input type="text" name="kategori_pengeluaran" id="kategori_pengeluaran" class="form-control" placeholder="Masukkan kategori" required>
        </div>
        <div class="mb-3">
            <label for="tanggal_pengeluaran" class="form-label">Tanggal</label>
            <input type="date" name="tanggal_pengeluaran" id="tanggal_pengeluaran" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Simpan Pengeluaran</button>
    </form>

    <!-- Form Pendapatan -->
    <form method="POST" id="form-pendapatan" class="hidden" action='transaksi.php'>
        <div class="mb-3">
            <label for="pendapatan" class="form-label">Nominal Pendapatan (Rp)</label>
            <input type="number" step="0.01" name="pendapatan" id="pendapatan" class="form-control" placeholder="Masukkan jumlah pendapatan" required>
        </div>
        <div class="mb-3">
            <label for="kategori_pendapatan" class="form-label">Kategori</label>
            <input type="text" name="kategori_pendapatan" id="kategori_pendapatan" class="form-control" placeholder="Masukkan kategori" required>
        </div>
        <div class="mb-3">
            <label for="tanggal_pendapatan" class="form-label">Tanggal</label>
            <input type="date" name="tanggal_pendapatan" id="tanggal_pendapatan" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Simpan Pendapatan</button>
    </form>
</div>
<br>

<?php include "footer.php"; ?>

<script>
    // Script untuk toggle form
    $('#btn-pengeluaran').on('click', function () {
        $('#form-pengeluaran').removeClass('hidden');
        $('#form-pendapatan').addClass('hidden');
    });

    $('#btn-pendapatan').on('click', function () {
        $('#form-pendapatan').removeClass('hidden');
        $('#form-pengeluaran').addClass('hidden');
    });
</script>
</body>
</html>
