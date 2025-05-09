<?php
session_start();
include 'database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['donation_done'])) {
    $donor_id = $_POST['donor_id'];
    $request_id = $_POST['request_id']; // Even if not used, retained for future-proofing
    $today = date('Y-m-d');

    // Fetch donor name
    $donor_sql = "SELECT Name FROM USER WHERE ID = $donor_id";
    $donor_result = mysqli_query($conn, $donor_sql);
    $donor_row = mysqli_fetch_assoc($donor_result);
    $donor_name = $donor_row['Name'];

    // Insert into Donation_History
    $insert_history = "INSERT INTO Donation_History (Donor_Name, Last_Donation_Date, Donor_ID)
                       VALUES ('$donor_name', '$today', $donor_id)";
    mysqli_query($conn, $insert_history);

    // Redirect to staff dashboard
    header("Location: staff_dashboard.php?status=donation_recorded");
    exit();
} else {
    echo "Invalid request.";
}
?>