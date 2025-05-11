<?php
include 'database.php';

$message = '';
$message_type = '';

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
    $is_donor = isset($_POST['is_donor']) ? 1 : 0;
    $is_patient = isset($_POST['is_patient']) ? 1 : 0;

    $email_check_sql = "SELECT ID FROM USER WHERE Email = '$email'";
    $email_check_result = mysqli_query($conn, $email_check_sql);

    if (mysqli_num_rows($email_check_result) > 0) {
        $message = "Email is already registered. Please use a different email.";
        $message_type = "error";
    } else {
        $sql = "INSERT INTO USER (Name, Age, Email, Phone_Number, Password, Is_Donor, Is_Patient)
                VALUES ('$name', '$age', '$email', '$number', '$password', $is_donor, $is_patient)";

        if (mysqli_query($conn, $sql)) {
            $user_id = mysqli_insert_id($conn);

            mysqli_query($conn, "INSERT INTO User_Blood_Type (USER_ID, Blood_Type) VALUES ($user_id, '$blood_type')");

            if (!empty($last_donation_date) && $is_donor == 1) {
                mysqli_query($conn, "INSERT INTO Donation_History (Donor_Name, Last_Donation_Date, Donor_ID)
                                     VALUES ('$name', '$last_donation_date', $user_id)");
            }

            if ($health_issues == 1 && !empty($health_issues_details)) {
                mysqli_query($conn, "INSERT INTO User_Health_Issues (USER_ID, Health_Issues)
                                     VALUES ($user_id, '$health_issues_details')");
            }

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

            $message = "User registered successfully! <a href='user_login.php'>Login Now</a>";
            $message_type = "success";
        } else {
            $message = "Registration failed: " . mysqli_error($conn);
            $message_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Register</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fff5f5;
            margin: 0;
            padding: 0;
        }
        .form-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 50px 20px;
        }
        form {
            background-color: #fff;
            padding: 25px 30px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(255, 0, 0, 0.2);
            max-width: 500px;
            width: 100%;
        }
        h2 {
            margin-top: 0;
            color: #e53935;
            text-align: center;
        }
        input[type="text"], input[type="number"], input[type="email"], input[type="password"], input[type="date"] {
            width: 100%;
            padding: 10px;
            margin: 6px 0 15px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        input[type="submit"] {
            background-color: #e53935;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            font-weight: bold;
        }
        input[type="submit"]:hover {
            background-color: #c62828;
        }
        .message {
            padding: 12px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 15px;
        }
        .success {
            background-color: #e8f5e9;
            color: #388e3c;
            border: 1px solid #c8e6c9;
        }
        .error {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        label {
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="form-wrapper">
    <form method="post">
        <?php if (!empty($message)): ?>
            <div class="message <?= $message_type ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <h2>User Registration</h2>
        <label>Name:</label>
        <input type="text" name="name" required>

        <label>Age:</label>
        <input type="number" name="age" required>

        <label>Contract Number:</label>
        <input type="text" name="contract_number" required>

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Password:</label>
        <input type="password" name="password" required>

        <label>Blood Type:</label>
        <input type="text" name="blood_type" required>

        <label>Last Donation Date:</label>
        <input type="date" name="last_donation_date">

        <label>Health Issues:</label><br>
        <input type="radio" name="health_issues" value="1" required> Yes
        <input type="radio" name="health_issues" value="0"> No<br><br>

        <label>Health Issues Details:</label>
        <input type="text" name="health_issues_details">

        <label>Registering as:</label><br>
        <input type="checkbox" name="is_donor" value="1" checked> Donor
        <input type="checkbox" name="is_patient" value="1"> Patient<br><br>

        <input type="submit" value="Register">
    </form>
</div>
</body>
</html>
