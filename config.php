<?php
// config.php - koneksi PostgreSQL
$host = 'localhost';
$db   = 'cv_db';
$user = 'postgres';
$pass = 'admin';
$port = "5433";

$conn = pg_connect("host=$host port=$port dbname=$db user=$user password=$pass");
if(!$conn){ die("Connection failed"); }

// Pastikan tabel-tabel utama ada
pg_query($conn, "CREATE TABLE IF NOT EXISTS work_experience (
    id SERIAL PRIMARY KEY,
    company VARCHAR(255),
    position VARCHAR(255),
    start_date DATE,
    end_date DATE,
    present CHAR(1) DEFAULT 'f',
    description TEXT,
    location VARCHAR(255),
    status_kerja VARCHAR(50) DEFAULT 'Full-time',
    project TEXT,
    stack TEXT,
    integrated VARCHAR(255),
    uptime VARCHAR(255),
    error_reduction VARCHAR(255),
    users_onboarded VARCHAR(255)
)");

pg_query($conn, "CREATE TABLE IF NOT EXISTS profile (
    id SERIAL PRIMARY KEY,
    full_name VARCHAR(255),
    headline VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(50),
    linkedin VARCHAR(500),
    summary TEXT,
    photo VARCHAR(500) DEFAULT 'uploads/profile/default.jpg',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

pg_query($conn, "CREATE TABLE IF NOT EXISTS skills (
    id SERIAL PRIMARY KEY,
    skill_name VARCHAR(255),
    level VARCHAR(50),
    years NUMERIC(4,1)
)");

pg_query($conn, "CREATE TABLE IF NOT EXISTS languages (
    id SERIAL PRIMARY KEY,
    language_name VARCHAR(100),
    proficiency VARCHAR(50)
)");
?>
