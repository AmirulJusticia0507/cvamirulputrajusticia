<?php
session_start();
include 'config.php';

// Cek role user
if(!isset($_SESSION['role'])) {
    die('Access denied');
}

$role = $_SESSION['role']; // Contoh: 'admin' atau 'viewer'

// Ambil ID surat
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if(!$id) die('ID surat tidak valid');

// Ambil data surat
$sql = "
SELECT s.*, t.nama AS nama_pic, t.nip AS nip_pic, t.jabatan AS jabatan_pic, t.unit_kerja,
       t.telp, t.email
FROM surat_tugas_splp s
LEFT JOIN tim_pic_splp t ON t.surat_id = s.id AND t.is_pic_utama = true
WHERE s.id = $1
";
$res = pg_query_params($conn, $sql, [$id]);
$d = pg_fetch_assoc($res);
if(!$d) die('Data surat tidak ditemukan');

$isLocked = ($d['status'] === 'LOCKED');

// Fungsi bulan Romawi
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

// Auto-lock surat jika tombol diklik (hanya admin dan belum dikunci)
if(isset($_POST['lock']) && $role==='admin' && !$isLocked){
    $update = pg_query_params($conn, "UPDATE surat_tugas_splp SET status='LOCKED', updated_at=CURRENT_TIMESTAMP WHERE id=$1", [$id]);
    if($update) {
        $isLocked = true;
        $d['status'] = 'LOCKED';
    }
}

// Ambil daftar tim, tugas, IP, bagan
$tim = pg_query_params($conn, "SELECT * FROM tim_pic_splp WHERE surat_id=$1", [$id]);
$tugas = pg_query_params($conn, "SELECT * FROM uraian_tugas_splp WHERE surat_id=$1 ORDER BY urutan ASC", [$id]);
$ips = pg_query_params($conn, "SELECT * FROM whitelist_ip WHERE surat_id=$1", [$id]);
$bagans = pg_query_params($conn, "SELECT * FROM struktur_organisasi_splp WHERE surat_id=$1 ORDER BY urutan ASC", [$id]);

// Tentukan watermark
$watermark = $isLocked ? 'FINAL' : 'DRAFT';
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Surat Tugas SPLP - <?= htmlspecialchars($nomor_surat) ?></title>
<link rel="icon" href="favicon.svg" type="image/svg+xml">
<style>
body { font-family: "Times New Roman", serif; font-size:12pt; margin:40px; line-height:1.5; position: relative; }
.center{text-align:center;}
.right{text-align:right;}
.underline{text-decoration:underline;}
.mt-2{margin-top:20px;}
.mt-4{margin-top:40px;}
table { border-collapse: collapse; width: 100%; margin-top:10px; border: 2.5px solid #000; }
table th, table td { border: 2.5px solid #000; padding:5px; vertical-align:top; }
.watermark {
    position: absolute;
    top: 45%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-30deg);
    font-size: 80px;
    color: rgba(200,0,0,0.2);
    z-index: 0;
    pointer-events: none;
}
.no-print { display: none; }
</style>
</head>
<body>

<div class="watermark"><?= $watermark ?></div>

<!-- Header Surat -->
<p>
Nomor : <?= $nomor_surat ?><br>
Sifat : Biasa<br>
Lampiran : 1 berkas<br>
Hal : Surat Penunjukan/Pergantian PIC SPLP
</p>

<p class="left"><strong>Kepada Yth.<br>
Direktur Aplikasi Pemerintah Digital<br>
di Tempat</strong></p>

<p>&emsp;&emsp;Dalam rangka menjalankan amanat Peraturan Presiden Nomor 95 Tahun 2018 tentang SPBE ...</p>

<p class="center underline"><strong>SURAT TUGAS</strong></p>
<p class="center">No: <?= $nomor_surat ?></p>

<!-- Daftar PIC -->
<h4 class="mt-4">MENUGASKAN:</h4>
<table width="100%" cellspacing="0" cellpadding="4">
<tr>
    <td width="5%" valign="top"><strong>Kepada</strong></td>
    <td width="1%">:</td>
    <td width="5%">Nama</td><td><?= $d['nama_pic'] ?></td>
</tr>
<tr>
    <td></td><td>NIP</td><td><?= $d['nip_pic'] ?></td>
</tr>
<tr>
    <td></td><td>Jabatan</td><td><?= $d['jabatan_pic'] ?></td>
</tr>
<tr>
    <td></td><td>Unit Kerja</td><td><?= $d['unit_kerja'] ?></td>
</tr>
<tr>
    <td></td><td>Telp</td><td><?= $d['telp'] ?></td>
</tr>
<tr>
    <td></td><td>Email</td><td><?= $d['email'] ?></td>
</tr>
</table>

<!-- Daftar Uraian Tugas -->
<h4>Uraian Tugas:</h4>
<table>
<tr><th>No</th><th>Uraian Tugas</th></tr>
<?php $no=1; while($u = pg_fetch_assoc($tugas)): ?>
<tr>
<td><?= $no++ ?></td>
<td><?= nl2br(htmlspecialchars($u['uraian'])) ?></td>
</tr>
<?php endwhile; ?>
</table>

<!-- Tombol Lock & Print hanya admin dan DRAFT -->
<?php if($role==='admin' && !$isLocked): ?>
<form method="post" style="margin-top:20px;">
    <button type="submit" name="lock">🔒 Lock & Print</button>
</form>
<?php endif; ?>

<script>
window.onload = function() {
    <?php if($isLocked): ?>
        window.print();
    <?php endif; ?>
</script>
<?php include __DIR__ . '/includes/darkmode.php'; ?>
</body>
</html>