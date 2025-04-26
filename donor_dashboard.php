<?php
include 'database.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['donor_id'])) {
    header("Location: donor_login.php");
    exit();
}

$donor_id = $_SESSION['donor_id'];
$donor_name = $_SESSION['donor_name'];

// Get donor details
$sql = "SELECT * FROM donors WHERE Donors_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $donor_id);
$stmt->execute();
$result = $stmt->get_result();
$donor = $result->fetch_assoc();
$stmt->close();

// Get donation history
$sql = "SELECT * FROM donation_history WHERE donor_id = ? ORDER BY donation_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $donor_id);
$stmt->execute();
$history_result = $stmt->get_result();
$stmt->close();

// Get active notifications
$sql = "SELECT n.*, b.required_blood_type, b.urgency_level 
        FROM notification n 
        JOIN blood_request b ON n.request_id = b.request_id 
        WHERE n.donor_id = ? AND n.response_status IS NULL";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $donor_id);
$stmt->execute();
$notification_result = $stmt->get_result();
$stmt->close();

// Handle donation response
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['respond_to_notification'])) {
    $notification_id = $_POST['notification_id'];
    $response = $_POST['response']; // 'accept' or 'decline'
    
    $sql = "UPDATE notification SET response_status = ? WHERE notification_id = ? AND donor_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $response, $notification_id, $donor_id);
    $stmt->execute();
    
    if ($response == 'accept') {
        // Add logic to handle accepted donation (e.g., create a match record)
        echo "<script>alert('Thank you for accepting the donation request!');</script>";
    }
    
    // Refresh the page to update notification status
    echo "<script>window.location.href = 'donor_dashboard.php';</script>";
    $stmt->close();
}

// Update donor availability
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_availability'])) {
    $is_available = $_POST['is_available'];
    
    $sql = "UPDATE donors SET is_Available = ? WHERE Donors_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $is_available, $donor_id);
    $stmt->execute();
    $stmt->close();
    
    // Refresh the page
    header("Location: donor_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Dashboard</title>
    <style>
        .section {
            margin-bottom: 30px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            text-align: left;
            background-color: #f2f2f2;
        }
        .notification {
            background-color: #f9f9f9;
            padding: 10px;
            margin-bottom: 10px;
            border-left: 4px solid #ff6b6b;
        }
        .urgent {
            border-left: 4px solid #ff0000;
            background-color: #ffe6e6;
        }
    </style>
</head>
<body>
    <h2>Welcome, <?php echo htmlspecialchars($donor_name); ?>!</h2>
    
    <div class="section">
        <h3>Your Profile</h3>
        <p>Blood Type: <?php echo htmlspecialchars($donor['Blood_type']); ?></p>
        <p>Last Donation: <?php echo $donor['Last_donation_date'] ? htmlspecialchars($donor['Last_donation_date']) : 'No previous donations'; ?></p>
        <p>Current Status: <?php echo $donor['is_Available'] ? 'Available for donation' : 'Not available for donation'; ?></p>
        
        <form method="post" action="">
            <input type="hidden" name="update_availability" value="1">
            <label>
                <input type="radio" name="is_available" value="1" <?php echo $donor['is_Available'] ? 'checked' : ''; ?>> Available for donation
            </label>
            <label>
                <input type="radio" name="is_available" value="0" <?php echo !$donor['is_Available'] ? 'checked' : ''; ?>> Not available
            </label>
            <button type="submit">Update Availability</button>
        </form>
    </div>
    
    <div class="section">
        <h3>Blood Donation Requests</h3>
        <?php if ($notification_result->num_rows > 0): ?>
            <?php while ($notification = $notification_result->fetch_assoc()): ?>
                <div class="notification <?php echo $notification['urgency_level'] == 'high' ? 'urgent' : ''; ?>">
                    <h4>Blood Request</h4>
                    <p>Blood Type Needed: <?php echo htmlspecialchars($notification['required_blood_type']); ?></p>
                    <p>Urgency: <?php echo htmlspecialchars($notification['urgency_level']); ?></p>
                    <p>Sent: <?php echo htmlspecialchars($notification['sent_time']); ?></p>
                    
                    <form method="post" action="">
                        <input type="hidden" name="respond_to_notification" value="1">
                        <input type="hidden" name="notification_id" value="<?php echo $notification['notification_id']; ?>">
                        <button type="submit" name="response" value="accept">Accept Request</button>
                        <button type="submit" name="response" value="decline">Decline</button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No active blood donation requests.</p>
        <?php endif; ?>
    </div>
    
    <div class="section">
        <h3>Your Donation History</h3>
        <?php if ($history_result->num_rows > 0): ?>
            <table>
                <tr>
                    <th>Date</th>
                    <th>Location</th>
                    <th>Health Status</th>
                </tr>
                <?php while ($history = $history_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($history['donation_date']); ?></td>
                        <td><?php echo htmlspecialchars($history['location']); ?></td>
                        <td><?php echo htmlspecialchars($history['health_check_status']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No donation history found.</p>
        <?php endif; ?>
    </div>
    
    <p><a href="logout.php">Logout</a></p>
</body>
</html>