<?php
include 'config.php';
$id = (int)$_GET['id'];

$d = pg_fetch_assoc(pg_query($conn,"SELECT * FROM tim_pic_splp WHERE id=$id"));

if ($_POST) {
    pg_query_params($conn,"
        UPDATE tim_pic_splp SET
        nama=$1,nip=$2,jabatan=$3,unit_kerja=$4,telp=$5,email=$6,is_pic_utama=$7
        WHERE id=$8",
        [
            $_POST['nama'],$_POST['nip'],$_POST['jabatan'],
            $_POST['unit_kerja'],$_POST['telp'],$_POST['email'],
            isset($_POST['is_pic_utama']),$id
        ]
    );

    echo "<script>
    Swal.fire('Berhasil','Data diperbarui','success')
    .then(()=>location='surat_tugas_preview.php?id={$d['surat_id']}');
    </script>";
}
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Form sama seperti add, value diisi -->
