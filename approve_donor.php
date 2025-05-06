//file: approve_donor.php

<?php
include 'database.php';

$request_id = $_GET['request_id'];
$donor_id = $_GET['donor_id'];

mysqli_query($conn, "UPDATE blood_request SET Status = 'Approved' WHERE Request_ID = $request_id");

echo "Donor approved! Notify both donor and patient.";
?>
