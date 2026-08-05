<?php
include 'config.php';
$id = (int)$_GET['id'];
$sql = "
SELECT
    s.*,
    t.nama   AS nama_pic,
    t.nip    AS nip_pic,
    t.jabatan AS jabatan_pic,
    t.unit_kerja,
    t.telp,
    t.email
FROM surat_tugas_splp s
LEFT JOIN tim_pic_splp t
    ON t.surat_id = s.id
   AND t.is_pic_utama = true
WHERE s.id = $1
";

$res = pg_query_params($conn, $sql, [$id]);
$d = pg_fetch_assoc($res);

if (!$d) {
    die('Data surat tidak ditemukan');
}

$isLocked = ($d['status'] === 'LOCKED');
function bulanRomawi($bulan){
    $map = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X',11=>'XI',12=>'XII'];
    return $map[$bulan];
}

$nomor_surat = $d['nomor_surat'] ?? sprintf(
    "%03d/%s/%s/%s/%d",
    $d['nomor_urut'] ?? 1,
    $d['kode_surat'] ?? 'SPLP',
    strtoupper($d['unit_kerja'] ?: 'DISKOMINFO'),
    bulanRomawi(date('n', strtotime($d['tanggal_surat']))),
    date('Y', strtotime($d['tanggal_surat']))
);

// Ambil daftar tim PIC
$tim = pg_query_params($conn, "SELECT * FROM tim_pic_splp WHERE surat_id=$1", [$id]);

// Ambil daftar Whitelist IP
$ips = pg_query_params($conn, "SELECT * FROM whitelist_ip WHERE surat_id=$1", [$id]);

$tugas = pg_query_params(
    $conn,
    "SELECT * FROM uraian_tugas_splp WHERE surat_id=$1 ORDER BY urutan ASC",
    [$id]
);

?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Surat Tugas SPLP</title>
<link rel="icon" href="favicon.svg" type="image/svg+xml">
<style>
body {
    font-family: "Times New Roman", serif;
    font-size:12pt;
    margin:40px;
    line-height:1.5;
}

.center{text-align:center;}
.right{text-align:right;}
.underline{text-decoration:underline;}
.mt-2{margin-top:20px;}
.mt-4{margin-top:40px;}

table {
    border-collapse: collapse;
    width: 100%;
    margin-top: 10px;
    border: 2.5px solid #000;
}

table th,
table td {
    border: 2.5px solid #000;
    padding: 5px;
    vertical-align: top;
}

button { margin-top:5px; }

</style>
</head>
<body>

<!-- Header Surat -->
<p>
Nomor &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; : <?= $nomor_surat ?><br>
Sifat &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; : Biasa<br>
Lampiran &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; : 1 (satu) berkas<br>
Hal &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; : Surat Penunjukan/Pergantian PIC SPLP
</p>

<p class="left"><strong>Kepada Yth.<br>
Direktur Aplikasi Pemerintah Digital<br>
di Tempat</strong></p>

<p>&emsp;&emsp;
Dalam rangka menjalankan amanat Peraturan Presiden Nomor 95 Tahun 2018 tentang Sistem Pemerintahan Berbasis Elektronik (SPBE), serta Peraturan Menteri Komunikasi dan Informatika Nomor 1 Tahun 2023 tentang Interoperabilitas Data dalam penyelenggaraan SPBE dan SDI, bersama ini kami sampaikan penunjukan Penanggung Jawab (PIC) Sistem Penghubung Layanan Pemerintah (SPLP) dan SPL IPPD.
</p>

<p>&emsp;&emsp;
Penunjukan ini dimaksudkan untuk memastikan terselenggaranya koordinasi, pelaksanaan teknis, serta pemeliharaan dan pengembangan API sesuai dengan ketentuan peraturan perundang-undangan. Informasi lengkap PIC SPLP tercantum dalam Surat Tugas ini.
</p>

<p class="center underline"><strong>SURAT TUGAS</strong></p>
<p class="center">No: <?= $nomor_surat ?></p>

<table>
<tr>
    <td>Menimbang:</td>
    <td></td>
    <td>
Bahwa dalam rangka implementasi Peraturan Presiden Nomor 95 Tahun 2018 tentang SPBE dan Peraturan Menkominfo No. 1 Tahun 2023 tentang Interoperabilitas Data, dipandang perlu menunjuk penanggung jawab Penyelenggaraan Layanan Interoperabilitas Data.
    </td>
</tr>
</table>

<table width="100%" cellspacing="0" cellpadding="4">
    <tr>
        <td width="15%" valign="top"><strong>Mengingat</strong></td>
        <td colspan="2" valign="top">:</td>
    </tr>
    <tr>
        <td></td>
        <td valign="top">1.</td>
        <td>Peraturan Presiden Nomor 95 Tahun 2018 tentang Sistem Pemerintahan Berbasis Elektronik</td>
    </tr>

    <tr>
        <td></td>
        <td valign="top">2.</td>
        <td>Peraturan Presiden Nomor 39 Tahun 2019 tentang Satu Data Indonesia</td>
    </tr>

    <tr>
        <td></td>
        <td valign="top">3.</td>
        <td>Peraturan Presiden Nomor 132 Tahun 2022 tentang Arsitektur Sistem Pemerintahan Berbasis Elektronik Nasional</td>
    </tr>

    <tr>
        <td></td>
        <td valign="top">4.</td>
        <td>Peraturan Presiden Nomor 82 Tahun 2023 tentang Percepatan Transformasi Digital dan Keterpaduan Layanan Digital Nasional</td>
    </tr>

    <tr>
        <td></td>
        <td valign="top">5.</td>
        <td>Peraturan Menteri Komunikasi dan Informatika Nomor 1 Tahun 2023 tentang Interoperabilitas Data Penyelenggaraan Sistem Pemerintahan Berbasis Elektronik dan Satu Data Indonesia</td>
    </tr>

    <tr>
        <td></td>
        <td valign="top">6.</td>
        <td>Surat Edaran Menteri Komunikasi dan Informatika Nomor 4 Tahun 2024 tentang Pemanfaatan Sistem Penghubung Layanan Pemerintah untuk Mendukung Interoperabilitas Data dalam Penyelenggaraan Sistem Pemerintahan Berbasis Elektronik</td>
    </tr>

    <tr>
        <td></td>
        <td valign="top">7.</td>
        <td>[Peraturan Internal IPPD] (misal: Pergub / Perbup / Perwali / Kepmen terkait SPBE)</td>
    </tr>
</table>

<h4 class="center">MENUGASKAN:</h4>
<table width="100%" cellspacing="0" cellpadding="4">
    <tr>
        <td width="5%" valign="top" rowspan="6"><strong>Kepada</strong></td>
        <td width="1%" valign="top">:</td>
        <td width="5%">Nama</td>
        <td width="25%"><?= $d['nama_pic'] ?></td>
    </tr>
    <tr>
        <td></td>
        <td>NIP</td>
        <td><?= $d['nip_pic'] ?></td>
    </tr>
    <tr>
        <td></td>
        <td>Jabatan</td>
        <td><?= $d['jabatan_pic'] ?></td>
    </tr>
    <tr>
        <td></td>
        <td>Unit Kerja</td>
        <td><?= $d['unit_kerja'] ?></td>
    </tr>
    <tr>
        <td></td>
        <td>No Telp / WA</td>
        <td><?= $d['telp_pic'] ?></td>
    </tr>
    <tr>
        <td></td>
        <td>Email</td>
        <td><?= $d['email_pic'] ?></td>
    </tr>
</table>

<h4>Untuk:</h4>

<?php if(!$isLocked): ?>
<button onclick="location.href='tugas_add.php?surat_id=<?= $id ?>'">
    Tambah Uraian Tugas
</button>
<?php endif; ?>


<table>
    <tr>
        <th width="5%">No</th>
        <th>Uraian Tugas</th>
        <th width="15%">Aksi</th>
    </tr>
    <?php $no=1; while($u = pg_fetch_assoc($tugas)): ?>
    <tr>
        <td><?= $no++ ?></td>
        <td><?= nl2br(htmlspecialchars($u['uraian'])) ?></td>
        <td>
            <a href="tugas_edit.php?id=<?= $u['id'] ?>">Edit</a> |
            <a href="tugas_delete.php?id=<?= $u['id'] ?>"
               onclick="return confirm('Yakin hapus uraian tugas ini?')">
               Hapus
            </a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<h4 class="mt-4">Daftar Tim Penanggung Jawab</h4>
<a href="tim_add.php?surat_id=<?= $id ?>" class="inline-block px-3 py-1 text-sm rounded-lg font-semibold text-center transition whitespace-nowrap bg-blue-600 text-white hover:bg-blue-700">+ Tambah PIC</a>
<table>
<tr><th>No</th><th>Nama</th><th>NIP</th><th>Jabatan</th><th>Telp</th><th>Email</th><th>Aksi</th></tr>
<?php $no=1; while($t=pg_fetch_assoc($tim)): ?>
<tr>
<td><?= $no++ ?></td>
<td><?= $t['nama'] ?></td>
<td><?= $t['nip'] ?></td>
<td><?= $t['jabatan'] ?></td>
<td><?= $t['telp'] ?></td>
<td><?= $t['email'] ?></td>
<td>
<a href="tim_edit.php?id=<?= $t['id'] ?>">Edit</a> | 
<a href="tim_delete.php?id=<?= $t['id'] ?>" onclick="return confirm('Yakin hapus?')">Hapus</a>
</td>
</tr>
<?php endwhile; ?>
</table>

<h4 class="mt-4">Daftar Whitelist IP Publik</h4>
<a href="ip_add.php?surat_id=<?= $id ?>" class="inline-block px-3 py-1 text-sm rounded-lg font-semibold text-center transition whitespace-nowrap bg-cyan-500 text-white hover:bg-cyan-600">+ Tambah IP</a>

<table>
<tr><th>No</th><th>IP Publik</th><th>AS Number</th><th>AS Name</th><th>Nama Jaringan</th><th>Aksi</th></tr>
<?php $no=1; while($ip=pg_fetch_assoc($ips)): ?>
<tr>
<td><?= $no++ ?></td>
<td><?= $ip['ip_publik'] ?></td>
<td><?= $ip['as_number'] ?></td>
<td><?= $ip['as_name'] ?></td>
<td><?= $ip['nama_jaringan'] ?></td>
<td>
<a href="ip_edit.php?id=<?= $ip['id'] ?>">Edit</a> | 
<a href="ip_delete.php?id=<?= $ip['id'] ?>" onclick="return confirm('Yakin hapus?')">Hapus</a>
</td>
</tr>
<?php endwhile; ?>
</table>

<h4 class="center">Bagan Struktur Organisasi Pengelola SPL IPPD dan Penanggung Jawab SPLP</h4>

<!-- Tombol CRUD -->
<div style="text-align:center; margin-bottom:15px;">
    <button onclick="location.href='struktur_add.php?surat_id=<?= $id ?>'" 
            style="padding:8px 15px; margin-right:5px; background-color:#28a745; color:white; border:none; border-radius:4px; cursor:pointer;">
        Tambah Bagan
    </button>
</div>

<?php
$bagans = pg_query_params($conn,
    "SELECT * FROM struktur_organisasi_splp WHERE surat_id=$1 ORDER BY urutan ASC",
    [$id]
);
?>

<?php while($b = pg_fetch_assoc($bagans)): ?>
<div class="container" style="text-align:center; margin-bottom:20px;">
    <p><strong><?= htmlspecialchars($b['instansi']) ?></strong></p>
    <?php if($b['keterangan']): ?>
        <p><em><?= htmlspecialchars($b['keterangan']) ?></em></p>
    <?php endif; ?>
    
    <?php if($b['foto'] && file_exists($b['foto'])): ?>
        <img src="<?= $b['foto'] ?>" style="max-width:600px; width:100%; height:auto; border:1px solid #000; border-radius:4px;">
    <?php else: ?>
        <p><em>Tidak ada foto bagan</em></p>
    <?php endif; ?>

    <!-- Tombol Edit & Hapus untuk setiap bagan -->
    <div class="action-links" style="margin-top:5px;">
        <a href="struktur_edit.php?id=<?= $b['id'] ?>"
           style="color:#007bff; margin-right:10px;">Edit</a>
        <a href="struktur_delete.php?id=<?= $b['id'] ?>"
           onclick="return confirm('Yakin hapus bagan ini?')"
           style="color:#dc3545;">Hapus</a>
    </div>
</div>
<?php endwhile; ?>

<table width="100%" style="margin-top:40px; border:none;">
    <tr>
        <td width="60%"></td>
        <td width="40%" style="text-align:center; border:none;">
            <?= $d['kota'] ?>, <?= date('d F Y',strtotime($d['tanggal_surat'])) ?><br>
            <strong><?= $d['pimpinan_jabatan'] ?></strong><br><br><br><br>
            <strong><?= $d['pimpinan_nama'] ?></strong><br>
            NIP <?= $d['pimpinan_nip'] ?>
        </td>
    </tr>
</table>

<?php if(!$isLocked): ?>
<form action="surat_tugas_lock.php" method="post"
      onsubmit="return confirm('Setelah dikunci, surat tidak bisa diubah. Lanjutkan?')">
    <input type="hidden" name="id" value="<?= $id ?>">
    <button class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition whitespace-nowrap bg-red-600 text-white hover:bg-red-700 mt-3">🔒 Kunci Surat Tugas</button>
</form>
<?php endif; ?>

<button id="previewBtn" style="margin-top:20px;">
    👁️ Preview Surat
</button>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('previewBtn').addEventListener('click', function(){
    Swal.fire({
        title: 'Preview Surat Tugas',
        html: `
            <iframe 
                src="surat_tugas_print.php?id=<?= $id ?>" 
                style="width:100%; height:75vh; border:1px solid #ccc; border-radius:4px;">
            </iframe>
        `,
        width: '90%',
        showCancelButton: true,
        confirmButtonText: '🖨️ Cetak',
        cancelButtonText: 'Tutup',
        preConfirm: () => {
            const iframe = document.querySelector('iframe');
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
            return false;
        }
    });
});
</script>

<?php include __DIR__ . '/includes/darkmode.php'; ?>
</body>
</html>