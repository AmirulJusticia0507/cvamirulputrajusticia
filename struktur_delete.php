<?php
include 'config.php';
$id = (int)$_GET['id'];
$d = pg_fetch_assoc(pg_query($conn,"SELECT * FROM struktur_organisasi_splp WHERE id=$id"));

// hapus foto fisik jika ada
if($d['foto'] && file_exists($d['foto'])){
    unlink($d['foto']);
}

// hapus record dari DB
pg_query_params($conn,"DELETE FROM struktur_organisasi_splp WHERE id=$1", [$id]);

header("Location: surat_tugas_preview.php?id=".$d['surat_id']);
exit;
