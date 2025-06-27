<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Document Tracking System</title>

    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    <!-- Poppins Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Custom Styles -->
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        .btn-custom {
            background-color: #5f9ea0;
            color: white;
        }

        .btn-custom:hover {
            background-color: #4f7b7b;
        }

        .text-cadet {
            color: #5f9ea0;
        }

        .text-cadet:hover {
            color: #4f7b7b;
        }
    </style>
</head>

<body class="bg-gray-100 leading-normal tracking-normal">

    <!-- Navbar -->
    <nav class="bg-transparent text-white p-4">
        <div class="container mx-auto flex items-center justify-between">
            <!-- Left: Logo -->
            <div class="flex items-center space-x-4">
                <img src="uploads/download.png" alt="Logo" class="h-8 w-8" />
                <span class="font-bold text-xl text-cadet">Document Tracking System</span>
            </div>

            <!-- Center: Nav Links -->
            <ul class="hidden md:flex space-x-6 absolute left-1/2 transform -translate-x-1/2">
                <li><a href="index.php" class="text-cadet hover:text-cadet">Home</a></li>
                <li><a href="about.php" class="text-cadet hover:text-cadet">About</a></li>
                <li><a href="send_document.php" class="text-cadet hover:text-cadet">Send Document</a></li>
                <li><a href="track.php" class="text-cadet hover:text-cadet">Track Document</a></li>
            </ul>

            <!-- Right: Login -->
            <div>
                <a href="login.php" class="btn-custom px-4 py-2 rounded font-semibold">Login</a>
                <a href="register.php" class="btn-custom px-4 py-2 rounded font-semibold">Register</a>

            </div>
        </div>
    </nav>


</body>

</html>