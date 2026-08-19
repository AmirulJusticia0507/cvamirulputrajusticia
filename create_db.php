<?php
// Buat database cv_db pakai password yang sudah di-set
$c = pg_connect("host=localhost port=5432 dbname=postgres user=postgres password='postgres123'");
if ($c) {
    echo "Terhubung ke postgres!<br>";
    
    // Create database
    $sql = "CREATE DATABASE cv_db";
    $result = pg_query($c, $sql);
    
    if ($result) {
        echo "Database cv_db berhasil dibuat!<br>";
    } else {
        echo "Gagal buat database (mungkin sudah ada): " . pg_last_error($c) . "<br>";
    }
    
    pg_close($c);
} else {
    echo "Gagal koneksi: " . pg_last_error();
}
?>