# CVamirulputrajusticia - Running Guide

## 📋 Overview
Sistem ini adalah aplikasi CV/resume generator berbasis PHP + PostgreSQL yang dioptimalkan untuk **Applicant Tracking Systems (ATS)** dan **human readers**, sesuai nasihat dari artikel TokyoDev.

## ⚡ Quick Start - 3 Menit

### 1. Start PostgreSQL Service
Jalankan PowerShell **sebagai Administrator**:

```powershell
# Start service
Start-Service postgresql-x64-18

# Cek status
Get-Service postgresql-x64-18 | Select-Object Name, DisplayName, Status
```

Atau melalui Command Prompt Admin:
```cmd
net start postgresql-x64-18
```

### 2. Pastikan File Konfigurasi Ada
File `config.local.php` harus ada di root project. File ini **tidak pernah ter-commit ke git** (sudah ada di .gitignore).

```php
// C:\laragon\www\cvamirulputrajusticia\config.local.php
return [
    'host' => 'localhost',
    'db' => 'cv_db', 
    'user' => 'postgres',
    'pass' => 'postgres123',  // Password PostgreSQL Anda
    'port' => '5432',
];
```

*Jika file belum ada, buat file baru dengan konten di atas.*

### 3. Akses via Browser
Buka browser dan kunjungi:

```
http://localhost/cvamirulputrajusticia/preview_cv_japan.php
```

Atau halaman utama:

```
http://localhost/cvamirulputrajusticia/
```

### 4. Available Pages
- `preview_cv_japan.php` - Resume format Jepang (sudah dioptimalkan)
- `preview_cv.php` - Halaman utama/menu
- `preview_cv_en_sg.php`, `preview_cv_en_au.php` - Variasi English
- `index.php` - Aplikasi utama

## 🗄️ Database Verifikasi

Pastikan data sudah terexecute dengan benar:

**Masuk ke Beekeeper Studio:**
- Host: `127.0.0.1` atau `localhost`
- Port: `5432`
- Username: `postgres`
- Password: `postgres123`
- Database: `cv_db`

**Cek tabel di database:**
```sql
SELECT table_name, (SELECT COUNT(*) FROM information_schema.tables t2 WHERE t2.table_name = t.table_name AND t2.table_schema = 'public') as count
FROM information_schema.tables t
WHERE table_schema = 'public'
ORDER BY table_name;
```

Expected result:
- `languages`: 3 rows
- `portfolio`: 9 rows  
- `profile`: 1 row
- `roles`: 2 rows
- `skills`: 15 rows
- `work_experience`: 8 rows

## 📁 Struktur File Penting

```
C:\laragon\www\cvamirulputrajusticia\
├── config.php          # Konfigurasi default (tidak diedit)
├── config.local.php    # ⚠️ Konfigurasi lokal (diabaikan git)
├── index.php           # Halaman utama
├── preview_cv_japan.php # Resume Jepang (sudah dibuat)
├── schema_cv_db.sql    # Schema database
├── uploads/            # Folder foto/profile
├── includes/           # Include files
├── vendor/             # Dependencies
└── *.php               # Halaman lain
```

## 🔄 Troubleshooting Umum

### Masalah: "Connection failed: fe_sendauth: no password supplied"
**Solusi:** Pastikan `config.local.php` ada dan berisi password `postgres123`. Service PostgreSQL juga harus running.

### Masalah: Halaman putih/error 500
**Solusi:**
1. Pastikan PostgreSQL service running: `Start-Service postgresql-x64-18`
2. Cek `php_errors.log` di folder `C:\laragon\www\cvamirulputrajusticia\`
3. Pastikan `config.local.php` bisa dibaca PHP

### Masalah: Tombol "PDF tidak muncul" atau error print
**Solusi:** Pastikan browser mendukung dan PostgreSQL koneksi OK. Coba refresh halaman.

### Masih koneksi gagal?
Jalankan test manual:
```php
<?php
$local = include 'config.local.php';
$c = pg_connect("host=$local['host'] port=$local['port'] dbname=$local['db'] user=$local['user'] password=$local['pass']");
if ($c) { echo "OK - Koneksi Berhasil"; pg_close($c); }
else { echo "GAGAL: " . pg_last_error(); }
```
```

## 📦 Fitur Utama

- ✅ **Optimized for ATS** - Gunakan section heading standar, text bukan graphics, keyword dari job description
- ✅ **Optimized for Humans** - Achievement-focused, concrete descriptions, specific context
- ✅ **Japanese Resume (Rirekisho)** - Format lengkap dengan Japanese ability concrete description
- ✅ **English Resume** - Terstruktur sesuai nasihat TokyoDev
- ✅ **Skills Management** - 15 skills dengan level
- ✅ **Work Experience** - 8 entries dari 2018-Present
- ✅ **Portfolio** - 9 project entries
- ✅ **Multiple Language Support** - Indonesia, English (SG, AU), Jepang, Prancis, Korea

## 🎯 Next Steps

1. **Test resume Japan:** Buka `preview_cv_japan.php`
2. **Generate English resume:** Copy struktur dari `preview_cv_japan.php` untuk English version
3. **Add personal projects:** Gunakan tabel `portfolio` untuk menambah project
4. **Customize for jobs:** Sesuaikan keyword sesuai job description yang Anda target

---
*Dibuat berdasarkan nasihat dari TokyoDev community about optimizing resumes for ATS and human readers.*