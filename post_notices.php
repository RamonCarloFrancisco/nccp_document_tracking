<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    die("Access Denied!");
}

include 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Trim input and prevent XSS
    $title = htmlspecialchars(trim($_POST['title']), ENT_QUOTES, 'UTF-8');
    $description = htmlspecialchars(trim($_POST['description']), ENT_QUOTES, 'UTF-8');
    $user_id = $_SESSION['user']['id'];

    if (empty($title) || empty($description)) {
        echo "Title and Description cannot be empty!";
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO notices (user_id, title, description, created_at) VALUES (?, ?, ?, NOW())");

    if (!$stmt) {
        echo "Database error: " . $conn->error;
        exit();
    }

    $stmt->bind_param("iss", $user_id, $title, $description);

    if ($stmt->execute()) {
        header("Location: dashboard.php?message=Notice posted successfully!");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>