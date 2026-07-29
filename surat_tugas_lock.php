<?php
include 'config.php';

$id = (int)$_POST['id'];

pg_query_params($conn,
    "UPDATE surat_tugas_splp SET status='LOCKED' WHERE id=$1",
    [$id]
);

header("Location: surat_tugas_preview.php?id=".$id);
exit;
