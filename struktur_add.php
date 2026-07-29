<?php
include 'config.php';
$surat_id = (int)$_GET['surat_id'];

if($_POST) {
    $instansi = $_POST['instansi'];
    $keterangan = $_POST['keterangan'];

    $foto_path = null;
    if(isset($_FILES['foto']) && $_FILES['foto']['error']==0) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $filename = 'bagan_'.$surat_id.'_'.time().'.'.$ext;
        $target = 'uploads/bagan/'.$filename;
        move_uploaded_file($_FILES['foto']['tmp_name'], $target);
        $foto_path = $target;
    }

    pg_query_params($conn,
        "INSERT INTO struktur_organisasi_splp (surat_id, instansi, keterangan, foto) VALUES ($1,$2,$3,$4)",
        [$surat_id, $instansi, $keterangan, $foto_path]
    );

    header("Location: surat_tugas_preview.php?id=$surat_id");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Tambah Struktur Organisasi</title>
<style>
body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; background:#f4f6f8; margin:0; padding:0;}
.container { max-width:600px; margin:40px auto; background:#fff; padding:25px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.1);}
h2 { text-align:center; color:#333; margin-bottom:20px;}
form label { display:block; margin-top:10px; font-weight:600; color:#555;}
form input[type="text"], form input[type="file"] { width:100%; padding:8px; margin-top:5px; border-radius:4px; border:1px solid #ccc; box-sizing:border-box; }
form button { margin-top:20px; background-color:#28a745; color:white; padding:10px 20px; border:none; border-radius:4px; cursor:pointer; font-size:14px; }
form button:hover { background-color:#218838; }
a.btn-back { display:inline-block; margin-top:15px; text-decoration:none; color:#007bff; }
a.btn-back:hover { text-decoration:underline; }
</style>
</head>
<body>
<div class="container">
<h2>Tambah Struktur Organisasi</h2>
<form method="post" enctype="multipart/form-data">
    <label>Nama Instansi:</label>
    <input type="text" name="instansi" placeholder="Contoh: [NAMA INSTANSI]" required>

    <label>Keterangan (opsional):</label>
    <input type="text" name="keterangan" placeholder="Contoh: Perubahan Struktur">

    <label>Upload Foto Bagan (jpg/png, max 2MB):</label>
    <input type="file" name="foto" accept=".jpg,.jpeg,.png">

    <button type="submit">Simpan</button>
</form>
<a class="btn-back" href="surat_tugas_preview.php?id=<?= $surat_id ?>">← Kembali ke Surat</a>
</div>
</body>
</html>
