<?php
include 'config.php';
$surat_id = (int)$_GET['surat_id'];

if ($_POST) {
    pg_query_params($conn,"
        INSERT INTO whitelist_ip
        (surat_id, ip_publik, as_number, as_name, nama_jaringan)
        VALUES ($1,$2,$3,$4,$5)",
        [
            $surat_id,
            $_POST['ip'],
            $_POST['asn'],
            $_POST['asname'],
            $_POST['jaringan']
        ]
    );

    echo "<script>
        Swal.fire('Berhasil','IP ditambahkan','success')
        .then(()=>location='surat_tugas_preview.php?id=$surat_id');
    </script>";
}
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

<div class="container mt-4">
<div class="card shadow">
<div class="card-header bg-info text-white">
    <strong>Tambah Whitelist IP</strong>
</div>
<div class="card-body">
<form method="post">
    <label>IP Publik</label>
    <input class="form-control mb-2" name="ip" required>

    <label>AS Number</label>
    <input class="form-control mb-2" name="asn">

    <label>AS Name</label>
    <input class="form-control mb-2" name="asname">

    <label>Nama Jaringan</label>
    <input class="form-control mb-3" name="jaringan">

    <button class="btn btn-success">Simpan</button>
    <a href="surat_tugas_preview.php?id=<?= $surat_id ?>" class="btn btn-secondary">Kembali</a>
</form>
</div>
</div>
</div>
