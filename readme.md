# CV Amirul Putra Justicia

Aplikasi web CV + surat lamaran (PHP native, PostgreSQL).

## Menjalankan Aplikasi

> **Penting:** database PostgreSQL berada di Windows (localhost:5432), sedangkan
> kode aplikasi di WSL. Dari dalam WSL, port 5432 Windows diblokir firewall.
> Gunakan salah satu cara di bawah.

### Opsi 1 (disarankan) — Jalankan dari Windows

Double-click `run_windows.bat` di Explorer (folder `\\wsl.localhost\Ubuntu\home\amirulputraj\cvamirulputrajusticia`),
atau dari WSL:

```bash
powershell.exe -NoProfile -Command "Start-Process -FilePath '\\\\wsl.localhost\\Ubuntu\\home\\amirulputraj\\cvamirulputrajusticia\\run_windows.bat' -WorkingDirectory 'C:\Users'"
```

Server PHP Laragon (Windows) akan serve folder WSL dan terhubung ke PostgreSQL Windows.
Buka `http://localhost:8000` di browser (otomatis naik port jika 8000 dipakai).

### Opsi 2 — Native di WSL (perlu buka firewall sekali)

Jalankan sekali di PowerShell **sebagai Administrator**:

```powershell
netsh advfirewall firewall add rule name="PostgreSQL 5432 WSL" dir=in action=allow protocol=TCP localport=5432
```

Lalu seperti biasa:

```bash
./run.sh          # port otomatis 8000 (naik ke port kosong berikutnya)
PORT=9000 ./run.sh
```

`config.local.php` sudah auto-detect IP host Windows (via `/etc/resolv.conf`) saat dijalankan di WSL.

## Koneksi Beekeeper Studio

Gunakan kredensial berikut untuk mengakses database `cv_db`:

| Field    | Value                     |
|----------|---------------------------|
| Host     | `localhost`               |
| Port     | `5432`                    |
| User     | `postgres`                |
| Password | `<password>` — cek di `config.local.php` (bukan di-commit ke git) |
| Database | `cv_db`                   |

> Catatan: username harus `postgres` (bukan `potgres`), dan port `5432` (bukan `5433`).

## Kredensial Aplikasi

- **Admin login**: `admin` / `admin123`
- **DB config**: kredensial database disimpan di `config.local.php` (tidak di-commit ke git). Password postgres di atas hanya berlaku untuk mesin lokal ini.
