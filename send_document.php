<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user']['id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user']['id'];
$query = $conn->prepare("SELECT users.name AS sender_name, departments.name AS sender_department 
                         FROM users 
                         JOIN departments ON users.department_id = departments.id 
                         WHERE users.id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();

$sender_name = $user['sender_name'];
$sender_department = $user['sender_department'];

$notification_message = "";
$notification_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $subject = $_POST['subject'];
    $purpose = $_POST['purpose'];
    $receiver_id = $_POST['receiver'];
    $status = isset($_POST['submit']) ? 'forwarded' : 'draft';

    $file_name = $_FILES['file']['name'];
    $file_tmp = $_FILES['file']['tmp_name'];
    $file_path = "uploads/" . basename($file_name);
    move_uploaded_file($file_tmp, $file_path);

    $sender_id = $_SESSION['user']['id'];
    $sender_dept_id = $_SESSION['user']['department_id'];

    $sql = "INSERT INTO documents (title, subject, purpose, file_path, sender_id, sender_department_id, receiver_id, receiver_department_id, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, (SELECT department_id FROM users WHERE id = ?), ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssiiiis", $title, $subject, $purpose, $file_path, $sender_id, $sender_dept_id, $receiver_id, $receiver_id, $status);

    if ($stmt->execute()) {
        $notification_message = ($status == 'forwarded') ? "Document sent successfully!" : "Document saved as draft!";
        $notification_type = "success";
    } else {
        $notification_message = "Error saving document.";
        $notification_type = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Send Document</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #eef2f7;
            margin: 0;
            padding: 0;
        }

        .form-container {
            width: 95%;
            max-width: 900px;
            margin: 30px auto;
            background-color: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .form-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        label {
            font-weight: 600;
            display: block;
            margin-top: 10px;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }



        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #5f9ea0;
            color: #fff;
            padding: 15px;
            border-radius: 10px;
            z-index: 9999;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .notification.show {
            opacity: 1;
        }

        .notification.success {
            background-color: #4CAF50;
        }

        .notification.error {
            background-color: #f44336;
        }

        .close-btn {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            float: right;
            cursor: pointer;
        }

        /* Table Responsiveness */
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
            overflow-x: auto;
            display: block;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #f2f2f2;
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

        .action-dropdown:hover .dropdown-menu {
            display: block;
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

        /* Mobile responsive layout */
        @media (max-width: 768px) {
            .form-container {
                padding: 15px;
            }

            table {
                font-size: 14px;
            }

            .btn {
                display: block;
                width: 100%;
                text-align: center;
                margin-bottom: 20px;
            }

            .dropdown-toggle {
                font-size: 16px;
                padding: 4px 10px;
            }
        }

        @media (max-width: 480px) {
            .form-container h2 {
                font-size: 20px;
            }

            input,
            select,
            textarea {
                font-size: 14px;
            }

            table {
                font-size: 13px;
            }

            .dropdown-toggle {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <div class="form-container">
        <a href="dashboard.php" class="btn">🏠 Home</a>
        <h2>Send Document</h2>
        <form method="POST" enctype="multipart/form-data">
            <label>Sender Name:</label>
            <input type="text" value="<?= htmlspecialchars($sender_name) ?>" disabled>

            <label>Sender Department:</label>
            <input type="text" value="<?= htmlspecialchars($sender_department) ?>" disabled>

            <label for="departmentSelect">Select Department:</label>
            <select id="departmentSelect" onchange="fetchUsers(this.value)" required>
                <option value="">-- Select Department --</option>
                <?php
                $deps = $conn->query("SELECT * FROM departments");
                while ($d = $deps->fetch_assoc()) {
                    echo "<option value='" . htmlspecialchars($d['id']) . "'>" . htmlspecialchars($d['name']) . "</option>";
                }
                ?>
            </select>

            <label for="userSelect">Recipient:</label>
            <select name="receiver" id="userSelect" required>
                <option value="">-- Select User --</option>
            </select>

            <label>Title</label>
            <input type="text" name="title" required>

            <label>Subject</label>
            <input type="text" name="subject" required>

            <label>Purpose</label>
            <textarea name="purpose" rows="4" required></textarea>

            <label>File</label>
            <input type="file" name="file" required>

            <button type="submit" name="submit">Send Document</button>
            <button type="submit" name="save_draft">Save as Draft</button>
        </form>

        <h2>Sent Documents</h2>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Subject</th>
                    <th>Purpose</th>
                    <th>Recipient</th>
                    <th>Department</th>
                    <th>File</th>
                    <th>Sent At</th>
                    <th>Remarks</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $conn->prepare("SELECT d.id, d.title, d.subject, d.purpose, u.name AS receiver_name, dep.name AS receiver_department, 
                                        d.file_path, d.created_at, d.remarks 
                                        FROM documents d
                                        JOIN users u ON d.receiver_id = u.id
                                        JOIN departments dep ON d.receiver_department_id = dep.id
                                        WHERE d.sender_id = ?
                                        ORDER BY d.created_at DESC");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>" . htmlspecialchars($row['title']) . "</td>
                            <td>" . htmlspecialchars($row['subject']) . "</td>
                            <td>" . htmlspecialchars($row['purpose']) . "</td>
                            <td>" . htmlspecialchars($row['receiver_name']) . "</td>
                            <td>" . htmlspecialchars($row['receiver_department']) . "</td>
                            <td><a href='" . htmlspecialchars($row['file_path']) . "' target='_blank'>View</a></td>
                            <td>" . htmlspecialchars($row['created_at']) . "</td>
                            <td>" . (!empty($row['remarks']) ? htmlspecialchars($row['remarks']) : "No remarks yet") . "</td>
                            <td>
                                <div class='action-dropdown'>
                                    <button class='dropdown-toggle'>⋮</button>
                                    <div class='dropdown-menu'>
                                        <a href='track_document.php?id=" . $row['id'] . "'>Track</a>
                                        <a href='delete_document.php?id=" . $row['id'] . "' onclick='return confirmDelete(this.href)'>Delete</a>
                                    </div>
                                </div>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='9' style='text-align:center;'>No sent documents.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <div id="deleteModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; 
    background:rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center;">
        <div style="background:white; padding:30px; border-radius:10px; text-align:center; width:90%; max-width:400px;">
            <p style="font-size:18px; margin-bottom: 20px;">Are you sure you want to delete this document?</p>
            <button id="confirmDelete"
                style="padding:10px 20px; margin-right:10px; background-color:#d9534f; color:white; border:none; border-radius:5px;">Delete</button>
            <button onclick="closeModal()"
                style="padding:10px 20px; background-color:#6c757d; color:white; border:none; border-radius:5px;">Cancel</button>
        </div>
    </div>

    <script>
        function fetchUsers(dept_id) {
            const xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById("userSelect").innerHTML = this.responseText;
                }
            };
            xhttp.open("GET", "fetch_users.php?dept_id=" + dept_id, true);
            xhttp.send();
        }

        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = 'notification ' + type;
            notification.innerHTML = `
                <div><strong>${type === "success" ? "Success:" : "Error:"}</strong> ${message}</div>
                <button class="close-btn" onclick="this.parentElement.remove()">×</button>
            `;
            document.body.appendChild(notification);
            setTimeout(() => notification.classList.add('show'), 100);
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        let deleteUrl = "";

        function confirmDelete(url) {
            deleteUrl = url;
            document.getElementById("deleteModal").style.display = "flex";
            return false;
        }

        function closeModal() {
            document.getElementById("deleteModal").style.display = "none";
            deleteUrl = "";
        }

        document.getElementById("confirmDelete").onclick = function () {
            window.location.href = deleteUrl;
        };
    </script>

    <?php if ($notification_message): ?>
        <script>
            showNotification("<?= addslashes($notification_message) ?>", "<?= $notification_type ?>");
        </script>
    <?php endif; ?>
</body>

</html>