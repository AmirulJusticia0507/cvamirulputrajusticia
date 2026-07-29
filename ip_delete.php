<?php
include 'config.php';
$id = (int)$_GET['id'];
$d = pg_fetch_assoc(pg_query($conn,"SELECT surat_id FROM whitelist_ip WHERE id=$id"));

pg_query($conn,"DELETE FROM whitelist_ip WHERE id=$id");

header("Location: surat_tugas_preview.php?id=".$d['surat_id']);
exit;
