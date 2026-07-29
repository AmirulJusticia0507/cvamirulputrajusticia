<?php
session_start();
include 'config.php';
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    die("Hanya admin yang bisa mengakses halaman ini.");
}

$error = $success = '';

// Simpan settings
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $session_minute = intval($_POST['session_minute']);
    $remember_day = intval($_POST['remember_day']);

    pg_query_params($conn, "INSERT INTO settings (key, value) VALUES ('session_minute',$1) ON CONFLICT (key) DO UPDATE SET value=$1", [$session_minute]);
    pg_query_params($conn, "INSERT INTO settings (key, value) VALUES ('remember_day',$1) ON CONFLICT (key) DO UPDATE SET value=$1", [$remember_day]);
    $success = "Pengaturan berhasil disimpan.";
}

// Ambil settings
$settings = pg_fetch_all(pg_query($conn, "SELECT * FROM settings")) ?: [];
$session_minute = 60; // default
$remember_day = 7; // default
foreach($settings as $s){
    if($s['key'] === 'session_minute') $session_minute = $s['value'];
    if($s['key'] === 'remember_day') $remember_day = $s['value'];
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Settings</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
<div class="row justify-content-center">
<div class="col-md-5">
<div class="card shadow">
<div class="card-body">
<h3 class="card-title mb-3">Pengaturan Session & Remember Me</h3>
<?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
<?php if($success) echo "<div class='alert alert-success'>$success</div>"; ?>
<form method="POST">
    <div class="mb-3">
        <label>Durasi Session (menit)</label>
        <input type="number" name="session_minute" class="form-control" value="<?= $session_minute ?>" required>
    </div>
    <div class="mb-3">
        <label>Durasi "Ingat Saya" (hari)</label>
        <input type="number" name="remember_day" class="form-control" value="<?= $remember_day ?>" required>
    </div>
    <button type="submit" class="btn btn-primary w-100">Simpan</button>
</form>
</div>
</div>
</div>
</div>
</div>
</body>
</html>
