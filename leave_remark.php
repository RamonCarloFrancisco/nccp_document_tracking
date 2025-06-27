<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user']['id'])) {
    die("User not logged in.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $document_id = $_POST['document_id'];
    $remark = trim($_POST['remark']);

    $sql = "UPDATE documents SET remarks = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $remark, $document_id);

    if ($stmt->execute()) {
        header("Location: receive_document.php?success=Remark added");
    } else {
        header("Location: receive_document.php?error=Failed to add remark");
    }
}
?>