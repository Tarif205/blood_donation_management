<?php
session_start();
include 'database.php';

// Only allow staff access
if (!isset($_SESSION['is_staff']) || $_SESSION['is_staff'] != 1) {
    header("Location: index.php");
    exit();
}

$staff_id = $_SESSION['user_id'];
$message = '';

// Search for external sources based on blood type
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $blood_type = mysqli_real_escape_string($conn, $_POST['blood_type']);
    
    $search_sql = "SELECT DISTINCT es.* 
                   FROM External_Source es
                   JOIN External_Source_Blood_Type esbt ON es.ID = esbt.External_Source_ID
                   WHERE esbt.Blood_Type = '$blood_type' AND es.Availability = 1";
    
    $search_result = mysqli_query($conn, $search_sql);
} else {
    // Show all external sources that have at least one blood type and are available
    $search_sql = "SELECT DISTINCT es.* 
                   FROM External_Source es
                   JOIN External_Source_Blood_Type esbt ON es.ID = esbt.External_Source_ID
                   WHERE es.Availability = 1";
    
    $search_result = mysqli_query($conn, $search_sql);
}


// Fetch blood types for dropdown
$blood_types = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>External Sources Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        
        .container {
            display: flex;
            gap: 20px;
        }
        
        .form-section, .results-section {
            flex: 1;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
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
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
        }
        
        input[type=text], input[type=email], select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        
        button {
            padding: 10px 15px;
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
    <h1>External Blood Sources Management</h1>
    
    <?php if (!empty($message)): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <div class="container">
        <div class="form-section">
            <h2>Search External Sources</h2>
            <form method="post">
                <div class="form-group">
                    <label for="blood_type">Blood Type:</label>
                    <select id="blood_type" name="blood_type">
                        <?php foreach ($blood_types as $type): ?>
                            <option value="<?php echo $type; ?>"><?php echo $type; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" name="search">Search</button>
            </form>
        </div>
        
        <div class="results-section">
            <h2>External Sources</h2>
            
            <?php if (isset($search_result) && mysqli_num_rows($search_result) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Email</th>
                            <th>Location</th>
                            <th>Available</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($search_result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['Name']); ?></td>
                                <td><?php echo htmlspecialchars($row['Contact_Number']); ?></td>
                                <td><?php echo htmlspecialchars($row['Email']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($row['Area']); ?>,
                                    <?php echo htmlspecialchars($row['Street_Number']); ?>,
                                    <?php echo htmlspecialchars($row['City_Zip']); ?>
                                </td>
                                <td><?php echo $row['Availability'] ? 'Yes' : 'No'; ?></td>
                                <td>
            
                                    
                                    <a href="view_source_details.php?id=<?php echo $row['ID']; ?>">
                                        <button type="button">View Details</button>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No external sources found.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <p><a href="staff_dashboard.php">Back to Dashboard</a></p>
</body>
</html>
