<?php
session_start();
include 'database.php';

// 1. Allow only staff
if (!isset($_SESSION['is_staff']) || $_SESSION['is_staff'] != 1) {
    header("Location: index.php");
    exit();
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
    $staff_id = $_SESSION['user_id'];
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
    $message = "✅ Your donation request #$request_id has been approved by Staff $staff_name (Phone: $staff_phone). Please contact staff for further steps.";
    mysqli_query($conn, "INSERT INTO Notifications (user_id, message) VALUES ($donor_id, '$message')");
    mysqli_query($conn, "INSERT INTO Notifications (user_id, message) VALUES ($patient_id, '$message')");
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
WHERE ec.is_accepted = 1 AND (ec.is_approved = 0 OR ec.is_approved IS NULL)
";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Staff Dashboard</title>
    <style>
        table { width: 80%; margin: 20px auto; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
        button { padding: 5px 15px; margin: 5px; }
    </style>
</head>
<body>
    <h2 style="text-align:center;">Staff Dashboard - Pending Donor Approvals</h2>

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
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="request_id" value="<?= $row['Request_ID'] ?>">
                            <input type="hidden" name="donor_id" value="<?= $row['Donor_ID'] ?>">
                            <button type="submit" name="approve" style="background:green; color:white;">Approve</button>
                        </form>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="request_id" value="<?= $row['Request_ID'] ?>">
                            <input type="hidden" name="donor_id" value="<?= $row['Donor_ID'] ?>">
                            <button type="submit" name="reject" style="background:red; color:white;">Reject</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4">No pending donor approvals found.</td></tr>
        <?php endif; ?>
    </table>
</body>
</html>
