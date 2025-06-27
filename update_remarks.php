<?php
session_start();
include 'db.php';

// Ensure the user is logged in
if (!isset($_SESSION['user']['id'])) {
    die("User not logged in.");
}

// Validate POST data
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['document_id']) && isset($_POST['remarks'])) {
    $document_id = $_POST['document_id'];
    $remarks = $_POST['remarks'];

    $sql = "UPDATE documents SET remarks = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("SQL Prepare Error: " . $conn->error);
    }

    $stmt->bind_param("si", $remarks, $document_id);
    if ($stmt->execute()) {
        echo "<script>alert('Remarks updated successfully!'); window.location.href='receive_document.php';</script>";
    } else {
        echo "<script>alert('Failed to update remarks.'); window.location.href='receive_document.php';</script>";
    }
} else {
    echo "<script>alert('Invalid request.'); window.location.href='receive_document.php';</script>";
}
?>