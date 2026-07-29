<?php
include 'config.php';

if (!isset($_FILES['photo'])) {
    die('No file');
}

$file = $_FILES['photo'];
$allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png'];

$mime = mime_content_type($file['tmp_name']);
if (!isset($allowed[$mime])) {
    die('Invalid file type');
}

$filename = 'profile.' . $allowed[$mime];
$dir = __DIR__ . '/uploads/profile/';
$path = $dir . $filename;

if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

move_uploaded_file($file['tmp_name'], $path);

pg_query_params(
    $conn,
    "UPDATE profile SET photo=$1, updated_at=NOW() WHERE id=1",
    ['uploads/profile/' . $filename]
);

header("Location: index.php");
exit;
