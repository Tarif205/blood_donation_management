<?php
session_start();
include 'database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $sql = "SELECT ID, Name, Password FROM USER WHERE Email = ? AND Is_Staff = 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $id, $name, $hashed_password);

    if (mysqli_stmt_fetch($stmt)) {
        if ($password === $hashed_password) {
            $_SESSION['user_id'] = $id;
            $_SESSION['user_name'] = $name;
            $_SESSION['is_staff'] = true;
            header("Location: staff_dashboard.php");
            exit();
        } else {
            $error = "❌ Incorrect password.";
        }
    } else {
        $error = "❌ Staff not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Login - Blood Bank</title>
    <style>
        body {
            background-color: #fff4f4;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            background: #fff;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.1);
            width: 100%;
            max-width: 400px;
            border: 2px solid #ff4d4d;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #d60000;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
        }

        input[type="submit"] {
            width: 100%;
            background-color: #d60000;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
        }

        input[type="submit"]:hover {
            background-color: #b30000;
        }

        .error {
            color: #dc3545;
            text-align: center;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .back-link {
            text-align: center;
            margin-top: 15px;
        }

        .back-link a {
            color: #d60000;
            text-decoration: none;
            font-weight: bold;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        .banner {
            text-align: center;
            font-size: 30px;
            margin-bottom: 15px;
        }

        .banner span {
            font-size: 38px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="banner">🩸 <strong>Blood Donation Staff Portal</strong></div>

    <h2>Staff Login</h2>

    <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <form method="post">
        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Password:</label>
        <input type="password" name="password" required>

        <input type="submit" value="Login">
    </form>

    <div class="back-link">
        <p><a href="index.php">🏠 Back to Home</a></p>
    </div>
</div>

</body>
</html>
