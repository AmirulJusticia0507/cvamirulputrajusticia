<?php
// Execute hanya CREATE TABLE statements dari schema_cv_db.sql
$local = include 'config.local.php';
$c = pg_connect("host=$local[host] port=$local[port] dbname=$local[db] user=$local[user] password=$local[pass]");
if (!$c) {
    echo "Gagal koneksi: " . pg_last_error();
    exit;
}

echo "Menjalankan CREATE TABLE statements...\n";

// Read schema file
$schemaContent = file_get_contents('schema_cv_db.sql');
if ($schemaContent === false) {
    echo "Gagal baca file schema_cv_db.sql\n";
    pg_close($c);
    exit;
}

// Ambil hanya bagian CREATE TABLE (dari baris 1 sampai sebelum seed data)
$lines = explode("\n", $schemaContent);
$inSeedData = false;
$createStatements = [];

foreach ($lines as $line) {
    // Cek apakah kita masuk ke bagian seed data
    if (trim($line) === '-- ============================================================') {
        // Cek apakah ini seed data marker
        $nextLine = $lines[array_search('-- ============================================================', $lines) + 1] ?? '';
        if (str_contains($nextLine, '-- SEED DATA') || str_contains($nextLine, '-- ============================================================')) {
            $inSeedData = true;
        }
    }
    
    if ($inSeedData) {
        // Hanya ambil baris yang bukan comment dan kosong
        $trimmed = trim($line);
        if (!empty($trimmed) && !str_starts_with($trimmed, '--') && !str_starts_with($trimmed, '/*')) {
            $createStatements[] = $trimmed;
        }
    } else {
        $createStatements[] = $line;
    }
}

// Gabungkan kembali menjadi content tanpa seed data
$filteredContent = implode("\n", $createStatements);

// Split by semicolons and execute
$statements = explode(';', $filteredContent);
$executed = 0;

foreach ($statements as $statement) {
    $statement = trim($statement);
    if (empty($statement) || str_starts_with($statement, '--') || str_starts_with($statement, '/*')) {
        continue;
    }
    
    $result = pg_query($c, $statement);
    if ($result === false) {
        $errormsg = pg_last_error($c);
        // Abaikan error "already exists" atau "relation exists"
        if (strpos($errormsg, 'already exists') === false && strpos($errormsg, 'relation exists') === false) {
            echo "Error: " . substr($errormsg, 0, 100) . "...<br>";
        } else {
            $executed++;
        }
    } else {
        $executed++;
    }
}

echo "<br>Selesai: $executed CREATE TABLE statements dieksekusi.<br>";

// Verify tables
$result = pg_query($c, "SELECT table_name FROM information_schema.tables WHERE table_schema='public' ORDER BY table_name");
if ($result && pg_num_rows($result) > 0) {
    echo "<br>Tabel di cv_db:<br>";
    while ($row = pg_fetch_row($result)) {
        echo "- " . $row[0] . "<br>";
    }
}

pg_close($c);
echo "<br>Selesai!";
?>