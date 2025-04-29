<?php
session_start();
include 'database.php';

if (!isset($_SESSION['donor_id'])) {
    die("You must be logged in to request blood.");
}

$blood_type = $_POST['blood_type'];
$donor_id = $_SESSION['donor_id'];

$sql = "INSERT INTO blood_requests (blood_type_needed, request_date, requester_id) 
        VALUES ('$blood_type', NOW(), $donor_id)";

if (mysqli_query($conn, $sql)) {
    header("Location: donor_dashboard.php");
    exit;
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
