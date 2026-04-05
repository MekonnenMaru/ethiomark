<?php
// Session Management
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Authorization Check
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] != "cashier") {
    header("Location: ../config/logout.php");
    exit;
}

// Database Connection
include_once '../config/Database.php';
$db = Database::getInstance();
$conn = $db->getConnection();
$cashier_id = isset($_SESSION['cashier_id']) ? $_SESSION['cashier_id'] : '';

// Pattern Checking Functions Description:
// check_any_two_lines: Checks if there are at least two full lines (horizontal or vertical)
// check_any_vertical: Checks if any column (vertical) is fully matched
// check_any_horizontal: Checks if any row (horizontal) is fully matched
// check_t_pattern: Checks if the top row and middle column are fully matched, forming a "T"
// check_reverse_t_pattern: Checks if the bottom row and middle column are matched, forming a reverse "T"
// check_x_pattern: Checks both diagonals to form an "X"
// check_l_pattern: Checks if the left column and bottom row are matched to form an "L"
// check_reverse_l_pattern: Checks if the right column and bottom row form a reverse "L"
// check_half_above: Checks if the top two rows are fully matched
// check_half_below: Checks if the bottom two rows are fully matched
// check_full_pattern: Checks if the entire Bingo card is matched (all numbers are drawn)

if ($cashier_id) {
    if (isset($_POST['chack_card']) && isset($_POST['card_no']) && isset($_POST['round']) && isset($_POST['cashier_id'])) {
        $response = [];
        $round_number = $_POST['round'];
        $cartela_number = $_POST['card_no'];
        $cashier_id = $_POST['cashier_id'];
        $new_bonus_status = '';
        
        // Check new bonus status
        $query_new_bonus_status = "SELECT * FROM `new_bonus` WHERE `game_id`=? and `cashier_id` = ?";
        $stmt = $conn->prepare($query_new_bonus_status);
        $stmt->bind_param("is", $_POST['round'], $cashier_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $new_bonus_status = $row['status'];
            }
        }

        // Main query to fetch card data
        $query = "
            SELECT 
                g.round_number,
                g.cashier_id,
                g.pattern,
                g.cartela_number,
                g.result,
                g.locked_cartela_number,
                c.category,
                c.color,
                ct.*
            FROM game g
            JOIN cashier c ON g.cashier_id = c.cashier_id
            LEFT JOIN cartela ct ON ct.cartela_number = ? AND ct.category = c.category
            WHERE g.round_number = ?
            AND g.cashier_id = ?
            AND FIND_IN_SET(?, REPLACE(g.cartela_number, ' ', '')) > 0
        ";

        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param('siss', $cartela_number, $round_number, $cashier_id, $cartela_number);
            
            if ($stmt->execute()) {
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    
                    // Check if card is locked
                    $locked_list_raw = $row['locked_cartela_number'] ?? '';
                    $locked_cards = array_map('intval', explode(',', str_replace(' ', '', $locked_list_raw)));
                    $is_locked = in_array((int)$cartela_number, $locked_cards) ? 'yes' : 'no';
                    
                    $category = $row['category'] ?? 'default';
                    $result_color = $row['color'] ?? 'two';
                    $card_data = $row;

                    // Prepare card data arrays
                    $b_col = isset($card_data['b']) ? explode(',', $card_data['b']) : [];
                    $i_col = isset($card_data['i']) ? explode(',', $card_data['i']) : [];
                    $n_col = isset($card_data['n']) ? explode(',', $card_data['n']) : [];
                    $g_col = isset($card_data['g']) ? explode(',', $card_data['g']) : [];
                    $o_col = isset($card_data['o']) ? explode(',', $card_data['o']) : [];

                    $card = [
                        'B' => $b_col,
                        'I' => $i_col,
                        'N' => $n_col,
                        'G' => $g_col,
                        'O' => $o_col
                    ];

                    // Get drawn numbers
                    $drawn_numbers = array_map('intval', array_filter(array_map('trim', explode(',', $row['result']))));
                    $pattern = $row['pattern'];

                    // Check for specific pattern
                    $is_bingo = check_pattern($card, $drawn_numbers, $pattern);
                    $win_status = 0;
                    $last_called = end($drawn_numbers);
                    $is_new_bonus_available = "no";
                    $count_winning_line = 0;
                    
                    $special_partner_id_condition = ($result_color == "two");

                    // Initialize HTML table
                    $html = "<div id='round' style='font-size: 30px; font-weight: bolder; background-color: white; color: black; padding: 5px; margin-bottom: 5px; display: flex; justify-content: space-between; align-items: center;'>
                                <span style='text-align: left;'>Card No: " . $cartela_number . "</span>
                                <span style='text-align: right;color : green;'>Any " . $pattern . " line</span>
                            </div>";
                    
                    $html .= "<table id='tablefetch' style='width: 90%; margin: 0 auto; position: relative; text-align: center; border-collapse: collapse;'>
                                <thead>
                                    <tr style='line-height: 2.5;'>
                                        <th class='b' style='background-color: #00478B; color: white;'>B</th>
                                        <th class='i' style='background-color: #00478B; color: white;'>I</th>
                                        <th class='n' style='background-color: #00478B; color: white;'>N</th>
                                        <th class='g' style='background-color: #00478B; color: white;'>G</th>
                                        <th class='o' style='background-color: #00478B; color: white;'>O</th>
                                    </tr>
                                </thead>
                                <tbody>";

                    if ($pattern) {
                        // Define winning corners
                        $winning_corners = [
                            $card['B'][0], // Top-left corner
                            $card['O'][0], // Top-right corner
                            $card['B'][4], // Bottom-left corner
                            $card['O'][4], // Bottom-right corner
                        ];

                        $winning_center_corners = [
                            $card['I'][1], // Top-left center corner
                            $card['G'][1], // Top-right center corner
                            $card['I'][3], // Bottom-left center corner
                            $card['G'][3], // Bottom-right center corner
                        ];
                        
                        // Check if all winning corners are found
                        $all_corners_found = true;
                        foreach ($winning_corners as $corner) {
                            if (!in_array($corner, $drawn_numbers)) {
                                $all_corners_found = false;
                                break;
                            }
                        }
                        if ($all_corners_found) { $count_winning_line++; }

                        // Check if all winning center corners are found
                        $all_center_corners_found = true;
                        foreach ($winning_center_corners as $corner) {
                            if (!in_array($corner, $drawn_numbers)) {
                                $all_center_corners_found = false;
                                break;
                            }
                        }
                        if ($all_center_corners_found) { $count_winning_line++; }

                        // Initialize arrays to track winning patterns
                        $winning_rows = [];
                        $winning_rows_num = [];
                        $winning_columns = [];
                        $winning_columns_num = [];
                        $winning_diagonals = [];
                        $winning_diagonals_num = [];

                        // Check winning rows
                        for ($i = 0; $i < 5; $i++) {
                            $row_numbers = [];
                            foreach ($card as $col_key => $col) {
                                $row_numbers[] = $col[$i];
                            }
                            $row_numbers = array_map('intval', $row_numbers);
                            $count_drawn = count(array_intersect($row_numbers, $drawn_numbers));
                            $expected_count = ($i == 2) ? 4 : 5;

                            if ($count_drawn >= $expected_count) { 
                                $winning_rows_num[$i] = implode(", ", $row_numbers); 
                            }
                            $winning_rows[$i] = $count_drawn >= $expected_count;
                            if ($winning_rows[$i]) { $count_winning_line++; }
                        }

                        // Check winning columns
                        $occurrences_per_column = [];
                        foreach (array_keys($card) as $col_key) {
                            $winning_columns[$col_key] = false;
                            $occurrences_per_column[$col_key] = 0;
                        }

                        foreach ($card as $col_key => $col) {
                            $expected_number_of_col_occu = 5;
                            for ($i = 0; $i < 5; $i++) {
                                if ($col_key === 'N' && $i == 2) {
                                    continue;
                                }
                                if (in_array($col[$i], $drawn_numbers)) {
                                    $occurrences_per_column[$col_key]++;
                                    $winning_columns_num[$col_key][] = $col[$i];
                                }
                                if ($col_key === 'N') {
                                    $expected_number_of_col_occu = 4;
                                }
                            }
                            $winning_columns[$col_key] = ($occurrences_per_column[$col_key] >= $expected_number_of_col_occu);
                            if ($winning_columns[$col_key]) { $count_winning_line++; }
                        }

                        // Check winning diagonals
                        $winning_diagonals[0] = count(array_intersect([$card['B'][0], $card['I'][1], $card['N'][2], $card['G'][3], $card['O'][4]], $drawn_numbers)) === 4;
                        $winning_diagonals[1] = count(array_intersect([$card['B'][4], $card['I'][3], $card['N'][2], $card['G'][1], $card['O'][0]], $drawn_numbers)) === 4;
                        $winning_diagonals_num[0] = [$card['B'][0], $card['I'][1], $card['N'][2], $card['G'][3], $card['O'][4]];
                        $winning_diagonals_num[1] = [$card['B'][4], $card['I'][3], $card['N'][2], $card['G'][1], $card['O'][0]];
                        
                        if ($winning_diagonals[0]) { $count_winning_line++; }
                        if ($winning_diagonals[1]) { $count_winning_line++; }

                        // Initialize locked cells arrays
                        $locked_cells = [];
                        $locked_cells_color = [
                            0 => ['B' => null, 'I' => null, 'N' => null, 'G' => null, 'O' => null],
                            1 => ['B' => null, 'I' => null, 'N' => null, 'G' => null, 'O' => null],
                            2 => ['B' => null, 'I' => null, 'N' => null, 'G' => null, 'O' => null],
                            3 => ['B' => null, 'I' => null, 'N' => null, 'G' => null, 'O' => null],
                            4 => ['B' => null, 'I' => null, 'N' => null, 'G' => null, 'O' => null],
                        ];

                        // Generate card table
                        for ($i = 0; $i < 5; $i++) {
                            $html .= "<tr>";
                            foreach ($card as $col_name => $col) {
                                $number = trim($col[$i]);
                                $cell_color = 'white';
                                $txt_color = 'black';
                                $blink_class = '';

                                if (in_array($number, $drawn_numbers)) {
                                    // Check winning corners
                                    if (in_array($number, $winning_corners)) {
                                        if ($all_corners_found) {
                                            if ($special_partner_id_condition) {
                                                $win_status = 1;
                                            }
                                            $found = in_array(end($drawn_numbers), $winning_corners);
                                            if ($found) {
                                                $cell_color = 'green';
                                                $txt_color = 'white';
                                                $locked_cells[$i][$col_name] = true;
                                                $locked_cells_color[$i][$col_name] = "green";
                                                $win_status = 1;
                                                $is_new_bonus_available = "yes";
                                            } else {
                                                $locked_cells[$i][$col_name] = true;
                                                $locked_cells_color[$i][$col_name] = "#bca106";
                                                $cell_color = '#bca106';
                                                $txt_color = 'white';
                                            }
                                        } else {
                                            $cell_color = 'red';
                                            $txt_color = 'white';
                                        }
                                    }

                                    // Check winning center corners
                                    if (in_array($number, $winning_center_corners)) {
                                        if ($all_center_corners_found) {
                                            if ($special_partner_id_condition) {
                                                $win_status = 1;
                                            }
                                            $found = in_array(end($drawn_numbers), $winning_center_corners);
                                            if ($found) {
                                                $cell_color = 'green';
                                                $txt_color = 'white';
                                                $locked_cells[$i][$col_name] = true;
                                                $locked_cells_color[$i][$col_name] = "green";
                                                $win_status = 1;
                                                $is_new_bonus_available = "yes";
                                            } else {
                                                $locked_cells[$i][$col_name] = true;
                                                $locked_cells_color[$i][$col_name] = "#bca106";
                                                $cell_color = '#bca106';
                                                $txt_color = 'white';
                                            }
                                        } else {
                                            $cell_color = 'red';
                                            $txt_color = 'white';
                                        }
                                    }

                                    // Check winning columns
                                    if (!empty($winning_columns[$col_name])) {
                                        $array_from_string = explode(', ', implode(", ", $winning_columns_num[$col_name]));
                                        $found = in_array(end($drawn_numbers), $array_from_string);

                                        if ($special_partner_id_condition) {
                                            $win_status = 1;
                                        }
                                        if ($found) {
                                            $cell_color = 'green';
                                            $txt_color = 'white';
                                            $locked_cells[$i][$col_name] = true;
                                            $locked_cells_color[$i][$col_name] = "green";
                                            $win_status = 1;
                                            $is_new_bonus_available = "yes";
                                        } else {
                                            if (!isset($locked_cells[$i][$col_name])) {
                                                $locked_cells[$i][$col_name] = true;
                                                $locked_cells_color[$i][$col_name] = "#bca106";
                                            }
                                            $cell_color = '#bca106';
                                            $txt_color = 'white';
                                        }
                                    } elseif (empty($winning_columns[$col_name])) {
                                        $cell_color = 'red';
                                        $txt_color = 'white';
                                    }

                                    // Check winning rows
                                    if ($winning_rows[$i]) {
                                        if ($special_partner_id_condition) {
                                            $win_status = 1;
                                        }
                                        $array_from_string = explode(', ', $winning_rows_num[$i]);
                                        $found = in_array(end($drawn_numbers), $array_from_string);
                                        if ($found) {
                                            $cell_color = 'green';
                                            $txt_color = 'white';
                                            $locked_cells[$i][$col_name] = true;
                                            $locked_cells_color[$i][$col_name] = "green";
                                            $win_status = 1;
                                            $is_new_bonus_available = "yes";
                                        } else {
                                            if (!isset($locked_cells[$i][$col_name])) {
                                                $locked_cells[$i][$col_name] = true;
                                                $locked_cells_color[$i][$col_name] = "#bca106";
                                            }
                                            $cell_color = '#bca106';
                                            $txt_color = 'white';
                                        }
                                    } elseif (!$winning_rows[$i]) {
                                        $cell_color = 'green';
                                        $txt_color = 'white';
                                    }

                                    // Check first diagonal
                                    if (($winning_diagonals[0] && $number === $card['B'][0]) ||
                                        ($winning_diagonals[0] && $number === $card['I'][1]) ||
                                        ($winning_diagonals[0] && $number === $card['N'][2]) ||
                                        ($winning_diagonals[0] && $number === $card['G'][3]) ||
                                        ($winning_diagonals[0] && $number === $card['O'][4])) {
                                        if ($special_partner_id_condition) {
                                            $win_status = 1;
                                        }
                                        $array_from_string0 = explode(', ', implode(", ", $winning_diagonals_num[0]));
                                        $found0 = in_array(end($drawn_numbers), $array_from_string0);
                                        if ($found0) {
                                            $cell_color = 'green';
                                            $txt_color = 'white';
                                            $locked_cells[$i][$col_name] = true;
                                            $locked_cells_color[0]['B'] = 'green';
                                            $locked_cells_color[1]['I'] = 'green';
                                            $locked_cells_color[2]['N'] = 'green';
                                            $locked_cells_color[3]['G'] = 'green';
                                            $locked_cells_color[4]['O'] = 'green';
                                            $win_status = 1;
                                            $is_new_bonus_available = "yes";
                                        } else {
                                            if (!isset($locked_cells[$i][$col_name])) {
                                                $locked_cells[$i][$col_name] = true;
                                                $locked_cells_color[0]['B'] = '#bca106';
                                                $locked_cells_color[1]['I'] = '#bca106';
                                                $locked_cells_color[2]['N'] = '#bca106';
                                                $locked_cells_color[3]['G'] = '#bca106';
                                                $locked_cells_color[4]['O'] = '#bca106';
                                            }
                                            $cell_color = '#bca106';
                                            $txt_color = 'white';
                                        }
                                    } else {
                                        $cell_color = 'red';
                                        $txt_color = 'white';
                                    }

                                    // Check second diagonal
                                    if (($winning_diagonals[1] && $number === $card['B'][4]) ||
                                        ($winning_diagonals[1] && $number === $card['I'][3]) ||
                                        ($winning_diagonals[1] && $number === $card['N'][2]) ||
                                        ($winning_diagonals[1] && $number === $card['G'][1]) ||
                                        ($winning_diagonals[1] && $number === $card['O'][0])) {
                                        if ($special_partner_id_condition) {
                                            $win_status = 1;
                                        }
                                        $array_from_string1 = explode(', ', implode(", ", $winning_diagonals_num[1]));
                                        $found1 = in_array(end($drawn_numbers), $array_from_string1);
                                        if ($found1) {
                                            $cell_color = 'green';
                                            $txt_color = 'white';
                                            $locked_cells[$i][$col_name] = true;
                                            $locked_cells_color[4]['B'] = 'green';
                                            $locked_cells_color[3]['I'] = 'green';
                                            $locked_cells_color[2]['N'] = 'green';
                                            $locked_cells_color[1]['G'] = 'green';
                                            $locked_cells_color[0]['O'] = 'green';
                                            $win_status = 1;
                                            $is_new_bonus_available = "yes";
                                        } else {
                                            if (!isset($locked_cells[$i][$col_name])) {
                                                $locked_cells[$i][$col_name] = true;
                                                $locked_cells_color[4]['B'] = '#bca106';
                                                $locked_cells_color[3]['I'] = '#bca106';
                                                $locked_cells_color[2]['N'] = '#bca106';
                                                $locked_cells_color[1]['G'] = '#bca106';
                                                $locked_cells_color[0]['O'] = '#bca106';
                                            }
                                            $cell_color = '#bca106';
                                            $txt_color = 'white';
                                        }
                                    } else {
                                        $cell_color = 'red';
                                        $txt_color = 'white';
                                    }
                                }

                                // Special handling for center cell
                                if ((!empty($winning_rows[2]) || !empty($winning_columns[$col_name]) || $winning_diagonals[0] == 1 || $winning_diagonals[1] == 1) && ($i == 2 && $col_name == 'N')) {
                                    $cell_color = '#bca106';
                                    $txt_color = 'white';
                                    if ($special_partner_id_condition) {
                                        $cell_color = 'green';
                                    }
                                    
                                    $isGreen = false;
                                    if (!empty($winning_rows[2]) && $locked_cells_color[2]['B'] == "green") {
                                        $isGreen = true;
                                    }
                                    if (!$isGreen && !empty($winning_columns[$col_name]) && $locked_cells_color[0][$col_name] == "green") {
                                        $isGreen = true;
                                    }
                                    if (!$isGreen) {
                                        if ($winning_diagonals[0] == 1) {
                                            if ($locked_cells_color[0]['B'] == "green") {
                                                $isGreen = true;
                                            }
                                        }
                                        if (!$isGreen && $winning_diagonals[1] == 1) {
                                            if ($locked_cells_color[0]['O'] == "green") {
                                                $isGreen = true;
                                            }
                                        }
                                    }
                                    
                                    if ($isGreen) {
                                        $cell_color = 'green';
                                        $txt_color = 'white';
                                        $locked_cells_color[$i][$col_name] = 'green';
                                    } else {
                                        $locked_cells_color[$i][$col_name] = $cell_color;
                                    }
                                } else if (count($drawn_numbers) > 0 && ($i == 2 && $col_name == 'N')) {
                                    $cell_color = 'red';
                                    $txt_color = 'white';
                                }

                                // Handle locked cells
                                if (isset($locked_cells[$i][$col_name])) {
                                    if ($special_partner_id_condition) {
                                        $cell_color = 'green';
                                    } else {
                                        $cell_color = $locked_cells_color[$i][$col_name];
                                    }
                                }

                                // Highlight last drawn number
                                if ($number == end($drawn_numbers) && array_search($number, $drawn_numbers) !== false) {
                                    $cell_color = 'blue';
                                    $txt_color = 'white';
                                    $blink_class = 'blink';
                                }

                                $display_number = ($number == 0) ? "★" : $number;
                                $html .= "<td class='$blink_class' style='background-color: $cell_color; color: $txt_color; border: 2px solid #ccc; font-size: 48px; text-align: center; vertical-align: middle;'>" . $display_number . "</td>";
                            }
                            $html .= "</tr>";
                        }
                    }
                    
                    $html .= "</tbody></table></br></br></br>";

                    $response['status'] = "success";
                    $response['message'] = "" . $html;
                    $response['win_status'] = $win_status;
                    $response['last_called'] = $last_called;
                    $response['new_bonus_status'] = $new_bonus_status;
                    $response['is_new_bonus_available'] = $is_new_bonus_available;
                    $response['count_winning_line'] = $count_winning_line;
                    $response['expected_pattern'] = $pattern;
                    $response['is_locked'] = $is_locked;

                } else {
                    $response['message'] = "No data found.";
                    $response['status'] = "success";
                }

                $result->free();
            } else {
                $response['error'] = "Execution error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $response['error'] = "Error in query preparation: " . $conn->error;
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
} else {
    header("Location: ../config/logout.php");
    exit;
}

// Pattern Checking Functions
function check_pattern($card, $drawn_numbers, $pattern) {
    switch ($pattern) {
        case 1:
        case 2:
        case 3:
            return check_any_one_line($card, $drawn_numbers);
        case 4:
            return check_any_horizontal($card, $drawn_numbers);
        case 5:
            return check_t_pattern($card, $drawn_numbers);
        case 6:
            return check_reverse_t_pattern($card, $drawn_numbers);
        case 7:
            return check_x_pattern($card, $drawn_numbers);
        case 8:
            return check_l_pattern($card, $drawn_numbers);
        case 9:
            return check_reverse_l_pattern($card, $drawn_numbers);
        case 10:
            return check_half_above($card, $drawn_numbers);
        case 11:
            return check_half_below($card, $drawn_numbers);
        case 12:
            return check_full_pattern($card, $drawn_numbers);
        default:
            return false;
    }
}

function check_any_one_line($card, $drawn_numbers) {
    foreach ($card as $column => $numbers) {
        $missing_numbers = array_diff($numbers, $drawn_numbers);
        if (empty($missing_numbers)) {
            return true;
        }
    }
    return false;
}

function check_any_two_lines($card, $drawn_numbers) {
    $line_count = 0;
    foreach ($card as $column => $numbers) {
        if (array_intersect($numbers, $drawn_numbers) == $numbers) {
            $line_count++;
        }
    }
    for ($i = 0; $i < 5; $i++) {
        $vertical = [$card['B'][$i], $card['I'][$i], $card['N'][$i], $card['G'][$i], $card['O'][$i]];
        if (array_intersect($vertical, $drawn_numbers) == $vertical) {
            $line_count++;
        }
    }
    return $line_count >= 2;
}

function check_any_vertical($card, $drawn_numbers) {
    for ($i = 0; $i < 5; $i++) {
        $vertical = [$card['B'][$i], $card['I'][$i], $card['N'][$i], $card['G'][$i], $card['O'][$i]];
        if (array_intersect($vertical, $drawn_numbers) == $vertical) {
            return true;
        }
    }
    return false;
}

function check_any_horizontal($card, $drawn_numbers) {
    foreach ($card as $column => $numbers) {
        if (array_intersect($numbers, $drawn_numbers) == $numbers) {
            return true;
        }
    }
    return false;
}

function check_t_pattern($card, $drawn_numbers) {
    $top_row = [$card['B'][0], $card['I'][0], $card['N'][0], $card['G'][0], $card['O'][0]];
    $middle_column = [$card['N'][0], $card['N'][1], $card['N'][2], $card['N'][3], $card['N'][4]];
    return (array_intersect($top_row, $drawn_numbers) == $top_row &&
            array_intersect($middle_column, $drawn_numbers) == $middle_column);
}

function check_reverse_t_pattern($card, $drawn_numbers) {
    $bottom_row = [$card['B'][4], $card['I'][4], $card['N'][4], $card['G'][4], $card['O'][4]];
    $middle_column = [$card['N'][0], $card['N'][1], $card['N'][2], $card['N'][3], $card['N'][4]];
    return (array_intersect($bottom_row, $drawn_numbers) == $bottom_row &&
            array_intersect($middle_column, $drawn_numbers) == $middle_column);
}

function check_x_pattern($card, $drawn_numbers) {
    $diag1 = [$card['B'][0], $card['I'][1], $card['N'][2], $card['G'][3], $card['O'][4]];
    $diag2 = [$card['B'][4], $card['I'][3], $card['N'][2], $card['G'][1], $card['O'][0]];
    return (array_intersect($diag1, $drawn_numbers) == $diag1 &&
            array_intersect($diag2, $drawn_numbers) == $diag2);
}

function check_l_pattern($card, $drawn_numbers) {
    $left_column = [$card['B'][0], $card['B'][1], $card['B'][2], $card['B'][3], $card['B'][4]];
    $bottom_row = [$card['B'][4], $card['I'][4], $card['N'][4], $card['G'][4], $card['O'][4]];
    return (array_intersect($left_column, $drawn_numbers) == $left_column &&
            array_intersect($bottom_row, $drawn_numbers) == $bottom_row);
}

function check_reverse_l_pattern($card, $drawn_numbers) {
    $right_column = [$card['O'][0], $card['O'][1], $card['O'][2], $card['O'][3], $card['O'][4]];
    $bottom_row = [$card['B'][4], $card['I'][4], $card['N'][4], $card['G'][4], $card['O'][4]];
    return (array_intersect($right_column, $drawn_numbers) == $right_column &&
            array_intersect($bottom_row, $drawn_numbers) == $bottom_row);
}

function check_half_above($card, $drawn_numbers) {
    $half_above = array_merge([$card['B'][0], $card['B'][1]], [$card['I'][0], $card['I'][1]], 
                              [$card['N'][0], $card['N'][1]], [$card['G'][0], $card['G'][1]], 
                              [$card['O'][0], $card['O'][1]]);
    return array_intersect($half_above, $drawn_numbers) == $half_above;
}

function check_half_below($card, $drawn_numbers) {
    $half_below = array_merge([$card['B'][3], $card['B'][4]], [$card['I'][3], $card['I'][4]], 
                              [$card['N'][3], $card['N'][4]], [$card['G'][3], $card['G'][4]], 
                              [$card['O'][3], $card['O'][4]]);
    return array_intersect($half_below, $drawn_numbers) == $half_below;
}

function check_full_pattern($card, $drawn_numbers) {
    $full_card = array_merge($card['B'], $card['I'], $card['N'], $card['G'], $card['O']);
    return array_intersect($full_card, $drawn_numbers) == $full_card;
}
?>