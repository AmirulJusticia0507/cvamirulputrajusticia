<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$view_user_id = get_view_user_id($conn);

if (!isset($_FILES['photo'])) {
    die('No file');
}

$file = $_FILES['photo'];
$allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png'];

$mime = mime_content_type($file['tmp_name']);
if (!isset($allowed[$mime])) {
    die('Invalid file type');
}

$filename = 'profile_' . $view_user_id . '.' . $allowed[$mime];
$dir = __DIR__ . '/uploads/profile/';
$path = $dir . $filename;

if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

move_uploaded_file($file['tmp_name'], $path);

// Cek profil user sudah ada
$chk = pg_query_params($conn, "SELECT id FROM profile WHERE user_id=$1", [$view_user_id]);
if (pg_num_rows($chk) > 0) {
    pg_query_params(
        $conn,
        "UPDATE profile SET photo=$1, updated_at=NOW() WHERE user_id=$2",
        ['uploads/profile/' . $filename, $view_user_id]
    );
} else {
    pg_query_params(
        $conn,
        "INSERT INTO profile (full_name, photo, user_id, updated_at) VALUES ('', $1, $2, NOW())",
        ['uploads/profile/' . $filename, $view_user_id]
    );
}

header("Location: index.php");
exit;