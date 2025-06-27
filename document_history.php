<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

$userId = $_SESSION['user']['id'];

$query = "
    SELECT d.id, d.title, d.file_path, d.status, d.created_at, 
           sender.name AS sender_name, receiver.name AS receiver_name 
    FROM documents d
    LEFT JOIN users sender ON d.sender_id = sender.id
    LEFT JOIN users receiver ON d.receiver_id = receiver.id
    WHERE d.sender_id = '$userId' OR d.receiver_id = '$userId'
    ORDER BY d.created_at DESC
";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Document History</title>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <a href="dashboard.php" class="btn">🏠 Home</a>
    <h2>Document History</h2>
    <table border="1" cellpadding="10">
        <tr>
            <th>Title</th>
            <th>Sender</th>
            <th>Receiver</th>
            <th>Status</th>
            <th>Date</th>
            <th>File</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= htmlspecialchars($row['sender_name']) ?></td>
                <td><?= htmlspecialchars($row['receiver_name']) ?></td>
                <td><?= htmlspecialchars($row['status']) ?></td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
                <td><a href="<?= htmlspecialchars($row['file_path']) ?>" target="_blank">View</a></td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>

</html>

<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f5f5f5;
        padding: 40px;
        margin: 0;
    }

    h2 {
        text-align: center;
        color: #333;
        margin-bottom: 30px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        background-color: white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    th,
    td {
        padding: 15px;
        text-align: left;
    }

    th {
        background-color: #5f9ea0;
        color: white;
    }

    tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    a {
        color: #2196F3;
        text-decoration: none;
    }

    a {
        color: #5f9ea0;
        font-weight: bold;
        text-decoration: none;
    }


    .btn {
        display: inline-block;
        padding: 10px 15px;
        font-size: 16px;
        color: white;
        background-color: #5f9ea0;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        text-decoration: none;
        text-align: center;
    }

    .btn:hover {
        background-color: #4f7b7b;
    }
</style>