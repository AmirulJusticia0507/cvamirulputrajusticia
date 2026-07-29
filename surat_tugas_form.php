<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Buat Surat Tugas SPLP</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>
<body class="bg-light">

<div class="container mt-4">
<div class="card p-4">

<h4 class="mb-3">Form Surat Tugas SPLP</h4>

<form method="POST" action="surat_tugas_save.php">

<div class="row">
<div class="col-md-6 mb-2">
<label>Nomor Surat</label>
<input type="text" name="nomor_surat" id="nomor_surat" class="form-control" readonly>
</div>

<div class="col-md-6 mb-2">
<label>Kota</label>
<input type="text" name="kota" class="form-control" required>
</div>

<div class="col-md-6 mb-2">
<label>Tanggal Surat</label>
<input type="date" name="tanggal_surat" id="tanggal_surat" class="form-control" required>
</div>
</div>

<hr>

<h6>PIC SPLP</h6>

<input class="form-control mb-2" name="nama_pic" placeholder="Nama PIC" required>
<input class="form-control mb-2" name="nip_pic" placeholder="NIP">
<input class="form-control mb-2" name="jabatan_pic" placeholder="Jabatan">
<input class="form-control mb-2" name="unit_kerja" id="unit_kerja" placeholder="Unit Kerja">
<input class="form-control mb-2" name="telp_pic" placeholder="No Telp / WA">
<input class="form-control mb-2" name="email_pic" placeholder="Email wajib.go.id">

<hr>

<h6>Pimpinan Penandatangan</h6>

<input class="form-control mb-2" name="pimpinan_nama" placeholder="Nama Pimpinan">
<input class="form-control mb-2" name="pimpinan_nip" placeholder="NIP Pimpinan">
<input class="form-control mb-3" name="pimpinan_jabatan" placeholder="Jabatan">

<!-- <button class="btn btn-success">Simpan & Preview</button> -->
 <button type="button" id="btnSubmit" class="btn btn-success">
    Simpan & Preview
</button>

<a href="index.php" class="btn btn-secondary">Batal</a>

</form>

</div>
</div>

<script>
// Fungsi untuk update nomor surat otomatis
function updateNomorSurat(){
    var tanggal = $('#tanggal_surat').val();
    var unit = $('#unit_kerja').val();

    if(tanggal && unit){
        $.post('ajax_nomor_surat.php', {tanggal: tanggal, unit_kerja: unit}, function(data){
            $('#nomor_surat').val(data);
        });
    }
}

// Trigger saat user ubah tanggal atau unit kerja
$('#tanggal_surat, #unit_kerja').on('change keyup', updateNomorSurat);
</script>
<script>
$('#btnSubmit').on('click', function () {
    Swal.fire({
        title: 'Simpan Surat Tugas?',
        text: 'Surat akan dibuat dalam status DRAFT dan bisa diedit sebelum dikunci.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Simpan',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $('form').submit();
        }
    });
});
</script>

</body>
</html>
