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
<link rel="icon" href="favicon.svg" type="image/svg+xml">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100">
<div class="max-w-lg mx-auto px-4 mt-5">
<div class="flex justify-center">
<div class="w-full max-w-md">
<div class="bg-white rounded-xl shadow p-6">
<h3 class="text-lg font-bold mb-3">Pengaturan Session & Remember Me</h3>
<?php if($error) echo "<div class='bg-red-100 text-red-800 border border-red-200 p-4 rounded-lg'>$error</div>"; ?>
<?php if($success) echo "<div class='bg-green-100 text-green-800 border border-green-200 p-4 rounded-lg'>$success</div>"; ?>
<form method="POST">
    <div class="mb-3">
        <label class="block text-sm font-medium text-gray-700 mb-1">Durasi Session (menit)</label>
        <input type="number" name="session_minute" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" value="<?= $session_minute ?>" required>
    </div>
    <div class="mb-3">
        <label class="block text-sm font-medium text-gray-700 mb-1">Durasi "Ingat Saya" (hari)</label>
        <input type="number" name="remember_day" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" value="<?= $remember_day ?>" required>
    </div>
    <button type="submit" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition bg-blue-600 text-white hover:bg-blue-700 w-full">Simpan</button>
</form>
</div>
</div>
</div>
</div>
<?php include __DIR__ . '/includes/darkmode.php'; ?>
</body>
</html>
