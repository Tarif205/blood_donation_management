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

    // Handle donor/patient selection
    $is_donor = isset($_POST['is_donor']) ? 1 : 0;
    $is_patient = isset($_POST['is_patient']) ? 1 : 0;

    // Check if email already exists
    $email_check_sql = "SELECT ID FROM USER WHERE Email = '$email'";
    $email_check_result = mysqli_query($conn, $email_check_sql);
    if (mysqli_num_rows($email_check_result) > 0) {
        echo "Email is already registered. Please use a different email.";
    } else {
        // Insert user into USER table
        $sql = "INSERT INTO USER (Name, Age, Email, Phone_Number, Password, Is_Donor, Is_Patient)
                VALUES ('$name', '$age', '$email', '$number', '$password', $is_donor, $is_patient)";

        if (mysqli_query($conn, $sql)) {
            $user_id = mysqli_insert_id($conn);

            // Insert into User_Blood_Type table
            mysqli_query($conn, "INSERT INTO User_Blood_Type (USER_ID, Blood_Type) VALUES ($user_id, '$blood_type')");

            // Insert into Donation_History (if last donation date provided)
            if (!empty($last_donation_date) && $is_donor == 1) {
                mysqli_query($conn, "INSERT INTO Donation_History (Donor_Name, Last_Donation_Date, Donor_ID)
                                     VALUES ('$name', '$last_donation_date', $user_id)");
            }

            // Insert health issues if any
            if ($health_issues == 1 && !empty($health_issues_details)) {
                mysqli_query($conn, "INSERT INTO User_Health_Issues (USER_ID, Health_Issues)
                                     VALUES ($user_id, '$health_issues_details')");
            }

            // Logic to insert into Eligibility_Check (only for donors)
            if ($is_donor == 1) {
                $today = new DateTime();
                $is_eligible = 0;

                if (!empty($last_donation_date)) {
                    $last_donation = new DateTime($last_donation_date);
                    $days_since = $today->diff($last_donation)->days;

                    if ($days_since > 15) {
                        $is_eligible = 1;
                    }

                    $donation_id_query = "SELECT Donation_ID FROM Donation_History WHERE Donor_ID = $user_id ORDER BY Donation_ID DESC LIMIT 1";
                    $donation_id_result = mysqli_query($conn, $donation_id_query);
                    $donation_row = mysqli_fetch_assoc($donation_id_result);
                    $donation_id = $donation_row['Donation_ID'] ?? "NULL";
                } else {
                    $is_eligible = 1;
                    $donation_id = "NULL";
                }

                $eligibility_sql = "INSERT INTO Eligibility_Check (Donor_ID, Donation_ID, Request_ID, Blood_Type, is_eligible)
                                    VALUES ($user_id, $donation_id, NULL, '$blood_type', $is_eligible)";
                mysqli_query($conn, $eligibility_sql);
            }

            echo "User registered successfully! <a href='user_login.php'>Login Now</a>";
        } else {
            echo "Registration failed: " . mysqli_error($conn);
        }
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
    <input type="text" name="health_issues_details"><br>
    Are you registering as:<br>
    <input type="checkbox" name="is_donor" value="1" checked> Donor<br>
    <input type="checkbox" name="is_patient" value="1"> Patient<br><br>
    <input type="submit" value="Register">
</form>
</body>
</html>
