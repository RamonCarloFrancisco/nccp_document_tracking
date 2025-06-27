<?php
// Start session if needed
session_start();

// Include your database connection
include 'db.php'; // Make sure this file contains your DB connection code

// Check if ID is set
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Optional: Log the delete or check permissions here

    // Perform soft delete (update deleted_at field) or hard delete
    $query = "DELETE FROM documents WHERE id = ?";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            // Redirect after successful delete
            header("Location: send_document.php?delete=success");


            exit;
        } else {
            echo "Failed to delete document.";
        }
    } else {
        echo "Failed to prepare statement.";
    }
} else {
    echo "Invalid request.";
}
?>