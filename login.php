<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = new mysqli('localhost', 'root', '', 'manajemen_keuangan');

    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = "SELECT * FROM user WHERE nama='$username'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if ($password == $user['password']) {
            $_SESSION['username'] = $user["id"];
            $_SESSION['nama'] = $user["nama"];
            header('Location: dashboard.php'); // Redirect ke dashboard
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Sistem Keuangan</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #4B79A1, #283E51); /* Using your color palette */
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px); /* Adding blur effect */
            padding: 30px;
            border-radius: 15px;
            width: 400px;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }

        .container h2 {
            text-align: center;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            font-size: 14px;
        }

        .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .btn {
            display: block;
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            background: linear-gradient(135deg, #4B79A1, #283E51);
            color: #fff;
            transition: 0.3s;
        }

        .btn:hover {
            background: linear-gradient(135deg, #283E51, #4B79A1);
        }

        .error {
            background: #f8d7da;
            color: #842029;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
        }

        .toggle {
            margin-top: 15px;
            text-align: center;
        }

        .toggle a {
            color: #4B79A1;
            text-decoration: none;
            font-weight: 600;
        }

        .toggle a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" placeholder="Enter your username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
        <div class="toggle">
            <p>Don't have an account yet?  <a href="register.php">Register here</a>.</p>
        </div>
    </div>
</body>
</html>
