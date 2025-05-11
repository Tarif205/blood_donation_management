
<?php
session_start();
include 'database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch donation history with Donor Name and Donation ID
$sql = "SELECT dh.Donation_ID, dh.Last_Donation_Date, ub.Blood_Type, u.Name AS Donor_Name
        FROM Donation_History dh
        JOIN User_Blood_Type ub ON dh.Donor_ID = ub.USER_ID
        JOIN USER u ON dh.Donor_ID = u.ID
        WHERE dh.Donor_ID = $user_id
        ORDER BY dh.Last_Donation_Date DESC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Donation History</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            margin: 0;
            padding: 0;
        }

        h2 {
            background-color: #e63946;
            color: white;
            text-align: center;
            padding: 20px;
            margin-bottom: 30px;
        }

        table {
            width: 80%;
            margin: 0 auto;
            border-collapse: collapse;
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #e63946;
            color: white;
            font-size: 16px;
        }

        tr:nth-child(even) {
            background-color: #f1f1f1;
        }

        tr:hover {
            background-color: #f8d7da;
        }

        a {
            display: inline-block;
            text-align: center;
            padding: 10px 20px;
            background-color: #e63946;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }

        a:hover {
            background-color: #d62828;
        }
    </style>
</head>
<body>

    <h2>Donation History for <?php echo htmlspecialchars($_SESSION['user_name']); ?></h2>

    <table>
        <thead>
            <tr>
                <th>Donation ID</th>
                <th>Donation Date</th>
                <th>Blood Type</th>
                <th>Donor Name</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo $row['Donation_ID']; ?></td>
                        <td><?php echo $row['Last_Donation_Date']; ?></td>
                        <td><?php echo $row['Blood_Type']; ?></td>
                        <td><?php echo $row['Donor_Name']; ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">No donation history available.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <p><a href="user_dashboard.php">Back to Dashboard</a></p>

</body>
</html>




