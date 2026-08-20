<?php
// config.php - koneksi PostgreSQL
// Kredensial diambil dari config.local.php (tidak di-commit ke git).

$host = getenv('CV_DB_HOST') ?: 'localhost';
$db   = getenv('CV_DB_NAME') ?: 'cv_db';
$user = getenv('CV_DB_USER') ?: 'postgres';
$pass = getenv('CV_DB_PASS') ?: 'postgres123';
$port = getenv('CV_DB_PORT') ?: '5432';

$localConfig = __DIR__ . '/config.local.php';
if (file_exists($localConfig)) {
    require $localConfig;
}

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

pg_query($conn, "CREATE TABLE IF NOT EXISTS portfolio (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255),
    description TEXT,
    tech_stack VARCHAR(500),
    repo_url VARCHAR(500),
    demo_url VARCHAR(500),
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// =======================
// HELPER: user yang sedang dilihat
// =======================
// - User biasa: selalu data miliknya sendiri ($_SESSION['user_id'])
// - Admin/superadmin: bisa lihat user lain via ?user_id=X
function get_view_user_id($conn){
    // User wajib login
    if(!isset($_SESSION['user_id'])){
        return null;
    }

    $my_id   = (int) $_SESSION['user_id'];
    $my_role = $_SESSION['role'] ?? 'viewer';

    // Admin bisa melihat user lain via ?user_id=
    if(isset($_GET['user_id']) && ($my_role === 'admin')){
        $target = (int) $_GET['user_id'];
        if($target > 0){
            // Pastikan user target benar-benar ada
            $res = pg_query_params($conn, "SELECT id FROM users WHERE id=$1", [$target]);
            if(pg_num_rows($res) > 0){
                return $target;
            }
        }
    }

    return $my_id;
}

// =======================
// HELPER: user untuk halaman preview (bisa diakses publik via ?user_id=)
// =======================
function get_preview_user_id($conn){
    // 1. Jika ada ?user_id= gunakan itu (link CV publik)
    if(isset($_GET['user_id'])){
        $target = (int) $_GET['user_id'];
        if($target > 0){
            $res = pg_query_params($conn, "SELECT id FROM users WHERE id=$1", [$target]);
            if(pg_num_rows($res) > 0){
                return $target;
            }
        }
    }

    // 2. Jika sudah login, gunakan user sendiri
    if(isset($_SESSION['user_id'])){
        return (int) $_SESSION['user_id'];
    }

    // 3. Fallback: user pertama yang punya profil (default superadmin)
    $res = pg_query($conn, "SELECT user_id FROM profile WHERE user_id IS NOT NULL ORDER BY id DESC LIMIT 1");
    $row = pg_fetch_assoc($res);
    return $row ? (int) $row['user_id'] : null;
}
?>
