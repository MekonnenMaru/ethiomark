<?php
header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);

$gameId     = $input['game_id'] ?? null;
$cashierId  = $input['cashier_id'] ?? null;
$cardNo     = $input['card_no'] ?? null;
$round      = $input['round'] ?? null;
$category   = $input['category'] ?? null;
$cartelaNo  = $input['cartela_number'] ?? null;

// --- 1. BONUS STATUS REQUEST ---
if ($gameId && $cashierId && !$cardNo) {
    echo json_encode([
        'status' => '1',
        'new_bonus_status' => true,
        'bonus_amount' => 25,
        'message' => 'Bonus available for this round!'
    ]);
    exit;
}

// --- 2. GAME RESULT REQUEST ---
if ($cardNo && $round && $cashierId) {
    echo json_encode([
        'rows' => [
            [
                'result' => '12, 8, 24, 36, 55, 60, 72',
                'pattern' => '1'
            ]
        ]
    ]);
    exit;
}

// --- 3. CATEGORY INFO REQUEST ---
if ($cashierId && !$gameId && !$cardNo && !$cartelaNo && !$category) {
    echo json_encode([
        'category' => 'default',
        'color' => 'two'
    ]);
    exit;
}

// --- 4. CARD DATA REQUEST ---
if ($cartelaNo && $category) {
    echo json_encode([
        'table_number' => $cartelaNo,
        'table_data' => [
            [5, 18, 33, 48, 61],
            [2, 22, 0, 51, 67],
            [10, 19, 37, 56, 70],
            [1, 29, 40, 49, 62],
            [6, 21, 36, 52, 73]
        ]
    ]);
    exit;
}

// --- Default fallback ---
echo json_encode([
    'error' => 'Invalid request or missing parameters.'
]);
