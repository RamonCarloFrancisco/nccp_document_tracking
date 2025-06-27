<?php
session_start();
include 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        if (!password_verify($password, $user['password'])) {
            $error = "Invalid username or password.";
        } elseif ($user['email_verified'] == 0) {
            $error = "Your email is not verified. Please check your email.";
        } else {
            $_SESSION['user'] = $user;

            // Redirect based on role
            header("Location: dashboard.php");
            exit();
        }
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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

        .form-group {
            position: relative;
            margin-bottom: 15px;
        }

        .form-group input {
            width: 100%;
            padding: 10px 40px 10px 10px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .form-group i {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #555;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #5f9ea0;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background: #4f7b7b;
        }

        a {
            color: #5f9ea0;
            text-decoration: none;
            margin-top: 10px;
            display: inline-block;
        }

        a:hover {
            text-decoration: underline;
        }

        @media(max-width: 480px) {
            .logo {
                width: 80px;
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
        <h2>Login</h2>

        <?php if (!empty($error)): ?>
            <p style="color:red; text-align:center;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST">
            <label>Username</label>
            <div class="form-group">
                <input type="text" name="username" required>
            </div>

            <label>Password</label>
            <div class="form-group">
                <input type="password" name="password" id="password" required>
                <i class="fa fa-eye" onclick="togglePassword()"></i>
            </div>

            <button type="submit">Login</button>
        </form>

        <a href="forgot_password.php">Forgot Password?</a>
    </div>

    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const passwordIcon = document.querySelector('.password-container i');
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                passwordIcon.classList.remove('fa-eye');
                passwordIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                passwordIcon.classList.remove('fa-eye-slash');
                passwordIcon.classList.add('fa-eye');
            }
        }
    </script>

</body>

</html>