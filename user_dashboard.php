<?php
// FILE: user_dashboard.php
include 'database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Get blood type
$blood_sql = "SELECT Blood_Type FROM User_Blood_Type WHERE USER_ID = $user_id";
$blood_result = mysqli_query($conn, $blood_sql);
$blood_row = mysqli_fetch_assoc($blood_result);
$blood_type = $blood_row['Blood_Type'] ?? 'Unknown';

// Get last donation date
$donation_sql = "SELECT Last_Donation_Date FROM Donation_History WHERE Donor_ID = $user_id ORDER BY Last_Donation_Date DESC LIMIT 1";
$donation_result = mysqli_query($conn, $donation_sql);
$donation_row = mysqli_fetch_assoc($donation_result);
$last_donation_date = $donation_row['Last_Donation_Date'] ?? null;

$days_since = 999;
if ($last_donation_date) {
    $today = new DateTime();
    $last_donation = new DateTime($last_donation_date);
    $days_since = $today->diff($last_donation)->days;
}

// Get health issues
$health_sql = "SELECT COUNT(*) AS has_issues FROM User_Health_Issues WHERE USER_ID = $user_id";
$health_result = mysqli_query($conn, $health_sql);
$health_row = mysqli_fetch_assoc($health_result);
$health_issues = $health_row['has_issues'] > 0 ? 'Yes' : 'No';

// Matching blood requests
$requests_sql = "SELECT r.Request_ID, u.Name AS patient_name, u.Phone_Number, r.Time_Stamp
                FROM Blood_Request r
                JOIN USER u ON r.Patient_ID = u.ID
                JOIN Requested_Blood_Type b ON r.Request_ID = b.Request_ID
                WHERE b.Blood_Type = '$blood_type'";
$requests = mysqli_query($conn, $requests_sql);
?>



<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard</title>
</head>
<body>
    <h2>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h2>
    <p><strong>Blood Type:</strong> <?php echo $blood_type; ?></p>
    <p><strong>Health Issues:</strong> <?php echo $health_issues; ?></p>
    <p><strong>Days Since Last Donation:</strong> <?php echo $days_since; ?></p>
    <p><a href="user_login.php">Logout</a></p>

    <form action="blood_request.php" method="post" style="margin-top: 20px;">
        <input type="hidden" name="auto_request" value="1">
        <input type="submit" value="Send Blood Request">
    </form>


    <hr>
    <h3>Matching Blood Requests</h3>
    <?php if (mysqli_num_rows($requests) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($requests)): ?>
            <div style="border: 1px solid #ccc; padding: 10px; margin: 8px;">
                <p><strong>Patient:</strong> <?php echo $row['patient_name']; ?></p>
                <p><strong>Contact:</strong> <?php echo $row['Phone_Number']; ?></p>
                <p><strong>Requested On:</strong> <?php echo $row['Time_Stamp']; ?></p>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No matching blood requests found.</p>
    <?php endif; ?>
</body>
</html>
