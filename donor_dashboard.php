<?php
include 'database.php';
session_start();

// Check if donor is logged in
if (!isset($_SESSION['donor_id'])) {
    header("Location: donor_login.php");
    exit();
}

$donor_id = $_SESSION['donor_id'];
$donor_name = $_SESSION['donor_name'];

// Fetch donor info
$sql = "SELECT * FROM donors WHERE Donors_id = $donor_id";
$result = mysqli_query($conn, $sql);
$donor = mysqli_fetch_assoc($result);

// Variables
$blood_type = $donor['Blood_type'];
$is_available = $donor['is_Available'];
$health_issues = $donor['Health_issues'];
$last_donation_date = $donor['Last_Donation_Date'];

// Calculate days since last donation
$days_since = 999;
if ($last_donation_date) {
    $days_since = (new DateTime())->diff(new DateTime($last_donation_date))->days;
}

// Fetch matching blood requests based on donor's blood type
$requests_sql = "SELECT * FROM blood_requests WHERE blood_type_needed = '$blood_type' ORDER BY request_date DESC";
$requests = mysqli_query($conn, $requests_sql);

// Fetch notifications sent to this donor
$notifs_sql = "SELECT * FROM notifications WHERE donor_id = $donor_id ORDER BY sent_time DESC";
$notifs = mysqli_query($conn, $notifs_sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Donor Dashboard</title>
</head>
<body>
    <h2>Welcome, <?php echo $donor_name; ?>!</h2>

    <p><strong>Blood Type:</strong> <?php echo $blood_type; ?></p>
    <p><strong>Availability:</strong> <?php echo $is_available ? 'Yes' : 'No'; ?></p>
    <p><strong>Health Issues:</strong> <?php echo $health_issues ? 'Yes' : 'No'; ?></p>
    <p><strong>Days Since Last Donation:</strong> <?php echo $days_since; ?></p>
    <p><a href="logout.php">Logout</a></p>

    <hr>

    <h3>Matching Blood Requests</h3>
    <?php if (mysqli_num_rows($requests) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($requests)): ?>
            <div style="border: 1px solid #ccc; padding: 10px; margin: 8px;">
                <p><strong>Request ID:</strong> <?php echo $row['id']; ?></p>
                <!-- <p><strong>Urgency:</strong> <?php echo $row['urgency_level']; ?></p> -->
                <p><strong>Requested On:</strong> <?php echo $row['request_date']; ?></p>
                <!-- You can add an "Accept" button here in the future -->
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No blood requests match your blood type right now.</p>
    <?php endif; ?>

    <hr>

    <h3>Your Notifications</h3>
    <?php if (mysqli_num_rows($notifs) > 0): ?>
        <?php while ($note = mysqli_fetch_assoc($notifs)): ?>
            <div style="border: 1px dashed #999; padding: 8px; margin: 5px;">
                <p><strong>Message:</strong> <?php echo $note['message']; ?></p>
                <p><strong>Sent On:</strong> <?php echo $note['sent_time']; ?></p>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>You have no notifications yet.</p>
    <?php endif; ?>
</body>
</html>
