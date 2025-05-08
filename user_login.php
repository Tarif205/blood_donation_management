//file: user_login.php

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
            echo "❌ Password did NOT match.";
        }
    } else {
        echo "❌ No user found with that email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Login</title>
</head>
<body>
    <h2>User Login</h2>

    <form method="post">
        Email:<br>
        <input type="email" name="email" required><br>
        Password:<br>
        <input type="password" name="password" required><br><br>
        <input type="submit" value="Login">
    </form>

    <p>Don't have an account? <a href="user_register.php">Register here</a></p>
    <p><a href="index.php">🏠 Back to Home</a></p>
</body>
</html>
