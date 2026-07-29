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
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
<div class="row justify-content-center">
<div class="col-md-5">
<div class="card shadow">
<div class="card-body">
<h3 class="card-title text-center mb-3">Reset Password</h3>
<?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
<?php if($success) echo "<div class='alert alert-success'>$success</div>"; ?>
<form method="POST">
    <div class="mb-3">
        <label>Password Baru</label>
        <input type="password" name="password" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Konfirmasi Password</label>
        <input type="password" name="password2" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary w-100">Reset Password</button>
</form>
</div>
</div>
</div>
</div>
</div>
</body>
</html>
