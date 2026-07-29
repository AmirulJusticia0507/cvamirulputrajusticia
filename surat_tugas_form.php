<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Buat Surat Tugas SPLP</title>
<link rel="icon" href="favicon.svg" type="image/svg+xml">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>
<body class="bg-gray-100">

<div class="max-w-6xl mx-auto px-4 mt-4">
<div class="bg-white rounded-xl shadow p-4">

<h4 class="mb-3">Form Surat Tugas SPLP</h4>

<form method="POST" action="surat_tugas_save.php">

<div class="grid grid-cols-1 md:grid-cols-12 gap-4">
<div class="md:col-span-6 mb-2">
<label>Nomor Surat</label>
<input type="text" name="nomor_surat" id="nomor_surat" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" readonly>
</div>

<div class="md:col-span-6 mb-2">
<label>Kota</label>
<input type="text" name="kota" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" required>
</div>

<div class="md:col-span-6 mb-2">
<label>Tanggal Surat</label>
<input type="date" name="tanggal_surat" id="tanggal_surat" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" required>
</div>
</div>

<hr>

<h6>PIC SPLP</h6>

<input class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none mb-2" name="nama_pic" placeholder="Nama PIC" required>
<input class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none mb-2" name="nip_pic" placeholder="NIP">
<input class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none mb-2" name="jabatan_pic" placeholder="Jabatan">
<input class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none mb-2" name="unit_kerja" id="unit_kerja" placeholder="Unit Kerja">
<input class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none mb-2" name="telp_pic" placeholder="No Telp / WA">
<input class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none mb-2" name="email_pic" placeholder="Email wajib.go.id">

<hr>

<h6>Pimpinan Penandatangan</h6>

<input class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none mb-2" name="pimpinan_nama" placeholder="Nama Pimpinan">
<input class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none mb-2" name="pimpinan_nip" placeholder="NIP Pimpinan">
<input class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none mb-3" name="pimpinan_jabatan" placeholder="Jabatan">

<!-- <button class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition whitespace-nowrap bg-green-600 text-white hover:bg-green-700">Simpan & Preview</button> -->
 <button type="button" id="btnSubmit" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition whitespace-nowrap bg-green-600 text-white hover:bg-green-700">
    Simpan & Preview
</button>

<a href="index.php" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition whitespace-nowrap bg-gray-500 text-white hover:bg-gray-600">Batal</a>

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
