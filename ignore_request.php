<?php
include 'database.php';
session_start();

if (!isset($_GET['donor_id']) || !isset($_GET['request_id'])) {
    die("Invalid request.");
}

$donor_id = intval($_GET['donor_id']);
$request_id = intval($_GET['request_id']);

// You can also use a dedicated 'is_ignored' column instead of modifying eligibility
// $sql = "UPDATE Eligibility_Check 
//         SET is_eligible = 0 
//         WHERE Donor_ID = $donor_id AND Request_ID = $request_id";
$sql = "DELETE FROM Eligibility_Check   WHERE WHERE Donor_ID = $donor_id AND Request_ID = $request_id";

if (mysqli_query($conn, $sql)) {
    header("Location: user_dashboard.php?");
    exit();
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
