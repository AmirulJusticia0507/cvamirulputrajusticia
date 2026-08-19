<?php
// Cek tabel-tabel di cv_db
$local = include 'config.local.php';
$c = pg_connect("host=$local[host] port=$local[port] dbname=$local[db] user=$local[user] password=$local[pass]");
if ($c) {
    // Coba query sederhana
    $result = pg_query($c, "SELECT table_name FROM information_schema.tables WHERE table_schema='public'");
    if ($result && pg_num_rows($result) > 0) {
        echo "Tabel di cv_db:<br>";
        while ($row = pg_fetch_row($result)) {
            echo "- " . $row[0] . "<br>";
        }
    } else {
        echo "Tabel belum ada di cv_db.<br>";
        // Coba list database
        $result2 = pg_query($c, "SELECT datname FROM pg_database");
        if ($result2) {
            echo "Database tersedia:<br>";
            while ($row2 = pg_fetch_row($result2)) {
                echo "- " . $row2[0] . "<br>";
            }
        }
    }
    pg_close($c);
} else {
    echo "Gagal koneksi: " . pg_last_error();
}
?>