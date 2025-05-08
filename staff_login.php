<?php
session_start();
include 'database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $sql = "SELECT ID, Name, Password FROM USER WHERE Email = ? AND Is_Staff = 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $id, $name, $hashed_password);

    if (mysqli_stmt_fetch($stmt)) {
        if ($password === $hashed_password) { // Add hashing in future
            $_SESSION['user_id'] = $id;
            $_SESSION['user_name'] = $name;
            $_SESSION['is_staff'] = true; // ✅ CRITICAL FIX
            header("Location: staff_dashboard.php");
            exit();
        } else {
            echo "❌ Incorrect password.";
        }
    } else {
        echo "❌ Staff not found.";
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Staff Login</title></head>
<body>
    <h2>Staff Login</h2>
    <form method="post">
        Email:<br><input type="email" name="email" required><br>
        Password:<br><input type="password" name="password" required><br><br>
        <input type="submit" value="Login">
    </form>
    <p><a href="index.php">🏠 Back to Home</a></p>
</body>
</html>
