<?php
include 'database.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $age = $_POST['age'];
    $email = $_POST['email'];
    $number = $_POST['contract_number'];
    $password = $_POST['password'];
    $blood_type = $_POST['blood_type'];
    $last_donation_date = $_POST['last_donation_date'];
    $health_issues = $_POST['health_issues'];
    $health_issues_details = $_POST["health_issues_details"];

    $is_donor = 1;
    $is_patient = 0;

    $sql = "INSERT INTO USER (Name, Age, Email, Phone_Number, Password, Is_Donor, Is_Patient)
            VALUES ('$name', '$age', '$email', '$number', '$password', $is_donor, $is_patient)";

    if (mysqli_query($conn, $sql)) {
        $user_id = mysqli_insert_id($conn);

        // Insert into User_Blood_Type
        mysqli_query($conn, "INSERT INTO User_Blood_Type (USER_ID, Blood_Type) VALUES ($user_id, '$blood_type')");

        // Insert into Donation_History
        if (!empty($last_donation_date)) {
            mysqli_query($conn, "INSERT INTO Donation_History (Donor_Name, Last_Donation_Date, Donor_ID)
                                 VALUES ('$name', '$last_donation_date', $user_id)");
        }

        // Insert health issues
        if ($health_issues == 1 && !empty($health_issues_details)) {
            mysqli_query($conn, "INSERT INTO User_Health_Issues (USER_ID, Health_Issues)
                                 VALUES ($user_id, '$health_issues_details')");
        }

        echo "User registered successfully! <a href='user_login.php'>Login Now</a>";
    } else {
        echo "Registration failed: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Register</title>
</head>
<body>
<h2>User Registration</h2>
<form method="post">
    Name:<br>
    <input type="text" name="name" required><br>
    Age:<br>
    <input type="number" name="age" required><br>
    Contract Number:<br>
    <input type="text" name="contract_number" required><br>
    Email:<br>
    <input type="email" name="email" required><br>
    Password:<br>
    <input type="password" name="password" required><br>
    Blood Type:<br>
    <input type="text" name="blood_type" required><br>
    Last Donation Date:<br>
    <input type="date" name="last_donation_date"><br>
    Health Issues:<br>
    <input type="radio" name="health_issues" value="1" required> Yes
    <input type="radio" name="health_issues" value="0"> No<br>
    Health Issues Details:<br>
    <input type="text" name="health_issues_details"><br><br>
    <input type="submit" value="Register">
</form>
</body>
</html>
