<?php
include 'database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Updated to use USER table
    $sql = "SELECT ID, Name, Password, Is_Donor FROM USER WHERE Email = '$email'";
    $result = mysqli_query($conn, $sql);

    if ($row = mysqli_fetch_assoc($result)) {
        if ($password === $row['Password']) {
            $_SESSION['user_id'] = $row['ID'];
            $_SESSION['user_name'] = $row['Name'];
            $_SESSION['is_donor'] = $row['Is_Donor'];
            header("Location: user_dashboard.php");
            exit();
        } else {
            echo "<p class='error'>❌ Password did NOT match.</p>";
        }
    } else {
        echo "<p class='error'>❌ No user found with that email.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Login</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
        }

        h2 {
            background-color: #e63946;
            color: white;
            text-align: center;
            padding: 20px;
            margin-bottom: 30px;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            margin: 50px auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        label {
            font-size: 16px;
            color: #333;
            margin-bottom: 8px;
            display: block;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #e63946;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        input[type="submit"]:hover {
            background-color: #d62828;
        }

        .error {
            color: #e63946;
            font-size: 14px;
            text-align: center;
        }

        p {
            text-align: center;
            margin-top: 20px;
        }

        a {
            color: #e63946;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <h2>User Login</h2>

    <div class="login-container">
        <form method="post">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required><br>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required><br>

            <input type="submit" value="Login">
        </form>

        <p>Don't have an account? <a href="user_register.php">Register here</a></p>
        <p><a href="index.php">🏠 Back to Home</a></p>
    </div>

</body>
</html>
