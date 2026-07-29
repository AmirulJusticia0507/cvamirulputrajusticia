<?php
include 'config.php';
$id = (int)$_GET['id'];
$d = pg_fetch_assoc(pg_query($conn,"SELECT surat_id FROM tim_pic_splp WHERE id=$id"));

pg_query($conn,"DELETE FROM tim_pic_splp WHERE id=$id");

header("Location: surat_tugas_preview.php?id=".$d['surat_id']);
exit;
