<?php
include 'database.php';

$query = "SELECT * FROM blood_type_forecast 
          WHERE Forecast_Date >= CURDATE() 
          ORDER BY Forecast_Date, Blood_Type";
$result = $conn->query($query);

// Check if the query executed successfully and if there are any results
if (!$result) {
    echo "Error executing query: " . $conn->error;
    exit;
}

if ($result->num_rows == 0) {
    echo "No forecast data available.";
    exit;
}

$grouped = [];
while ($row = $result->fetch_assoc()) {
    $grouped[$row['Forecast_Date']][] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Type Forecast</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .forecast-container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h3 {
            color: #333;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        img {
            display: block;
            margin: 20px auto;
            max-width: 100%;
            border: 2px solid #ddd;
            border-radius: 8px;
        }
        .back-button {
            display: block;
            text-align: center;
            margin: 20px 0;
        }
        .back-button a {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .back-button a:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<h1>Blood Type Forecast</h1>

<!-- Back to Dashboard Button -->
<div class="back-button">
    <a href="staff_dashboard.php">Back to Dashboard</a>
</div>

<div class="forecast-container">
    <?php
    foreach ($grouped as $date => $entries) {
        echo "<h3>Forecast for $date</h3><table><tr><th>Blood Type</th><th>Forecast Value</th></tr>";
        foreach ($entries as $entry) {
            echo "<tr><td>{$entry['Blood_Type']}</td><td>{$entry['Forecast_Value']}</td></tr>";
        }
        echo "</table>";
    }
    ?>
    
    <img src="forecast_chart.png" alt="Blood Forecast Chart">
</div>

</body>
</html>
