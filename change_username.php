<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = $_POST['new_username'];

    // Check if the username already exists
    $result = $conn->query("SELECT * FROM users WHERE username = '$new_username'");
    if ($result->num_rows > 0) {
        echo "Username already exists! Please choose a different one.";
    } else {
        // Update username in the database
        $conn->query("UPDATE users SET username = '$new_username' WHERE id = {$user['id']}");
        echo "Your username has been changed successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Username</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="change-username-container">
        <a href="dashboard.php" class="btn">Home</a>
        <h2>Change Your Username</h2>
        <form method="POST" class="change-username-form">
            <label for="new_username">New Username:</label>
            <input type="text" name="new_username" id="new_username" required>

            <button type="submit">Change Username</button>
        </form>
    </div>
</body>

</html>

<style>
    body {
        font-family: 'Poppins', sans-serif;
        background-color: #f4f4f9;
        margin: 0;
        padding: 0;
    }

    .change-username-container {
        width: 100%;
        max-width: 500px;
        margin: 50px auto;
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    h2 {
        text-align: center;
        margin-bottom: 20px;
        color: #333;
    }

    .change-username-form {
        display: flex;
        flex-direction: column;
    }

    label {
        font-size: 14px;
        margin-bottom: 5px;
    }

    input {
        padding: 12px;
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 14px;
    }

    button {
        padding: 12px;
        background-color: #5f9ea0;
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
    }

    button:hover {
        background-color: #4f7b7b;
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