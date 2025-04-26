<?php
include 'database.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    $name = $_POST['name'];

    $age = $_POST['age'];
    $email = $_POST['email'];
    $number = $_POST['contract_number'];
    $password = $password = $_POST['password'];
    $blood_type = $_POST['blood_type'];
    $last_donation_date = $_POST['last_donation_date'];
    $health_issues = $_POST['health_issues'];
    $health_issues_details = $_POST["health_issues_details"];
    $availibility = $_POST["is_available"];
    $sql = "INSERT INTO donors (Donors_name,password,Age, Blood_type,Contract_number,Email,Last_donation_date,Health_issues,Health_issues_details,is_Available) 
            VALUES ('$name','$password','$age','$blood_type','$number','$email','$last_donation_date','$health_issues','$health_issues_details','$availibility')";
    // $stmt = $conn->prepare($sql);
    // $stmt->bind_param("sssssss", $name, $email, $password, $blood_type, $rh_factor, $last_donation_date, $health_issues);
    // $stmt->execute();
    try {
        if (mysqli_query($conn, $sql)) {
            echo "Donor registered successfully! <a href='donor_login.php'>Login Now</a>";
        } else {
            echo "Registration failed: " . mysqli_error($conn);
        }
    } catch (mysqli_sql_exception $e) {
        echo "Exception occurred: " . $e->getMessage();
    }
    
    // echo "Donor registered successfully! <a href='donor_login.php'>Login Now</a>";   
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<form method="post">
    Name:<br>
    <input type="text" name="name" required><br>
    Age:<br>
    <input type="number" name="age" required><br>
    Contract_number:<br>
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
    Health issues details:<br>
    <input type="text" name="health_issues_details"><br>
    Are you available for donation:<br>
    <input type="radio" name="is_available" value="1" required> Yes
    <input type="radio" name="is_available" value="0"> No<br>
    <input type="submit" value="Register">
</form>
</body>
</html>