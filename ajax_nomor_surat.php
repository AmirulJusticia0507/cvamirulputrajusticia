<?php
include 'config.php';

$tanggal = $_POST['tanggal'];
$unit_kerja = $_POST['unit_kerja'];

$bulan = (int)date('n', strtotime($tanggal));
$tahun = (int)date('Y', strtotime($tanggal));
$kode_surat = 'SPLP';

// Cari nomor urut terbaru
$res = pg_query_params($conn, "SELECT MAX(nomor_urut) as max_urut FROM surat_tugas_splp WHERE bulan=$1 AND tahun=$2", [$bulan, $tahun]);
$row = pg_fetch_assoc($res);
$nomor_urut = $row['max_urut'] ? $row['max_urut'] + 1 : 1;

// Fungsi bulan romawi
function bulanRomawi($bulan){
    $map = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X',11=>'XI',12=>'XII'];
    return $map[$bulan];
}

// Generate nomor surat
$nomor_surat = sprintf("%03d/%s/%s/%s/%d", 
    $nomor_urut, 
    $kode_surat, 
    strtoupper(str_replace(' ', '', $unit_kerja)), 
    bulanRomawi($bulan), 
    $tahun
);

echo $nomor_surat;
