<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$userId = $user['id'];

if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
    $uploadDir = 'uploads/profile_pictures/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = uniqid() . '_' . basename($_FILES['profile_picture']['name']);
    $targetFile = $uploadDir . $fileName;

    // Move uploaded file
    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile)) {
        // Update in database
        $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
        $stmt->bind_param("si", $targetFile, $userId);
        $stmt->execute();

        // Refresh session user data from DB
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $_SESSION['user'] = $result->fetch_assoc();
    }
}

header("Location: dashboard.php");
exit();
