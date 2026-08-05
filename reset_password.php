<?php
session_start();
include 'config.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

if(!$token){
    die("Token tidak valid.");
}

$res = pg_query_params($conn, "SELECT * FROM users WHERE reset_token=$1 AND reset_expire > NOW()", [$token]);
if(!$user = pg_fetch_assoc($res)){
    die("Token expired atau tidak valid.");
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $password = $_POST['password'];
    $password2 = $_POST['password2'];

    if($password !== $password2){
        $error = "Password dan konfirmasi tidak cocok.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        pg_query_params($conn, "UPDATE users SET password_hash=$1, reset_token=NULL, reset_expire=NULL, failed_login=0, is_locked=FALSE WHERE id=$2", [$hash, $user['id']]);
        $success = "Password berhasil diubah. <a href='login.php'>Login sekarang</a>";
    }
}
?>

<!-- Form Bootstrap -->
<!DOCTYPE html>
<html>
<head>
<title>Reset Password</title>
<link rel="icon" href="favicon.svg" type="image/svg+xml">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100">
<div class="max-w-lg mx-auto px-4 mt-5">
<div class="flex justify-center">
<div class="w-full max-w-md">
<div class="bg-white rounded-xl shadow p-6">
<h3 class="text-center mb-3">Reset Password</h3>
<?php if($error) echo "<div class='bg-red-100 text-red-800 border border-red-200 p-4 rounded-lg'>$error</div>"; ?>
<?php if($success) echo "<div class='bg-green-100 text-green-800 border border-green-200 p-4 rounded-lg'>$success</div>"; ?>
<form method="POST">
    <div class="mb-3">
        <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
        <input type="password" name="password" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" required>
    </div>
    <div class="mb-3">
        <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
        <input type="password" name="password2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" required>
    </div>
    <button type="submit" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition bg-blue-600 text-white hover:bg-blue-700 w-full">Reset Password</button>
</form>
</div>
</div>
</div>
</div>
<?php include __DIR__ . '/includes/darkmode.php'; ?>
</body>
</html>
