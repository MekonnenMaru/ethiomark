#!/bin/bash

MYSQL_DATA=/home/runner/workspace/mysql_data
MYSQL_SOCK=/tmp/mysql.sock
MYSQL_PORT=3306
BOOTSTRAP_SQL=/home/runner/workspace/mysql_bootstrap.sql
PHP_PORT=5000

echo "[start] Ethiomark Bingo startup..."

# Remove stale socket and pid files
rm -f "$MYSQL_SOCK"
find "$MYSQL_DATA" -name "*.pid" -delete 2>/dev/null

# Check if full bootstrap is needed (check for enough mysql tables)
MYSQL_TABLE_COUNT=$(ls "$MYSQL_DATA/mysql/" 2>/dev/null | wc -l)
if [ "$MYSQL_TABLE_COUNT" -lt "20" ]; then
    echo "[setup] MariaDB not initialized (found $MYSQL_TABLE_COUNT files), running bootstrap..."
    rm -rf "$MYSQL_DATA"
    mkdir -p "$MYSQL_DATA"
    mysqld --no-defaults \
        --datadir="$MYSQL_DATA" \
        --skip-networking \
        --bootstrap < "$BOOTSTRAP_SQL" 2>&1
    echo "[setup] Bootstrap complete. Tables: $(ls $MYSQL_DATA/mysql 2>/dev/null | wc -l)"
fi

# Start mysqld in background (note: no --skip-name-resolve to allow localhost root access)
echo "[start] Starting MariaDB server..."
mysqld --no-defaults \
    --datadir="$MYSQL_DATA" \
    --socket="$MYSQL_SOCK" \
    --port=$MYSQL_PORT \
    --bind-address=127.0.0.1 \
    --user=runner \
    --log-error=/tmp/mysql_error.log &

MYSQL_PID=$!
echo "[start] MariaDB started with PID $MYSQL_PID"

# Wait for MySQL socket and connection
echo "[start] Waiting for MariaDB to be ready..."
READY=false
for i in $(seq 1 30); do
    if [ -S "$MYSQL_SOCK" ]; then
        # Try connecting as root with no password via socket
        if mysql --socket="$MYSQL_SOCK" -u root --skip-password -e "SELECT 1;" > /dev/null 2>&1; then
            READY=true
            echo "[start] MariaDB is ready!"
            break
        fi
    fi
    sleep 1
    echo "[start] Waiting... ($i/30)"
done

if [ "$READY" = "false" ]; then
    echo "[start] MariaDB failed to start. Error log:"
    tail -20 /tmp/mysql_error.log 2>/dev/null
    echo "[start] Starting PHP server anyway (DB features won't work)..."
else
    # Setup root password and database
    echo "[start] Setting up database credentials and schema..."
    mysql --socket="$MYSQL_SOCK" -u root --skip-password -e "
        ALTER USER 'root'@'localhost' IDENTIFIED BY 'iseeddmmaallkkjjhhaa1212';
        CREATE DATABASE IF NOT EXISTS bingo_original CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
        GRANT ALL PRIVILEGES ON bingo_original.* TO 'root'@'localhost';
        FLUSH PRIVILEGES;
    " 2>&1

    # Check and import SQL dump
    TABLE_COUNT=$(mysql --socket="$MYSQL_SOCK" -u root -piseeddmmaallkkjjhhaa1212 -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='bingo_original';" -s -N 2>/dev/null || echo "0")
    echo "[start] Tables in bingo_original: $TABLE_COUNT"
    
    if [ "$TABLE_COUNT" -lt "5" ]; then
        echo "[start] Importing database from SQL dump..."
        DUMP_FILE="/home/runner/workspace/attached_assets/Pasted--phpMyAdmin-SQL-Dump-version-5-2-1-https-www-phpmyadmin_1775417665994.txt"
        if [ -f "$DUMP_FILE" ]; then
            grep -v "^--\|^/\*\|^CREATE DATABASE\|^DROP DATABASE\|^USE \`bingo\`" "$DUMP_FILE" | \
                sed 's/USE `bingo`;/USE `bingo_original`;/g' | \
                mysql --socket="$MYSQL_SOCK" -u root -piseeddmmaallkkjjhhaa1212 bingo_original 2>&1
            echo "[start] Database import complete"
        fi
    fi
fi

# Start PHP built-in server in foreground
echo "[start] Starting PHP server on port $PHP_PORT..."
exec php -S 0.0.0.0:$PHP_PORT -t /home/runner/workspace
