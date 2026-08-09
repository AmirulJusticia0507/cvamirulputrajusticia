-- ============================================================
-- Schema lengkap untuk database cv_db (PostgreSQL)
-- Import file ini via Beekeeper Studio atau psql
-- ============================================================

-- Buat database (jalankan terpisah jika perlu):
-- CREATE DATABASE cv_db;

-- 1. roles (wajib diisi dulu karena dirujuk users)
CREATE TABLE IF NOT EXISTS roles (
    id SERIAL PRIMARY KEY,
    role_name VARCHAR(50)
);

-- 2. users
CREATE TABLE IF NOT EXISTS users (
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
);

-- 3. settings
CREATE TABLE IF NOT EXISTS settings (
    key VARCHAR(255) PRIMARY KEY,
    value TEXT
);

-- 4. profile
CREATE TABLE IF NOT EXISTS profile (
    id SERIAL PRIMARY KEY,
    full_name VARCHAR(255),
    headline VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(50),
    linkedin VARCHAR(500),
    summary TEXT,
    photo VARCHAR(500) DEFAULT 'uploads/profile/default.jpg',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. skills
CREATE TABLE IF NOT EXISTS skills (
    id SERIAL PRIMARY KEY,
    skill_name VARCHAR(255),
    level VARCHAR(50),
    years NUMERIC(4,1)
);

-- 5b. languages
CREATE TABLE IF NOT EXISTS languages (
    id SERIAL PRIMARY KEY,
    language_name VARCHAR(100),
    proficiency VARCHAR(50)
);

-- 6. work_experience
CREATE TABLE IF NOT EXISTS work_experience (
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
);

-- 7. cv_history
CREATE TABLE IF NOT EXISTS cv_history (
    id SERIAL PRIMARY KEY,
    work_snapshot JSONB,
    url TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 8. apply_destination
CREATE TABLE IF NOT EXISTS apply_destination (
    id SERIAL PRIMARY KEY,
    company_name VARCHAR(255),
    position VARCHAR(255)
);

-- 9. cover_letters
CREATE TABLE IF NOT EXISTS cover_letters (
    id SERIAL PRIMARY KEY,
    destination_id INTEGER REFERENCES apply_destination(id),
    subject VARCHAR(255),
    content TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 10. cover_letter_waiting_list
CREATE TABLE IF NOT EXISTS cover_letter_waiting_list (
    id SERIAL PRIMARY KEY,
    cover_letter_id INTEGER REFERENCES cover_letters(id),
    email VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 11. motivation_letters
CREATE TABLE IF NOT EXISTS motivation_letters (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255),
    country_code VARCHAR(5),
    language_code VARCHAR(5),
    content TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 12. surat_tugas_splp
CREATE TABLE IF NOT EXISTS surat_tugas_splp (
    id SERIAL PRIMARY KEY,
    nomor_urut INTEGER,
    kode_surat VARCHAR(20),
    bulan INTEGER,
    tahun INTEGER,
    nomor_surat VARCHAR(100),
    kota VARCHAR(100),
    tanggal_surat DATE,
    pimpinan_nama VARCHAR(255),
    pimpinan_nip VARCHAR(50),
    pimpinan_jabatan VARCHAR(255),
    status VARCHAR(20) DEFAULT 'DRAFT',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 13. tim_pic_splp
CREATE TABLE IF NOT EXISTS tim_pic_splp (
    id SERIAL PRIMARY KEY,
    surat_id INTEGER REFERENCES surat_tugas_splp(id),
    nama VARCHAR(255),
    nip VARCHAR(50),
    jabatan VARCHAR(255),
    unit_kerja VARCHAR(255),
    telp VARCHAR(50),
    email VARCHAR(255),
    is_pic_utama BOOLEAN DEFAULT FALSE
);

-- 14. uraian_tugas_splp
CREATE TABLE IF NOT EXISTS uraian_tugas_splp (
    id SERIAL PRIMARY KEY,
    surat_id INTEGER REFERENCES surat_tugas_splp(id),
    uraian TEXT,
    urutan INTEGER,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 15. whitelist_ip
CREATE TABLE IF NOT EXISTS whitelist_ip (
    id SERIAL PRIMARY KEY,
    surat_id INTEGER REFERENCES surat_tugas_splp(id),
    ip_publik VARCHAR(45),
    as_number VARCHAR(50),
    as_name VARCHAR(255),
    nama_jaringan VARCHAR(255)
);

-- 16b. portfolio (Featured Projects)
CREATE TABLE IF NOT EXISTS portfolio (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255),
    description TEXT,
    tech_stack VARCHAR(500),
    repo_url VARCHAR(500),
    demo_url VARCHAR(500),
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 16. struktur_organisasi_splp
CREATE TABLE IF NOT EXISTS struktur_organisasi_splp (
    id SERIAL PRIMARY KEY,
    surat_id INTEGER REFERENCES surat_tugas_splp(id),
    instansi VARCHAR(255),
    keterangan TEXT,
    foto VARCHAR(500),
    urutan INTEGER,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- SEED DATA AWAL
-- ============================================================

-- Roles
INSERT INTO roles (role_name) VALUES ('admin'), ('viewer')
ON CONFLICT DO NOTHING;

-- User admin default (password: admin123)
-- Hash bcrypt untuk 'admin123': $2y$10$...
INSERT INTO users (username, email, password_hash, role_id)
SELECT 'admin', 'admin@cv.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', id
FROM roles WHERE role_name = 'admin'
ON CONFLICT DO NOTHING;

-- Languages seed
INSERT INTO languages (language_name, proficiency) VALUES
    ('Indonesian', 'Native'),
    ('English', 'Fluent'),
    ('Javanese', 'Native')
ON CONFLICT DO NOTHING;

-- Settings default
INSERT INTO settings (key, value) VALUES
    ('session_minute', '60'),
    ('remember_day', '30'),
    ('admin_register_key', 'admin123')
ON CONFLICT DO NOTHING;

-- Work experience seed (hanya DISKOMINFO DIY yang present='t')
INSERT INTO work_experience
    (company, position, start_date, end_date, present, description, location, status_kerja)
VALUES
    ('PT. Syariah Al Amin Life Insurance', 'Web Systems Engineer', '2018-10-01', '2019-10-31', 'f', 'Delivered internal office systems in an early full-stack development role.', 'Yogyakarta, Indonesia', 'Full-time'),
    ('Sekretariat DPRD Sleman', 'Web Systems Engineer', '2020-01-01', '2022-10-31', 'f', 'Built JDIH, a legal documentation and information system for regional legislation. Developed a visitor-management system and a DPRD work-meeting (rapat kerja) coordination system.', 'Sleman, Indonesia', 'Full-time'),
    ('PT. ASI Pudjiastuti Aviation (Susi Air)', 'Web Systems Engineer', '2022-10-01', '2023-01-31', 'f', 'Contributed to a pilot digital project and supported general-affairs system needs for the airline.', 'Yogyakarta, Indonesia', 'Full-time'),
    ('PT. Maybank Indonesia Finance Tbk.', 'Web Systems Engineer', '2023-07-01', '2023-07-31', 'f', 'Provided short-term technical support during a peak operational period.', 'Indonesia', '1-week engagement'),
    ('PT. BPRS HIK MCI Yogyakarta', 'Web Systems Engineer', '2023-09-01', '2024-04-30', 'f', 'Developed a mobile field-collection application, an internal web portal, and eForm/eVisit digital service modules. Delivered additional internal systems supporting company operations.', 'Yogyakarta, Indonesia', 'Full-time'),
    ('CV Milimeter Yogyakarta', 'Web Systems Engineer', '2024-06-01', '2024-07-31', 'f', 'Built the DEP (Digital Employee Performance) Service - a PHP/Laravel backend for office employee-performance management workflows.', 'Yogyakarta, Indonesia', 'Full-time'),
    ('PT Kencana Konsep Indonesia', 'Web Systems Engineer', '2024-10-01', '2026-01-31', 'f', 'Led development on a national-scale SPLP system and a multi-agency data consolidation platform. Built and maintained services using PHP (Laravel), Vue.js, Node.js, and Python (Django) with MySQL/PostgreSQL; applied REST/SOAP APIs and data-validation pipelines to keep cross-system data consistent.', 'Bandung, West Java, Indonesia', 'Contract'),
    ('DISKOMINFO DIY', 'Web Systems Engineer', '2026-03-01', NULL, 't', 'Maintain core government server infrastructure and an AI-powered CCTV monitoring portal for the DIY regional government. Develop public survey systems used across regional agencies. Build the DTSEN/DTKS social welfare registration platform (Dinsos DIY) - a Vue 3 + TypeScript system with multi-step registration, KRT/family-member data sync, location cascade logic, and PDF proof-of-registration generation - integrating data across multiple government agencies.', 'Yogyakarta, Indonesia', 'Full-time')
ON CONFLICT DO NOTHING;

-- Portfolio seed (Featured Projects)
INSERT INTO portfolio (title, description, tech_stack, repo_url, sort_order) VALUES
    ('54_testDNA', 'DNA testing platform - automated sample workflow, test-result processing, and reporting engine. Tech: JavaScript, Node.js.', 'JavaScript, Node.js', 'https://github.com/AmirulJusticia0507/54_testDNA', 1),
    ('DEP Service OfficeWill', 'DEP (Digital Employee Performance) service for office management - backend covering employee performance workflows. Built during the CV Milimeter Yogyakarta engagement. Tech: PHP, Laravel.', 'PHP, Laravel', 'https://github.com/AmirulJusticia0507/DEP_Service_OfficeWill', 2),
    ('E-Voting System Netizen', 'Online e-voting platform for citizen participation with secure, auditable ballot processing. Tech: Python, Django.', 'Python, Django', 'https://github.com/AmirulJusticia0507/e-voting-system-netizen', 3),
    ('Healthcare Queue Scheduling Engine', 'Hospital queue management system - appointment scheduling and queue allocation engine. Tech: JavaScript, Node.js.', 'JavaScript, Node.js', 'https://github.com/AmirulJusticia0507/healthcare-queue-scheduling-engine', 4),
    ('Islamic Digital Currency Engine', 'Islamic Digital Currency Engine (IDCE) - asset-backed, sharia-compliant ledger and payment engine built on NewSQL (CockroachDB) for strict ACID financial consistency, integrated with legal modules (notary & law firm) and supervised by a Sharia Supervisory Board. Tech: JavaScript, Node.js, CockroachDB.', 'JavaScript, Node.js, CockroachDB', 'https://github.com/AmirulJusticia0507/islamic-currency-engine', 5),
    ('Lex Integrity', 'Local AI Policy & Regulatory Compliance Matrix - legal integrity analysis and automatic regulation-contradiction tracker built with MERN stack + Local LLM (100% offline). Maps the Indonesian legal hierarchy (UU, PP, Perpres, Perda), detects discretion gaps (abuse of power), analyzes policy impact, and recommends sanctions visually. Tech: JavaScript, MERN, Local LLM.', 'JavaScript, MERN, Local LLM', 'https://github.com/AmirulJusticia0507/lex-integrity', 6),
    ('Universal Fans WooCommerce Hybrid Architecture (Concept & Prototype)', 'High-performance WooCommerce starter theme combining Tailwind CSS, Gutenberg, and isolation wrappers for WPBakery/Elementor/Flatsome with Core Web Vitals & PostgreSQL migration readiness. Tech: WordPress, WooCommerce, PHP, Tailwind CSS, JavaScript.', 'WordPress, WooCommerce, PHP, Tailwind CSS, JavaScript', '', 7)
ON CONFLICT DO NOTHING;
