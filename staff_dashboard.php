<?php
session_start();
include 'database.php';

// 1. Allow only staff and check if logged in
if (!isset($_SESSION['is_staff']) || $_SESSION['is_staff'] != 1) {
    header("Location: index.php"); // Redirect to login page if not logged in as staff
    exit();
}

// Show success messages
$status = $_GET['status'] ?? '';
if ($status === 'approved') {
    echo "<p style='color:green; text-align:center;'>Request approved successfully.</p>";
} elseif ($status === 'rejected') {
    echo "<p style='color:red; text-align:center;'>Request rejected successfully.</p>";
}
// 2. Approve request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['approve'])) {
    $request_id = $_POST['request_id'];
    $donor_id = $_POST['donor_id'];

    // Mark request as approved
    $update_status = "UPDATE Blood_Request SET Status = 'Approved' WHERE Request_ID = $request_id";
    mysqli_query($conn, $update_status);

    // Mark eligibility approved
    $staff_id = $_SESSION['user_id'];
    $update_eligibility = "UPDATE Eligibility_Check 
                           SET is_approved = 1, Approved_By = $staff_id 
                           WHERE Donor_ID = $donor_id AND Request_ID = $request_id";
    mysqli_query($conn, $update_eligibility);

    // Get staff info
    $staff_sql = "SELECT Name, Phone_Number FROM USER WHERE ID = $staff_id";
    $staff_result = mysqli_query($conn, $staff_sql);
    $staff_data = mysqli_fetch_assoc($staff_result);
    $staff_name = $staff_data['Name'];
    $staff_phone = $staff_data['Phone_Number'];

    // Get patient ID
    $info_sql = "SELECT Patient_ID FROM Blood_Request WHERE Request_ID = $request_id";
    $info_result = mysqli_query($conn, $info_sql);
    $patient_id = mysqli_fetch_assoc($info_result)['Patient_ID'];

    // Send notifications
// No need to insert notifications; info will be shown on dashboard dynamically
}

// 3. Reject request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reject'])) {
    $request_id = $_POST['request_id'];
    $donor_id = $_POST['donor_id'];

    mysqli_query($conn, "DELETE FROM Eligibility_Check WHERE Donor_ID = $donor_id AND Request_ID = $request_id");
    mysqli_query($conn, "DELETE FROM Blood_Request WHERE Request_ID = $request_id");
}

// 4. Fetch all pending approvals
$query = "
SELECT ec.Donor_ID, ec.Request_ID, u.Name AS Donor_Name, br.Patient_ID, p.Name AS Patient_Name
FROM Eligibility_Check ec
JOIN USER u ON ec.Donor_ID = u.ID
JOIN Blood_Request br ON br.Request_ID = ec.Request_ID
JOIN USER p ON br.Patient_ID = p.ID
WHERE ec.is_accepted = 1 
AND ec.is_eligible = 1
";
$result = mysqli_query($conn, $query);

// 5. Fetch all panic requests
$panic_sql = "
SELECT 
    br.Request_ID, 
    u.Name AS Patient_Name, 
    u.Phone_Number AS Patient_Phone_Number, 
    br.Time_Stamp, 
    rbt.Blood_Type
FROM Blood_Request br
JOIN Requested_Blood_Type rbt ON br.Request_ID = rbt.Request_ID
JOIN USER u ON br.Patient_ID = u.ID
WHERE br.is_panic = 1
ORDER BY br.Time_Stamp DESC
";

$panic_result = mysqli_query($conn, $panic_sql);

// Fetch all donation history
$donation_history_sql = "
SELECT 
    dh.Donation_ID, 
    dh.Donor_Name, 
    dh.Last_Donation_Date, 
    u.Name AS Donor_Name, 
    u.Phone_Number AS Donor_Phone
FROM Donation_History dh
JOIN USER u ON dh.Donor_ID = u.ID
ORDER BY dh.Last_Donation_Date DESC
";
$donation_history_result = mysqli_query($conn, $donation_history_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 20px;
        }

        .header, .section-title {
            text-align: center;
            margin: 20px 0;
        }

        .header a {
            margin: 0 10px;
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: 0.3s;
        }

        .header a.logout-button {
            background-color: #dc3545;
        }

        .header a:hover {
            opacity: 0.9;
        }

        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }

        th {
            background-color: #007BFF;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: white;
            margin: 2px;
            font-size: 14px;
        }

        .btn-approve { background-color: #28a745; }
        .btn-reject { background-color: #dc3545; }
        .btn-done { background-color: #007bff; }
        .btn-delete { background-color: #343a40; }

        .btn:hover {
            opacity: 0.9;
        }

        .status-msg {
            text-align: center;
            font-size: 16px;
            margin-top: 10px;
        }

        .status-msg.green { color: green; }
        .status-msg.red { color: red; }

    </style>
</head>
<body>

<div class="header">
    <a href="external_source.php">➤ External Source Dashboard</a>
    <a href="staff_donation_history_view.php">🩸 Donation History</a>
    <a href="forecast_view.php" style="background-color: #ffc107;">📊 Blood Forecast</a>
    <a href="logout.php" class="logout-button">🚪 Log Out</a>
</div>

<?php if ($status === 'approved'): ?>
    <p class="status-msg green">Request approved successfully.</p>
<?php elseif ($status === 'rejected'): ?>
    <p class="status-msg red">Request rejected successfully.</p>
<?php endif; ?>

<h2 class="section-title">📝 Pending Donor Approvals</h2>

<table>
    <tr>
        <th>Request ID</th>
        <th>Donor Name</th>
        <th>Patient Name</th>
        <th>Actions</th>
    </tr>
    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= $row['Request_ID'] ?></td>
                <td><?= $row['Donor_Name'] ?></td>
                <td><?= $row['Patient_Name'] ?></td>
                <td>
                    <form method="post" action="approve.php" style="display:inline;">
                        <input type="hidden" name="request_id" value="<?= $row['Request_ID'] ?>">
                        <input type="hidden" name="donor_id" value="<?= $row['Donor_ID'] ?>">
                        <button type="submit" class="btn btn-approve">Approve</button>
                    </form>
                    <form method="post" action="reject.php" style="display:inline;">
                        <input type="hidden" name="request_id" value="<?= $row['Request_ID'] ?>">
                        <input type="hidden" name="donor_id" value="<?= $row['Donor_ID'] ?>">
                        <button type="submit" class="btn btn-reject">Reject</button>
                    </form>
                    <form method="post" action="donation_done.php" style="display:inline;">
                        <input type="hidden" name="request_id" value="<?= $row['Request_ID'] ?>">
                        <input type="hidden" name="donor_id" value="<?= $row['Donor_ID'] ?>">
                        <button type="submit" name="donation_done" class="btn btn-done">Donation Done</button>
                    </form>
                    <form method="post" action="delete_record.php" style="display:inline;" 
                        onsubmit="return confirm('Are you sure you want to delete this blood request?');">
                        <input type="hidden" name="request_id" value="<?= $row['Request_ID'] ?>">
                        <button type="submit" class="btn btn-delete">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="4">No pending donor approvals found.</td></tr>
    <?php endif; ?>
</table>

<h2 class="section-title">🚨 Panic Blood Requests</h2>

<table>
    <tr>
        <th>Request ID</th>
        <th>Patient Name</th>
        <th>Phone</th>
        <th>Requested Blood Type</th>
        <th>Request Time</th>
    </tr>
    <?php if (mysqli_num_rows($panic_result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($panic_result)): ?>
            <tr>
                <td><?= $row['Request_ID'] ?></td>
                <td><?= $row['Patient_Name'] ?></td>
                <td><?= $row['Patient_Phone_Number'] ?></td>
                <td><?= $row['Blood_Type'] ?></td>
                <td><?= $row['Time_Stamp'] ?></td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="5">No panic requests found.</td></tr>
    <?php endif; ?>
</table>

</body>
</html>