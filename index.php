<?php include 'includes/header.php'; ?>

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
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
</style>

<!-- Hero Section -->
<section class="relative bg-cover bg-center h-screen" style="background-image: url('uploads/logo.jpg');">
    <div class="bg-black bg-opacity-60 h-full flex flex-col justify-center items-center text-white text-center px-4">
        <h2 class="text-3xl md:text-5xl font-bold">
            Welcome to <span style="color: #5f9ea0;">Document Tracking System</span>
        </h2>
        <p class="mt-4 text-lg">NATIONAL COUNCIL OF CHURCHES IN THE PHILIPPINES</p>
        <div class="mt-6 space-x-4">
            <a href="login.php"
                class="btn-custom px-6 py-3 rounded text-white font-semibold hover:bg-[#4f7b7b] transition-colors">Login
                Now</a>
            <a href="about.php"
                class="bg-transparent border border-white px-6 py-3 rounded text-white hover:bg-white hover:text-black transition-colors">About</a>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-12 bg-white">
    <div class="max-w-6xl mx-auto px-6 grid md:grid-cols-3 gap-8">
        <!-- Receive -->
        <div class="bg-white shadow-lg rounded-lg p-6 text-center hover:shadow-xl transition">
            <div class="bg-blue-900 p-4 rounded-full w-24 h-24 mx-auto flex items-center justify-center">
                <img src="uploads/receive.png" alt="Receive" class="h-12 w-12">
            </div>
            <h3 class="mt-4 font-bold text-lg">RECEIVE</h3>
            <a href="receive.php" class="mt-4 inline-block text-orange-500 font-semibold hover:underline">Learn More</a>
        </div>

        <!-- Forward -->
        <div class="bg-white shadow-lg rounded-lg p-6 text-center hover:shadow-xl transition">
            <div class="bg-blue-900 p-4 rounded-full w-24 h-24 mx-auto flex items-center justify-center">
                <img src="uploads/forward.png" alt="Forward" class="h-12 w-12">
            </div>
            <h3 class="mt-4 font-bold text-lg">FORWARD</h3>
            <a href="forward.php" class="mt-4 inline-block text-orange-500 font-semibold hover:underline">Learn More</a>
        </div>

        <!-- Track -->
        <div class="bg-white shadow-lg rounded-lg p-6 text-center hover:shadow-xl transition">
            <div class="bg-blue-900 p-4 rounded-full w-24 h-24 mx-auto flex items-center justify-center">
                <img src="uploads/track.png" alt="Track" class="h-12 w-12">
            </div>
            <h3 class="mt-4 font-bold text-lg">TRACK</h3>
            <a href="track.php" class="mt-4 inline-block text-orange-500 font-semibold hover:underline">Learn More</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>