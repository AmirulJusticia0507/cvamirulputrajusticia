<?php
// Set password postgres menjadi postgres123
$c = pg_connect("host=localhost port=5432 dbname=postgres user=postgres");
if ($c) {
    echo "Terhubung!<br>";
    
    // Execute ALTER USER to set password
    $sql = "ALTER USER postgres WITH PASSWORD 'postgres123'";
    $result = pg_query($c, $sql);
    
    if ($result) {
        echo "Password berhasil di-set menjadi 'postgres123'!<br>";
    } else {
        echo "Gagal set password: " . pg_last_error($c) . "<br>";
    }
    
    pg_close($c);
} else {
    echo "Gagal koneksi: " . pg_last_error();
}
?>