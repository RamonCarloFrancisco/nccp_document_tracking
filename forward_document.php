<?php
session_start();
include 'db.php';

// Check if document ID is provided
if (!isset($_GET['id'])) {
    die("Document ID not provided.");
}

$document_id = intval($_GET['id']);


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_receiver_id = intval($_POST['new_receiver_id']);

    $stmt = $conn->prepare("UPDATE documents SET receiver_id = ?, date_sent = NOW() WHERE id = ?");
    $stmt->bind_param("ii", $new_receiver_id, $document_id);

    if ($stmt->execute()) {
        echo "<script>alert('Document forwarded successfully.'); window.location.href='receive_document.php';</script>";
    } else {
        echo "Error forwarding document: " . $conn->error;
    }
    exit;
}

// Fetch all users (to display as possible receivers)
$users_result = $conn->query("SELECT id, name FROM users ORDER BY name ASC");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Forward Document</title>
</head>

<body>
    <h2>Forward Document</h2>
    <form method="POST">
        <label>Select new receiver:</label><br>
        <select name="new_receiver_id" required>
            <option value="">-- Select User --</option>
            <?php while ($user = $users_result->fetch_assoc()): ?>
                <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?></option>
            <?php endwhile; ?>
        </select><br><br>
        <button type="submit">Forward</button>
        <a href="receive_document.php">Cancel</a>
    </form>
</body>

</html>