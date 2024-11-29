<?php
$user = $_SESSION["nama"];
?>
<div class="sidebar">
        <div class="profile">
            <h3><?=$user;?></h3>
            <p>Administrator</p>
        </div>
        <nav>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="transaksi.php">Transaksi</a></li>
                <li><a href="kategori_keuangan.php">Kategori Keuangan</a></li>
                <li><a href="grafik.php">Grafik dan Laporan</a></li>
                <li><a href="reminder.php">Tagihan</a></li>
                <li><a href="logout.php" onclick="return confirm('apakah '">Logout</a></li>
            </ul>
        </nav>
    </div>

    <div class="main">
        <header>
            <h1>Home</h1>
            <p>Dashboard</p>

        </header>