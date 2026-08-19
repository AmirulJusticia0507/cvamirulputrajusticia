<?php
// Test koneksi pakai config.local.php
$local = include 'config.local.php';
echo "Config loaded<br>";

// Coba cara yang lebih sederhana
$host = $local['host'];
$db = $local['db'];
$user = $local['user'];
$pass = $local['pass'];
$port = $local['port'];

$conn = @pg_connect("host=$host port=$port dbname=$db user=$user password=$pass");
if ($conn) {
    echo "KONEKSI BERHASIL!<br>";
    pg_close($conn);
} else {
    echo "KONEKSI GAGAL: " . pg_last_error() . "<br>";
}
?>