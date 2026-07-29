<?php
session_start();
include 'config.php';
require 'vendor/autoload.php'; // untuk PHPMailer jika pakai

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $email = trim($_POST['email']);
    $res = pg_query_params($conn, "SELECT * FROM users WHERE email=$1", [$email]);
    if($user = pg_fetch_assoc($res)){
        $token = bin2hex(random_bytes(16));
        $expire = date('Y-m-d H:i:s', strtotime('+1 hour'));
        pg_query_params($conn, "UPDATE users SET reset_token=$1, reset_expire=$2 WHERE id=$3", [$token, $expire, $user['id']]);

        // Kirim email reset password (PHPMailer contoh)
        $link = "http://localhost/cvamirulputrajusticia/reset_password.php?token=$token";
        $success = "Link reset password telah dikirim ke email: $email. <br>Link: <a href='$link'>$link</a>";
    } else {
        $error = "Email tidak ditemukan.";
    }
}
?>

<!-- Form Bootstrap -->
<!DOCTYPE html>
<html>
<head>
<title>Lupa Password</title>
<link rel="icon" href="favicon.svg" type="image/svg+xml">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100">
<div class="max-w-lg mx-auto px-4 mt-5">
<div class="flex justify-center">
<div class="w-full max-w-md">
<div class="bg-white rounded-xl shadow p-6">
<h3 class="text-center mb-3">Lupa Password</h3>
<?php if($error) echo "<div class='bg-red-100 text-red-800 border border-red-200 p-4 rounded-lg'>$error</div>"; ?>
<?php if($success) echo "<div class='bg-green-100 text-green-800 border border-green-200 p-4 rounded-lg'>$success</div>"; ?>
<form method="POST">
    <div class="mb-3">
        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
        <input type="email" name="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" required>
    </div>
    <button type="submit" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition bg-blue-600 text-white hover:bg-blue-700 w-full">Kirim Link Reset</button>
    <div class="mt-3 text-center"><a href="login.php">Kembali ke Login</a></div>
</form>
</div>
</div>
</div>
</div>
</body>
</html>
