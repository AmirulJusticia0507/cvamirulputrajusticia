<?php
session_start();
include 'config.php';

// Tentukan user yang snapshot CV-nya direkam
if(isset($_POST['user_id']) && (int)$_POST['user_id'] > 0){
    $log_user_id = (int)$_POST['user_id'];
} elseif(isset($_SESSION['user_id'])){
    $log_user_id = (int)$_SESSION['user_id'];
} else {
    $log_user_id = get_preview_user_id($conn);
}
if(!$log_user_id){
    die('Profile not found');
}

// Ambil snapshot work experience
$work_exp = pg_fetch_all(pg_query_params($conn, "SELECT * FROM work_experience WHERE user_id=$1 ORDER BY start_date DESC", [$log_user_id]));

// Simpan record baru dan ambil ID
$result = pg_query($conn, "INSERT INTO cv_history (work_snapshot, user_id) VALUES ('" . pg_escape_string(json_encode($work_exp)) . "', $log_user_id) RETURNING id, created_at");
$row = pg_fetch_assoc($result);
$id = $row['id'];

// Generate URL detail
$url = "preview_cv.php?id=" . $id;
pg_query($conn, "UPDATE cv_history SET url='" . pg_escape_string($url) . "' WHERE id=$id");

// Kirim response JSON
echo json_encode(['id' => $id, 'created_at' => $row['created_at'], 'url' => $url]);
?>
