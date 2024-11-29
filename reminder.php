<?php
include 'database.php';
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['username'];

try {
    // Query menggunakan prepared statement untuk mendapatkan tagihan jatuh tempo dalam 3 hari ke depan
    $sql = "SELECT * FROM tagihan 
            WHERE user_id = ? 
            AND due_date IS NOT NULL 
            AND due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id); // "i" berarti tipe integer
    $stmt->execute();
    $result = $stmt->get_result();

    // Periksa apakah ada hasil
    $tagihan = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $tagihan[] = $row;
        }
    }
} catch (Exception $e) {
    echo "Terjadi kesalahan: " . $e->getMessage();
    exit;
}

// Proses form untuk menambahkan tagihan baru
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = $_POST['category'] ?? '';
    $amount = $_POST['amount'] ?? 0;
    $due_date = $_POST['due_date'] ?? null;
    $description = $_POST['description'] ?? '';

    // Validasi sederhana
    if (!empty($category) && !empty($due_date) && $amount > 0) {
        $sql = "INSERT INTO tagihan (user_id, category, amount, due_date, description) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isdss", $user_id, $category, $amount, $due_date, $description);
        if ($stmt->execute()) {
            header('Location: ' . $_SERVER['PHP_SELF']); // Refresh halaman untuk menampilkan data terbaru
            exit;
        } else {
            $error_message = "Gagal menambahkan tagihan. Silakan coba lagi.";
        }
    } else {
        $error_message = "Harap isi semua data dengan benar.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <title>Pengingat Tagihan</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
          
        }
    </style>
</head>
<body>

<?php include "sidebar.php"; ?>
<h1>Pengingat Tagihan</h1>

<?php if (!empty($tagihan)): ?>
    <ul class="list-group my-4">
        <?php foreach ($tagihan as $row): ?>
            <li class="list-group-item">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-1"><?= htmlspecialchars($row['category']); ?></h5>
                    <span class="badge bg-primary text-white">Rp<?= number_format($row['amount'], 0, ',', '.'); ?></span>
                </div>
                <p class="mb-1">
                    <strong>Jatuh Tempo:</strong> <?= htmlspecialchars($row['due_date']); ?><br>
                    <strong>Deskripsi:</strong> <?= htmlspecialchars($row['description']); ?>
                </p>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p class="alert alert-info">Tidak ada tagihan jatuh tempo dalam 3 hari ke depan.</p>
<?php endif; ?>

<!-- Form untuk menambahkan tagihan baru -->
<div class="mx-4 mb-4">
    <h2>Tambah Tagihan Baru</h2>
    <?php if (isset($error_message)): ?>
        <p class="alert alert-danger"><?= htmlspecialchars($error_message); ?></p>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label for="category" class="form-label">Kategori</label>
            <input type="text" class="form-control" id="category" name="category" required>
        </div>
        <div class="mb-3">
            <label for="amount" class="form-label">Jumlah (Rp)</label>
            <input type="number" class="form-control" id="amount" name="amount" required>
        </div>
        <div class="mb-3">
            <label for="due_date" class="form-label">Tanggal Jatuh Tempo</label>
            <input type="date" class="form-control" id="due_date" name="due_date" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Deskripsi</label>
            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
        </div>
        <button type="submit" class="btn btn-success">Tambah Tagihan</button>
    </form>
</div>

<?php include "footer.php"; ?>
</body>
</html>
