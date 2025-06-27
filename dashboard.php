<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';
include 'functions.php';

$user = $_SESSION['user']; // Make sure this is defined before use
$userId = $user['id']; // Now this won't cause an error

// Profile picture fallback
$profilePath = 'uploads/profile_pictures/default.png';
if (!empty($user['profile_picture']) && file_exists($user['profile_picture'])) {
    $profilePath = $user['profile_picture'];
}

// Received Documents
$receivedCount = 0;
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM documents WHERE receiver_id = '$userId'");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $receivedCount = $row['total'] ?? 0;
}

// Forwarded Documents
$forwardedCount = 0;
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM documents WHERE sender_id = '$userId' AND status = 'forwarded'");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $forwardedCount = $row['total'] ?? 0;
}

// Deferred Documents
$deferredCount = 0;
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM documents WHERE status = 'draft' AND receiver_id = '$userId'");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $deferredCount = $row['total'] ?? 0;
}

// Documents History
$historyCount = 0;
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM documents WHERE sender_id = '$userId' OR receiver_id = '$userId'");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $historyCount = $row['total'] ?? 0;
}

// Handle default profile picture logic
$profilePath = 'uploads/profile_pictures/default.png';
if (!empty($user['profile_picture']) && file_exists($user['profile_picture'])) {
    $profilePath = $user['profile_picture'];
}

// Handle document submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $receiver_id = $_POST['receiver'];
    $file_name = $_FILES['file']['name'];
    $file_tmp = $_FILES['file']['tmp_name'];
    $file_path = "uploads/" . basename($file_name);

    move_uploaded_file($file_tmp, $file_path);

    // Get sender details
    $sender_id = $_SESSION['user']['id'];
    $sender_dept_id = $_SESSION['user']['department_id'];

    // Check if the user is saving as draft or submitting
    if (isset($_POST['submit'])) {
        $status = 'forwarded'; // Document is sent
    } else {
        $status = 'draft';  // For drafts
    }

    $sql = "INSERT INTO documents (title, file_path, sender_id, sender_department_id, receiver_id, receiver_department_id, status) 
            VALUES (?, ?, ?, ?, ?, (SELECT department_id FROM users WHERE id = ?), ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiiiis", $title, $file_path, $sender_id, $sender_dept_id, $receiver_id, $receiver_id, $status);

    if ($stmt->execute()) {
        $message = ($status == 'forwarded') ? "Document sent successfully!" : "Document saved as draft!";
        echo "<script>showNotification('$message', 'success');</script>";
    } else {
        echo "<script>showNotification('Error saving document.', 'error');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f4f4;
        }

        .main-wrapper {
            display: flex;
            height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            display: flex;
            flex-direction: column;
            padding: 20px 15px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .sidebar-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .sidebar-logo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
        }

        .sidebar-header h1 {
            font-size: 16px;
            margin-top: 10px;
            line-height: 1.4;
        }

        .sidebar-header span {
            font-size: 12px;
            color: #bdc3c7;
        }

        .menu {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .menu a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            transition: background 0.2s;
        }

        .menu a:hover,
        .menu a.active {
            background-color: #34495e;
        }

        .menu a i {
            margin-right: 10px;
        }

        /* User box */
        .user-box {
            margin-top: 5px;
            text-align: center;
            position: relative;
        }

        .profile-picture-container {
            margin-bottom: 10px;
            cursor: pointer;
        }

        .profile-picture {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            border: 3px solid white;
            object-fit: cover;
            transition: transform 0.2s;
        }

        .profile-picture:hover {
            transform: scale(1.05);
        }

        /* Dropdown Menu */
        .dropdown-content {
            opacity: 0;
            transform: translateY(-10px);
            transition: all 0.25s ease;
            pointer-events: none;

            position: absolute;
            top: 90px;
            left: 50%;
            transform: translateX(-50%) translateY(-10px);

            background-color: #34495e;
            min-width: 180px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 10;
            padding: 10px 0;
        }

        .dropdown-content.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
            pointer-events: auto;
        }

        .dropdown-content::before {
            content: '';
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            border-width: 6px;
            border-style: solid;
            border-color: transparent transparent #34495e transparent;
        }

        .dropdown-content a {
            color: white;
            padding: 12px 20px;
            text-decoration: none;
            display: block;
            transition: background-color 0.2s;
            font-size: 14px;
            text-align: left;
        }

        .dropdown-content a:hover {
            background-color: #3e5a70;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 30px;
        }

        .dashboard-top {
            display: flex;
            gap: 20px;
        }

        .card {
            flex: 1;
            padding: 20px;
            border-radius: 12px;
            color: white;
            font-size: 18px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .blue-card {
            background-color: #3498db;
        }

        .green-card {
            background-color: #2ecc71;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.6);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 30px;
            border: 1px solid #888;
            width: 90%;
            max-width: 400px;
            border-radius: 10px;
            text-align: center;
            position: relative;
        }

        .modal-content h2 {
            margin-bottom: 20px;
        }

        .modal-content input[type="file"] {
            margin-bottom: 20px;
        }

        .modal-content button {
            padding: 10px 20px;
            background-color: #2980b9;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .modal-content button:hover {
            background-color: #1c5980;
        }

        .close {
            position: absolute;
            top: 15px;
            right: 20px;
            color: #aaa;
            font-size: 24px;
            cursor: pointer;
        }

        .close:hover {
            color: #000;
        }

        .qr-section {
            text-align: center;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .pgc-logo {
            max-width: 150px;
            margin-bottom: 20px;
        }

        .qr-section h2 {
            margin: 0 0 20px 0;
            font-weight: 600;
        }

        .qr-box {
            border: 1px solid #ccc;
            padding: 20px;
            display: inline-block;
            border-radius: 8px;
        }

        .qr-icon {
            font-size: 40px;
            color: #007bff;
            margin-bottom: 10px;
        }

        .qr-box button {
            margin-top: 10px;
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .qr-box a {
            display: block;
            margin-top: 10px;
            color: #007bff;
            text-decoration: underline;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="main-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="uploads/download.jpg" class="sidebar-logo">
                <h1>DOCUMENT TRACKING<br><span>MANAGEMENT SYSTEM</span></h1>
            </div>

            <div class="user-box">
                <div class="profile-picture-container" onclick="toggleMenu()">
                    <img src="<?= htmlspecialchars($profilePath) ?>" class="profile-picture"
                        title="Click to manage profile" />
                </div>

                <div class="user-info">
                    <strong><?= htmlspecialchars($user['name']) ?></strong><br>
                    <small><?= htmlspecialchars($user['username']) ?></small>
                </div>

                <div id="dropdown-menu" class="dropdown-content">
                    <a href="#" onclick="openModal()">Change Profile Picture</a>
                    <a href="change_username.php">Change Username</a>
                    <a href="change_password.php">Change Password</a>
                    <a href="index.php">Logout</a>
                </div>
            </div>

            <nav class="menu">
                <a href="dashboard.php" class="active"><i class="fa fa-home"></i> Dashboard</a>
                <a href="receive_document.php"><i class="fa fa-inbox"></i> Received Documents <span
                        style="margin-left:auto; font-size: 12px;">(<?= $receivedCount ?>)</span></a>
                <a href="send_document.php"><i class="fa fa-share"></i> Sent Documents <span
                        style="margin-left:auto; font-size: 12px;">(<?= $forwardedCount ?>)</span></a>
                <a href="deferred.php"><i class="fa fa-pause"></i> Deferred Documents <span
                        style="margin-left:auto; font-size: 12px;">(<?= $deferredCount ?>)</span></a>
                <a href="document_history.php"><i class="fa fa-history"></i> Documents History <span
                        style="margin-left:auto; font-size: 12px;">(<?= $historyCount ?>)</span></a>

            </nav>

        </aside>

        <section class="main-content">
            <div class="dashboard-top">
                <div class="card blue-card">
                    <p>Received Documents</p>
                    <h2><?= $receivedCount ?></h2>
                </div>
                <div class="card green-card">
                    <p>Forwarded Documents</p>
                    <h2><?= $forwardedCount ?></h2>
                </div>
            </div>


            <div class="qr-section">
                <h1>National Council of Churches in the Philippines</h1>
                <h2>Document Tracking System</h2>
                <img src="uploads/download.png" alt="National Council of Churches in the Philippines" class="pgc-logo">

            </div>
        </section>
    </div>

    <!-- Profile Picture Upload Modal -->
    <div id="profileModal" class="modal">
        <div class="modal-content">
            <span onclick="document.getElementById('profileModal').style.display='none'" class="close">&times;</span>
            <h2>Update Profile Picture</h2>
            <form action="upload_profile_picture.php" method="POST" enctype="multipart/form-data">
                <input type="file" name="profile_picture" accept="image/*" required>
                <button type="submit">Upload</button>
            </form>
        </div>
    </div>

    <script>
        function toggleMenu() {
            document.getElementById("dropdown-menu").classList.toggle("show");
        }

        function openModal() {
            document.getElementById("profileModal").style.display = 'block';
            document.getElementById("dropdown-menu").classList.remove("show");
        }

        window.onclick = function (event) {
            if (!event.target.closest('.user-box')) {
                document.getElementById("dropdown-menu").classList.remove("show");
            }
        };
    </script>
</body>

</html>