<?php
// Koneksi ke database
session_start();
$conn = new mysqli("localhost", "root", "", "manajemen_keuangan");

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Query transaksi berdasarkan kategori
$user = $_SESSION["username"];
$kategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';
$sql = "SELECT p.*, u.nama AS user_nama 
        FROM pengeluaran p 
        JOIN user u ON p.user_id = u.id 
        WHERE kategori = ? and user_id = $user";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $kategori);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Berdasarkan Kategori</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="styles.css">
    <style>
        footer {
        margin-top: 10rem;
        }
    </style>
</head>
<body>
<?php include "sidebar.php";?>
<div class="container mt-5">
    <h2>Transaksi Berdasarkan Kategori</h2>
    <form method="GET" class="mt-3">
        <div class="mb-3">
            <label for="kategori" class="form-label">Pilih Kategori</label>
            <input type="text" name="kategori" id="kategori" class="form-control" value="<?php echo htmlspecialchars($kategori); ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Tampilkan</button>
    </form>

    <?php if ($kategori): ?>
        <h3 class="mt-4">Hasil untuk kategori: <?php echo htmlspecialchars($kategori); ?></h3>
        <?php if ($result->num_rows > 0): ?>
            <table class="table table-bordered mt-3">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama User</th>
                    <th>Pengeluaran</th>
                    <th>Kategori</th>
                    <th>Tanggal</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['user_nama']); ?></td>
                        <td>Rp<?php echo number_format($row['pengeluaran'], 2); ?></td>
                        <td><?php echo htmlspecialchars($row['kategori']); ?></td>
                        <td><?php echo $row['tanggal']; ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-warning">Tidak ada transaksi untuk kategori ini.</div>
        <?php endif; ?>
    <?php endif; ?>
    <?php include "footer.php";?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
