<?php
$local = include 'config.local.php';
$c = pg_connect("host=$local[host] port=$local[port] dbname=$local[db] user=$local[user] password=$local[pass]");
if ($c) {
    $r = pg_query($c, "SELECT table_name, column_name, data_type FROM information_schema.columns WHERE table_schema='public' ORDER BY table_name, ordinal_position");
    if ($r && pg_num_rows($r) > 0) {
        $lastTable = '';
        while ($row = pg_fetch_row($r)) {
            if ($row[0] !== $lastTable) {
                $lastTable = $row[0];
                echo "<br>" . $row[0] . ":<br>";
            }
            echo "  - " . $row[1] . " (" . $row[2] . ")<br>";
        }
    }
    pg_close($c);
} else {
    echo "Gagal koneksi";
}
?>