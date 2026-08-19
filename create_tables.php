<?php
// Buat tabel-tabel di cv_db
$local = include 'config.local.php';
$c = pg_connect("host=$local[host] port=$local[port] dbname=$local[db] user=$local[user] password=$local[pass]");
if ($c) {
    // Table: roles
    pg_query($c, "CREATE TABLE IF NOT EXISTS roles (
        id SERIAL PRIMARY KEY,
        role_name VARCHAR(50)
    )");
    
    // Table: users
    pg_query($c, "CREATE TABLE IF NOT EXISTS users (
        id SERIAL PRIMARY KEY,
        username VARCHAR(100) UNIQUE,
        email VARCHAR(255) UNIQUE,
        password_hash VARCHAR(255),
        role_id INTEGER REFERENCES roles(id),
        failed_login INTEGER DEFAULT 0,
        is_locked BOOLEAN DEFAULT FALSE,
        remember_token VARCHAR(255),
        reset_token VARCHAR(255),
        reset_expire TIMESTAMP
    )");
    
    // Table: profile
    pg_query($c, "CREATE TABLE IF NOT EXISTS profile (
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
    
    // Table: skills
    pg_query($c, "CREATE TABLE IF NOT EXISTS skills (
        id SERIAL PRIMARY KEY,
        skill_name VARCHAR(255),
        level VARCHAR(50),
        years NUMERIC(4,1)
    )");
    
    // Table: languages
    pg_query($c, "CREATE TABLE IF NOT EXISTS languages (
        id SERIAL PRIMARY KEY,
        language_name VARCHAR(100),
        proficiency VARCHAR(50)
    )");
    
    // Table: work_experience
    pg_query($c, "CREATE TABLE IF NOT EXISTS work_experience (
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
    
    // Table: portfolio
    pg_query($c, "CREATE TABLE IF NOT EXISTS portfolio (
        id SERIAL PRIMARY KEY,
        title VARCHAR(255),
        description TEXT,
        tech_stack VARCHAR(500),
        repo_url VARCHAR(500),
        demo_url VARCHAR(500),
        sort_order INTEGER DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    echo "Tabel berhasil dibuat di cv_db!<br>";
    pg_close($c);
} else {
    echo "Gagal koneksi: " . pg_last_error();
}
?>