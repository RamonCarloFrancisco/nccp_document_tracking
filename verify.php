<?php
include 'db.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Check if token exists in the database
    $stmt = $conn->prepare("SELECT id FROM users WHERE verification_token = ? AND email_verified = 0");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // If a match is found, update the email verification status
        $update = $conn->prepare("UPDATE users SET email_verified = 1, verification_token = NULL WHERE verification_token = ?");
        $update->bind_param("s", $token);

        if ($update->execute()) {
            $message = "Email verification successful! You can now <a href='login.php'>log in</a>.";
            $messageType = "success";
        } else {
            $message = "Something went wrong. Please try again.";
            $messageType = "error";
        }
    } else {
        $message = "Invalid or expired token.";
        $messageType = "error";
    }
} else {
    $message = "No verification token provided.";
    $messageType = "error";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .form-container {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
            font-weight: 600;
            color: #333;
        }

        .message {
            font-size: 16px;
            margin-top: 20px;
        }

        .message.success {
            color: #4caf50;
        }

        .message.error {
            color: #f44336;
        }

        .message a {
            color: #5f9ea0;
            text-decoration: none;
        }

        .message a:hover {
            text-decoration: underline;
        }

        .back-to-login {
            display: inline-block;
            background-color: #5f9ea0;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            margin-top: 20px;
            text-decoration: none;
        }

        .back-to-login:hover {
            background-color: #4f7b7b;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="form-container">
            <h2>Email Verification</h2>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
            <?php if ($messageType == "success"): ?>
                <a href="login.php" class="back-to-login">Go to Login</a>
            <?php endif; ?>
        </div>
    </div>

</body>

</html>