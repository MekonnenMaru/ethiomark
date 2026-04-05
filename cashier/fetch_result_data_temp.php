<?php
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}
		
		if (!isset($_SESSION['loggedin'])   || $_SESSION['role']!="cashier") {
			header("Location: ../config/logout.php");
		}
				include_once '../config/Database.php';

				$db = Database::getInstance();
				$conn = $db->getConnection(); // Get the connection object

				$cashier_id = isset($_SESSION['cashier_id']) ? $_SESSION['cashier_id'] : '';

				// // Description of Each Function:
				// // 	check_any_two_lines: Checks if there are at least two full lines (horizontal or vertical).
				// // 	check_any_vertical: Checks if any column (vertical) is fully matched.
				// // 	check_any_horizontal: Checks if any row (horizontal) is fully matched.
				// // 	check_t_pattern: Checks if the top row and middle column are fully matched, forming a "T."
				// // 	check_reverse_t_pattern: Checks if the bottom row and middle column are matched, forming a reverse "T."
				// // 	check_x_pattern: Checks both diagonals to form an "X."
				// // 	check_l_pattern: Checks if the left column and bottom row are matched to form an "L."
				// // 	check_reverse_l_pattern: Checks if the right column and bottom row form a reverse "L."
				// // 	check_half_above: Checks if the top two rows are fully matched.
				// // 	check_half_below: Checks if the bottom two rows are fully matched.
				// // 	check_full_pattern: Checks if the entire Bingo card is matched (all numbers are drawn).



				if ($cashier_id)
				{
			
							if (isset($_POST['chack_card']) && isset($_POST['card_no']) && isset($_POST['round']) && isset($_POST['cashier_id'])) 
							{
								$response = [];
								$partner_id = '';
								
								
								
								 // Query to get partner IDs based on cashier_id
                                $query_partner_id = "SELECT partner.partner_id 
                                                     FROM partner 
                                                     JOIN cashier ON partner.partner_id = cashier.partner_id 
                                                     WHERE cashier.cashier_id = ?";
                                $stmt = $conn->prepare($query_partner_id);
                                $stmt->bind_param("s", $cashier_id);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                
                                
                            
                                if ($result && $result->num_rows > 0) {
                                    // Loop through results to fetch all partner IDs
                                    while ($row = $result->fetch_assoc()) {
                                        $partner_id = $row['partner_id'];
                                    }
                                }
                                
                                
                                
                                
								
								$round_number = $_POST['round']; // The round number
								$cartela_number = $_POST['card_no']; // The card number to check
								$cashier_id = $_POST['cashier_id']; // The cashier ID

								// Prepare the query with FIND_IN_SET
								$query = "SELECT round_number,cashier_id,pattern,cartela_number,result FROM game 
										WHERE round_number = ? 
										AND cashier_id = ? 
										AND FIND_IN_SET(?, REPLACE(cartela_number, ' ', '')) > 0;";

								// Create a prepared statement
								$stmt = $conn->prepare($query);

								if ($stmt === false) {
									$response['error'] = "Error in query preparation: " . $conn->error;
								} else {
									// Bind parameters
									$stmt->bind_param('iss', $round_number, $cashier_id, $cartela_number);

									// Execute the statement
									if (!$stmt->execute()) {
										$response['error'] = "Execution error: " . $stmt->error;
									} 
									else 
									{
										// Get the result set
										$result = $stmt->get_result();

										// Cartela number is registered
										if ($result->num_rows > 0) 
										{ 
											
									    	// Get the drawn numbers
											$row = $result->fetch_assoc();
											$drawn_numbers = array_map('intval', array_filter(array_map('trim', explode(',', $row['result']))));
											$pattern = $row['pattern'];
											
											
											
											// Step 1: Retrieve the category for the given cartela_number and cashier_id
											$category_query = "SELECT `category` FROM `cashier` WHERE  `cashier_id` = ?";
											$category_stmt = $conn->prepare($category_query);
											$category_stmt->bind_param('s',$cashier_id); // Bind parameters
											$category_stmt->execute();
											$category_result = $category_stmt->get_result();
											$category = ($category_result->num_rows > 0) ? $category_result->fetch_assoc()['category'] : "default";
											
											
											
											

										// Retrieve the card layout from the `cartela` table
											$card_query = "SELECT * FROM `cartela` WHERE `cartela_number` = ? and category = ?";
											$card_stmt = $conn->prepare($card_query);
											$card_stmt->bind_param('ss', $cartela_number, $category);
											$card_stmt->execute();
											$card_result = $card_stmt->get_result();
											$card_data = $card_result->fetch_assoc();
											
											

											// Convert card data into arrays
											$b_col = explode(',', $card_data['b']);
											$i_col = explode(',', $card_data['i']);
											$n_col = explode(',', $card_data['n']);
											$g_col = explode(',', $card_data['g']);
											$o_col = explode(',', $card_data['o']);

											$card = [
												'B' => $b_col,
												'I' => $i_col,
												'N' => $n_col,
												'G' => $g_col,
												'O' => $o_col
											];



																
											
											
											
											
											
											
											
											
											
											
																//  Check for specific pattern
																$is_bingo = check_pattern($card, $drawn_numbers, $pattern);
																$win_status = 0;
																$special_partner_id_condition = ($partner_id == '2' || $partner_id == '13');
																// $special_partner_id_condition = ($partner_id == '2' );
																	

																// Initialize a variable to store the HTML table
											                   $html = "<div id='round' style='font-size: 30px; font-weight: bolder;  background-color: white; color: black; padding:5px;margin-bottom: 5px;'>																			
																			<span style='text-align: left;'> Card No : ".$cartela_number."</span><br>
																		</div>";
																$html.="<table id='tablefetch' style='width: 90%; margin: 0 auto; position: relative; text-align: center; border-collapse: collapse;'>
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
											                    
    															
																
																
																
																
																
																//for pattern 1
                    											if($pattern==1)
                    											{
                    											    
                    											    
                    												// Print the header row for the table (B, I, N, G, O)
                    												// $html .= "<tr>";
                    												// foreach (array_keys($card) as $col_name) {
                    												// 	$html .= "<th>$col_name</th>";
                    												// }
                    												// $html .= "</tr>";
                    												// Define winning corners
                    												$winning_corners = [
                    													$card['B'][0], // Top-left corner
                    													$card['O'][0], // Top-right corner
                    													$card['B'][4], // Bottom-left corner
                    													$card['O'][4], // Bottom-right corner
                    												];
																	
                    												// Check if all winning corners are found in the winning numbers
                    												$all_corners_found = true; // Assume true until proven otherwise
                    												foreach ($winning_corners as $corner) {
                    													if (!in_array($corner, $drawn_numbers)) { // Check if corner is in winning numbers
                    														$all_corners_found = false; // At least one corner is not found
                    														break; // Exit early if we already know not all corners are found
                    													}
                    												}
                    												// Initialize arrays to track winning patterns
                    												$winning_rows = [];
																	$winning_rows_num = [];

                    												$winning_columns = [];
																	$winning_columns_num = [];

                    												$winning_diagonals = [];
																	$winning_diagonals_num = [];
                    


                    												for ($i = 0; $i < 5; $i++) 
                    												{
                    													$row_numbers = [];
                    													
                    													foreach ($card as $col_key => $col) {
                    														$row_numbers[] = $col[$i]; // Collect numbers in the i-th row across all columns
                    													}
                    
                    													// Ensure row numbers are integers
                    													$row_numbers = array_map('intval', $row_numbers);
                    													
                    													// Count how many drawn numbers are present in the current row
                    													$count_drawn = count(array_intersect($row_numbers, $drawn_numbers));
                    													
                    													// Debugging output
                    													// echo "Row $i Numbers: " . implode(", ", $row_numbers) . "\n";
                    													// echo "Count Drawn in Row $i: $count_drawn\n";
                    
                    													// Determine expected count based on the row index
                    													$expected_count = ($i == 2) ? 4 : 5; // Row 2 should have 4, others should have 5
                    													

																		// Check if the count of drawn numbers meets or exceeds the expected count
																		if ($count_drawn >= $expected_count) { $winning_rows_num[$i] = implode(", ", $row_numbers); }

																		// Check if the count of drawn numbers meets the expected count
                    													$winning_rows[$i] = $count_drawn >= $expected_count;
                    												}
                    
                    												
                    												// Check for winning columns
                    												$occurrences_per_column = []; // Initialize an array to hold occurrences for each column
                    												$expected_number_of_col_occu = 5; // Default expected number of drawn numbers in a column
                    												// Initialize winning columns to false
                    												foreach (array_keys($card) as $col_key) {
                    													$winning_columns[$col_key] = false; // Default to false
                    													$occurrences_per_column[$col_key] = 0; // Initialize count to 0 for each column
                    												}
                    												// Check for winning columns and count occurrences
                    												foreach ($card as $col_key => $col) 
																	{
                    													$expected_number_of_col_occu = 5; // Reset expected number for each column
                    													for ($i = 0; $i < 5; $i++) 
																		{
                    														// Center point in column 'N' (row 2) is free
                    														if ($col_key === 'N' && $i == 2) {
                    															continue; // Skip checking this free space
                    														}
                    														// Check if the current number is in the drawn numbers
                    														if (in_array($col[$i], $drawn_numbers)) {
                    															$occurrences_per_column[$col_key]++; // Increment the count for this column
																				$winning_columns_num[$col_key][] = $col[$i]; // Add the drawn number to winning_columns_num for that column
																			}
                    														// If we are checking column 'N', reduce expected number to 4 since the center is free
                    														if ($col_key === 'N') {
                    															$expected_number_of_col_occu = 4;
                    														}
                    													}
																		// if ($occurrences_per_column[$col_key] >= $expected_number_of_col_occu) { $winning_columns_row[$i] = implode(", ", $row_numbers); }
																			// Output the collected column data
    																	
                    													// Determine if this column is a winning column
                    													$winning_columns[$col_key] = ($occurrences_per_column[$col_key] >= $expected_number_of_col_occu);
																		
                    												}
																	// print_r($winning_columns_num);
                    												// print_r("<br>Column  ");
                    												// print_r($winning_columns);
                    
                    												// Check for winning diagonals
                    												$winning_diagonals[0] = count(array_intersect([$card['B'][0], $card['I'][1], $card['N'][2], $card['G'][3], $card['O'][4]], $drawn_numbers)) === 4; // Top-left to bottom-right
                    												$winning_diagonals[1] = count(array_intersect([$card['B'][4], $card['I'][3], $card['N'][2], $card['G'][1], $card['O'][0]], $drawn_numbers)) === 4; // Top-right to bottom-left
                    												$winning_diagonals_num[0]=[$card['B'][0], $card['I'][1], $card['N'][2], $card['G'][3], $card['O'][4]];
																	$winning_diagonals_num[1]= [$card['B'][4], $card['I'][3], $card['N'][2], $card['G'][1], $card['O'][0]];
                    												// print_r("<br>Diagonal[0]  ");
                    												// print_r($winning_diagonals_num);
                    												// print_r("<br>Diagonal[1]  ");
                    												// print_r($winning_diagonals[1]);
                    
                    
                    													$locked_cells = [];
																		$locked_cells_color = [
																			0 => ['B' => null, 'I' => null, 'N' => null, 'G' => null, 'O' => null],
																			1 => ['B' => null, 'I' => null, 'N' => null, 'G' => null, 'O' => null],
																			2 => ['B' => null, 'I' => null, 'N' => null, 'G' => null, 'O' => null],
																			3 => ['B' => null, 'I' => null, 'N' => null, 'G' => null, 'O' => null],
																			4 => ['B' => null, 'I' => null, 'N' => null, 'G' => null, 'O' => null],
																		];
																		
                    													$center_point_row = 2; // The row for the center point (N2)
                    													$center_point_col = 'N'; // The column for the center point (N2)
                    
                    												// 	// Initialize the HTML table
                    												// 	$html = "<table border='1' cellpadding='10' cellspacing='0'>";
                    
                    													// Loop through the rows and add each value for each column into the table
                    													for ($i = 0; $i < 5; $i++) 
                    													{
                    														$html .= "<tr>"; // Start a new row
                    														foreach ($card as $col_name => $col) 
																			{
                    															$number = trim($col[$i]); // Get the number for the current column and row
                    															$cell_color = 'white'; // Default cell color
                    															$txt_color = 'black'; // Default text color
                    
                    															// Check if the current number is drawn
                    															if (in_array($number, $drawn_numbers)) 
                    															{
                    






                    																// // If the number is part of a winning corner
                    																if (in_array($number, $winning_corners)) 
                    																{
                    																	if($all_corners_found)
                    																	{
																							// XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
																								if ($special_partner_id_condition) {
																									$win_status = 1;
																								}
																							//  XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
																							$found = in_array(end($drawn_numbers), $winning_corners);
																							if ($found) 
																							{
																								$cell_color = 'green'; // Mark winning column in green
																								$txt_color = 'white';
																								$locked_cells[$i][$col_name] = true; // Lock this cell
																								$locked_cells_color[$i][$col_name]="green";
																								
																								$win_status = 1;
																							}
																							else
																							{
																								$locked_cells[$i][$col_name] = true; // Lock this cell
																								$locked_cells_color[$i][$col_name]="#bca106";
																								$cell_color = '#bca106'; // Mark winning column in green
																								$txt_color = 'white';
																								
																							}
                    																	}
                    																	else
                    																	{
                    																		$cell_color = 'red'; // Mark winning corners in green
                    																		$txt_color = 'white';
                    																	}
                    																}


                    																// ==================Check if the number is part of a winning column
                    																if (!empty($winning_columns[$col_name])) 
																					{
																						 // Create an array from the winning numbers string for the specified column
																						$array_from_string = explode(', ', implode(", ", $winning_columns_num[$col_name]));
																						// Check if the last drawn number exists in this array
																						$found = in_array( end($drawn_numbers), $array_from_string);

																						// XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
																							if ($special_partner_id_condition) {
																								$win_status = 1;
																							}
																						//  XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
																						// echo "Find " . ($number) . " in " . $winning_rows_num[$i] . "<br>";
																						if ($found) 
																						{
																							//echo "Find " . end($drawn_numbers) . " in " . $winning_rows_num[$i] . " @Row $i<br>";
																							$cell_color = 'green'; // Mark winning column in green
																							$txt_color = 'white';
																							$locked_cells[$i][$col_name] = true; // Lock this cell
																							$locked_cells_color[$i][$col_name]="green";
																							
																							$win_status = 1;
																						}
																						else
																						{
																							if(!isset($locked_cells[$i][$col_name])){
																								$locked_cells[$i][$col_name] = true; // Lock this cell
																								$locked_cells_color[$i][$col_name]="#bca106";
																							}
																							$cell_color = '#bca106'; // Mark winning column in green
																							$txt_color = 'white';
																						}
                    																} elseif (empty($winning_columns[$col_name])){
                    																	// If drawn but not winning, color it red
                    																	$cell_color = 'red'; // Mark drawn but non-winning numbers in red
                    																	$txt_color = 'white';
                    																}




																					// ========================Check if the number is part of a winning row
                    																if ($winning_rows[$i])
																					{
																						// echo in_array($number, $winning_rows_num)."<br>";
																						// echo "Row: '$i'winning_rows : '$winning_rows[$i]' ---->number ='$number'    '$winning_rows_num[$i]'<br>";
																						// XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
																							if ($special_partner_id_condition) {
																								$win_status = 1;
																							}
																						//  XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
																						$array_from_string = explode(', ', $winning_rows_num[$i]);
																						$found = in_array(end($drawn_numbers), $array_from_string);
																						// echo "Find " . ($number) . " in " . $winning_rows_num[$i] . "<br>";
																						if ($found) 
																						{
																							//echo "Find " . end($drawn_numbers) . " in " . $winning_rows_num[$i] . " @Row $i<br>";
																							$cell_color = 'green'; // Mark winning column in green
																							$txt_color = 'white';
																							$locked_cells[$i][$col_name] = true; // Lock this cell
																							$locked_cells_color[$i][$col_name]="green";
																							
																							$win_status = 1;
																						}
																						else
																						{
																							if(!isset($locked_cells[$i][$col_name])){
																								$locked_cells[$i][$col_name] = true; // Lock this cell
																								$locked_cells_color[$i][$col_name]="#bca106";
																							}
																							$cell_color = '#bca106'; // Mark winning column in green
																							$txt_color = 'white';
																							
																						}
                    																	
                    																}
                    																elseif (!$winning_rows[$i]){
                    																	$cell_color = 'green'; // Mark winning row or diagonal in green
                    																	$txt_color = 'white';
                    																}
                    																
                    																if (($winning_diagonals[0] && $number === $card['B'][0]) ||
                    																	($winning_diagonals[0] && $number === $card['I'][1]) ||
                    																	($winning_diagonals[0] && $number === $card['N'][2]) ||
                    																	($winning_diagonals[0] && $number === $card['G'][3]) ||
                    																	($winning_diagonals[0] && $number === $card['O'][4])
                    																	) 
																						{
																							// XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
																								if ($special_partner_id_condition) {
																									$win_status = 1;
																								}
																							//  XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
																							$array_from_string0 = explode(', ', implode(", ", $winning_diagonals_num[0]));
																							$found0 = in_array( end($drawn_numbers), $array_from_string0);
																							// echo "Find " . ($number) . " in " . $winning_rows_num[$i] . "<br>";
																							if ($found0) 
																							{
																								//echo "Find " . end($drawn_numbers) . " in " . $winning_rows_num[$i] . " @Row $i<br>";
																								$cell_color = 'green'; // Mark winning column in green
																								$txt_color = 'white';
																								$locked_cells[$i][$col_name] = true; // Lock this cell
																								$locked_cells_color[0]['B']='green';
																								$locked_cells_color[1]['I']='green';
																								$locked_cells_color[2]['N']='green';
																								$locked_cells_color[3]['G']='green';
																								$locked_cells_color[4]['O']='green';
																								
																								$win_status = 1;

																								// $winning_diagonals_num[0]=[$card['B'][0], $card['I'][1], $card['N'][2], $card['G'][3], $card['O'][4]];
																								// $winning_diagonals_num[1]= [$card['B'][4], $card['I'][3], $card['N'][2], $card['G'][1], $card['O'][0]];
																							}
																							else
																							{
																								if(!isset($locked_cells[$i][$col_name])){
																									$locked_cells[$i][$col_name] = true; 
																									$locked_cells_color[0]['B']='#bca106';
																									$locked_cells_color[1]['I']='#bca106';
																									$locked_cells_color[2]['N']='#bca106';
																									$locked_cells_color[3]['G']='#bca106';
																									$locked_cells_color[4]['O']='#bca106';
																								}
																								$cell_color = '#bca106'; // Mark winning column in green
																								$txt_color = 'white';
																								
																							}

																						} else {
																							$cell_color = 'red'; // Mark drawn numbers not part of winning patterns in red
																							$txt_color = 'white';
																						}


																						if(($winning_diagonals[1] && $number === $card['B'][4]) ||
                    																	($winning_diagonals[1] && $number === $card['I'][3]) ||
                    																	($winning_diagonals[1] && $number === $card['N'][2]) ||
                    																	($winning_diagonals[1] && $number === $card['G'][1]) ||
                    																	($winning_diagonals[1] && $number === $card['O'][0])) 
																						{
																							// XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
																								if ($special_partner_id_condition) {
																									$win_status = 1;
																								}
																							//  XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
																							$array_from_string1 = explode(', ', implode(", ", $winning_diagonals_num[1]));
																							$found1 = in_array( end($drawn_numbers), $array_from_string1);
																							if ($found1) 
																							{
																								//echo "Find " . end($drawn_numbers) . " in " . $winning_rows_num[$i] . " @Row $i<br>";
																								$cell_color = 'green'; // Mark winning column in green
																								$txt_color = 'white';
																								$locked_cells[$i][$col_name] = true; // Lock this cell
																								$locked_cells_color[4]['B']='green';
																								$locked_cells_color[3]['I']='green';
																								$locked_cells_color[2]['N']='green';
																								$locked_cells_color[1]['G']='green';
																								$locked_cells_color[0]['O']='green';

																								
																								$win_status = 1;

																								// $winning_diagonals_num[0]=[$card['B'][0], $card['I'][1], $card['N'][2], $card['G'][3], $card['O'][4]];
																								// $winning_diagonals_num[1]= [$card['B'][4], $card['I'][3], $card['N'][2], $card['G'][1], $card['O'][0]];
																							}
																							else
																							{
																								if(!isset($locked_cells[$i][$col_name])){
																									$locked_cells[$i][$col_name] = true; 
																									$locked_cells_color[4]['B']='#bca106';
																									$locked_cells_color[3]['I']='#bca106';
																									$locked_cells_color[2]['N']='#bca106';
																									$locked_cells_color[1]['G']='#bca106';
																									$locked_cells_color[0]['O']='#bca106';
																								}
																								$cell_color = '#bca106'; // Mark winning column in green
																								$txt_color = 'white';
																								
																							}

																						} else {
																							$cell_color = 'red'; // Mark drawn numbers not part of winning patterns in red
																							$txt_color = 'white';
																						}



																						

                    															}
                    															
                    															
																				
																				// Check if the center cell [2][2] (column 'N') should be colored based on winning conditions
																				if ((!empty($winning_rows[2]) || !empty($winning_columns[$col_name]) || $winning_diagonals[0] == 1 || $winning_diagonals[1] == 1) && ($i == 2 && $col_name == 'N')) 
																				{
																					// Initialize cell_color and txt_color variables
																					$cell_color = '#bca106'; // Default color for winning column
																					$txt_color = 'white';
																					// XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
                                                                                    if ($special_partner_id_condition) {
                                                                                        $cell_color = 'green';
                                                                                    }
                                                                                    
																					// Check if the center cell should be green
																					$isGreen = false;

																					// Check winning rows
																					if (!empty($winning_rows[2]) && $locked_cells_color[2]['B'] == "green") {
																						$isGreen = true;
																					}

																					// Check winning columns
																					if (!$isGreen && !empty($winning_columns[$col_name]) && $locked_cells_color[0][$col_name] == "green") {
																						$isGreen = true;
																					}

																					// Check diagonals
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
																					

																					// Set color based on winning condition
																					if ($isGreen) {
																						$cell_color = 'green'; // Override color to green if any condition is met
																						$txt_color = 'white';
																						$locked_cells_color[$i][$col_name] = 'green'; // Lock the color as green
																					} else {
																						$locked_cells_color[$i][$col_name] = $cell_color; // Use default winning column color
																					}
																				} else if (count($drawn_numbers) > 0 && ($i == 2 && $col_name == 'N')) {
																					$cell_color = 'red'; // If no winning condition and drawn numbers exist
																					$txt_color = 'white';
																				}

                    
                    															// XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
																				// If the cell is locked from previous checks, keep it green
                    															if (isset($locked_cells[$i][$col_name])) {
                    																// $cell_color = $locked_cells_color[$i][$col_name];
                                                                                    if ($special_partner_id_condition) {
                                                                                        $cell_color = 'green';
                                                                                    } else {
                                                                                        $cell_color = $locked_cells_color[$i][$col_name];
                                                                                    }
                    															}
                    															// If the current number is the last drawn number, highlight it in blue
                    															// if ($number == end($drawn_numbers) && array_search($number, $drawn_numbers) !== false) {
                    															// 	$cell_color = 'blue'; // Highlight last drawn number in blue
                    															// 	$txt_color = 'white';
                    															// }
																				// If the current number is the last drawn number, highlight it in blue
																					if ($number == end($drawn_numbers) && array_search($number, $drawn_numbers) !== false) {
																						$cell_color = 'blue'; // Highlight last drawn number in blue
																						$txt_color = 'white';
																						$blink_class = 'blink'; // Add blink class for blinking effect
																						
																					} else {
																						$blink_class = ''; // No blinking for other numbers
																					}
                    
                    
                    															// Inside your loop, where you set up the cell
                    															$display_number = ($number == 0) ? "★" : $number;
                    															// Output the cell with the appropriate color and value
                    															// $html.= "<td style='background-color: $cell_color; color: $txt_color; border: 2px solid #ccc; font-size: 48px; text-align: center; vertical-align: middle;'>" . $display_number . "</td>";

																				$html .= "<td class='$blink_class' style='background-color: $cell_color; color: $txt_color; border: 2px solid #ccc; font-size: 48px; text-align: center; vertical-align: middle;'>" . $display_number . "</td>";
                    														}
                    														$html .= "</tr>"; // End the row
                    													}
                    
                    													// End the table
                    												// 	$html .= "</table>";
                    
                    													// Output the result and pattern
                    													// $html .= "Result: " . implode(', ', $drawn_numbers) . "<br/>";
                    													// $html .= "Pattern: " . $pattern . "<br/>";
                    
                    													// Now print the entire table at once
                    													 
                    
                    											}
																// elseif if($pattern==2) {
																// 	# code...
																// }
                    											// End the table
																$html.= "
																</tbody></table>
																</br></br></br>
																";

																							
													$response['status'] = "success";
													$response['message'] = "".$html;
													$response['win_status'] = $win_status;

										} else { // Cartela number is not registered
											$response['message'] = "No data found.";
											$response['status'] = "success";
										}

										// Free result set
										$result->free();
									}
									// Close the statement
									$stmt->close();
								}

								// Set content type and return the response
								header('Content-Type: application/json');
								echo json_encode($response);
								exit;
							}

				} 










				
			// Function to check if the card matches the selected pattern
			function check_pattern($card, $drawn_numbers, $pattern) {
				// Pattern logic based on value of $pattern
				switch ($pattern) {
					case 1: // any one line
						return check_any_one_line($card, $drawn_numbers);
					case 2: // any two lines
						return check_any_two_lines($card, $drawn_numbers);
					case 3: // any vertical
						return check_any_vertical($card, $drawn_numbers);
					case 4: // any horizontal
						return check_any_horizontal($card, $drawn_numbers);
					case 5: // T
						return check_t_pattern($card, $drawn_numbers);
					case 6: // reverse T
						return check_reverse_t_pattern($card, $drawn_numbers);
					case 7: // X
						return check_x_pattern($card, $drawn_numbers);
					case 8: // L
						return check_l_pattern($card, $drawn_numbers);
					case 9: // reverse L
						return check_reverse_l_pattern($card, $drawn_numbers);
					case 10: // half above
						return check_half_above($card, $drawn_numbers);
					case 11: // half below
						return check_half_below($card, $drawn_numbers);
					case 12: // full
						return check_full_pattern($card, $drawn_numbers);
					default:
						return false;
				}
			}

			// Example function for one pattern
			function check_any_one_line($card, $drawn_numbers) {
				foreach ($card as $column => $numbers) {
					$missing_numbers = array_diff($numbers, $drawn_numbers);
					if (empty($missing_numbers)) {
						return true; // Found a winning line
					} else {
						// echo "Column $column missing: " . implode(', ', $missing_numbers) . "<br/>";
					}
				}
				return false; // No winning line found
			}

			// Function to check any two lines (horizontal, vertical, or diagonal)
			function check_any_two_lines($card, $drawn_numbers) {
				$line_count = 0;

				// Check horizontal lines
				foreach ($card as $column => $numbers) {
					if (array_intersect($numbers, $drawn_numbers) == $numbers) {
						$line_count++;
					}
				}

				// Check vertical lines
				for ($i = 0; $i < 5; $i++) {
					$vertical = [$card['B'][$i], $card['I'][$i], $card['N'][$i], $card['G'][$i], $card['O'][$i]];
					if (array_intersect($vertical, $drawn_numbers) == $vertical) {
						$line_count++;
					}
				}

				return $line_count >= 2; // Return true if there are at least two lines
			}

			// Function to check any vertical line
			function check_any_vertical($card, $drawn_numbers) {
				for ($i = 0; $i < 5; $i++) {
					$vertical = [$card['B'][$i], $card['I'][$i], $card['N'][$i], $card['G'][$i], $card['O'][$i]];
					if (array_intersect($vertical, $drawn_numbers) == $vertical) {
						return true;
					}
				}
				return false;
			}

			// Function to check any horizontal line
			function check_any_horizontal($card, $drawn_numbers) {
				foreach ($card as $column => $numbers) {
					if (array_intersect($numbers, $drawn_numbers) == $numbers) {
						return true;
					}
				}
				return false;
			}

			// Function to check the 'T' pattern (top row and middle column)
			function check_t_pattern($card, $drawn_numbers) {
				$top_row = [$card['B'][0], $card['I'][0], $card['N'][0], $card['G'][0], $card['O'][0]];
				$middle_column = [$card['N'][0], $card['N'][1], $card['N'][2], $card['N'][3], $card['N'][4]];

				if (array_intersect($top_row, $drawn_numbers) == $top_row &&
					array_intersect($middle_column, $drawn_numbers) == $middle_column) {
					return true;
				}
				return false;
			}

			// Function to check the reverse 'T' pattern (bottom row and middle column)
			function check_reverse_t_pattern($card, $drawn_numbers) {
				$bottom_row = [$card['B'][4], $card['I'][4], $card['N'][4], $card['G'][4], $card['O'][4]];
				$middle_column = [$card['N'][0], $card['N'][1], $card['N'][2], $card['N'][3], $card['N'][4]];

				if (array_intersect($bottom_row, $drawn_numbers) == $bottom_row &&
					array_intersect($middle_column, $drawn_numbers) == $middle_column) {
					return true;
				}
				return false;
			}

			// Function to check the 'X' pattern (diagonal lines)
			function check_x_pattern($card, $drawn_numbers) {
				$diag1 = [$card['B'][0], $card['I'][1], $card['N'][2], $card['G'][3], $card['O'][4]];
				$diag2 = [$card['B'][4], $card['I'][3], $card['N'][2], $card['G'][1], $card['O'][0]];

				if (array_intersect($diag1, $drawn_numbers) == $diag1 &&
					array_intersect($diag2, $drawn_numbers) == $diag2) {
					return true;
				}
				return false;
			}

			// Function to check the 'L' pattern (left column and bottom row)
			function check_l_pattern($card, $drawn_numbers) {
				$left_column = [$card['B'][0], $card['B'][1], $card['B'][2], $card['B'][3], $card['B'][4]];
				$bottom_row = [$card['B'][4], $card['I'][4], $card['N'][4], $card['G'][4], $card['O'][4]];

				if (array_intersect($left_column, $drawn_numbers) == $left_column &&
					array_intersect($bottom_row, $drawn_numbers) == $bottom_row) {
					return true;
				}
				return false;
			}

			// Function to check the reverse 'L' pattern (right column and bottom row)
			function check_reverse_l_pattern($card, $drawn_numbers) {
				$right_column = [$card['O'][0], $card['O'][1], $card['O'][2], $card['O'][3], $card['O'][4]];
				$bottom_row = [$card['B'][4], $card['I'][4], $card['N'][4], $card['G'][4], $card['O'][4]];

				if (array_intersect($right_column, $drawn_numbers) == $right_column &&
					array_intersect($bottom_row, $drawn_numbers) == $bottom_row) {
					return true;
				}
				return false;
			}

			// Function to check half above pattern (top two rows)
			function check_half_above($card, $drawn_numbers) {
				$half_above = array_merge([$card['B'][0], $card['B'][1]], [$card['I'][0], $card['I'][1]], 
										[$card['N'][0], $card['N'][1]], [$card['G'][0], $card['G'][1]], 
										[$card['O'][0], $card['O'][1]]);

				return array_intersect($half_above, $drawn_numbers) == $half_above;
			}

			// Function to check half below pattern (bottom two rows)
			function check_half_below($card, $drawn_numbers) {
				$half_below = array_merge([$card['B'][3], $card['B'][4]], [$card['I'][3], $card['I'][4]], 
										[$card['N'][3], $card['N'][4]], [$card['G'][3], $card['G'][4]], 
										[$card['O'][3], $card['O'][4]]);

				return array_intersect($half_below, $drawn_numbers) == $half_below;
			}

			// Function to check full card pattern
			function check_full_pattern($card, $drawn_numbers) {
				$full_card = array_merge($card['B'], $card['I'], $card['N'], $card['G'], $card['O']);
				return array_intersect($full_card, $drawn_numbers) == $full_card;
			}

					
			?>
