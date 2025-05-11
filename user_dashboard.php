
<?php 
include 'database.php';
session_start();

$status = $_GET['status'] ?? '';
$flash_message = $status === 'accepted' ? '✅ Request accepted successfully.' : '';

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

$requests = mysqli_query($conn, $requests_sql);

// Approved requests
$approved_sql = "SELECT 
    ec.Request_ID, 
    ec.Donor_ID,
    br.Patient_ID,
    br.Time_Stamp,
    donor.Name AS donor_name,
    patient.Name AS patient_name,
    patient.Phone_Number AS patient_phone,
    staff.Name AS staff_name,
    staff.Phone_Number AS staff_phone
FROM Eligibility_Check ec 
JOIN Blood_Request br ON br.Request_ID = ec.Request_ID 
JOIN USER donor ON donor.ID = ec.Donor_ID
JOIN USER patient ON patient.ID = br.Patient_ID 
LEFT JOIN USER staff ON staff.ID = ec.Approved_By 
WHERE ec.is_approved = 1
  AND ec.Donor_ID = $user_id
ORDER BY ec.Request_ID DESC";

$approved_requests = mysqli_query($conn, $approved_sql);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Donation Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

    <div class="user-welcome">
    <div class="welcome-message">
        <span class="welcome-text">Welcome back,</span>
        <span class="user-name"><?php echo htmlspecialchars($user_name); ?>!</span>
    </div>
    <div class="user-icon">
        <i class="fas fa-user-circle"></i>
    </div>
    </div>

    <?php if ($flash_message): ?>
        <p class="success"><?php echo $flash_message; ?></p>
    <?php endif; ?>

        <div class="info-box">
            <h2>My Profile</h2>
            <p><strong>Blood Type:</strong> <span class="status <?php echo $blood_type == 'Unknown' ? 'status-unavailable' : 'status-available'; ?>"><?php echo htmlspecialchars($blood_type); ?></span></p>
            <p><strong>Health Issues:</strong> <?php echo $health_issues; ?></p>
            <p><strong>Days Since Last Donation:</strong> <?php echo $days_since; ?> days</p>

            <div class="links">
                <a href="donation_history.php"><i class="fas fa-history"></i> View Donation History</a>
                <a href="user_login.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>

            <form method="post">
                <p>
                    <strong>Availability Status:</strong>
                    <button type="submit" name="toggle_availability" class="btn <?php echo $is_available ? 'btn-outline' : 'btn-success'; ?>">
                        <?php echo $is_available ? '<i class="fas fa-toggle-off"></i> Mark as Unavailable' : '<i class="fas fa-toggle-on"></i> Mark as Available'; ?>
                    </button>
                    <span class="status <?php echo $is_available ? 'status-available' : 'status-unavailable'; ?>">
                        <?php echo $is_available ? '<i class="fas fa-check-circle"></i> Available' : '<i class="fas fa-times-circle"></i> Unavailable'; ?>
                    </span>
                </p>
            </form>
        </div>
    <div style="max-width: 500px; margin: 40px auto; padding: 20px; border: 2px solid #e74c3c; border-radius: 10px; background-color: #fff5f5; text-align: center;">
    <h3 style="color: #c0392b;">🚨 Emergency Blood Request</h3>
    <p style="color: #555;">Use this only in urgent situations when immediate blood donation is required.</p>

    <form method="post" action="panic_button.php">
        <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?>">
        
        <label for="blood_type" style="font-weight: bold;">Select Blood Type:</label><br>
        <select name="blood_type" required style="margin: 10px 0; padding: 8px;">
            <option value="">--Choose--</option>
            <option value="A+">A+</option>
            <option value="A-">A-</option>
            <option value="B+">B+</option>
            <option value="B-">B-</option>
            <option value="AB+">AB+</option>
            <option value="AB-">AB-</option>
            <option value="O+">O+</option>
            <option value="O-">O-</option>
        </select><br>

        <button type="submit" name="panic" style="background-color:#e74c3c; color:white; padding:10px 25px; border:none; border-radius: 5px; cursor:pointer;">
            🚨 Send Panic Request
        </button>
    </form>
</div>


    <form action="blood_request.php" method="post">
        <input type="hidden" name="auto_request" value="1">
        <input type="submit" value="Send Blood Request" class="btn">
    </form>

    <hr>

 <!-- Matching Blood Requests -->
<div class="card">
    <h2>Matching Blood Requests</h2>
    <?php if (mysqli_num_rows($requests) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($requests)): ?>
            <div class="request-box">
                <div class="request-header">
                    <i class="fas fa-user"></i>
                    <span class="patient-name"><?php echo htmlspecialchars($row['patient_name']); ?></span>
                </div>
                <div class="request-details">
                    <p><strong>Contact:</strong> <?php echo htmlspecialchars($row['Phone_Number']); ?></p>
                    <p><strong>Requested On:</strong> <?php echo htmlspecialchars($row['Time_Stamp']); ?></p>
                </div>
                <div class="request-actions">
                    <form action="accept_request.php" method="get" style="display:inline;">
                        <input type="hidden" name="donor_id" value="<?php echo $user_id; ?>">
                        <input type="hidden" name="request_id" value="<?php echo $row['Request_ID']; ?>">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check"></i> Accept
                        </button>
                    </form>

                    <form action="ignore_request.php" method="get" style="display:inline;">
                        <input type="hidden" name="donor_id" value="<?php echo $user_id; ?>">
                        <input type="hidden" name="request_id" value="<?php echo $row['Request_ID']; ?>">
                        <button type="submit" class="btn btn-outline">
                            <i class="fas fa-times"></i> Ignore
                        </button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="no-requests">No matching blood requests found.</p>
    <?php endif; ?>
</div>

<!-- Approved Requests -->
<div class="card">
    <h2>Approved Requests</h2>
    <?php if (mysqli_num_rows($approved_requests) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($approved_requests)): ?>
            <div class="request-box approved">
                <div class="request-header">
                    <i class="fas fa-user-md"></i>
                    <span class="patient-name"><?php echo htmlspecialchars($row['patient_name'] ?? 'N/A'); ?></span>
                </div>
                <div class="request-details">
                    <p><strong>Contact:</strong> <?php echo htmlspecialchars($row['patient_phone'] ?? 'N/A'); ?></p>
                    <p><strong>Approved By:</strong> <?php echo htmlspecialchars($row['staff_name'] ?? 'Staff N/A'); ?> (<?php echo htmlspecialchars($row['staff_phone'] ?? 'Phone N/A'); ?>)</p>
                    <p><strong>Requested On:</strong> <?php echo htmlspecialchars($row['Time_Stamp'] ?? 'Unknown'); ?></p>
                </div>
                <div class="request-status">
                    <p class="flash-success"><i class="fas fa-check-circle"></i> Approved for donation.</p>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="no-requests">No approved donations yet.</p>
    <?php endif; ?>
</div>