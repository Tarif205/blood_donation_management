// FILE: user_dashboard.php

<?php 
include 'database.php';
session_start();
$status = $_GET['status'] ?? '';

$flash_message = '';
if ($status === 'accepted') {
    $flash_message = '✅ Request accepted successfully.';
}


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

// Get current availability
$avail_sql = "SELECT Is_Available FROM USER WHERE ID = $user_id";
$avail_result = mysqli_query($conn, $avail_sql);
$avail_row = mysqli_fetch_assoc($avail_result);
$is_available = $avail_row['Is_Available'] ?? 0;

// Handle availability toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_availability'])) {
    $new_status = $is_available ? 0 : 1;
    $update_sql = "UPDATE USER SET Is_Available = $new_status WHERE ID = $user_id";
    mysqli_query($conn, $update_sql);
    $is_available = $new_status;
}

// Matching blood requests
$requests_sql = "SELECT r.Request_ID, u.Name AS patient_name, u.Phone_Number, r.Time_Stamp
    FROM Blood_Request r
    JOIN USER u ON r.Patient_ID = u.ID
    JOIN Eligibility_Check e ON r.Request_ID = e.Request_ID
    WHERE e.Donor_ID = $user_id
      AND e.is_eligible = 1
      AND EXISTS (
          SELECT 1 FROM USER d WHERE d.ID = e.Donor_ID AND d.Is_Available = 1
      )
    ORDER BY r.Time_Stamp DESC";

// $requests_sql = "SELECT r.Request_ID, u.Name AS patient_name, u.Phone_Number, r.Time_Stamp
//                 FROM Blood_Request r
//                 JOIN USER u ON r.Patient_ID = u.ID
//                 JOIN Requested_Blood_Type b ON r.Request_ID = b.Request_ID
//                 WHERE b.Blood_Type = '$blood_type' AND r.Patient_ID != $user_id ";
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
    <p><a href="donation_history.php">View Donation History</a></p>
    <p><a href="user_login.php">Logout</a></p>

    <form method="post" style="margin-top: 10px;">
        <p>
            <label><strong>Availability:</strong></label>
            <input type="submit" name="toggle_availability" value="<?php echo $is_available ? 'Mark as Unavailable' : 'Mark as Available'; ?>">
            <span style="margin-left:10px;"><?php echo $is_available ? '✅ Available' : '❌ Unavailable'; ?></span>
        </p>
    </form>

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
                <?php if (isset($_GET['accepted_id']) && $_GET['accepted_id'] == $row['Request_ID']): ?>
                    <p style="color: green; font-weight: bold;">✅ Request accepted successfully.</p>
                <?php endif; ?>

                <form action="accept_request.php" method="get" style="display:inline;">
                    <input type="hidden" name="donor_id" value="<?= $user_id ?>">
                    <input type="hidden" name="request_id" value="<?= $row['Request_ID'] ?>">
                    <button type="submit">✅ Accept</button>

                </form>

                <form action="ignore_request.php" method="get" style="display:inline;">
                    <input type="hidden" name="donor_id" value="<?= $user_id ?>">
                    <input type="hidden" name="request_id" value="<?= $row['Request_ID'] ?>">
                    <button type="submit">❌ Ignore</button>

                </form>

            </div>
            
        <?php endwhile; ?>
    <?php else: ?>
        <p>No matching blood requests found.</p>
    <?php endif; ?>
</body>
</html>








