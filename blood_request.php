<?php
session_start();
include 'database.php';

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to request blood.");
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Fetch phone number
$phone_sql = "SELECT Phone_Number FROM USER WHERE ID = $user_id";
$phone_result = mysqli_query($conn, $phone_sql);
$phone_row = mysqli_fetch_assoc($phone_result);
$phone = $phone_row['Phone_Number'] ?? '';

// Fetch blood type
$blood_sql = "SELECT Blood_Type FROM User_Blood_Type WHERE USER_ID = $user_id";
$blood_result = mysqli_query($conn, $blood_sql);
$blood_row = mysqli_fetch_assoc($blood_result);
$blood_type = $blood_row['Blood_Type'] ?? '';

if (!$blood_type || !$phone) {
    die("Missing user blood type or phone number.");
}

// Insert blood request
$status = 'Pending';
$req_sql = "INSERT INTO Blood_Request (Patient_Name, Patient_Phone_Number, Status, Patient_ID)
            VALUES ('$user_name', '$phone', '$status', $user_id)";

if (mysqli_query($conn, $req_sql)) {
    $request_id = mysqli_insert_id($conn);

    // Insert into Requested_Blood_Type
    $bt_sql = "INSERT INTO Requested_Blood_Type (Request_ID, Blood_Type)
               VALUES ($request_id, '$blood_type')";
    mysqli_query($conn, $bt_sql);

    header("Location: user_dashboard.php?msg=Request sent successfully");
    exit();
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
