#!/usr/bin/env bash
# Runner dinamis untuk CV App
# - Port default 8000, otomatis naik ke port kosong jika 8000 dipakai
# - Ganti port manual:  PORT=9000 ./run.sh
set -e

DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PORT="${PORT:-8000}"

# cek apakah port sudah dipakai (bash built-in /dev/tcp)
is_busy() {
    (echo > /dev/tcp/127.0.0.1/"$1") >/dev/null 2>&1
}

while is_busy "$PORT"; do
    echo "Port $PORT sedang dipakai, mencoba port berikutnya..."
    PORT=$((PORT + 1))
done

echo "Menjalankan CV App -> http://localhost:$PORT"
echo "Log: server.log  (Ctrl+C untuk stop)"
exec php -S 127.0.0.1:"$PORT" -t "$DIR" >> "$DIR/server.log" 2>&1
