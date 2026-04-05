<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

if (!isset($_SESSION['loggedin'])   || $_SESSION['role']!="cashier") {
	header("Location: ../config/logout.php");
}
// Include your database configuration
include_once '../config/Database.php';

$db = Database::getInstance();
$conn = $db->getConnection(); // Get the connection object

// ====================================================================================================
// Disable error reporting
error_reporting(0);
ini_set('display_errors', 0);
error_reporting(E_ALL);
// Set timezone and display PHP time
date_default_timezone_set('Africa/Nairobi');
// ====================================================================================================


$response = ['status' => 'error', 'message' => ''];
$start_datetime = date("Y-m-d") . " 00:00:00"; // Start of today
$end_datetime = date("Y-m-d") . " 23:59:59";   // End of today

if (isset($_POST['start_date'], $_POST['end_date'])) 
{
    $cashier_id = $_SESSION['cashier_id']; // Get cashier ID from session
    $start_datetime = $_POST['start_date'] . ' 00:00:00'; // Start date
    $end_datetime = $_POST['end_date'] . ' 23:59:59'; // End date

    // Prepare the SQL query
    $query = "
        SELECT
            p.partner_id, p.partner_full_name, p.partner_phone, 
            p.user_name, p.password AS partner_password, p.is_locked AS partner_locked, 
            p.is_blocked AS partner_blocked, p.percent AS partner_percent, 
            c.cashier_id,c.cashier_package, c.password AS cashier_password, c.is_loggedin, 
            c.loggin_time, c.is_locked AS cashier_locked, c.is_active, 
            c.login_attempts, c.speed_range, c.sound,
            t.id AS transaction_id, t.game_round, t.santim, t.sew, 
            t.income,t.bonus, t.date
        FROM partner p
        INNER JOIN cashier c ON p.partner_id = c.partner_id
        LEFT JOIN transaction t ON c.cashier_id = t.cashier_id
        WHERE c.cashier_id = ? AND t.date BETWEEN ? AND ?
    ";

    // Prepare the statement
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        $response['message'] = "Error in query: " . $conn->error;
    } else 
	{
        // Bind parameters and execute
        $stmt->bind_param('sss', $cashier_id, $start_datetime, $end_datetime);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) 
		{
            $response['status'] = 'success';
            $response['data'] = $result->fetch_all(MYSQLI_ASSOC);
        } else 
		{
            $response['message'] = "No data found for the given date.";
        }

        // Cleanup
        $stmt->free_result();
        $stmt->close();
    }
}
else
{
    $cashier_id = $_SESSION['cashier_id']; // Get cashier ID from session
    
    // Prepare the SQL query
    $query = "
        SELECT 
            p.partner_id, p.partner_full_name, p.partner_phone, 
            p.user_name, p.password AS partner_password, p.is_locked AS partner_locked, 
            p.is_blocked AS partner_blocked, p.percent AS partner_percent, 
            c.cashier_id,c.cashier_package, c.password AS cashier_password, c.is_loggedin, 
            c.loggin_time, c.is_locked AS cashier_locked, c.is_active, 
            c.login_attempts, c.speed_range, c.sound,
            t.id AS transaction_id, t.game_round, t.santim, t.sew, 
            t.income,t.bonus, t.date
        FROM partner p
        INNER JOIN cashier c ON p.partner_id = c.partner_id
        LEFT JOIN transaction t ON c.cashier_id = t.cashier_id
        WHERE c.cashier_id = ? AND t.date BETWEEN CURDATE() AND CURDATE() + INTERVAL 1 DAY - INTERVAL 1 SECOND
    ";

    // Prepare the statement
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        $response['message'] = "Error in query: " . $conn->error;
    } else 
	{
        // Bind parameters and execute
        $stmt->bind_param('s', $cashier_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) 
		{
            $response['status'] = 'success';
            $response['data'] = $result->fetch_all(MYSQLI_ASSOC);
        } else 
		{
            $response['message'] = "No data found for the given date.";
        }

        // Cleanup
        $stmt->free_result();
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ethiomark</title>
    	
    <link rel="stylesheet" href="../bootstrap/css/style.css">
    <script src="../bootstrap/js/jquery.js"></script>
    


	<link rel="stylesheet" type="text/css" href="side-nav.css">
	<link rel="stylesheet" type="text/css" href="container.css">
	<link rel="stylesheet" type="text/css" href="../bootstrap/css/cashier_option_container.css">
	<script src="pagination.js"></script>
</head>
<body>

			<!-- Loader -->
			<div id="loader">
				<div class="spinner"></div>
				<span> ethiomark.com </span>
			</div>

			<!-- Main Content -->
			<div id="content" style="display: none;">
				<!-- Your main content goes here -->
			</div>

			<style>
				/* Loader Styles */
				#loader {
					position: fixed;
					top: 0;
					left: 0;
					width: 100%;
					height: 100%;
					background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent black */
					color: #fff;
					display: flex;
					justify-content: center;
					align-items: center;
					flex-direction: column;
					font-size: 24px;
					font-weight: bold;
					z-index: 9999;
				}

				/* Rotating Spinner */
				.spinner {
					width: 50px;
					height: 50px;
					border: 10px solid rgba(255, 255, 255, 0.2);
					border-top: 10px solid #fff;
					border-radius: 50%;
					animation: spin 1s linear infinite;
					margin-bottom: 15px;
				}

				@keyframes spin {
					0% { transform: rotate(0deg); }
					100% { transform: rotate(360deg); }
				}
			</style>

			<script>
				// Hide loader and show content when page loads
				window.onload = function() {
					document.getElementById("loader").style.display = "none";
					document.getElementById("content").style.display = "block";
				};
			</script>





    <div id="customAlert" class="custom-alert" style="display: block; position: fixed; top: 20px; right: 20px; z-index: 1000;"></div>
    <div id="user-content" style="display: none;">
          


        <?php include_once("side-nav.php"); ?>






				
        <div class="main-content">
            <div id="cashier_option_content" class="cashier_option_container" style="min-width: 1700px;">                
                <!-- Partner Information -->
                <div class="cashier_option_info">
				<?php
					
					$phpTimeZone = date_default_timezone_get();
					$phpTime = date('Y-m-d H:i:s');

					// Get MySQL timezone and current time
					$sql_time = "SELECT @@global.time_zone AS global_zone, @@session.time_zone AS session_zone, NOW()";
					$result_time = $conn->query($sql_time);

					if (!$result_time) {
						// Hide the error and display a custom message without file path
						echo '<p><strong>Error fetching time zone.</strong></p>';
						exit;  // Exit after error
					}

					// Fetch and display results
					$row_time = $result_time->fetch_array(MYSQLI_NUM);
				
					?>
                    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; background-color: #000; padding: 5px;">
							<!-- Left-Aligned Section -->
							<p style="margin: 5px 0; background-color: #000; color: #fff; padding: 10px; flex: 1; text-align: left;">
								<strong style="color: #4CAF50;"><span id="cashier_option_cashier_name" class="cashier_option_name" style="font-weight: bold; color: #fff;"></span>  </strong>  
								<strong style="color: blue;"> [ <span id="remaining_balance" class="cashier_option_balance" style="font-weight: bold;"></span>  ብር ]</strong>
							</p>


							<!-- Right-Aligned Section -->
							<p style="font-size: 16px; text-align: right; margin-right: 100px; color: #fff; flex: 1;">
								<strong style="color: blue;">P [ </strong>
								<span style="color: blue;" class="cashier_option_balance"><?php echo $phpTimeZone . ' : ' . $phpTime; ?> ]</span>

								&nbsp;&nbsp;&nbsp;

								<strong style="color: green;">[</strong>
								<strong style="color: green;">  </strong>
								<span style="color: green;" class="cashier_option_balance"><?php echo $row_time[2] . '   ]'; ?></span>
							</p>
					</div>



                    <p hidden><strong>Cashier Name:</strong> <span id="cashier_option_cashier_name" class="cashier_option_name"></span></p>
					
					
					<form id="report_form" method="POST" action="report.php" class="cashier_option_report_form" style="display: flex; align-items: center; gap: 10px;">
						<div class="mini-box">
							<label for="report_choice" class="cashier_option_label">Report Choice &nbsp;&nbsp;</label>
							<select id="report_choice" class="cashier_option_select required cashier_option_date_input date-inputs">
								<option value="today">Today</option>
								<option value="yesterday">Yesterday</option>
								<option value="this_week">This Week</option>
								<option value="last_week">Last Week</option>
								<option value="this_month">This Month</option>
								<option value="last_month" hidden>Last Month</option>
							</select>
						</div>
						
						<div class="mini-box">
							<label for="start_date" class="cashier_option_label">From Date  &nbsp;&nbsp;</label>
							<input type="date" name="start_date" id="start_dates" required class="cashier_option_date_input date-inputs">
						</div>

						<div class="mini-box">
							<label for="end_date" class="cashier_option_label">To Date  &nbsp;&nbsp;</label>
							<input type="date" name="end_date" id="end_dates" required class="cashier_option_date_input date-inputs">
						</div>

						<input type="hidden" name="report_cashiername" id="report_cashiernames" required class="cashier_option_hidden_input">
						
						<button type="submit" name="generate_report" value="Generate Report" class="cashier_option_submit_button">Search</button>
					</form>

					<script>
						document.getElementById("report_choice").addEventListener("change", function() {
							let startDateInput = document.getElementById("start_dates");
							let endDateInput = document.getElementById("end_dates");
							let today = new Date();
							let startDate, endDate;

							switch (this.value) {
								case "today":
									startDate = endDate = today;
									break;
								case "yesterday":
									startDate = new Date(today);
									startDate.setDate(today.getDate() - 1);
									endDate = startDate;
									break;
								case "this_week":
									startDate = new Date(today);
									startDate.setDate(today.getDate() - today.getDay()); // Start of week (Sunday)
									endDate = today;
									break;
								case "last_week":
									startDate = new Date(today);
									startDate.setDate(today.getDate() - today.getDay() - 7); // Previous week's Monday
									endDate = new Date(startDate);
									endDate.setDate(startDate.getDate() + 6); // Last week's Sunday
									break;
								case "this_month":
									startDate = new Date(today.getFullYear(), today.getMonth(), 1);
									endDate = today;
									break;
								case "last_month":
									startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
									endDate = new Date(today.getFullYear(), today.getMonth(), 0); // Last day of last month
									break;
								default:
									startDate = endDate = null; // Custom date selection
							}

							if (startDate && endDate) {
								startDateInput.value = startDate.toISOString().split('T')[0];
								endDateInput.value = endDate.toISOString().split('T')[0];
							} else {
								startDateInput.value = "";
								endDateInput.value = "";
							}
						});
					</script>

                </div>
                
                <!-- Date Range Report -->
                <div id="report_section" class="cashier_option_report_section">

					

                    <div class="cashier_option_table_container">
                       
                        
                        <?php if ($response['status'] === 'success'): 
							
							$totalBet = 0;
							$totalPayed = 0;
							$totalNetBalance = 0;
							$totalIncome = 0;
							$total_bonus=0;

							?>


							<label for="itemsPerPage" class="cashier_option_label">Items per page:</label>
							<select id="itemsPerPage" class="cashier_option_items_per_page">
								<option value="10">10</option>
								<option value="20">20</option>
								<option value="50">50</option>
								<option value="100">100</option>
							</select>
							<input type="text" id="searchInput_report" placeholder="Search by Card..." class="cashier_option_search_input">
                            <table class=" cashier_option_table table-bordered" id="report-dataTable">
                                <thead>
                                    <tr>
                                        <th class="cashier_option_table_header">Round</th>
										<th class="cashier_option_table_header">Total Card</th>
										<th class="cashier_option_table_header">Birr</th>
                                        <th class="cashier_option_table_header">Total Bet</th>
                                        <th class="cashier_option_table_header">Paid</th>
                                        <th class="cashier_option_table_header">Income </th>
										<th class="cashier_option_table_header">bonus </th>
										<th class="cashier_option_table_header">Income - bonus  </th>
                                        <th class="cashier_option_table_header">Time</th>
                                    </tr>
                                </thead>
                                <tbody id="cashier_report_display_table" class="cashier_option_table_body">
                                    <?php foreach ($response['data'] as $row): 
                                        
										$santim = floatval($row['santim']);
										$sew = floatval($row['sew']);
										$income = floatval($row['income']);

										

										// Accumulate totals
										$totalBet += ($santim * $sew);
										$totalPayed += (($santim * $sew)-$income); // Assuming this is how total paid is calculated
										$totalIncome += $income;
										$total_bonus+= floatval($row['bonus']);
							
										?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['game_round']); ?></td>
											<td><?php echo htmlspecialchars($row['sew']); ?></td>
											<td><?php echo htmlspecialchars($row['santim']); ?></td>
											<td><?php echo number_format(($row['santim'] * $row['sew']), 2); ?></td>
                                            <td><?php echo number_format(($row['santim'] * $row['sew'])-$row['income'], 2); ?></td>
                                            <td><?php echo number_format($row['income'], 2); ?></td>
											<td><?php echo number_format($row['bonus'], 2); ?></td>
											<td><?php echo number_format($row['income']-$row['bonus'], 2); ?></td>
                                            <td><?php echo htmlspecialchars($row['date']); ?></td>
                                        </tr>
										
                                    <?php endforeach; ?>
									<!-- Totals Row -->
									
                                </tbody>
								<!-- Totals Row Outside Pagination -->
								<tfoot>
									<tr>
										<td colspan="3">
                                            <nobr>Total<strong style="color:#f39292">
                                                &nbsp;(<?php echo date("Y-m-d", strtotime($start_datetime)) . " - " . date("Y-m-d", strtotime($end_datetime)); ?>)
                                            </strong></nobr>
                                        </td>
										<td><strong><?php echo number_format($totalBet, 2); ?></strong></td>
										<td><strong><?php echo number_format($totalPayed, 2); ?></strong></td>
										<td><strong><?php echo number_format($totalIncome, 2); ?></strong></td>
										<td><strong><?php echo number_format($total_bonus, 2); ?></strong></td>
										<td>
											<strong class="cashier_option_table_td_fade_in"><?php echo number_format($totalIncome - $total_bonus, 2); ?> ብር</strong>
										</td>
										<td></td>
									</tr>
								</tfoot>
                            </table>
                            <div id="pagination" class="cashier_option_pagination"></div>
                        <?php else: ?>
                            <p style="color:white;font-size:30px;"><?php echo htmlspecialchars($response['message']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
		
	<script>

			// Simulate login check (replace this with real check)
			const isLoggedIn = localStorage.getItem('loggedin') === 'true';
			// DOM elements
			const userContent = document.getElementById('user-content');

			// If the user is logged in, show user content
			// If the user is not logged in, show overly
			if (isLoggedIn) {
				userContent.style.display = 'block';
			}else
            {
                window.location.href = '../config/logout.php'
            }
			// Handle logout
			const logoutBtn = document.getElementById('logoutbtn');
			logoutBtn.addEventListener('click', function() {

				localStorage.removeItem('loggedin');
				localStorage.removeItem('cashier_id');
				showAlert('Successfully loggedout!', 'success');
				setTimeout(function() {
					window.location.href = '../config/logout.php'
				}, 1000);
				
			});


			

			// Function to show alert in the top right corner
			function showAlert(message, type) 
			{
				const alertDiv = $('#customAlert');
				alertDiv.html(message);
				alertDiv.removeClass('custom-alert-success custom-alert-danger slide-out'); // Clear previous alert classes
				alertDiv.addClass('custom-alert-' + type + ' slide-in'); // Add the appropriate class and slide-in

				alertDiv.show(); // Show the alert

				// Animate the alert by adding the slide-in class
				setTimeout(function() {
					alertDiv.removeClass('slide-in'); // Remove slide-in for smooth transition
				}, 50); // Delay to allow for CSS transition to kick in

				// Hide the alert after 3 seconds
				setTimeout(function() {
					alertDiv.addClass('slide-out'); // Add slide-out class for hiding animation
					setTimeout(function() {
						alertDiv.fadeOut(); // Fade out after the slide-out animation
					}, 300); // Wait for the slide-out transition to complete
				}, 3000);
			}
			// Function to set today's date as default value for the input fields
			function setDefaultDates() 
			{
				var today = new Date();
				var year = today.getFullYear();
				var month = String(today.getMonth() + 1).padStart(2, '0');  // padStart ensures two digits for month
				var day = String(today.getDate()).padStart(2, '0'); // padStart ensures two digits for day

				var formattedDate = year + '-' + month + '-' + day; // Format date as YYYY-MM-DD

				// Set both the from and to date inputs to today's date
				document.getElementById("start_dates").value = formattedDate;
				document.getElementById("end_dates").value = formattedDate;
			}



			//========================================================================================
			//========================================================================================
			//========================================================================================
			//=============================load cashier information====================================
			if (isLoggedIn) 
			{
					$(document).ready(function() 
					{
						// Function to set today's date as default value for the input fields
						setDefaultDates();


						var form_data = {
							get_cashier_data: localStorage.getItem('cashier_id'),
						};

						$.ajax({
							url: "../config/DbFunction.php",
							method: "POST",
							data: form_data,
							dataType: "JSON",
							success: function(resp) {
								// Check if the response contains data
								if (resp.length > 0) {
									// Assuming the first record contains the cashier data
									var cashier = resp[0]; // If there's at least one record
									
									// Set the cashier profile name from the fetched data
									document.getElementById("cashier_profile_name").innerHTML = cashier.cashier_id;
									document.getElementById("report_cashiernames").value = cashier.cashier_id;
									document.getElementById("cashier_option_cashier_name").innerHTML = resp[0].cashier_id;
									let formattedNumber = new Intl.NumberFormat('en-US').format(cashier.cashier_package);
									document.getElementById("remaining_balance").innerHTML = formattedNumber;
								} else {
									// If no data found, you can handle the case accordingly
									console.log("No cashier data found.");
									showAlert('No data found for this cashier!', 'danger');
								}
							},
							error: function(jqXHR, textStatus, errorThrown) {
                                // Log detailed error information
                                console.error('Error Details:');
                                console.error('Status: ' + textStatus);
                                console.error('Error Thrown: ' + errorThrown);
                                console.error('Response Text: ' + jqXHR.responseText);
                                showAlert("An error occurred while updating the partner. Check console for details.", "danger");
                            }
						});
					});





					
					
				
					
				
					
										
			}
			
			


			// Call the function to initialize the table
			initializeTable('report-dataTable', 'pagination', 'searchInput_report', 'itemsPerPage');


	</script>











		<div style="margin-left: 100px;margin-top: 10px;font-size:12px;text-align: center;bottom-margin: 50px;">
				<?php

					


						if (session_status() === PHP_SESSION_NONE) {
							session_start();
						}
						$cashier_id = isset($_SESSION['cashier_id']) ? $_SESSION['cashier_id'] : '';

						include_once '../config/Database.php';
						$db = Database::getInstance();
						$conn = $db->getConnection();


						// Fetch partner id
						$sql = "SELECT partner_id FROM cashier WHERE cashier_id = ?";
						// echo "Debug: Preparing to fetch current local package for cashier_id = " . $cashier_id . "\n";  // Removed
						$stmt1 = $conn->prepare($sql);
						$stmt1->bind_param("s", $cashier_id);
						$stmt1->execute();
						$stmt1->store_result();
						$stmt1->bind_result($partner_id);
						$stmt1->fetch();

						// Remote server URL
						$remote_url = "https://admin.bingo.ethiomark.com/admin/sync_cashier_package.php"; // Remote script URL

						// Step 1: Fetch cashier_package from the remote server
						$data = [
							'cashier_id' => $cashier_id,
							'partner_id'=> $partner_id,
							'request_type'=>"fetch_cashier_package"
						];
						// echo "Debug: Sending request to remote server with cashier_id = " . $cashier_id . "\n";  // Removed for less verbosity

						$ch = curl_init();
						curl_setopt($ch, CURLOPT_URL, $remote_url);
						curl_setopt($ch, CURLOPT_POST, true);
						curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

						// Disable SSL verification for development (you can enable this for production with proper certs)
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // Disable SSL verification
						curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  // Disable host verification (for development purposes only)

						$response = curl_exec($ch);

						// Check for cURL errors
						if (curl_errno($ch)) {
							// echo "Debug: cURL Error - " . curl_error($ch) . "\n";  // Only keep this for cURL error troubleshooting
							echo "cURL Error: Offline\n";
							curl_close($ch);
							exit;
						}

						curl_close($ch);

						// Debug: Raw response from remote server (optional for debugging only)
						// echo "Debug: Raw Response: " . $response . "\n";  // Optional - can be removed for production

						$response_data = json_decode($response, true);

						// Check if the JSON decoding is successful
						if ($response_data === null) {
							echo "Error decoding JSON response: " . json_last_error_msg() . "\n";
							exit;
						}

						if ($response_data['status'] == 'success') 
						{
							// Reduced debug information here
							// echo "Debug: Remote server response successful. Received cashier_package: " . $response_data['cashier_package'] . "\n"; // Removed for clarity
							$remote_package = $response_data['cashier_package'];

							if ($remote_package > 0 || $remote_package <= -500) {
								// Fetch current local package
								$sql = "SELECT cashier_package FROM cashier WHERE cashier_id = ?";
								// echo "Debug: Preparing to fetch current local package for cashier_id = " . $cashier_id . "\n";  // Removed
								$stmt = $conn->prepare($sql);
								$stmt->bind_param("s", $cashier_id);
								$stmt->execute();
								$stmt->store_result();
								$stmt->bind_result($local_package);
								$stmt->fetch();

								// echo "Debug: Current local package is " . $local_package . "\n";  // Removed for less verbosity
								$new_local_package = $local_package + $remote_package;
								// echo "Debug: New local package value will be " . $new_local_package . "\n";  // Removed
								$stmt->close();

								// Update local package
								$update_sql = "UPDATE cashier SET cashier_package = ? WHERE cashier_id = ?";
								// echo "Debug: Preparing to update local package for cashier_id = " . $cashier_id . "\n";  // Removed
								$stmt = $conn->prepare($update_sql);
								$stmt->bind_param("ds", $new_local_package, $cashier_id);
								if ($stmt->execute()) {
									echo "Local package updated successfully.\n";

									// Step 3: Send confirmation back to the remote server
									$confirm_data = [
										'cashier_id' => $cashier_id,
										'partner_id'=> $partner_id,
										'request_type'=>"cashier_package_confirm_is_ok"
									];
							
									// echo "Debug: Sending confirmation to remote server for cashier_id = " . $cashier_id . "\n";  // Removed
									$ch = curl_init();
									curl_setopt($ch, CURLOPT_URL, $remote_url);
									curl_setopt($ch, CURLOPT_POST, true);
									curl_setopt($ch, CURLOPT_POSTFIELDS, $confirm_data);
									curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

									// Disable SSL verification for confirmation request (only for development)
									curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  
									curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 

									$confirm_response = curl_exec($ch);
									curl_close($ch);

									// Debug: Raw confirmation response from remote server (optional)
									// echo "Debug: Raw confirmation response: " . $confirm_response . "\n";  // Optional - can be removed for production

									$confirm_response_data = json_decode($confirm_response, true);
									if ($confirm_response_data['status'] == 'success') {
										echo "Remote package updated successfully.\n";
									} else {
										echo "Error updating remote package \n";
									}
								} else {
									echo "Error updating local package:  ";
								}
								$stmt->close();
							} else {
								echo "No valid package found on the remote server.\n";
							}


							// Sending the request to fetch cashier information for update
								$update_cashier_information = [
									'cashier_id' => $cashier_id,
									'partner_id' => $partner_id,
									'request_type' => "get_cashier_information_for_update"
								];

								$ch = curl_init();
								curl_setopt($ch, CURLOPT_URL, $remote_url);
								curl_setopt($ch, CURLOPT_POST, true);
								curl_setopt($ch, CURLOPT_POSTFIELDS, $update_cashier_information);
								curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

								// Disable SSL verification for development (not recommended for production)
								curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  
								curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 

								$response = curl_exec($ch);
								curl_close($ch);

								if (!$response) {
									echo "Error fetching data from remote server.\n";
									exit;
								}

								$response_data = json_decode($response, true);

								if ($response_data['status'] == 'success') {
									// Step 2: Check the remote data (cashier data)
									$remote_cashier_data = $response_data['data']; // The data returned from the remote server

									// Extract remote cashier data
									$is_locked_remote = $remote_cashier_data['is_locked'];
									$is_active_remote = $remote_cashier_data['is_active'];
									$login_attempts_remote = $remote_cashier_data['login_attempts'];
									$category_remote = $remote_cashier_data['category'];
									$cashier_profit_remote = $remote_cashier_data['cashier_profit'];
									$cashier_bonus_status_remote = $remote_cashier_data['cashier_bonus_status'];
									$cashier_fixed_bonus_amount_remote = $remote_cashier_data['cashier_fixed_bonus_amount'];

									$sql = "SELECT is_locked, is_active, login_attempts, category, cashier_profit, cashier_bonus_status, cashier_fixed_bonus_amount FROM cashier WHERE cashier_id = ? AND partner_id = ?";
									$stmt = $conn->prepare($sql);
									$stmt->bind_param("ss", $cashier_id, $partner_id);
									$stmt->execute();
									$stmt->store_result();
									$stmt->bind_result($is_locked_local, $is_active_local, $login_attempts_local, $category_local, $cashier_profit_local, $cashier_bonus_status_local, $cashier_fixed_bonus_amount_local);

									if ($stmt->fetch()) {
										// Step 4: Check for differences and update the local database if necessary
										$update_sql = "UPDATE cashier SET 
														is_locked = ?, 
														is_active = ?, 
														login_attempts = ?, 
														category = ?, 
														cashier_profit = ?, 
														cashier_bonus_status = ?, 
														cashier_fixed_bonus_amount = ? 
													WHERE cashier_id = ? AND partner_id = ?";
										
										// Check for differences before updating
										$should_update = false;

										if ($is_locked_remote != $is_locked_local) $should_update = true;
										if ($is_active_remote != $is_active_local) $should_update = true;
										if ($login_attempts_remote != $login_attempts_local) $should_update = true;
										if ($category_remote != $category_local) $should_update = true;
										if ($cashier_profit_remote != $cashier_profit_local) $should_update = true;
										if ($cashier_bonus_status_remote != $cashier_bonus_status_local) $should_update = true;
										if ($cashier_fixed_bonus_amount_remote != $cashier_fixed_bonus_amount_local) $should_update = true;

										if ($should_update) {
											// Proceed with updating the local database
											$stmt = $conn->prepare($update_sql);
											$stmt->bind_param("sssssssss", 
												$is_locked_remote, 
												$is_active_remote, 
												$login_attempts_remote, 
												$category_remote, 
												$cashier_profit_remote, 
												$cashier_bonus_status_remote, 
												$cashier_fixed_bonus_amount_remote, 
												$cashier_id, 
												$partner_id
											);
											if ($stmt->execute()) {
												echo "\n <br>Cashier data updated successfully.\n";
											} else {
												echo "\n <br>Error updating cashier data.\n";
											}
										} else {
											echo "\n <br>No changes detected in cashier data. No update needed.\n";
										}
									} else {
										echo "\n <br> Cashier data not found for the given cashier_id and partner_id.\n";
									}
									$stmt->close();
								} else {
									echo "\n <br>Error fetching cashier information from the remote server: " . $response_data['message'] . "\n";
								}



						} else {
							echo "Error fetching data from remote server: " . $response_data['message'] . "\n";
						}

						?>

		</div>
</body>
</html>