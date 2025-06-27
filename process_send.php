<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $sender_id = $_SESSION['user']['id'];
    $sender_name = $_POST['sender_name']; // Get sender name
    $receiver_id = $_POST['receiver_id'];
    $sender_department = $_POST['sender_department'];
    $receiver_department = $_POST['receiver_department'];
    $remarks = $_POST['remarks'];

    // File Upload Handling
    $upload_dir = "uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_name = basename($_FILES["document"]["name"]);
    $file_tmp = $_FILES["document"]["tmp_name"];
    $file_path = $upload_dir . $file_name;

    // Move file to uploads folder
    if (move_uploaded_file($file_tmp, $file_path)) {
        // Insert into database
        $sql = "INSERT INTO documents (title, file_path, sender_id, receiver_id, receiver_department_id, remarks) 
        VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssississ", $title, $file_path, $sender_id, $sender_name, $sender_department, $receiver_id, $receiver_department, $remarks);

        if ($stmt->execute()) {
            echo "<script>alert('Document sent successfully!'); window.location.href='send_document.php';</script>";
        } else {
            echo "<script>alert('Database error: " . $stmt->error . "'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('File upload failed!'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('Invalid request!'); window.history.back();</script>";
}
?>