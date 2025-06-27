<?php
session_start();
include 'db.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$notificationMessage = "";
$notificationType = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $emp_num = trim($_POST['employee_number']);
    $username = trim($_POST['username']);
    $password_plain = $_POST['password'];
    $department_id = $_POST['department_id'];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $notificationMessage = "Invalid email format.";
        $notificationType = "error";
    } elseif (!preg_match("/^(?=.*[A-Za-z])(?=.*\d)(?=.*[!@#$%^&*]).{8,}$/", $password_plain)) {
        $notificationMessage = "Password must be at least 8 characters, contain a letter, number, and special character.";
        $notificationType = "error";
    } else {
        $password = password_hash($password_plain, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(50));

        $conn->begin_transaction();

        try {
            $stmt = $conn->prepare("SELECT * FROM users WHERE employee_number = ?");
            $stmt->bind_param("s", $emp_num);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user) {
                if (!empty($user['username'])) {
                    throw new Exception("Employee number already registered.");
                } else {
                    $update = $conn->prepare("
                        UPDATE users 
                        SET name = ?, email = ?, username = ?, password = ?, department_id = ?, verification_token = ?, email_verified = 0 
                        WHERE employee_number = ?
                    ");
                    $update->bind_param("ssssiss", $name, $email, $username, $password, $department_id, $token, $emp_num);

                    if (!$update->execute()) {
                        throw new Exception("Failed to update user record.");
                    }
                }
            } else {
                throw new Exception("Employee number not found.");
            }

            if (!sendVerificationEmail($email, $token)) {
                throw new Exception("Email verification failed.");
            }

            $conn->commit();
            $notificationMessage = "Registration successful! Check your email to verify your account.";
            $notificationType = "success";
        } catch (Exception $e) {
            $conn->rollback();
            $notificationMessage = $e->getMessage();
            $notificationType = "error";
        }
    }
}

// Function to send verification email
function sendVerificationEmail($email, $token)
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'nccpdms@gmail.com'; // Change this
        $mail->Password = 'ffvq kily umks vdcg'; // Change this
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('nccpdms@gmail.com', 'NCCP Document Tracking');
        $mail->addAddress($email);
        $mail->Subject = "Verify Your Email";
        $mail->isHTML(true);
        $verification_link = "http://localhost/nccp_document_tracking/verify.php?token=$token";
        $mail->Body = "Click <a href='$verification_link'>here</a> to verify your email.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>

<body>
    <div class="container">
        <div class="form-container">
            <img src="uploads/logo.jpg" alt="Logo" class="logo">
            <h2>Register</h2>

            <?php if (!empty($notificationMessage)): ?>
                <script>
                    document.addEventListener("DOMContentLoaded", function () {
                        showNotification("<?php echo $notificationMessage; ?>", "<?php echo $notificationType; ?>");
                    });
                </script>
            <?php endif; ?>

            <form method="POST">
                <label>Name</label>
                <input type="text" name="name" required>

                <label>Email</label>
                <input type="email" name="email" required>

                <label>Employee Number</label>
                <input type="text" name="employee_number" required>

                <label>Department</label>
                <select name="department_id" required>
                    <option value="">Select a Department</option>
                    <?php
                    $dept_query = $conn->query("SELECT * FROM departments");
                    while ($row = $dept_query->fetch_assoc()) {
                        echo "<option value='{$row['id']}'>{$row['name']} ({$row['code']})</option>";
                    }
                    ?>
                </select>

                <label>Username</label>
                <input type="text" name="username" required>

                <label>Password</label>
                <div class="password-container">
                    <input type="password" name="password" id="password" required>
                    <i class="fa fa-eye" id="togglePassword"></i>
                </div>
                <div id="password-strength">
                    <span id="strength-text">Password Strength: </span><span id="strength-status"></span>
                </div>

                <button type="submit">Register</button>
                <p>Already have an account? <a href="login.php">Log in</a></p>

            </form>
        </div>
    </div>

    <script>
        document.getElementById("togglePassword").addEventListener("click", function () {
            let passwordField = document.getElementById("password");
            this.classList.toggle("fa-eye-slash");
            passwordField.type = passwordField.type === "password" ? "text" : "password";
        });

        document.getElementById("password").addEventListener("input", function () {
            const password = this.value;
            const strengthStatus = document.getElementById("strength-status");
            const strengthText = document.getElementById("strength-text");

            let strength = "Weak";
            let color = "red";
            if (password.length >= 8) {
                strength = "Moderate";
                color = "orange";
            }
            if (password.match(/^(?=.*[A-Za-z])(?=.*\d)(?=.*[!@#$%^&*]).{8,}$/)) {
                strength = "Strong";
                color = "green";
            }

            strengthStatus.textContent = strength;
            strengthStatus.style.color = color;
        });

        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.classList.add('notification', type);
            notification.innerHTML = `
                <div class="notification-content">
                    <strong>${type === "success" ? "Success!" : "Error!"}</strong> ${message}
                </div>
                <button class="close-btn" onclick="this.parentElement.remove()">×</button>
            `;
            document.body.appendChild(notification);
            setTimeout(() => notification.classList.add('show'), 100);
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
    </script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
        }

        .container {
            margin-top: 100px;
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

        .logo {

            max-width: 200px;
            margin-bottom: 10px;
        }

        h2 {
            margin-bottom: 20px;
            font-weight: 600;
            color: #333;
        }

        label {
            font-size: 14px;
            margin-bottom: 5px;
            display: block;
            color: #555;
            text-align: left;
        }

        input,
        select {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            color: #333;
            box-sizing: border-box;
        }

        .password-container {
            position: relative;
        }

        .password-container i {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
        }

        #password-strength {
            font-size: 14px;
            margin-top: 10px;
        }

        #strength-status {
            font-weight: bold;
        }

        button {
            background-color: #5f9ea0;
            color: #fff;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background-color: #4f7b7b;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: white;
            color: black;
            border-radius: 5px;
            padding: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: opacity 0.3s ease-in-out;
            opacity: 0;
            z-index: 999;
        }

        .notification.show {
            opacity: 1;
        }

        .notification.success {
            border-left: 5px solid #4caf50;
        }

        .notification.error {
            border-left: 5px solid #f44336;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 18px;
            color: #888;
            cursor: pointer;
        }

        .close-btn:hover {
            color: #555;
        }

        @media (max-width: 600px) {
            .header {
                flex-direction: row;
                align-items: center;
            }

            .logo {
                max-width: 35px;
            }

            .user-info {
                font-size: 12px;
            }
        }
    </style>
</body>

</html>