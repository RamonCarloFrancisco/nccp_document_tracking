<?php
session_start();
include 'db.php';

// Ensure only admin can delete
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo "Access Denied!";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['notice_id'])) {
    $notice_id = intval($_POST['notice_id']);

    // Prepared statement for security
    $stmt = $conn->prepare("DELETE FROM notices WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $notice_id);
        if ($stmt->execute()) {
            header("Location: admin_dashboard.php"); // Redirect after delete
            exit();
        } else {
            echo "Error deleting notice: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Database error: " . $conn->error;
    }
} else {
    echo "Invalid request!";
}
?>