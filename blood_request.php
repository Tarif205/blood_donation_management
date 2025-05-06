<?php
session_start();
include 'database.php';

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to request blood.");
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Fetch patient phone and blood type
$phone_sql = "SELECT Phone_Number FROM USER WHERE ID = $user_id";
$phone_result = mysqli_query($conn, $phone_sql);
$phone_row = mysqli_fetch_assoc($phone_result);
$phone = $phone_row['Phone_Number'] ?? '';

$blood_sql = "SELECT Blood_Type FROM User_Blood_Type WHERE USER_ID = $user_id";
$blood_result = mysqli_query($conn, $blood_sql);
$blood_row = mysqli_fetch_assoc($blood_result);
$blood_type = $blood_row['Blood_Type'] ?? '';

if (!$blood_type || !$phone) {
    die("Missing user blood type or phone number.");
}

// Insert blood request
$status = 'Pending';
$req_sql = "INSERT INTO Blood_Request (Patient_Name, Patient_Phone_Number, Status, Patient_ID)
            VALUES ('$user_name', '$phone', '$status', $user_id)";

if (mysqli_query($conn, $req_sql)) {
    $request_id = mysqli_insert_id($conn);

    // Insert into Requested_Blood_Type
    $bt_sql = "INSERT INTO Requested_Blood_Type (Request_ID, Blood_Type)
               VALUES ($request_id, '$blood_type')";
    mysqli_query($conn, $bt_sql);

    // Fetch matching donors
    $donor_sql = "SELECT u.ID AS donor_id
                  FROM USER u
                  JOIN User_Blood_Type b ON u.ID = b.USER_ID
                  WHERE u.ID != $user_id AND u.Is_Donor = 1 AND b.Blood_Type = '$blood_type'";

    $donor_result = mysqli_query($conn, $donor_sql);

    while ($donor = mysqli_fetch_assoc($donor_result)) {
        $donor_id = $donor['donor_id'];

        // Fetch last donation date
        $last_sql = "SELECT Last_Donation_Date FROM Donation_History 
                     WHERE Donor_ID = $donor_id ORDER BY Last_Donation_Date DESC LIMIT 1";
        $last_result = mysqli_query($conn, $last_sql);
        $last_row = mysqli_fetch_assoc($last_result);
        $last_donation_date = $last_row['Last_Donation_Date'] ?? null;

        $is_eligible = 0;
        if (!$last_donation_date) {
            $is_eligible = 1; // never donated => eligible
        } else {
            $today = new DateTime();
            $last_donation = new DateTime($last_donation_date);
            $days_since = $today->diff($last_donation)->days;

            if ($days_since > 15) {
                $is_eligible = 1;
            }
        }

        // Get latest donation ID for this donor
        $donation_id_sql = "SELECT Donation_ID FROM Donation_History WHERE Donor_ID = $donor_id ORDER BY Donation_ID DESC LIMIT 1";
        $donation_id_result = mysqli_query($conn, $donation_id_sql);
        $donation_row = mysqli_fetch_assoc($donation_id_result);
        $donation_id = $donation_row['Donation_ID'] ?? "NULL";

        // Insert into Eligibility_Check
        $elig_sql = "INSERT INTO Eligibility_Check (Donor_ID, Donation_ID, Request_ID, Blood_Type, is_eligible)
                     VALUES ($donor_id, $donation_id, $request_id, '$blood_type', $is_eligible)";
        mysqli_query($conn, $elig_sql);
    }

    header("Location: user_dashboard.php?msg1=Request sent successfully");
    exit();
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
