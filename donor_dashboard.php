<?php
include 'database.php';
session_start();


if (!isset($_SESSION['donor_id'])) {
    header("Location: donor_login.php");
    
}
$donor_id = $_SESSION['donor_id'];
$donor_name = $_SESSION['donor_name'];
$sql = "SELECT * FROM donors WHERE Donors_id = $donor_id";
$result = mysqli_query($conn, $sql);
$donor = mysqli_fetch_assoc($result);
$blood_type = $donor['Blood_type'];
$is_available = $donor['is_Available'];
$health_issues = $donor['Health_issues'];
$last_donation_date = $donor['Last_Donation_Date'];
$days_since = 999;
if ($last_donation_date) {
    $today = new DateTime();
    $last_donation = new DateTime($last_donation_date);
    $date_difference = $today->diff($last_donation);
    $days_since = $date_difference->days;
}


$requests_sql = "SELECT * FROM blood_requests WHERE blood_type_needed = '$blood_type' ORDER BY request_date DESC";
$requests = mysqli_query($conn, $requests_sql);


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
    <p><a href="donor_login.php">Logout</a></p>

    <hr>

</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="blood_request.php" method="post">
        <input type="hidden" name="blood_type" value="<?php echo $blood_type; ?>">
        <input type="submit" value="Request Blood">
    </form>
</body>
</html>

<?php

    echo "<h3>Blood Requests Matching You:</h3>";

    if ($is_available) {
        $requests_sql = "SELECT r.*, d.Donors_name AS requester_name, d.Contract_number AS requester_contact 
                 FROM blood_requests r
                 JOIN donors d ON r.requester_id = d.Donors_id
                 WHERE r.blood_type_needed = '$blood_type'
                 AND r.requester_id != $donor_id
                 ORDER BY r.request_date DESC";
        $requests = mysqli_query($conn, $requests_sql);

        if (mysqli_num_rows($requests) > 0) {
            while ($row = mysqli_fetch_assoc($requests)) {
                echo "<p>" . $row['requester_name'] . 
                " (Contact: " . $row['requester_contact'] . ") is requesting " .
                $row['blood_type_needed'] . " blood.</p>";
            }
        } else {
            echo "<p>No matching blood requests found.</p>";
        }
    } else {
        echo "<p>You are currently unavailable to donate blood.</p>";
    }
?>
