#!/bin/bash
# Bootstrap script - runs mysqld in bootstrap mode to initialize the database

MYSQL_DATA=/home/runner/workspace/mysql_data
BOOTSTRAP_SQL=/home/runner/workspace/mysql_bootstrap.sql

echo "[bootstrap] Cleaning up old data..."
rm -rf "$MYSQL_DATA"
mkdir -p "$MYSQL_DATA"

echo "[bootstrap] Running mysqld bootstrap..."
mysqld --no-defaults \
    --datadir="$MYSQL_DATA" \
    --skip-networking \
    --bootstrap < "$BOOTSTRAP_SQL" 2>&1
EXIT=$?

echo "[bootstrap] Done with exit code: $EXIT"
ls "$MYSQL_DATA/mysql/" 2>/dev/null | wc -l
echo "[bootstrap] Tables created"
