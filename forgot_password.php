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
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
<div class="row justify-content-center">
<div class="col-md-5">
<div class="card shadow">
<div class="card-body">
<h3 class="card-title text-center mb-3">Lupa Password</h3>
<?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
<?php if($success) echo "<div class='alert alert-success'>$success</div>"; ?>
<form method="POST">
    <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary w-100">Kirim Link Reset</button>
    <div class="mt-3 text-center"><a href="login.php">Kembali ke Login</a></div>
</form>
</div>
</div>
</div>
</div>
</div>
</body>
</html>
