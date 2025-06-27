<?php
include 'db.php';

if (isset($_GET['dept_id'])) {
    $dept_id = intval($_GET['dept_id']);
    $sql = "SELECT id, name FROM users WHERE department_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $dept_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($user = $result->fetch_assoc()) {
        echo "<option value='" . $user['id'] . "'>" . htmlspecialchars($user['name']) . "</option>";
    }
}
?>