<?php

header('Content-Type: application/json');

/* ================= CONFIG ================= */
$secret = "ETHIO_SECRET_2026_SECURE";

/* internal secrets (NOT exposed) */
$_LS  = 'EMbingo!X9pQ#2025-EthioMark-Lic3ns3-S3cr3t@Key';
$_ULS = 'EMbingo!UNLOCK@2025-EthioMark-Unl0ck-S3cr3t';

/* ================= HELPERS ================= */

// Clean hardware values
function clean($str) {
    return trim(preg_replace('/[^A-Za-z0-9]/', '', $str));
}

// Machine ID
function getMachineId($secret) {
    $cpu  = clean(shell_exec("wmic cpu get ProcessorId"));
    $bios = clean(shell_exec("wmic bios get SerialNumber"));
    return hash('sha256', $cpu . $bios . $secret);
}

// Encrypt
function encryptData($data, $key) {
    $iv = random_bytes(16);

    $encrypted = openssl_encrypt(
        json_encode($data),
        'AES-256-CBC',
        $key,
        0,
        $iv
    );

    return base64_encode($iv . $encrypted);
}

// Decrypt
function decryptData($data, $key) {
    $data = base64_decode($data);
    if (!$data) return null;

    $iv = substr($data, 0, 16);
    $encrypted = substr($data, 16);

    $json = openssl_decrypt(
        $encrypted,
        'AES-256-CBC',
        $key,
        0,
        $iv
    );

    $decoded = json_decode($json, true);
    return is_array($decoded) ? $decoded : null;
}

// Storage path (system-like)
function getStorageFile($static_machine_id, $secret) {

    $base = getenv('APPDATA');

    $machine = substr(hash('sha256', $static_machine_id), 0, 8);

    $dir = $base . "\\Microsoft\\Windows\\WinPadVersionController\\Cache\\" . $machine;

    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    return $dir . "\\syscache.dat";
    // $file = __DIR__ . "syscache.dat";
    // return $file;
}

/* ================= INIT ================= */

$static_machine_id = getMachineId($secret);
$serial_number = clean(shell_exec("wmic bios get SerialNumber"));
$file = getStorageFile($static_machine_id, $secret);

$action = $_GET['action'] ?? 'get';


if ($action === "saveCheckpoint") {

    $input = json_decode(file_get_contents("php://input"), true);

    $payload = [
        "static_machine_id" => $static_machine_id,
        "total_deposited" => $input["total_deposited"] ?? 0,
        "total_revenue" => $input["total_revenue"] ?? 0,
        "time" => time()
    ];

    // FIXED: proper HMAC (exclude sig)
    $temp = $payload;
    $payload["sig"] = hash_hmac('sha256', json_encode($temp), $secret);

    $encrypted = encryptData($payload, $secret);

    file_put_contents($file, $encrypted);

    echo json_encode(["status" => "saved"]);
    exit;
}

/* ================= LOAD ================= */

if ($action === "load_checkpoint") {

    if (!file_exists($file) || filesize($file) === 0) {
        echo json_encode(null);
        exit;
    }

    $content = file_get_contents($file);
    $data = decryptData($content, $secret);

    if (!$data) {
        echo json_encode(null);
        exit;
    }

    // Machine validation
    if (($data["static_machine_id"] ?? '') !== $static_machine_id) {
        echo json_encode(["error" => "INVALID_MACHINE"]);
        exit;
    }

    // Tamper check
    $sig = $data["sig"] ?? '';
    $temp = $data;
    unset($temp["sig"]);

    $checkSig = hash_hmac('sha256', json_encode($temp), $secret);

    if (!hash_equals($sig, $checkSig)) {
        echo json_encode(["error" => "TAMPER_DETECTED"]);
        exit;
    }

    echo json_encode($data);
    exit;
}



// ==================================================================================================
// ==================================================================================================
// ==================================================================================================
if ($action === 'hmac') {

    $input = json_decode(file_get_contents("php://input"), true);
    $data  = $input['data'] ?? '';

    $hash = hash_hmac("sha256", $data, $_LS);

    echo json_encode([
        "hmac" => $hash
    ]);
    exit;
}

/* ── verify_key: check key signature against last 7 days (UTC) — one request ── */
if ($action === 'verify_key') {

    $input  = json_decode(file_get_contents("php://input"), true);
    $mid    = strtoupper($input['mid']    ?? '');
    $sn     = $input['sn']    ?? '';
    $amt    = $input['amt']   ?? '';
    $sigIn  = strtoupper($input['sigIn'] ?? '');

    for ($i = 0; $i <= 7; $i++) {
        $dateStr  = gmdate('Ymd', time() - 86400 * $i);
        $payload  = $mid . '|' . $sn . '|' . $amt . '|' . $dateStr;
        $expected = strtoupper(substr(hash_hmac('sha256', $payload, $_LS), 0, 16));
        if ($expected === $sigIn) {
            echo json_encode(['valid' => true, 'daysOld' => $i]);
            exit;
        }
    }

    echo json_encode(['valid' => false]);
    exit;
}

if ($action === 'unlock_hmac') {

    $input = json_decode(file_get_contents("php://input"), true);
    $data  = $input['data'] ?? '';

    $hash = hash_hmac("sha256", $data, $_ULS);

    echo json_encode([
        "hmac" => $hash
    ]);
    exit;
}

if ($action === 'get_dynamic_machin_id') {

    
    exit;
}


/* ================= DEFAULT ================= */

echo json_encode([
    "static_machine_id" => $static_machine_id,
    "serial_number" => $serial_number
], JSON_PRETTY_PRINT);