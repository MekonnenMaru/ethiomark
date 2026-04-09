<?php
/* ═══════════════════════════════════════════════════════════════════
   api.php  —  Ethiomark Bingo  |  PHP / MySQL backend
   ───────────────────────────────────────────────────────────────────
   Place this file in the same folder as index.html inside your XAMPP
   ethiomark directory (e.g. C:\xampp\htdocs\ethiomark\api.php).

   SETUP:
     1. Start Apache + MySQL in XAMPP Control Panel
     2. Open http://localhost/ethiomark/index.html
        api.php auto-creates the database and all tables on first run.
     3. Load index.html with <script src="api_php.js"> instead of api.js
        (or keep api.js for browser-only IndexedDB mode)

   DATABASE CONFIG — edit the four constants below if needed:
   ═══════════════════════════════════════════════════════════════════ */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');   /* phpMyAdmin default */
define('DB_PASS', '');        /* phpMyAdmin default — empty */
define('DB_NAME', 'ethiomark_bingo');

/* ── Response headers ───────────────────────────────────────────── */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

/* ── Connect to MySQL (create DB automatically) ─────────────────── */
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    $pdo->exec(
        'CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '`
         CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
    );
    $pdo->exec('USE `' . DB_NAME . '`');
} catch (PDOException $e) {
    echo json_encode(['error' => 'DB connection failed: ' . $e->getMessage()]);
    exit;
}

/* ── Route ──────────────────────────────────────────────────────── */
$action = $_GET['action'] ?? '';
$body   = json_decode(file_get_contents('php://input'), true) ?? [];

switch ($action) {
    case 'init':                     handleInit();                      break;
    case 'getGameState':             handleGetGameState();              break;
    case 'saveGameState':            handleSaveGameState($body);        break;
    case 'clearGameState':           handleClearGameState();            break;
    case 'getSettings':              handleGetSettings();               break;
    case 'saveSettings':             handleSaveSettings($body);         break;
    case 'getHistory':               handleGetHistory();                break;
    case 'addHistory':               handleAddHistory($body);           break;
    case 'seedCashiers':             handleSeedCashiers($body);         break;
    case 'getCashier':               handleGetCashier($body);           break;
    case 'verifyCredentials':        handleVerifyCredentials($body);    break;
    case 'getMachineId':             handleGetMachineId();              break;
    case 'getLicenseInfo':           handleGetLicenseInfo();            break;
    case 'getBalance':               handleGetBalance();                break;
    case 'activatePackage':          handleActivatePackage($body);      break;
    case 'addRevenue':               handleAddRevenue($body);           break;
    case 'checkAndResetDailyRound':  handleCheckAndReset();            break;
    case 'stampActiveDate':          handleStampActiveDate();           break;
    default:
        echo json_encode(['error' => 'Unknown action: ' . htmlspecialchars($action)]);
}
exit;

/* ════════════════════════════════════════════════════════════════════
   INIT — auto-create all tables
════════════════════════════════════════════════════════════════════ */

function handleInit()
{
    global $pdo;

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `em_game_state` (
            `id`         INT PRIMARY KEY DEFAULT 1,
            `state_json` LONGTEXT,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                         ON UPDATE CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `em_app_settings` (
            `id`            INT PRIMARY KEY DEFAULT 1,
            `settings_json` LONGTEXT,
            `updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                            ON UPDATE CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `em_game_history` (
            `id`           INT AUTO_INCREMENT PRIMARY KEY,
            `cashier_id`   VARCHAR(64),
            `round`        INT           DEFAULT 1,
            `date`         VARCHAR(50),
            `cards`        TEXT,
            `price`        DECIMAL(10,2) DEFAULT 0,
            `pattern`      INT           DEFAULT 1,
            `sound`        INT           DEFAULT 1,
            `randomstring` VARCHAR(255)  DEFAULT '',
            `status`       VARCHAR(20)   DEFAULT 'played',
            `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `em_cashiers` (
            `id`            VARCHAR(64)  NOT NULL PRIMARY KEY,
            `password_hash` VARCHAR(255) NOT NULL,
            `settings_json` LONGTEXT,
            `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `em_license` (
            `key_name`   VARCHAR(100) NOT NULL PRIMARY KEY,
            `value`      LONGTEXT,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                         ON UPDATE CURRENT_TIMESTAMP
        )
    ");

    echo json_encode(['ok' => true, 'db' => DB_NAME]);
}

/* ════════════════════════════════════════════════════════════════════
   GAME STATE
════════════════════════════════════════════════════════════════════ */

function handleGetGameState()
{
    global $pdo;
    $row = $pdo->query('SELECT state_json FROM em_game_state WHERE id=1')->fetch();
    echo $row ? ($row['state_json'] ?: 'null') : 'null';
}

function handleSaveGameState($body)
{
    global $pdo;
    $json = json_encode($body['state'] ?? null, JSON_UNESCAPED_UNICODE);
    $stmt = $pdo->prepare(
        'INSERT INTO em_game_state (id, state_json) VALUES (1, ?)
         ON DUPLICATE KEY UPDATE state_json = ?'
    );
    $stmt->execute([$json, $json]);
    echo json_encode(['ok' => true]);
}

function handleClearGameState()
{
    global $pdo;
    $pdo->exec('DELETE FROM em_game_state WHERE id = 1');
    echo json_encode(['ok' => true]);
}

/* ════════════════════════════════════════════════════════════════════
   APP SETTINGS
════════════════════════════════════════════════════════════════════ */

function handleGetSettings()
{
    global $pdo;
    $row = $pdo->query('SELECT settings_json FROM em_app_settings WHERE id=1')->fetch();
    echo $row ? ($row['settings_json'] ?: 'null') : 'null';
}

function handleSaveSettings($body)
{
    global $pdo;
    $json = json_encode($body['settings'] ?? null, JSON_UNESCAPED_UNICODE);
    $stmt = $pdo->prepare(
        'INSERT INTO em_app_settings (id, settings_json) VALUES (1, ?)
         ON DUPLICATE KEY UPDATE settings_json = ?'
    );
    $stmt->execute([$json, $json]);
    echo json_encode(['ok' => true]);
}

/* ════════════════════════════════════════════════════════════════════
   GAME HISTORY
════════════════════════════════════════════════════════════════════ */

function handleGetHistory()
{
    global $pdo;
    $rows = $pdo->query(
        'SELECT * FROM em_game_history ORDER BY created_at DESC'
    )->fetchAll();

    foreach ($rows as &$r) {
        $r['cards'] = json_decode($r['cards'] ?? '[]', true);
        $r['price'] = (float)$r['price'];
        $r['round'] = (int)$r['round'];
    }
    echo json_encode($rows);
}

function handleAddHistory($body)
{
    global $pdo;
    $e    = $body['entry'] ?? [];
    $stmt = $pdo->prepare('
        INSERT INTO em_game_history
            (cashier_id, round, date, cards, price, pattern, sound, randomstring, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([
        $e['cashier_id']   ?? '',
        (int)($e['round']  ?? 1),
        $e['date']         ?? date('c'),
        json_encode($e['cards'] ?? []),
        (float)($e['price']     ?? 0),
        (int)($e['pattern']     ?? 1),
        (int)($e['sound']       ?? 1),
        $e['randomstring'] ?? '',
        $e['status']       ?? 'played',
    ]);
    echo json_encode(['id' => (int)$pdo->lastInsertId()]);
}

/* ════════════════════════════════════════════════════════════════════
   CASHIERS / AUTHENTICATION
════════════════════════════════════════════════════════════════════ */

function handleSeedCashiers($body)
{
    global $pdo;
    $list = $body['list'] ?? [];
    $stmt = $pdo->prepare(
        'INSERT IGNORE INTO em_cashiers (id, password_hash, settings_json)
         VALUES (?, ?, ?)'
    );
    foreach ($list as $c) {
        $stmt->execute([
            $c['id'],
            $c['password_hash'],
            $c['settings_json'] ?? null,
        ]);
    }
    echo json_encode(['ok' => true]);
}

function handleGetCashier($body)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM em_cashiers WHERE id = ?');
    $stmt->execute([$body['id'] ?? '']);
    echo json_encode($stmt->fetch() ?: null);
}

function handleVerifyCredentials($body)
{
    global $pdo;
    $stmt = $pdo->prepare(
        'SELECT * FROM em_cashiers WHERE id = ? AND password_hash = ?'
    );
    $stmt->execute([$body['id'] ?? '', $body['password_hash'] ?? '']);
    echo json_encode($stmt->fetch() ?: null);
}

/* ════════════════════════════════════════════════════════════════════
   LICENSE / PACKAGE SYSTEM
════════════════════════════════════════════════════════════════════ */

function licGet($key)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT value FROM em_license WHERE key_name = ?');
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    return $row ? $row['value'] : null;
}

function licSet($key, $val)
{
    global $pdo;
    $stmt = $pdo->prepare(
        'INSERT INTO em_license (key_name, value) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE value = ?'
    );
    $stmt->execute([$key, $val, $val]);
}

function getLicData()
{
    $json = licGet('license_data');
    if (!$json) {
        return ['machine_id' => null, 'packages' => [], 'total_deposited' => 0, 'total_revenue' => 0];
    }
    return json_decode($json, true);
}

function saveLicData($data)
{
    licSet('license_data', json_encode($data));
}

function handleGetMachineId()
{
    $data = getLicData();
    if (empty($data['machine_id'])) {
        /* Generate a stable 8-char machine ID on first run */
        $raw  = uniqid('em', true) . random_bytes(8);
        $mid  = strtoupper(substr(sha1($raw), 0, 8));
        $data['machine_id'] = $mid;
        saveLicData($data);
    }
    echo json_encode(['machine_id' => $data['machine_id']]);
}

function handleGetLicenseInfo()
{
    echo json_encode(getLicData());
}

function handleGetBalance()
{
    $d = getLicData();
    $deposited = (float)($d['total_deposited'] ?? 0);
    $used      = (float)($d['total_revenue']   ?? 0);
    echo json_encode(['deposited' => $deposited, 'used' => $used, 'available' => $deposited - $used]);
}

function handleActivatePackage($body)
{
    $mid = $body['mid'] ?? '';
    $sn  = $body['sn']  ?? '';
    $amt = (float)($body['amt'] ?? 0);

    $data = getLicData();

    /* Duplicate serial check */
    foreach ($data['packages'] as $p) {
        if ($p['sn'] === $sn) {
            echo json_encode(['error' => 'Serial ' . $sn . ' already activated on this machine']);
            return;
        }
    }

    $data['packages'][]      = ['sn' => $sn, 'amount' => $amt, 'activated_at' => date('c')];
    $data['total_deposited'] = ((float)($data['total_deposited'] ?? 0)) + $amt;
    saveLicData($data);

    echo json_encode([
        'amount'      => $amt,
        'new_balance' => $data['total_deposited'] - ((float)($data['total_revenue'] ?? 0)),
    ]);
}

function handleAddRevenue($body)
{
    $amount = (float)($body['amount'] ?? 0);
    $data   = getLicData();
    $data['total_revenue'] = ((float)($data['total_revenue'] ?? 0)) + $amount;
    saveLicData($data);
    echo json_encode(['ok' => true]);
}

/* ════════════════════════════════════════════════════════════════════
   DAILY ROUND RESET
════════════════════════════════════════════════════════════════════ */

function handleCheckAndReset()
{
    global $pdo;
    $today = date('Y-m-d');
    $row   = $pdo->query('SELECT state_json FROM em_game_state WHERE id=1')->fetch();
    $gs    = $row ? json_decode($row['state_json'] ?? 'null', true) : null;
    $last  = $gs['last_active_date'] ?? null;

    if ($last && $last !== $today) {
        /* New day — reset round to 1, wipe current game */
        $newGs = [
            'round'            => 1,
            'cards'            => [],
            'pattern'          => 1,
            'price'            => 0,
            'sound'            => $gs['sound']       ?? 1,
            'speed_range'      => $gs['speed_range'] ?? 3,
            'randomstring'     => '',
            'last_active_date' => $today,
        ];
        $json = json_encode($newGs);
        $stmt = $pdo->prepare(
            'INSERT INTO em_game_state (id, state_json) VALUES (1, ?)
             ON DUPLICATE KEY UPDATE state_json = ?'
        );
        $stmt->execute([$json, $json]);
        echo json_encode(['reset' => true]);
        return;
    }

    if (!$last) {
        /* First boot — stamp today */
        $gs = array_merge($gs ?? [], ['last_active_date' => $today]);
        $json = json_encode($gs);
        $stmt = $pdo->prepare(
            'INSERT INTO em_game_state (id, state_json) VALUES (1, ?)
             ON DUPLICATE KEY UPDATE state_json = ?'
        );
        $stmt->execute([$json, $json]);
    }
    echo json_encode(['reset' => false]);
}

function handleStampActiveDate()
{
    global $pdo;
    $today = date('Y-m-d');
    $row   = $pdo->query('SELECT state_json FROM em_game_state WHERE id=1')->fetch();
    $gs    = $row ? json_decode($row['state_json'] ?? '{}', true) : [];

    if (($gs['last_active_date'] ?? null) !== $today) {
        $gs['last_active_date'] = $today;
        $json = json_encode($gs, JSON_UNESCAPED_UNICODE);
        $stmt = $pdo->prepare(
            'INSERT INTO em_game_state (id, state_json) VALUES (1, ?)
             ON DUPLICATE KEY UPDATE state_json = ?'
        );
        $stmt->execute([$json, $json]);
    }
    echo json_encode(['ok' => true]);
}
