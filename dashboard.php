<?php
include 'database.php';
session_start();

$user = $_SESSION["username"];
$hasil = mysqli_query($conn, "SELECT * FROM user");
$jumlah = mysqli_num_rows($hasil);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        p {
            color: white !important;
        }
    </style>
</head>
<body>
<?php include "sidebar.php";?>
        <div class="content mx-5">
            <h2>Halaman Dashboard</h2>
            <h3 >Hai <strong><?= $user?></strong>, selamat datang di aplikasi Manajemen Keuangan!</h3>
           
            <div class="dropdown">
                <div class="card green" >
                    <p>Jumlah Pengguna saat ini <?= $jumlah; ?></p>
                   
                </div>
              
            </div>
        </div>
<?php include "footer.php";?>
    </div>
</body>
</html>
