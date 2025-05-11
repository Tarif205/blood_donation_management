<?php
session_start();
include 'database.php';

// Only allow staff access
if (!isset($_SESSION['is_staff']) || $_SESSION['is_staff'] != 1) {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: external_source.php");
    exit();
}

$source_id = intval($_GET['id']);
$staff_id = $_SESSION['user_id'];
$message = '';

// Check if staff is connected to this source
$check_sql = "SELECT COUNT(*) as connected FROM Connects 
              WHERE Staff_ID = $staff_id AND External_Source_ID = $source_id";
$check_result = mysqli_query($conn, $check_sql);
$is_connected = mysqli_fetch_assoc($check_result)['connected'] > 0;

// If not connected, create connection
if (!$is_connected) {
    $connect_sql = "INSERT INTO Connects (Staff_ID, External_Source_ID) VALUES ($staff_id, $source_id)";
    mysqli_query($conn, $connect_sql);
}

// Get source details
$source_sql = "SELECT * FROM External_Source WHERE ID = $source_id";
$source_result = mysqli_query($conn, $source_sql);
$source = mysqli_fetch_assoc($source_result);

if (!$source) {
    header("Location: external_source.php");
    exit();
}

// Get blood types offered by this source
$blood_sql = "SELECT Blood_Type FROM External_Source_Blood_Type WHERE External_Source_ID = $source_id";
$blood_result = mysqli_query($conn, $blood_sql);
$blood_types = [];
while ($row = mysqli_fetch_assoc($blood_result)) {
    $blood_types[] = $row['Blood_Type'];
}

// Handle request to external source
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_request'])) {
    $request_id = intval($_POST['request_id']);
    
    // Update the request to indicate external source being used
    $update_sql = "UPDATE Blood_Request SET Status = 'External Source Contacted' WHERE Request_ID = $request_id";
    
    if (mysqli_query($conn, $update_sql)) {
        // You could add code here to send an actual email to the external source
        $message = "External source has been contacted for Request #$request_id";
    } else {
        $message = "Error: " . mysqli_error($conn);
    }
}

// Get pending requests that match this external source's blood types
$requests_sql = "SELECT br.Request_ID, br.Patient_Name, br.Patient_Phone_Number, br.Time_Stamp, rbt.Blood_Type
                FROM Blood_Request br
                JOIN Requested_Blood_Type rbt ON br.Request_ID = rbt.Request_ID
                WHERE br.Status = 'Pending' 
                AND rbt.Blood_Type IN (
                    SELECT Blood_Type FROM External_Source_Blood_Type 
                    WHERE External_Source_ID = $source_id
                )
                AND NOT EXISTS (
                    SELECT 1 FROM Eligibility_Check ec 
                    WHERE ec.Request_ID = br.Request_ID AND ec.is_eligible = 1
                )";
$requests_result = mysqli_query($conn, $requests_sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>External Source Details</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 20px;
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
        }

        .container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .card {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            flex: 1;
            min-width: 300px;
        }

        .card h2, .card h5 {
            margin-top: 0;
            color: #444;
        }

        .badge {
            display: inline-block;
            padding: 5px 10px;
            font-size: 12px;
            border-radius: 12px;
            color: #fff;
            margin: 2px;
        }

        .badge.green { background-color: #28a745; }
        .badge.red { background-color: #dc3545; }
        .badge.gray { background-color: #6c757d; }

        .alert {
            background-color: #e7f3fe;
            padding: 12px;
            border-left: 4px solid #2196F3;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        button {
            padding: 6px 12px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        a.back-link {
            display: inline-block;
            margin-top: 30px;
            text-decoration: none;
            color: #007BFF;
        }

        a.back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <h1>External Source Details</h1>

    <?php if (!empty($message)): ?>
        <div class="alert"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="container">
        <!-- Source Information -->
        <div class="card">
            <h2><?php echo htmlspecialchars($source['Name']); ?></h2>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($source['Email']); ?></p>
            <p><strong>Contact:</strong> <?php echo htmlspecialchars($source['Contact_Number']); ?></p>
            <p><strong>Address:</strong> 
                <?php echo htmlspecialchars($source['Area']); ?>,
                <?php echo htmlspecialchars($source['Street_Number']); ?>,
                <?php echo htmlspecialchars($source['City_Zip']); ?>
            </p>
            <p><strong>First Contacted:</strong> <?php echo htmlspecialchars($source['Date_Requested']); ?></p>
            <p><strong>Status:</strong> 
                <span class="badge <?php echo $source['Availability'] ? 'green' : 'red'; ?>">
                    <?php echo $source['Availability'] ? 'Available' : 'Unavailable'; ?>
                </span>
            </p>
            <h5>Blood Types Available:</h5>
            <?php foreach ($blood_types as $type): ?>
                <span class="badge gray"><?php echo htmlspecialchars($type); ?></span>
            <?php endforeach; ?>
        </div>

    <a href="external_source.php" class="back-link">← Back to External Sources</a>
</body>
</html>
