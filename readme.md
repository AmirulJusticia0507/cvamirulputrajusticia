# CV Amirul Putra Justicia

Aplikasi web CV + surat lamaran (PHP native, PostgreSQL).

## Menjalankan Aplikasi

```bash
# Cara cepat (port otomatis: 8000, atau naik ke port kosong berikutnya)
./run.sh

# Atau tentukan port sendiri
PORT=9000 ./run.sh

# Server bawaan PHP langsung
php -S localhost:8000
```

Buka `http://localhost:PORT` di browser.

## Koneksi Beekeeper Studio

Gunakan kredensial berikut untuk mengakses database `cv_db`:

| Field    | Value                     |
|----------|---------------------------|
| Host     | `localhost`               |
| Port     | `5432`                    |
| User     | `postgres`                |
| Password | `cv_8da230eb0d88bec8`     |
| Database | `cv_db`                   |

> Catatan: username harus `postgres` (bukan `potgres`), dan port `5432` (bukan `5433`).

## Kredensial Aplikasi

- **Admin login**: `admin` / `admin123`
- **DB config**: kredensial database disimpan di `config.local.php` (tidak di-commit ke git). Password postgres di atas hanya berlaku untuk mesin lokal ini.
