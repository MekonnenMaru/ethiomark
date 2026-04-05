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
            t.income,t.bonus, t.date, t.result, t.checked_number
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
            $response['message'] = "No data found for the given date. ";
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
            t.income,t.bonus, t.date, t.result, t.checked_number
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
    <meta name="viewport" content="width=1280, user-scalable=no">
    <title>Ethiomark</title>
    	
    <link rel="stylesheet" href="../bootstrap/css/style.css">
    <script src="../bootstrap/js/jquery.js"></script>
	<script src="../cashier/pagination.js"></script>
    


	<link rel="stylesheet" type="text/css" href="side-nav.css">
	<link rel="stylesheet" type="text/css" href="container.css">
	<link rel="stylesheet" type="text/css" href="../bootstrap/css/cashier_option_container.css?v=2">


<link rel="manifest" href="/manifest.json">
	<script>
      if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/service-worker.js')
          .then(registration => {
            console.log('Service Worker registered with scope:', registration.scope);
          })
          .catch(error => {
            console.log('Service Worker registration failed:', error);
          });
      }
    </script>
</head>
<body>
    <div id="customAlert" class="custom-alert" style="display: block; position: fixed; top: 20px; right: 20px; z-index: 1000;"></div>
    <div id="user-content" style="display: none;">
          
<script>
  document.addEventListener('contextmenu', function (e) {
    e.preventDefault();
    showAlert('Hello! You are using Ethiomark Bingo.', "success"); 
  });
</script>

        <?php include_once("side-nav.php"); ?>






				
        <div class="main-content">
            <div id="cashier_option_content" class="cashier_option_container" style="min-width: 1700px;">                
                <!-- Partner Information -->


				<?php
					// Get last transaction (simplified)
					$last = $conn->query("SELECT amount, action, timestamp 
										FROM transaction_logs
										WHERE cashier_id = '{$_SESSION['cashier_id']}' 
										ORDER BY timestamp DESC LIMIT 1")->fetch_assoc();

					$amount = $last ? number_format($last['amount'], 2) : '0.00';
					$action = $last ? (strpos($last['action'], 'deposit') !== false ? 'deposit' : 'withdrawal') : 'none';
					$time = $last ? date('M j, g:i a', strtotime($last['timestamp'])) : 'Never';
					?>

				<div class="cashier-dashboard-connected compact">
					<div class="cashier-dashboard-section cashier-id">
						<span class="cashier-dashboard-label">Cashier ID</span>
						<span class="cashier-dashboard-value"  id = "dashboard_cashier_id"><?php echo  $_SESSION['cashier_id']; ?></span>
					</div>
					<div class="cashier-dashboard-section remaining-package">
						<span class="cashier-dashboard-label">Remaining Package</span>
						<span class="cashier-dashboard-value" id="remaining_balance"><span id="remaining_balance" ></span> Birr</span>
					</div>

					
					<div class="cashier-dashboard-section last-deposit">
						<span class="cashier-dashboard-label">Last Transaction</span>
						<span class="cashier-dashboard-value" id="dashboard_last_deposit">
						<?php 
						if (!empty($last) && isset($last['amount'], $last['action'], $last['timestamp'])) {?>
							<?= $amount ?> ብር  <?= $action ?> at <?= $time ?> 
						<?php } else {
							echo 'No transaction';
						}
						?>
					</span>
					</div>
					
				</div>
				

				
                <div class="cashier_option_info">
                    <p><strong>Cashier Name:</strong> <span id="cashier_option_cashier_name" class="cashier_option_name"></span></p>
					
					<form id="report_form" method="POST" action="report.php" class="cashier_option_report_form" style="display: flex; align-items: center; gap: 10px;">
						<div class="mini-box">
							<label for="report_choice" class="cashier_option_label"> &nbsp;&nbsp;</label>
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
						// For the Search button in the report form
						document.getElementById('report_form').addEventListener('submit', function (e) {
								e.preventDefault(); // Stop immediate form submission

								const form = this;
								const submitBtn = form.querySelector('button[type="submit"]');
								const startDateInput = document.getElementById('start_dates');
								const endDateInput = document.getElementById('end_dates');

								const startDate = new Date(startDateInput.value);
								const endDate = new Date(endDateInput.value);
								const today = new Date();
								const twoMonthsAgo = new Date();
								twoMonthsAgo.setMonth(today.getMonth() - 2);

								// Validate: from date must be within last 2 months
								if (startDate < twoMonthsAgo) {
									showAlert("ከሁለት ወር በላይ የሆነዉን ዳታ ማየት አይችሉም.", "danger");
									return;
								}

								// Validate: from date must be before or same as to date
								if (startDate > endDate) {
									showAlert("'From Date' ከ todate መብለጥ የለበትም ", "danger");
									return;
								}

								// Disable the button and show loading
								submitBtn.disabled = true;
								submitBtn.innerText = 'Searching...';
								submitBtn.style.opacity = '0.6';
								submitBtn.style.cursor = 'not-allowed';

								// Delay for 1 second before submitting
								setTimeout(() => {
									form.submit();
								}, 1000);
							});



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
                                        
                                        <th class="cashier_option_table_header">Bet Time</th>
                                        <th class="cashier_option_table_header">Round</th>
                                        <th class="cashier_option_table_header">On Call</th>
										<th class="cashier_option_table_header" style="font-weight: bold; color: #4CAF50; text-align: left; padding: 10px;">Winner Number</th>

										<th class="cashier_option_table_header">Total Card</th>
										<th class="cashier_option_table_header">Birr</th>
                                        <th class="cashier_option_table_header">Total Bet</th>
                                        <th class="cashier_option_table_header">Paid</th>
                                        <th class="cashier_option_table_header">Income </th>
										<th class="cashier_option_table_header">bonus </th>
										<th class="cashier_option_table_header">Income - bonus  </th>
                                        <th class="cashier_option_table_header">Action</th>
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
                                            <td><?php echo htmlspecialchars($row['date']); ?></td>
                                            <td><?php echo htmlspecialchars($row['game_round']); ?></td>
                                            <td><?php echo count(array_filter(explode(',', $row['result']))); ?></td>
											<td style="font-size: 16px; font-family: Arial, sans-serif; text-align: center; padding: 10px; 
												<?php 
												$checkedNumbers = explode(',', $row['checked_number']); // Split numbers into an array
												$checkedNumbers = array_map('trim', $checkedNumbers); // Remove any extra spaces

												// Check if the array is empty or contains only empty strings
												$count = count(array_filter($checkedNumbers));

												// Apply color based on the number of results
												if ($count >= 1) {
													echo "color:rgb(125, 255, 116);"; // Exactly one result
												} else {
													echo "color: rgb(255, 147, 147);"; // Default case, red
												}
												?>">
												<?php echo " [".$row['checked_number']. " ]"; ?>
											</td>



											<td><?php echo htmlspecialchars($row['sew']); ?></td>
											<td><?php echo htmlspecialchars($row['santim']); ?></td>
											<td><?php echo number_format(($row['santim'] * $row['sew']), 2); ?></td>
                                            <td><?php echo number_format(($row['santim'] * $row['sew'])-$row['income'], 2); ?></td>
                                            <td><?php echo number_format($row['income'], 2); ?></td>
											<td><?php echo number_format($row['bonus'], 2); ?></td>
											<td><?php echo number_format($row['income']-$row['bonus'], 2); ?></td>
                                            <td><button class="action-button">Action</button></td>
                                        </tr>
										
                                    <?php endforeach; ?>
									<!-- Totals Row -->
									
                                </tbody>
								<!-- Totals Row Outside Pagination -->
								<tfoot>
									<tr>
										<td colspan="6">
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
</body>
</html>