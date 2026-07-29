<?php
include 'config.php';
$surat_id = (int)$_GET['surat_id'];

if ($_POST) {
    $sql = "INSERT INTO tim_pic_splp
        (surat_id, nama, nip, jabatan, unit_kerja, telp, email, is_pic_utama)
        VALUES ($1,$2,$3,$4,$5,$6,$7,$8)";
    pg_query_params($conn, $sql, [
        $surat_id,
        $_POST['nama'],
        $_POST['nip'],
        $_POST['jabatan'],
        $_POST['unit_kerja'],
        $_POST['telp'],
        $_POST['email'],
        isset($_POST['is_pic_utama'])
    ]);

    echo "<script>
        Swal.fire('Berhasil','Data PIC ditambahkan','success')
        .then(()=>location='surat_tugas_preview.php?id=$surat_id');
    </script>";
}
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container mt-4">
<div class="card shadow">
<div class="card-header bg-primary text-white">
    <strong>Tambah Tim PIC SPLP</strong>
</div>
<div class="card-body">
<form method="post">
    <div class="row mb-3">
        <div class="col-md-6">
            <label>Nama</label>
            <input class="form-control" name="nama" required>
        </div>
        <div class="col-md-6">
            <label>NIP</label>
            <input class="form-control" name="nip">
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <label>Jabatan</label>
            <input class="form-control" name="jabatan">
        </div>
        <div class="col-md-6">
            <label>Unit Kerja</label>
            <input class="form-control" name="unit_kerja">
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <label>No Telp</label>
            <input class="form-control" name="telp">
        </div>
        <div class="col-md-6">
            <label>Email</label>
            <input class="form-control" name="email">
        </div>
    </div>

    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="is_pic_utama">
        <label class="form-check-label">PIC Utama</label>
    </div>

    <button class="btn btn-success">💾 Simpan</button>
    <a href="surat_tugas_preview.php?id=<?= $surat_id ?>" class="btn btn-secondary">Kembali</a>
</form>
</div>
</div>
</div>
