<?php
session_start();
include 'db.php';

// Ensure only admin can access
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo "Access Denied!";
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['title'], $_POST['description'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $user_id = $_SESSION['user']['id'];

    if (empty($title) || empty($description)) {
        $message = "<p class='error'>Title and Description cannot be empty!</p>";
    } else {
        // Use prepared statements for security
        $stmt = $conn->prepare("INSERT INTO notices (user_id, title, description, created_at) VALUES (?, ?, ?, NOW())");

        if ($stmt) {
            $stmt->bind_param("iss", $user_id, $title, $description);
            if ($stmt->execute()) {
                $message = "<p class='success'>Notice posted successfully!</p>";
            } else {
                $message = "<p class='error'>Error posting notice: " . $stmt->error . "</p>";
            }
            $stmt->close();
        } else {
            $message = "<p class='error'>Database error: " . $conn->error . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        /* General Styles */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h2,
        h3 {
            color: #333;
        }

        .input-container {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 20px;
        }

        .input-container label {
            font-weight: 600;
            color: #555;
        }

        .input-container input,
        .input-container textarea {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        .btn {
            background-color: #5f9ea0;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            border-radius: 5px;
        }

        .btn:hover {
            background-color: #4f7b7b;
        }

        .error,
        .success {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
            /* Black with opacity */
        }

        .modal-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            width: 300px;
        }

        .close-btn {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            float: right;
            cursor: pointer;
        }

        .close-btn:hover {
            color: #000;
        }

        .delete-btn {
            background-color: red;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .cancel-btn {
            background-color: #ccc;
            color: black;
            padding: 10px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .delete-btn:hover {
            background-color: darkred;
        }

        .cancel-btn:hover {
            background-color: #bbb;
        }

        .notice-card {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 8px;
            border: 1px solid #e3e3e3;
        }

        .notice-card h4 {
            margin: 0;
            font-size: 20px;
        }

        .notice-card p {
            margin: 10px 0;
            font-size: 16px;
        }

        .notices-container {
            margin-top: 20px;
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>Welcome, Admin</h2>
        <a href="dashboard.php" class="btn">Home</a>
        <h3>Post a Notice</h3>
        <?= $message; ?>
        <form method="POST" class="input-container">
            <label>Title:</label>
            <input type="text" name="title" required>

            <label>Description:</label>
            <textarea name="description" rows="4" required></textarea>

            <button type="submit" class="btn">Post Notice</button>
        </form>

        <h3>Recent Notices</h3>
        <div class="notices-container">
            <?php
            $result = $conn->query("SELECT id, title, description, created_at FROM notices ORDER BY created_at DESC");
            while ($row = $result->fetch_assoc()) {
                echo "<div class='notice-card'>
                    <h4>" . htmlspecialchars($row['title']) . "</h4>
                    <p>" . nl2br(htmlspecialchars($row['description'])) . "</p>
                    <small>Posted on: " . $row['created_at'] . "</small>
                    <button type='button' class='delete-btn' onclick='openDeleteModal(" . $row['id'] . ")'>Delete</button>
                </div>
                <hr>";
            }
            ?>
        </div>
    </div>

    <!-- Modal for delete confirmation -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h3>Are you sure you want to delete this notice?</h3>
            <form id="deleteForm" method="POST" action="delete_notice.php">
                <input type="hidden" name="notice_id" id="notice_id_to_delete">
                <button type="submit" class="delete-btn">Yes, Delete</button>
                <button type="button" class="cancel-btn" onclick="closeModal()">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        // Open the delete confirmation modal
        function openDeleteModal(noticeId) {
            document.getElementById("notice_id_to_delete").value = noticeId; // Set the notice ID to be deleted
            document.getElementById("deleteModal").style.display = "block"; // Show the modal
        }

        // Close the modal
        function closeModal() {
            document.getElementById("deleteModal").style.display = "none"; // Hide the modal
        }

        // Close the modal when the user clicks the close button
        document.querySelector(".close-btn").addEventListener("click", closeModal);

        // Close the modal if the user clicks outside the modal content
        window.onclick = function (event) {
            var modal = document.getElementById("deleteModal");
            if (event.target == modal) {
                closeModal();
            }
        };
    </script>

</body>

</html>