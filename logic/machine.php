<?php
header('Content-Type: application/json');

$secret = "ETHIO_SECRET_2026_SECURE";
/* HMAC secret — same constant must be in keygen.html */
$_LS = 'EMbingo!X9pQ#2025-EthioMark-Lic3ns3-S3cr3t@Key';
/* Separate secret for unlock codes */
$_ULS = 'EMbingo!UNLOCK@2025-EthioMark-Unl0ck-S3cr3t';

// ---------- CLEAN ----------
function clean($str) {
    $str = trim(preg_replace('/\s+/', '', $str));
    return preg_replace('/^(ProcessorId|SerialNumber)+/i', '', $str);
}

$cpu  = clean(shell_exec("wmic cpu get ProcessorId"));
$bios = clean(shell_exec("wmic bios get SerialNumber"));

$static_machine_id = hash('sha256', $cpu . $bios . $secret);

// ---------- FILE ----------
$file = __DIR__ . "license.lock";

// ---------- ACTION ----------
$action = $_GET['action'] ?? 'get';

// ---------- SAVE CHECKPOINT ----------
if ($action === "saveCheckpoint") {
    $input = json_decode(file_get_contents("php://input"), true);

    file_put_contents($file, json_encode([
        "static_machine_id" => $static_machine_id,
        "total_deposited" => $input["total_deposited"],
        "total_revenue" => $input["total_revenue"],
    ]));

    echo json_encode(["status" => "saved"]);
    exit;
}

// ---------- LOAD CHECKPOINT ----------
if ($action === "load_checkpoint") {
    if (!file_exists($file)) {
        echo json_encode(null);
        exit;
    }

    echo file_get_contents($file);
    exit;
}

// ---------- DEFAULT ----------
//getStaticMachineId() is called from wallet.js to get the machine id for live checking
echo json_encode([
    "static_machine_id" => $static_machine_id,
    "secrets" => [
        "ls" => $_LS,
        "uls" => $_ULS
    ]
], JSON_PRETTY_PRINT);




