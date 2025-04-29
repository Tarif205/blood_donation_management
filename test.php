<h3>Matching Blood Requests</h3>
    <?php if (mysqli_num_rows($requests) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($requests)): ?>
            <div style="border: 1px solid #ccc; padding: 10px; margin: 8px;">
                <p><strong>Request ID:</strong> <?php echo $row['id']; ?></p>
                <!-- <p><strong>Urgency:</strong> <?php echo $row['urgency_level']; ?></p> -->
                <p><strong>Requested On:</strong> <?php echo $row['request_date']; ?></p>
                <!-- You can add an "Accept" button here in the future -->
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No blood requests match your blood type right now.</p>
    <?php endif; ?>

    <hr>

    <h3>Your Notifications</h3>
    <?php if (mysqli_num_rows($notifs) > 0): ?>
        <?php while ($note = mysqli_fetch_assoc($notifs)): ?>
            <div style="border: 1px dashed #999; padding: 8px; margin: 5px;">
                <p><strong>Message:</strong> <?php echo $note['message']; ?></p>
                <p><strong>Sent On:</strong> <?php echo $note['sent_time']; ?></p>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>You have no notifications yet.</p>
    <?php endif; ?>
</body>
</html>

// Fetch notifications sent to this donor
$notifs_sql = "SELECT * FROM notifications WHERE donor_id = $donor_id ORDER BY sent_time DESC";
$notifs = mysqli_query($conn, $notifs_sql);