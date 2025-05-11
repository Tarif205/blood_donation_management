<?php
include 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'])) {
    $request_id = intval($_POST['request_id']);

    $sql = "DELETE FROM Blood_Request WHERE Request_ID = $request_id";
    if (mysqli_query($conn, $sql)) {
        // Optional: redirect or echo success
        header("Location: staff_dashboard.php?status=deleted");
    } else {
        echo "Error deleting record: " . mysqli_error($conn);
    }
}
?>
