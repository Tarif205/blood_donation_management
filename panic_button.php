<?php
include 'database.php';

if (isset($_POST['panic'])) {
    $user_id = intval($_POST['user_id']);
    $blood_type = $_POST['blood_type']; // Assume valid string like 'A+'

    // Insert into Blood_Request
    $insert_request = "INSERT INTO Blood_Request (Patient_ID, is_panic, Time_Stamp) VALUES (?, 1, NOW())";
    $stmt = $conn->prepare($insert_request);
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        $request_id = $conn->insert_id;

        // Insert requested blood type
        $insert_type = "INSERT INTO Requested_Blood_Type (Request_ID, Blood_Type) VALUES (?, ?)";
        $stmt2 = $conn->prepare($insert_type);
        $stmt2->bind_param("is", $request_id, $blood_type);
        $stmt2->execute();
        $stmt2->close();

        echo "<p style='color:green;'>🚨 Panic request submitted! Staff will be notified.</p>";
    } else {
        echo "<p style='color:red;'>Error submitting panic request.</p>";
    }
    $stmt->close();
}
?>
