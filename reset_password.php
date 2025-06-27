<?php
session_start();
include 'db.php';

$error = '';
$success = '';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Check if the token exists in the database
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        $error = "Invalid or expired token.";
    } else {
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $new_password = trim($_POST['password']);
            $confirm_password = trim($_POST['confirm_password']);

            if ($new_password != $confirm_password) {
                $error = "Passwords do not match.";
            } else {
                // Hash the new password
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

                // Update the password in the database
                $update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL WHERE id = ?");
                $update->bind_param("si", $hashed_password, $user['id']);

                if ($update->execute()) {
                    $success = "Your password has been reset successfully. You can now <a href='login.php'>log in</a>.";
                } else {
                    $error = "There was an error resetting your password. Please try again.";
                }
            }
        }
    }
} else {
    $error = "No reset token provided.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .form-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .form-container .logo {
            width: 100px;
            margin-bottom: 20px;
        }

        h2 {
            margin-bottom: 20px;
            color: #333;
        }

        label {
            display: block;
            text-align: left;
            margin: 10px 0 5px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            width: 100%;
            padding: 12px;
            background:  #5f9ea0;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background:  #4f7b7b;
        }

        .success-message,
        .error-message {
            color: #fff;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
        }

        .success-message {
            background-color: #28a745;
        }

        .error-message {
            background-color: #dc3545;
        }

        @media(max-width: 480px) {
            .logo {
                width: 100px;
            }

            h2 {
                font-size: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="form-container">
        <img src="uploads/logo.jpg" alt="Logo" class="logo">
        <h2>Reset Password</h2>

        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (!$error && !$success): ?>
            <form method="POST">
                <label>New Password</label>
                <input type="password" name="password" required>

                <label>Confirm Password</label>
                <input type="password" name="confirm_password" required>

                <button type="submit">Reset Password</button>
            </form>
        <?php endif; ?>

        <a href="login.php"
            style="color:  #5f9ea0; text-decoration: none; margin-top: 10px; display: inline-block;">Back
            to Login</a>
    </div>
</body>

</html>