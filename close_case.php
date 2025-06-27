<?php
session_start();
include 'db.php';

if (!isset($_GET['id'])) {
    die("No document ID provided.");
}

$document_id = intval($_GET['id']);

$stmt = $conn->prepare("UPDATE documents SET status = 'closed' WHERE id = ?");
$stmt->bind_param("i", $document_id);

if ($stmt->execute()) {
    echo "<script>alert('Case closed successfully.'); window.location.href='receive_document.php';</script>";
} else {
    echo "Error closing case: " . $conn->error;
}
