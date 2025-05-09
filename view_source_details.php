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
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        
        .container {
            display: flex;
            gap: 20px;
        }
        
        .details-section, .requests-section {
            flex: 1;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        table, th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        
        th {
            background-color: #f2f2f2;
        }
        
        .message {
            padding: 10px;
            margin-bottom: 15px;
            background-color: #e6f7ff;
            border-left: 4px solid #1890ff;
        }
        
        .blood-type {
            display: inline-block;
            padding: 5px 10px;
            margin: 5px;
            background-color: #f8d7da;
            border-radius: 3px;
        }
        
        button {
            padding: 8px 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h1>External Source Details</h1>
    
    <?php if (!empty($message)): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <div class="container">
        <div class="details-section">
            <h2><?php echo htmlspecialchars($source['Name']); ?></h2>
            
            <p><strong>Email:</strong> <?php echo htmlspecialchars($source['Email']); ?></p>
            <p><strong>Contact:</strong> <?php echo htmlspecialchars($source['Contact_Number']); ?></p>
            <p><strong>Address:</strong> 
                <?php echo htmlspecialchars($source['Area']); ?>,
                <?php echo htmlspecialchars($source['Street_Number']); ?>,
                <?php echo htmlspecialchars($source['City_Zip']); ?>
            </p>
            <p><strong>First Contacted:</strong> <?php echo htmlspecialchars($source['Date_Requested']); ?></p>
            <p><strong>Status:</strong> <?php echo $source['Availability'] ? 'Available' : 'Unavailable'; ?></p>
            
            <h3>Blood Types Available:</h3>
            <div>
                <?php foreach ($blood_types as $type): ?>
                    <span class="blood-type"><?php echo htmlspecialchars($type); ?></span>
                <?php endforeach; ?>

        
        <div class="requests-section">
            <h2>Matching Blood Requests</h2>
            
            <?php if (mysqli_num_rows($requests_result) > 0): ?>
                <p>The following requests need blood types that this external source can provide:</p>
                
                <table>
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Patient</th>
                            <th>Contact</th>
                            <th>Blood Type</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($req = mysqli_fetch_assoc($requests_result)): ?>
                            <tr>
                                <td><?php echo $req['Request_ID']; ?></td>
                                <td><?php echo htmlspecialchars($req['Patient_Name']); ?></td>
                                <td><?php echo htmlspecialchars($req['Patient_Phone_Number']); ?></td>
                                <td><?php echo htmlspecialchars($req['Blood_Type']); ?></td>
                                <td><?php echo htmlspecialchars($req['Time_Stamp']); ?></td>
                                <td>
                                    <form method="post">
                                        <input type="hidden" name="request_id" value="<?php echo $req['Request_ID']; ?>">
                                        <button type="submit" name="send_request">Contact Source</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No matching blood requests found that need this external source.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <p><a href="external_source.php">Back to External Sources</a></p>
</body>
</html>
