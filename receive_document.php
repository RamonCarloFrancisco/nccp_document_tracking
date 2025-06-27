<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user']['id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user']['id'];

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

$sql = "SELECT d.id, d.title, d.subject, d.purpose, d.file_path, d.remarks, d.date_sent, d.is_read,  
               u.name AS sender_name, dept.name AS sender_department
        FROM documents d
        JOIN users u ON d.sender_id = u.id
        JOIN departments dept ON d.sender_department_id = dept.id
        WHERE d.receiver_id = ? 
        ORDER BY d.date_sent DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL Prepare Error: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Received Documents</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>

<body>

    <div class="document-container">
        <a href="dashboard.php" class="btn">🏠 Home</a>
        <h2>Received Documents</h2>

        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Subject</th>
                    <th>Purpose</th>
                    <th>Sender</th>
                    <th>Department</th>
                    <th>Date Sent</th>
                    <th>Action</th>
                    <th>Leave Remarks</th>
                </tr>
            </thead>
            <tbody id="documentList">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="<?= $row['is_read'] ? '' : 'highlight' ?>" id="row-<?= $row['id'] ?>">
                            <td><?= htmlspecialchars($row['title']) ?></td>
                            <td><?= htmlspecialchars($row['subject']) ?></td>
                            <td><?= htmlspecialchars($row['purpose']) ?></td>
                            <td><?= htmlspecialchars($row['sender_name']) ?></td>
                            <td><?= htmlspecialchars($row['sender_department']) ?></td>
                            <td><?= isset($row['date_sent']) ? htmlspecialchars($row['date_sent']) : "N/A" ?></td>
                            <td>
                                <div class="action-dropdown">
                                    <button class="dropdown-toggle">⋮</button>
                                    <div class="dropdown-menu">
                                        <a href="#" class="action-btn view" data-id="<?= $row['id'] ?>"
                                            data-file="<?= htmlspecialchars($row['file_path']) ?>">View</a>
                                        <a href="track_document.php?id=<?= $row['id'] ?>" class="action-btn track">Track</a>
                                        <a href="delete_document.php?id=<?= $row['id'] ?>" class="action-btn delete"
                                            onclick="return confirm('Are you sure you want to delete this document?');">Delete</a>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <form method="POST" action="leave_remark.php">
                                    <input type="hidden" name="document_id" value="<?= $row['id'] ?>">
                                    <input type="text" name="remark" placeholder="Leave a remark" required>
                                    <button type="submit">Submit</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align:center;">No received documents.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        document.querySelectorAll('.dropdown-toggle').forEach(button => {
            button.addEventListener('click', function (e) {
                e.stopPropagation();
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    if (menu !== this.nextElementSibling) {
                        menu.style.display = 'none';
                    }
                });
                const menu = this.nextElementSibling;
                menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
            });
        });

        document.addEventListener('click', function () {
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.style.display = 'none';
            });
        });

        // Handle AJAX read and open
        document.querySelectorAll('.action-btn.view').forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const docId = this.dataset.id;
                const filePath = this.dataset.file;
                const row = document.getElementById("row-" + docId);

                fetch('mark_as_read.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'id=' + encodeURIComponent(docId)
                })
                    .then(response => response.text())
                    .then(data => {
                        row.classList.remove('highlight');
                        window.open(filePath, '_blank');
                    })
                    .catch(err => {
                        alert("Failed to mark as read.");
                        console.error(err);
                    });
            });
        });
    </script>

</body>

</html>

<style>
    .document-container {
        width: 80%;
        margin: auto;
        font-family: 'Poppins', sans-serif;
    }

    h2 {
        text-align: center;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    th {
        background-color: #f2f2f2;
    }

    tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    tr:hover {
        background-color: #ddd;
    }

    .highlight {
        background-color: #fff6cc !important;
        font-weight: bold;
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

    .action-btn {
        display: inline-block;
        padding: 5px 8px;
        margin: 2px;
        font-size: 13px;
        border-radius: 4px;
        color: white;
        text-decoration: none;
    }

    .view {
        background-color: #4CAF50;
    }

    .track {
        background-color: #2196F3;
    }

    .delete {
        background-color: #f44336;
    }

    .action-dropdown {
        position: relative;
    }

    .dropdown-toggle {
        background-color: #5f9ea0;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 18px;
        cursor: pointer;
    }

    .dropdown-menu {
        display: none;
        position: absolute;
        right: 0;
        background-color: #ffffff;
        border: 1px solid #ddd;
        border-radius: 6px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        margin-top: 5px;
        z-index: 1000;
        min-width: 140px;
    }

    .dropdown-menu a {
        display: block;
        padding: 10px 15px;
        color: #333;
        text-decoration: none;
    }

    .dropdown-menu a:hover {
        background-color: #f0f0f0;
    }
</style>