<?php
include 'config.php';
$id = (int)$_GET['id'];
$d = pg_fetch_assoc(pg_query($conn,"SELECT * FROM struktur_organisasi_splp WHERE id=$id"));

if($_POST){
    $instansi = $_POST['instansi'];
    $keterangan = $_POST['keterangan'];

    $foto_path = $d['foto']; // default foto lama
    if(isset($_FILES['foto']) && $_FILES['foto']['error']==0){
        if($d['foto'] && file_exists($d['foto'])) unlink($d['foto']); // hapus lama
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $filename = 'bagan_'.$d['surat_id'].'_'.time().'.'.$ext;
        $target = 'uploads/bagan/'.$filename;
        move_uploaded_file($_FILES['foto']['tmp_name'], $target);
        $foto_path = $target;
    }

    pg_query_params($conn,
        "UPDATE struktur_organisasi_splp SET instansi=$1, keterangan=$2, foto=$3, updated_at=NOW() WHERE id=$4",
        [$instansi,$keterangan,$foto_path,$id]
    );

    header("Location: surat_tugas_preview.php?id=".$d['surat_id']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Edit Struktur Organisasi</title>
<link rel="icon" href="favicon.svg" type="image/svg+xml">
<style>
<?php include 'style_form.css'; ?> /* Bisa pakai file css sama seperti add */
</style>
</head>
<body>
<div class="container">
<h2>Edit Struktur Organisasi</h2>
<form method="post" enctype="multipart/form-data">
    <label>Nama Instansi:</label>
    <input type="text" name="instansi" value="<?= htmlspecialchars($d['instansi']) ?>" required>

    <label>Keterangan (opsional):</label>
    <input type="text" name="keterangan" value="<?= htmlspecialchars($d['keterangan']) ?>">

    <label>Foto Bagan Saat Ini:</label><br>
    <?php if($d['foto'] && file_exists($d['foto'])): ?>
        <img src="<?= $d['foto'] ?>" style="max-width:100%; height:auto; border:1px solid #ccc; border-radius:4px; margin-bottom:10px;">
    <?php else: ?>
        <p><em>Tidak ada foto</em></p>
    <?php endif; ?>

    <label>Ganti Foto Bagan (opsional):</label>
    <input type="file" name="foto" accept=".jpg,.jpeg,.png">

    <button type="submit">Update</button>
</form>
<a class="btn-back" href="surat_tugas_preview.php?id=<?= $d['surat_id'] ?>">← Kembali ke Surat</a>
</div>
</body>
</html>
