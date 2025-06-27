<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (password_verify($current_password, $user['password'])) {
        if ($new_password === $confirm_password) {
            $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
            // Update password in the database
            $conn->query("UPDATE users SET password = '$new_password_hashed' WHERE id = {$user['id']}");
            echo "Your password has been changed successfully!";
        } else {
            echo "New passwords do not match!";
        }
    } else {
        echo "Current password is incorrect!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="change-password-container">
        <a href="dashboard.php" class="btn">Home</a>
        <h2>Change Your Password</h2>

        <form method="POST" class="change-password-form">
            <label for="current_password">Current Password:</label>
            <input type="password" name="current_password" id="current_password" required>

            <label for="new_password">New Password:</label>
            <input type="password" name="new_password" id="new_password" required>

            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" name="confirm_password" id="confirm_password" required>

            <button type="submit">Change Password</button>
        </form>
        <p><a href="forgot_password.php">Forgot your password?</a></p>
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

    .change-password-container {
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

    .change-password-form {
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

    p {
        text-align: center;
    }

    a {
        color: #5f9ea0;
        text-decoration: none;
    }



    .btn {
        color: #5f9ea0;
        font-weight: bold;
        text-decoration: none;
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