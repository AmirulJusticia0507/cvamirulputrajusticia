<?php
include 'config.php';

// Ambil snapshot work experience
$work_exp = pg_fetch_all(pg_query($conn, "SELECT * FROM work_experience ORDER BY start_date DESC"));

// Simpan record baru dan ambil ID
$result = pg_query($conn, "INSERT INTO cv_history (work_snapshot) VALUES ('" . pg_escape_string(json_encode($work_exp)) . "') RETURNING id, created_at");
$row = pg_fetch_assoc($result);
$id = $row['id'];

// Generate URL detail
$url = "preview_cv.php?id=" . $id;
pg_query($conn, "UPDATE cv_history SET url='" . pg_escape_string($url) . "' WHERE id=$id");

// Kirim response JSON
echo json_encode(['id' => $id, 'created_at' => $row['created_at'], 'url' => $url]);
?>
