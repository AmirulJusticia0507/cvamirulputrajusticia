<?php
// config.php - koneksi PostgreSQL
$host = 'localhost';
$db   = 'cv_db';
$user = 'postgres';
$pass = 'admin';
$port = "5432";

$conn = pg_connect("host=$host port=$port dbname=$db user=$user password=$pass");
if(!$conn){ die("Connection failed"); }

// Pastikan tabel work_experience ada
pg_query($conn, "CREATE TABLE IF NOT EXISTS work_experience (
    id SERIAL PRIMARY KEY,
    company VARCHAR(255),
    position VARCHAR(255),
    start_date DATE,
    end_date DATE,
    description TEXT,
    stack TEXT
)");
?>
