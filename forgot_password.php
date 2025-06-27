<?php
session_start();
include 'db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';  // Include PHPMailer's autoloader

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $email = trim($_POST['email']);

    // Check if email exists in the database
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ?");

    if (!$stmt) {
        die("Query preparation failed: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $username = $user['username'];

        // Generate reset token
        $reset_token = bin2hex(random_bytes(32));

        // Update reset token in the database
        $update = $conn->prepare("UPDATE users SET reset_token = ? WHERE id = ?");

        if (!$update) {
            die("Update query preparation failed: " . $conn->error);
        }

        $update->bind_param("si", $reset_token, $user['id']);
        if ($update->execute()) {
            // Send password reset email using PHPMailer
            $resetLink = "http://localhost/nccp_document_tracking/reset_password.php?token=" . $reset_token;
            $subject = "Password Reset Request";

            // Enhanced HTML email message
            $message = '
            <html>
            <head>
              <style>
                .container {
                  font-family: Arial, sans-serif;
                  background-color: #f9f9f9;
                  padding: 20px;
                  border-radius: 10px;
                  color: #333;
                  max-width: 600px;
                  margin: auto;
                  box-shadow: 0 0 10px rgba(0,0,0,0.1);
                }
                .header {
                  background-color: #5f9ea0;
                  color: white;
                  padding: 15px;
                  border-radius: 10px 10px 0 0;
                  text-align: center;
                  font-size: 20px;
                }
                .content {
                  padding: 20px;
                  font-size: 16px;
                }
                .button {
                  display: inline-block;
                  padding: 12px 20px;
                  margin-top: 20px;
                  background-color: #5f9ea0;
                  color: white;
                  text-decoration: none;
                  border-radius: 5px;
                  font-weight: bold;
                }
                .footer {
                  margin-top: 30px;
                  font-size: 12px;
                  color: #777;
                  text-align: center;
                }
              </style>
            </head>
            <body>
              <div class="container">
                <div class="header">
                  Password Reset Request
                </div>
                <div class="content">
                  <p>Hi ' . htmlspecialchars($username) . ',</p>
                  <p>We received a request to reset your password. If this was you, click the button below to reset it:</p>
                  <a href="' . $resetLink . '" class="button">Reset Password</a>
                  <p>If you did not request a password reset, please ignore this email. Your account is safe.</p>
                </div>
                <div class="footer">
                  © ' . date("Y") . ' NCCP Document Tracking System. All rights reserved.
                </div>
              </div>
            </body>
            </html>';

            $mail = new PHPMailer(true);
            try {
                //Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'nccpdms@gmail.com';  // Replace with your email
                $mail->Password = 'ffvq kily umks vdcg';  // Replace with your app password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                //Recipients
                $mail->setFrom('nccpdms@gmail.com', 'NCCP DMS');
                $mail->addAddress($email);

                // Content
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = $message;

                $mail->send();
                $success = "Password reset link has been sent to your email.";
            } catch (Exception $e) {
                $error = "There was an error sending the email. Please try again.";
            }
        } else {
            $error = "There was an error updating the reset token.";
        }
    } else {
        $error = "Email not found.";
    }
}
?>

<!-- The rest of the HTML code remains the same -->

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
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
            display: flex;
            flex-direction: column;
            align-items: center;
            max-height: 600px;
            overflow-y: auto;
        }

        .form-container .logo {
            width: 200px;
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
            padding: 12px;
            /* Increased padding */
            margin-bottom: 20px;
            /* Increased space between input and button */
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
            /* Increased font size for better readability */
        }

        button {
            width: 100%;
            padding: 14px;
            /* Increased padding */
            background: #5f9ea0;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 18px;
            /* Increased font size */
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background: #4f7b7b;
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

        a {
            color: #5f9ea0;
            text-decoration: none;
            margin-top: 10px;
            display: inline-block;
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
        <h2>Forgot Password</h2>

        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Enter your email</label>
            <input type="email" name="email" required>

            <button type="submit">Reset Password</button>
        </form>

        <a href="login.php"
            style="color:  #5f9ea0; text-decoration: none; margin-top: 10px; display: inline-block;">Back
            to Login</a>
    </div>
</body>

</html>