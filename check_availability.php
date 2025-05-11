<?php
include 'database.php';

// Get count of available donors by blood type
$sql = "
SELECT ubt.Blood_Type, COUNT(*) AS available_donors
FROM USER u
JOIN User_Blood_Type ubt ON u.ID = ubt.USER_ID
WHERE u.Is_Donor = 1 AND u.Is_Available = 1
GROUP BY ubt.Blood_Type
ORDER BY ubt.Blood_Type
";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Available Blood Units</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
        }
        table {
            border-collapse: collapse;
            width: 50%;
            margin-bottom: 30px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #aaa;
            text-align: center;
        }
        .back-btn {
            padding: 8px 15px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            text-decoration: none;
        }
    </style>
</head>
<body>

    <h2>🩸 Available Blood Units (by Blood Type)</h2>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <table>
            <tr>
                <th>Blood Type</th>
                <th>Available Donors</th>
            </tr>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['Blood_Type']); ?></td>
                    <td><?php echo $row['available_donors']; ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No available donors at the moment.</p>
    <?php endif; ?>

    <a href="user_dashboard.php" class="back-btn">← Back to Dashboard</a>

</body>
</html>
