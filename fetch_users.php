<?php
include 'db.php';

if (isset($_GET['dept_id'])) {
    $dept_id = $_GET['dept_id'];

    // Use DISTINCT to ensure unique results
    $stmt = $conn->prepare("SELECT DISTINCT id, name FROM users WHERE department_id = ?");
    $stmt->bind_param("i", $dept_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Start the dropdown options
    echo "<option value=''>-- Select User --</option>";

    // Store already displayed names to prevent duplicates
    $displayed_names = [];

    while ($row = $result->fetch_assoc()) {
        // Check if name is already displayed
        if (!in_array($row['name'], $displayed_names)) {
            echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['name']) . "</option>";
            $displayed_names[] = $row['name']; // Add to array
        }
    }
}
?>