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

    mysqli_query($conn, "DELETE FROM Eligibility_Check WHERE Donor_ID = $donor_id AND Request_ID = $request_id");
    mysqli_query($conn, "DELETE FROM Blood_Request WHERE Request_ID = $request_id");

    header("Location: staff_dashboard.php?status=rejected");
    exit();
}
?>
