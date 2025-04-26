<?php
include 'database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $sql = "SELECT Donors_id, Donors_name, password FROM donors WHERE Email = '$email'";
    $result = mysqli_query($conn, $sql);

    if ($row = mysqli_fetch_assoc($result)) {
        echo "Entered Password: $password<br>";
        echo "DB Password: " . $row['password'] . "<br>";

        if ($password === $row['password']) {
            echo "✅ Password matched. Login successful!";
            $_SESSION['donor_id'] = $row['Donors_id'];
            $_SESSION['donor_name'] = $row['Donors_name'];
            header("Location: donor_dashboard.php");
            exit();
        } else {
            echo "Password did NOT match.";
        }
    } else {
        echo "No user found with that email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Donor Login</title>
</head>
<body>
    <h2>Donor Login</h2>

    <?php if (!empty($error)): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="post">
        Email:<br>
        <input type="email" name="email" required><br>
        Password:<br>
        <input type="password" name="password" required><br><br>
        <input type="submit" value="Login">
    </form>

    <p>Don't have an account? <a href="donor_register.php">Register here</a></p>
</body>
</html>
