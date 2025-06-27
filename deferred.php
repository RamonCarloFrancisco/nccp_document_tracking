<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user']['id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user']['id'];

$query = $conn->prepare("SELECT d.id, d.title, d.file_path, d.created_at, 
                                u.name AS receiver_name, dep.name AS receiver_department 
                         FROM documents d
                         LEFT JOIN users u ON d.receiver_id = u.id
                         LEFT JOIN departments dep ON d.receiver_department_id = dep.id
                         WHERE d.sender_id = ? AND d.status = 'draft'
                         ORDER BY d.created_at DESC");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Saved Drafts</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7fc;
            margin: 0;
        }

        .container {
            width: 80%;
            margin: 30px auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #5f9ea0;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            padding: 8px 12px;
            background-color: #5f9ea0;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .back-link:hover {
            background-color: #4f7b7b;
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 15px;
            border: none;
            background-color: #5f9ea0;
            color: white;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover {
            background-color: #497f81;
        }

        .btn {
            display: inline-block;
            margin-bottom: 15px;
            padding: 8px 15px;
            background-color: #5f9ea0;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }
    </style>
</head>

<body>
    <div class="container">
        <a href="dashboard.php" class="btn">🏠 Home</a>
        <h2>Saved Drafts</h2>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Recipient</th>
                    <th>Department</th>
                    <th>File</th>
                    <th>Saved At</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['title']) ?></td>
                            <td><?= htmlspecialchars($row['receiver_name'] ?? 'Not Set') ?></td>
                            <td><?= htmlspecialchars($row['receiver_department'] ?? 'Not Set') ?></td>
                            <td>
                                <?php if ($row['file_path']): ?>
                                    <a href="<?= htmlspecialchars($row['file_path']) ?>" target="_blank">View</a>
                                <?php else: ?>
                                    No file
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row['created_at']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center;">No drafts found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>