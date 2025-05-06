<?php
include 'database.php';
session_start();

if (!isset($_GET['donor_id']) || !isset($_GET['request_id'])) {
    die("Invalid request.");
}

$donor_id = intval($_GET['donor_id']);
$request_id = intval($_GET['request_id']);

$sql = "UPDATE Eligibility_Check 
        SET is_accepted = 1 
        WHERE Donor_ID = $donor_id AND Request_ID = $request_id";

if (mysqli_query($conn, $sql)) {
    header("Location: user_dashboard.php?accepted_id=$request_id");

    exit();
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
