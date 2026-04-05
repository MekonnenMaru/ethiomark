# Ethiomark Bingo

A web-based Bingo management and gaming system tailored for the Ethiopian market, featuring Amharic UI text.

## Architecture

- **Backend**: PHP 8.2 (native/procedural with OOP database handling)
- **Database**: MariaDB 10.11 (MySQL-compatible)
- **Frontend**: HTML5/CSS3 with Bootstrap, jQuery, AJAX polling for real-time updates
- **PWA**: Includes manifest.json and service-worker.js

## Key Files

- `index.php` — Main login/entry page
- `config/Database.php` — MySQL singleton connection (uses `127.0.0.1` for TCP connection)
- `config/DbFunction.php` — All database operations and business logic (~2200 lines)
- `cashier/` — Core cashier game management interface
- `supportive-cashier/` — Support cashier interface
- `bootstrap/` — Local CSS/JS assets (Bootstrap, jQuery, etc.)
- `start.sh` — Startup script (initializes DB + starts MariaDB + PHP server)
- `mysql_bootstrap.sql` — MariaDB initialization SQL (generated from MariaDB share files)
- `mysql_data/` — MariaDB data directory (auto-initialized on first run)
- `attached_assets/` — SQL dump of the original database

## Startup Process

The `start.sh` script:
1. Checks if MariaDB data directory is initialized (looks for 20+ files in mysql subdir)
2. If not, runs `mysqld --bootstrap` with the bootstrap SQL file
3. Starts `mysqld` in background on port 3306 (127.0.0.1)
4. Waits for MariaDB to be ready
5. Sets up `root` user with password and creates `bingo_original` database
6. Imports the SQL dump if the database is empty
7. Starts PHP built-in server on `0.0.0.0:5000`

## Database

- **Database name**: `bingo_original`
- **Host**: `127.0.0.1` (TCP, not Unix socket)
- **Port**: 3306
- **User**: `root`
- **Password**: `iseeddmmaallkkjjhhaa1212`
- **Socket**: `/tmp/mysql.sock`

## Workflow

The "Start application" workflow runs `bash start.sh` and serves on port 5000.

## Deployment

Configured as VM deployment (needs persistent MariaDB process).
Run command: `bash /home/runner/workspace/start.sh`

## Notes

- The `run_parent` Replit sandbox prevents running `mysqld` as a child process from the agent bash tool - only works in the workflow context
- The PHP built-in server is used for simplicity; no Apache/Nginx configured
- Hardware binding check in original code has been left as-is (uses `wmic bios` which won't work on Linux)
- TimeAPI.io sync is active; clock warning shown is cosmetic
