<?php
// Start the session to access session variables
session_start();
include 'database.php';
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit;
}

$patient_user_id = $_SESSION['user_id']; // Get the logged-in patient's user ID

// Updated query to fetch donor details based on the logged-in patient and approved eligibility
$sql = "
    SELECT 
        e.Donor_ID, 
        e.Blood_Type, 
        u.Name AS Donor_Name, 
        u.Phone_Number AS Donor_Contact, 
        u.Email AS Donor_Email
    FROM Eligibility_Check e
    INNER JOIN Blood_Request br ON e.Request_ID = br.Request_ID
    INNER JOIN USER u ON e.Donor_ID = u.ID
    WHERE e.is_approved = 1 
    AND br.Patient_ID = '$patient_user_id'
";

$result = mysqli_query($conn, $sql);

// Start HTML output for the donor information page
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Information</title>
    <style>
        /* General body styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        /* Container for the donor info */
        .donor-info-container {
            width: 80%;
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        /* Header styling */
        .donor-info-container h1 {
            text-align: center;
            color: #333;
            font-size: 2rem;
            margin-bottom: 20px;
        }

        /* Styling for each donor info block */
        .donor-info {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            transition: background-color 0.3s ease;
        }

        .donor-info:hover {
            background-color: #f0f0f0;
        }

        /* Styling for the individual donor info fields */
        .donor-info p {
            font-size: 1rem;
            color: #333;
            margin: 8px 0;
        }

        .donor-info p strong {
            color: #007bff;
        }

        /* Styling for "No donors found" message */
        .donor-info-container p {
            text-align: center;
            font-size: 1.2rem;
            color: #777;
        }

        /* Responsive design for smaller screens */
        @media (max-width: 768px) {
            .donor-info-container {
                width: 95%;
            }

            .donor-info p {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="donor-info-container">
        <h1>Approved Donor Information</h1>
        <?php
        // Check if there are any approved donors
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<div class='donor-info'>";
                echo "<p><strong>Donor ID:</strong> " . $row['Donor_ID'] . "</p>";
                echo "<p><strong>Blood Type:</strong> " . $row['Blood_Type'] . "</p>";
                echo "<p><strong>Donor Name:</strong> " . $row['Donor_Name'] . "</p>";
                echo "<p><strong>Contact:</strong> " . $row['Donor_Contact'] . "</p>";
                echo "<p><strong>Email:</strong> " . $row['Donor_Email'] . "</p>";
                echo "</div>";
            }
        } else {
            echo "<p>No approved donors found for your request.</p>";
        }
        ?>
    </div>
</body>
</html>

<?php
// Close the database connection
mysqli_close($conn);
?>
