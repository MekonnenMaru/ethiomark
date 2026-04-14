<?php
// header('Content-Type: application/json');

// $secret = "ETHIO_SECRET_2026_SECURE";
// /* HMAC secret — same constant must be in keygen.html */
// $_LS = 'EMbingo!X9pQ#2025-EthioMark-Lic3ns3-S3cr3t@Key';
// /* Separate secret for unlock codes */
// $_ULS = 'EMbingo!UNLOCK@2025-EthioMark-Unl0ck-S3cr3t';

// // ---------- CLEAN ----------
// function clean($str) {
//     $str = trim(preg_replace('/\s+/', '', $str));
//     return preg_replace('/^(ProcessorId|SerialNumber)+/i', '', $str);
// }

// $cpu  = clean(shell_exec("wmic cpu get ProcessorId"));
// $bios = clean(shell_exec("wmic bios get SerialNumber"));

// $static_machine_id = hash('sha256', $cpu . $bios . $secret);

// // ---------- FILE ----------
// $file = __DIR__ . "license.lock";

// // ---------- ACTION ----------
// $action = $_GET['action'] ?? 'get';

// // ---------- SAVE CHECKPOINT ----------
// if ($action === "saveCheckpoint") {
//     $input = json_decode(file_get_contents("php://input"), true);

//     file_put_contents($file, json_encode([
//         "static_machine_id" => $static_machine_id,
//         "total_deposited" => $input["total_deposited"],
//         "total_revenue" => $input["total_revenue"],
//     ]));

//     echo json_encode(["status" => "saved"]);
//     exit;
// }

// // ---------- LOAD CHECKPOINT ----------
// if ($action === "load_checkpoint") {

//     if (!file_exists($file) || filesize($file) === 0) {
//         echo json_encode(null); // always return valid JSON
//         exit;
//     }

//     $content = file_get_contents($file);

//     // extra safety: if somehow still empty
//     if (!$content) {
//         echo json_encode(null);
//     } else {
//         echo $content;
//     }

//     exit;
// }

// // ---------- DEFAULT ----------
// //getStaticMachineId() is called from wallet.js to get the machine id for live checking
// echo json_encode([
//     "static_machine_id" => $static_machine_id,
//     "secrets" => [
//         "ls" => $_LS,
//         "uls" => $_ULS
//     ]
// ], JSON_PRETTY_PRINT);






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

/* ================= DEFAULT ================= */

echo json_encode([
    "static_machine_id" => $static_machine_id
], JSON_PRETTY_PRINT);