#!/bin/bash

PORT=5000

echo "[start] Ethiomark Bingo — client-side app"
echo "[start] Serving static files on port $PORT..."

exec php -S 0.0.0.0:$PORT -t /home/runner/workspace
