<?php
$conn = new mysqli("localhost", "root", "", "nccp_document_tracking");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['notice_id'])) {
    $notice_id = $_POST['notice_id'];
    $user_id = $_SESSION['user']['id'];

    $conn->query("INSERT INTO likes (notice_id, user_id) VALUES ('$notice_id', '$user_id') ON DUPLICATE KEY UPDATE id=id");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['notice_id'], $_POST['comment'])) {
    $notice_id = $_POST['notice_id'];
    $user_id = $_SESSION['user']['id'];
    $comment = $conn->real_escape_string($_POST['comment']);

    $conn->query("INSERT INTO comments (notice_id, user_id, comment_text, created_at) VALUES ('$notice_id', '$user_id', '$comment', NOW())");
}
?>