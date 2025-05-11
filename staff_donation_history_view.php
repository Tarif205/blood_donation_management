<?php
// filepath: c:\xampp\htdocs\Project_cse370\blood_donation_management\donation_history.php
session_start();
include 'database.php';

// Allow only staff
if (!isset($_SESSION['is_staff']) || $_SESSION['is_staff'] != 1) {
    header("Location: index.php");
    exit();
}

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
<html>
<head>
    <title>Donation History</title>
    <style>
        table { width: 80%; margin: 20px auto; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
        button, a { padding: 5px 15px; margin: 5px; text-decoration: none; }
        a { background-color: #007BFF; color: white; border-radius: 5px; }
    </style>
</head>
<body>
<div style="text-align: center; margin-top: 20px;">
    <a href="staff_dashboard.php">⬅ Back to Dashboard</a>
</div>

<h2 style="text-align:center; margin-top: 50px;">🩸 Donation History</h2>
<table>
    <tr>
        <th>Donation ID</th>
        <th>Donor Name</th>
        <th>Phone</th>
        <th>Last Donation Date</th>
    </tr>
    <?php if (mysqli_num_rows($donation_history_result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($donation_history_result)): ?>
            <tr>
                <td><?= $row['Donation_ID'] ?></td>
                <td><?= htmlspecialchars($row['Donor_Name']) ?></td>
                <td><?= htmlspecialchars($row['Donor_Phone']) ?></td>
                <td><?= htmlspecialchars($row['Last_Donation_Date']) ?></td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="4">No donation history found.</td></tr>
    <?php endif; ?>
    
</table>
</body>
</html>