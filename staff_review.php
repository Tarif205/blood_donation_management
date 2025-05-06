//file:staff_review.php

<?php
include 'database.php';

$result = mysqli_query($conn, "
SELECT ec.Donor_ID, ec.Request_ID, u.Name AS Donor_Name, br.Patient_Name
FROM Eligibility_Check ec
JOIN USER u ON ec.Donor_ID = u.ID
JOIN blood_request br ON br.Request_ID = ec.Request_ID
WHERE ec.Eligible = TRUE AND br.Status = 'Pending'
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Staff Approval</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Pending Donor Approvals</h2>
    <table border="1">
        <tr>
            <th>Donor Name</th>
            <th>Patient Name</th>
            <th>Action</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= $row['Donor_Name'] ?></td>
            <td><?= $row['Patient_Name'] ?></td>
            <td>
                <a href="approve_donor.php?request_id=<?= $row['Request_ID'] ?>&donor_id=<?= $row['Donor_ID'] ?>">Approve</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
