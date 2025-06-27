<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user']['id'])) {
    die("Unauthorized");
}

$user_id = $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $doc_id = intval($_POST['id']);

    $stmt = $conn->prepare("UPDATE documents SET is_read = 1 WHERE id = ? AND receiver_id = ?");
    $stmt->bind_param("ii", $doc_id, $user_id);
    if ($stmt->execute()) {
        echo "Marked as read.";
    } else {
        http_response_code(500);
        echo "Failed to update.";
    }
}
?>