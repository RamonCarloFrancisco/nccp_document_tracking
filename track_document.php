<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user']['id'])) {
    die("User not logged in.");
}

if (!isset($_GET['id'])) {
    die("No document ID specified.");
}

$document_id = $_GET['id'];
$current_user_id = $_SESSION['user']['id'];

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Handle Forward
if (isset($_POST['forward'])) {
    $receiver_id = $_POST['receiver_id'];
    $receiver_department_id = $_POST['receiver_department_id'];

    // Get sender details from session or DB
    $sender_id = $current_user_id;

    $sql_sender_dept = "SELECT department_id FROM users WHERE id = ?";
    $stmt_dept = $conn->prepare($sql_sender_dept);
    $stmt_dept->bind_param("i", $sender_id);
    $stmt_dept->execute();
    $stmt_dept->bind_result($sender_department_id);
    $stmt_dept->fetch();
    $stmt_dept->close();

    $sql_forward = "INSERT INTO document_forwards (document_id, sender_id, sender_department_id, receiver_id, receiver_department_id) 
                    VALUES (?, ?, ?, ?, ?)";
    $stmt_forward = $conn->prepare($sql_forward);
    $stmt_forward->bind_param("iiiii", $document_id, $sender_id, $sender_department_id, $receiver_id, $receiver_department_id);
    $stmt_forward->execute();

    header("Location: track_document.php?id=" . $document_id);
    exit();
}

// Handle Close
if (isset($_POST['close'])) {
    $sql_close = "UPDATE documents SET status = 'close case' WHERE id = ?";
    $stmt_close = $conn->prepare($sql_close);
    $stmt_close->bind_param("i", $document_id);
    $stmt_close->execute();
    header("Location: track_document.php?id=" . $document_id);
    exit();
}

// Handle Reopen
if (isset($_POST['reopen'])) {
    $sql_reopen = "UPDATE documents SET status = 'open' WHERE id = ?";
    $stmt_reopen = $conn->prepare($sql_reopen);
    $stmt_reopen->bind_param("i", $document_id);
    $stmt_reopen->execute();
    header("Location: track_document.php?id=" . $document_id);
    exit();
}

// Get Document Details
$sql = "SELECT d.id, d.title, d.subject, d.purpose, d.file_path, d.date_sent, 
               u.name AS sender_name, u.email, dept.name AS sender_department,
               d.status
        FROM documents d
        JOIN users u ON d.sender_id = u.id
        JOIN departments dept ON d.sender_department_id = dept.id
        WHERE d.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $document_id);
$stmt->execute();
$result = $stmt->get_result();
$document = $result->fetch_assoc();

$can_forward = $document['status'] !== 'close case';

// Fetch Forwarding History
$sql_history = "SELECT 
    df.forward_date,
    sender.name AS sender_name,
    sdept.name AS sender_department,
    receiver.name AS receiver_name,
    rdept.name AS receiver_department
FROM document_forwards df
JOIN users sender ON df.sender_id = sender.id
JOIN departments sdept ON df.sender_department_id = sdept.id
JOIN users receiver ON df.receiver_id = receiver.id
JOIN departments rdept ON df.receiver_department_id = rdept.id
WHERE df.document_id = ?
ORDER BY df.forward_date ASC";

$stmt_history = $conn->prepare($sql_history);
$stmt_history->bind_param("i", $document_id);
$stmt_history->execute();
$history_result = $stmt_history->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Track Document</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .container {
            max-width: 1000px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            margin-top: 40px;
            box-shadow: 0 0 10px #ccc;
        }

        .details-grid {
            display: flex;
            gap: 40px;
            flex-wrap: wrap;
        }

        .details-grid>div {
            flex: 1;
        }

        .label {
            font-weight: bold;
            margin-top: 10px;
        }

        .value {
            margin-bottom: 10px;
            color: #444;
        }

        .timeline-container {
            margin-top: 40px;
        }

        .timeline-card {
            background: #f8f9fa;
            border-left: 4px solid #1a73e8;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .arrow {
            text-align: center;
            font-size: 30px;
            margin-bottom: 20px;
            color: #1a73e8;
        }

        .status-closed {
            text-align: center;
            font-weight: bold;
            color: crimson;
            margin-top: 20px;
        }

        .form-section {
            margin-top: 40px;
        }

        .sender-entry {
            background-color: #e0f7fa;
            border-left: 4px solid #00796b;
            font-weight: bold;
        }

        .receiver-entry {
            background-color: #f1f8e9;
            border-left: 4px solid #388e3c;
        }

        a {
            color: #5f9ea0;
            font-weight: bold;
            text-decoration: none;
        }

        .btn {
            display: inline-block;
            padding: 5px 10px;
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
    <script>
        function filterUsersByDepartment() {
            const departmentId = document.getElementById('receiver_department_id').value;
            const xhr = new XMLHttpRequest();
            xhr.open("GET", "get_users_by_department.php?dept_id=" + departmentId, true);
            xhr.onload = function () {
                document.getElementById('receiver_id').innerHTML = xhr.responseText;
            };
            xhr.send();
        }
    </script>
</head>

<body>
    <div class="container">
        <a href="dashboard.php" class="btn">Home</a>

        <h2>Document Details</h2>
        <div class="details-grid">
            <div>
                <div class="label">Tracking Number:</div>
                <div class="value"><?= htmlspecialchars($document['id']) ?></div>
                <div class="label">Document Type:</div>
                <div class="value"><?= htmlspecialchars($document['title']) ?></div>
                <div class="label">Origin:</div>
                <div class="value"><?= htmlspecialchars($document['sender_name']) ?></div>
                <div class="label">Email:</div>
                <div class="value"><?= htmlspecialchars($document['email']) ?></div>
            </div>
            <div>
                <div class="label">Subject Matter:</div>
                <div class="value"><?= htmlspecialchars($document['subject']) ?></div>
                <div class="label">Purpose:</div>
                <div class="value"><?= htmlspecialchars($document['purpose']) ?></div>
                <div class="label">Origin Unit:</div>
                <div class="value"><?= htmlspecialchars($document['sender_department']) ?></div>
            </div>
        </div>

        <a href="<?= htmlspecialchars($document['file_path']) ?>" class="download-btn" target="_blank">Download
            Details</a>

        <div class="timeline-container">
            <h3>Document Route</h3>

            <div class="timeline-card sender-entry">

                <strong>From:</strong> <?= htmlspecialchars($document['sender_department']) ?> |
                <?= htmlspecialchars($document['sender_name']) ?><br>
                <strong>Actions Taken:</strong> Document Sent<br>
                <strong>Sent on:</strong> <?= htmlspecialchars($document['date_sent']) ?>

            </div>

            <?php
            // Get the first receiver directly from the documents table
            $sql_first_receiver = "SELECT u.name AS receiver_name, d.date_sent, dept.name AS receiver_department
                       FROM documents d
                       JOIN users u ON d.receiver_id = u.id
                       JOIN departments dept ON d.receiver_department_id = dept.id
                       WHERE d.id = ?";
            $stmt_first = $conn->prepare($sql_first_receiver);
            $stmt_first->bind_param("i", $document_id);
            $stmt_first->execute();
            $result_first = $stmt_first->get_result();
            $first_receiver = $result_first->fetch_assoc();
            ?>

            <div class="arrow">&#x2193;</div>
            <div class="timeline-card receiver-entry">
                <strong>To:</strong> <?= htmlspecialchars($first_receiver['receiver_department']) ?> |
                <?= htmlspecialchars($first_receiver['receiver_name']) ?><br>
                <strong>Actions Taken:</strong> First Receiver<br>
                <strong>Received:</strong> <?= htmlspecialchars($first_receiver['date_sent']) ?>
            </div>

            <?php while ($row = $history_result->fetch_assoc()): ?>
                <div class="arrow">&#x2193;</div>
                <div class="timeline-card receiver-entry">
                    <strong>From:</strong> <?= htmlspecialchars($row['sender_department']) ?> |
                    <?= htmlspecialchars($row['sender_name']) ?><br>
                    <strong>To:</strong> <?= htmlspecialchars($row['receiver_department']) ?> |
                    <?= htmlspecialchars($row['receiver_name']) ?><br>
                    <strong>Actions Taken:</strong> Document Forwarded<br>
                    <strong>Received:</strong> <?= htmlspecialchars($row['forward_date']) ?>
                </div>
            <?php endwhile; ?>

            <?php if ($document['status'] === 'close case'): ?>
                <div class="status-closed">Document case has been closed.</div>
            <?php endif; ?>
        </div>

        <?php if ($can_forward): ?>
            <div class="form-section">
                <h3>Forward Document</h3>
                <form method="POST">
                    <label>Department:</label>
                    <select name="receiver_department_id" id="receiver_department_id" onchange="filterUsersByDepartment()"
                        required>
                        <option value="">Select Department</option>
                        <?php
                        $dept_sql = "SELECT id, name FROM departments";
                        $dept_result = $conn->query($dept_sql);
                        while ($department = $dept_result->fetch_assoc()) {
                            echo "<option value='{$department['id']}'>" . htmlspecialchars($department['name']) . "</option>";
                        }
                        ?>
                    </select><br><br>

                    <label>User:</label>
                    <select name="receiver_id" id="receiver_id" required>
                        <option value="">Select User</option>
                    </select><br><br>

                    <button type="submit" name="forward" class="btn">Forward</button>
                </form>
            </div>
        <?php endif; ?>

        <form method="POST" style="margin-top: 20px;">
            <?php if ($document['status'] !== 'close case'): ?>
                <button type="submit" name="close" class="btn" style="background-color: crimson;">Close Document</button>
            <?php else: ?>
                <button type="submit" name="reopen" class="btn" style="background-color: orange;">Reopen Document</button>
            <?php endif; ?>
        </form>
    </div>
</body>

</html>