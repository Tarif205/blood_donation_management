<?php
session_start();
include 'database.php';

if (!isset($_SESSION['is_staff']) || $_SESSION['is_staff'] != 1) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = intval($_POST['request_id']);
    $donor_id = intval($_POST['donor_id']);
    $staff_id = intval($_SESSION['user_id']);

    // Approve request
    $update_status = "UPDATE Blood_Request SET Status = 'Approved' WHERE Request_ID = $request_id";
    mysqli_query($conn, $update_status);

    // Mark eligibility as approved
    $update_eligibility = "UPDATE Eligibility_Check 
        SET is_approved = 1, Approved_By = $staff_id 
        WHERE Donor_ID = $donor_id AND Request_ID = $request_id";
    mysqli_query($conn, $update_eligibility);

    header("Location: staff_dashboard.php?status=approved");
    exit();
}
?>
