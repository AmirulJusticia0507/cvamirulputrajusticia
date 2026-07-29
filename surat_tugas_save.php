<?php
include 'config.php';

/* =========================
   1. DATA SURAT
========================= */
$kota            = $_POST['kota'];
$tanggal_surat   = $_POST['tanggal_surat'];
$pimpinan_nama   = $_POST['pimpinan_nama'];
$pimpinan_nip    = $_POST['pimpinan_nip'];
$pimpinan_jabatan= $_POST['pimpinan_jabatan'];

/* =========================
   2. DATA PIC (UTAMA)
========================= */
$nama_pic   = $_POST['nama_pic'];
$nip_pic    = $_POST['nip_pic'];
$jabatan_pic= $_POST['jabatan_pic'];
$unit_kerja = $_POST['unit_kerja'];
$telp_pic   = $_POST['telp_pic'];
$email_pic  = $_POST['email_pic'];

/* =========================
   3. GENERATE NOMOR SURAT
========================= */
$bulan = (int)date('n', strtotime($tanggal_surat));
$tahun = (int)date('Y', strtotime($tanggal_surat));
$kode_surat = 'SPLP';

function bulanRomawi($b){
    return ['','I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'][$b];
}

$res = pg_query_params($conn,
    "SELECT COALESCE(MAX(nomor_urut),0)+1 AS next
     FROM surat_tugas_splp
     WHERE bulan=$1 AND tahun=$2",
    [$bulan,$tahun]
);
$row = pg_fetch_assoc($res);
$nomor_urut = $row['next'];

$nomor_surat = sprintf(
    "%03d/%s/%s/%s/%d",
    $nomor_urut,
    $kode_surat,
    strtoupper(preg_replace('/\s+/','',$unit_kerja)),
    bulanRomawi($bulan),
    $tahun
);

/* =========================
   4. SIMPAN SURAT
========================= */
$sql_surat = "
INSERT INTO surat_tugas_splp
(nomor_urut,kode_surat,bulan,tahun,nomor_surat,kota,tanggal_surat,
 pimpinan_nama,pimpinan_nip,pimpinan_jabatan)
VALUES
($1,$2,$3,$4,$5,$6,$7,$8,$9,$10)
RETURNING id
";

$res = pg_query_params($conn,$sql_surat,[
    $nomor_urut,$kode_surat,$bulan,$tahun,$nomor_surat,
    $kota,$tanggal_surat,
    $pimpinan_nama,$pimpinan_nip,$pimpinan_jabatan
]);

if(!$res){
    die(pg_last_error($conn));
}

$surat_id = pg_fetch_result($res,0,'id');

/* =========================
   5. SIMPAN PIC UTAMA
========================= */
$sql_pic = "
INSERT INTO tim_pic_splp
(surat_id,nama,nip,jabatan,unit_kerja,telp,email,is_pic_utama)
VALUES
($1,$2,$3,$4,$5,$6,$7,true)
";

pg_query_params($conn,$sql_pic,[
    $surat_id,
    $nama_pic,$nip_pic,$jabatan_pic,
    $unit_kerja,$telp_pic,$email_pic
]);

/* =========================
   6. REDIRECT
========================= */
header("Location: surat_tugas_preview.php?id=".$surat_id);
exit;
