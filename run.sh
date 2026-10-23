#!/usr/bin/env bash
# Runner dinamis untuk CV App
# - Port default 8000, otomatis naik ke port kosong jika 8000 dipakai
# - Ganti port manual:  PORT=9000 ./run.sh
set -e

DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PORT="${PORT:-8093}"

echo "Menjalankan CV App -> http://localhost:$PORT (port statis 8093)"
echo "Log: server.log  (Ctrl+C untuk stop)"
exec php -S 127.0.0.1:"$PORT" -t "$DIR" >> "$DIR/server.log" 2>&1
