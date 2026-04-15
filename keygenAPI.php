<?php
header('Content-Type: application/json');

/* 🔐 KEEP THESE SECRET — never expose */
$SECRET        = 'EMbingo!X9pQ#2025-EthioMark-Lic3ns3-S3cr3t@Key';
$UNLOCK_SECRET = 'EMbingo!UNLOCK@2025-EthioMark-Unl0ck-S3cr3t';

/* ── get input ── */
$input = json_decode(file_get_contents("php://input"), true);

$action = $input['action'] ?? '';

function respond($data) {
    echo json_encode($data);
    exit;
}

/* ── generate license key ── */
if ($action === 'generate_key') {

    $mid = strtoupper(preg_replace('/[^A-F0-9]/', '', $input['mid'] ?? ''));
    $sn  = intval($input['sn'] ?? 0);
    $amt = intval($input['amt'] ?? 0);

    if (strlen($mid) !== 8) respond(['error' => 'Invalid Machine ID']);
    if ($sn <= 0)           respond(['error' => 'Invalid Serial']);
    if ($amt <= 0)          respond(['error' => 'Invalid Amount']);

    $data = $mid . '|' . $sn . '|' . $amt;

    $hash = strtoupper(substr(
        hash_hmac('sha256', $data, $SECRET),
        0, 8
    ));

    $key = "EM-$mid-$amt-$sn-$hash";

    respond(['key' => $key]);
}

/* ── generate unlock code ── */
if ($action === 'generate_unlock') {

    $mid   = strtoupper(preg_replace('/[^A-F0-9]/', '', $input['mid'] ?? ''));
    $nonce = intval($input['nonce'] ?? 0);

    if (strlen($mid) !== 8) respond(['error' => 'Invalid Machine ID']);
    if ($nonce < 0)         respond(['error' => 'Invalid nonce']);

    $data = $mid . ':' . $nonce;

    $code = strtoupper(substr(
        hash_hmac('sha256', $data, $UNLOCK_SECRET),
        0, 8
    ));

    respond(['code' => $code]);
}

respond(['error' => 'Invalid action']);