<?php
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}
		
		if (!isset($_SESSION['loggedin'])   || $_SESSION['role']!="cashier") {
			header("Location: ../config/logout.php");
		}
		 // // Set session timeout duration (5 minutes = 300 seconds)
        $timeout_duration = 7200000;//2hr // 5 minutes = 300,000 ms
        
        // Check if last activity is set in the session
        if (isset($_SESSION['last_activity'])) {
            // Calculate elapsed time since last activity
            $elapsed_time = time() - $_SESSION['last_activity'];
        
            // If the elapsed time exceeds the timeout duration, destroy the session
            if ($elapsed_time > $timeout_duration) {
                session_unset();
                session_destroy();
                // Optionally, redirect to a login page
                header("Location: ../cashier/index.html");
                exit();
            }
        }
        
        // Update the last activity timestamp
        $_SESSION['last_activity'] = time();
        
        
        
        
		
		include_once '../config/Database.php';
	
		$db = Database::getInstance();
		$conn = $db->getConnection(); // Get the connection object

		$response = array('status' => 'error', 'message' => 'An error occurred.');

		$cashier_id = isset($_SESSION['cashier_id']) ? $_SESSION['cashier_id'] : '';
		$round_number = 1;
		$cartela_number = '';
		$speed_range = 3;
		$pattern = 1;
		$sound = 1;
		$price = 0;
		$sew=0;
		$birr=0;
		$bala=0;
		$profit=0;
		$bonus_eligiblity_starts_from = 0;
		$start_get_mony_from = 0 ;
		$lock_round_up_last_digit = 0;//
		
		// Clear specific session variables
		unset($_SESSION['sew']);
		unset($_SESSION['birr']);
		unset($_SESSION['bala']);

		if ($cashier_id) {
			$query = "
				SELECT 
					game.`round_number`, 
					game.`cartela_number`, 
					game.`iscompleted`, 
					cashier.`speed_range`, 
					cashier.`sound`,
					cashier.`is_active`,
					cashier.`cashier_package`, 
					cashier.cashier_id,
					game.`price`, 
					game.`pattern`, 
					partner.`is_blocked`,
					partner.`profit`,
					partner.`bonus_eligiblity_starts_from`,
					partner.`start_get_mony_from`,
					partner.`lock_round_up_last_digit`
				FROM `game` 
				JOIN `cashier` ON game.`cashier_id` = cashier.`cashier_id`
				JOIN `partner` ON cashier.`partner_id` = partner.`partner_id`
				WHERE 
					game.`cashier_id` = ?
				ORDER BY game.`round_number` DESC
				LIMIT 1;

			";

			$stmt = $conn->prepare($query);
			$stmt->bind_param("s", $cashier_id);
			$stmt->execute();
			$result = $stmt->get_result();

			if ($row = $result->fetch_assoc()) 
			{
				if ($row['iscompleted'] == 0) 
				{
					$round_number = $row['round_number'];
					$cartela_number = str_replace(' ', '', $row['cartela_number']);


					$price = $row['price'];
					$numbersArray = array_filter(array_map('trim', explode(',', $row['cartela_number']))); // Split, trim, and filter
					$sew = count($numbersArray); // Count of numbers
					$birr = $row['price'];
					$bala = $row['cashier_package'];
					$profit =  $row['profit'];
					$bonus_eligiblity_starts_from = $row['bonus_eligiblity_starts_from'];
					$start_get_mony_from = $row['start_get_mony_from'] ;
					$lock_round_up_last_digit = $row['lock_round_up_last_digit'] ;
					
					
					if ($row['is_blocked'] == "1")
					{
						echo '
							<div id="overlay" style="
								position: fixed; 
								top: 0; 
								left: 0; 
								width: 100%; 
								height: 100%; 
								background: rgba(0, 0, 0, 0.7); 
								color: white; 
								display: flex; 
								align-items: center; 
								justify-content: center; 
								z-index: 9999;">
								<div style="text-align: center;">
									<h2>Blocked Partnners Account</h2>
									<p>Please contact your admin to continue.</p>
								</div>
							</div>
						';
					}

					if ($row['is_active'] == "1")
					{
						echo '
							<div id="overlay" style="
								position: fixed; 
								top: 0; 
								left: 0; 
								width: 100%; 
								height: 100%; 
								background: rgba(0, 0, 0, 0.7); 
								color: white; 
								display: flex; 
								align-items: center; 
								justify-content: center; 
								z-index: 9999;">
								<div style="text-align: center;">
									<h2>Sorry, your account is locked out!</h2>
									<p>Please contact <i style="color:#EF6769;"> 0918101037 </i> for assistance.</p>
								</div>
							</div>
						';
					}


					if ($row['cashier_package'] <= "100") 
					{
						echo '
						<div id="overlay" style="
							position: fixed; 
							top: 0; 
							left: 0; 
							width: 100%; 
							height: 100%; 
							background: rgba(0, 0, 0, 0.7); 
							color: white; 
							display: flex; 
							align-items: center; 
							justify-content: center; 
							z-index: 9999;
						">
							<div style="text-align: center;">
								<a href="../cashier/report.php" 
                                   style="display: inline-block; text-decoration: none; color: black; background-color: white; padding: 20px; margin-bottom: 20px; width: 200px; font-size: 30px; text-align: center; border: 2px solid black; border-radius: 8px;">
                                   Go to Report
                                </a>

								<h2>Insufficient Balance {'.$row['cashier_id']." = ".$row['cashier_package'] .' ብር}</h2>
								<p>Please recharge your balance to continue.</p>
							</div>
						</div>
						';
					}



				} 
				else 
				{
					$round_number = $row['round_number'] + 1;

					
					if ($row['is_blocked'] == "1") 
					{
						echo '
						<div id="overlay" style="
							position: fixed; 
							top: 0; 
							left: 0; 
							width: 100%; 
							height: 100%; 
							background: rgba(0, 0, 0, 0.7); 
							color: white; 
							display: flex; 
							align-items: center; 
							justify-content: center; 
							z-index: 9999;
						">
							<div style="text-align: center;">
								<h2>Blocked Partnners Account</h2>
								<p>Please contact your admin to continue.</p>
							</div>
						</div>
						';
					}


					if ($row['is_active'] == "1")
					{
						echo '
							<div id="overlay" style="
								position: fixed; 
								top: 0; 
								left: 0; 
								width: 100%; 
								height: 100%; 
								background: rgba(0, 0, 0, 0.7); 
								color: white; 
								display: flex; 
								align-items: center; 
								justify-content: center; 
								z-index: 9999;">
								<div style="text-align: center;">
									<h2>Sorry, your account is locked out!</h2>
									<p>Please contact <i style="color:red;"> 0918101037 </i> for assistance.</p>
								</div>
							</div>
						';
					}


					if ($row['cashier_package'] <= "100") 
					{
						echo '
						<div id="overlay" style="
							position: fixed; 
							top: 0; 
							left: 0; 
							width: 100%; 
							height: 100%; 
							background: rgba(0, 0, 0, 0.7); 
							color: white; 
							display: flex; 
							align-items: center; 
							justify-content: center; 
							z-index: 9999;
						">
							<div style="text-align: center;">
							<a href="../cashier/report.php" 
                               style="display: inline-block; text-decoration: none; color: black; background-color: white; padding: 20px; margin-bottom: 20px; width: 200px; font-size: 30px; text-align: center; border: 2px solid black; border-radius: 8px;">
                               Go to Report
                            </a>

								<h2>Insufficient Balance {'.$row['cashier_id']." = ".$row['cashier_package'] .' ብር}</h2>
								<p>Please recharge your balance to continue.</p>
							</div>
						</div>
						';
					}
				}
				
				$speed_range = $row['speed_range'];
				$pattern = $row['pattern'];
				$sound = $row['sound'];
			}

			// Store values in session for later use
			$_SESSION['sew'] = !empty($cartela_number) ? count(explode(',', $cartela_number)) : 0;
			$_SESSION['birr'] = $price;
			$_SESSION['bala'] = 10000; // Assuming a constant value for now
		}

		// Render the HTML
		
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.5">
    <title>Ethiomark Bingo</title>
	

	 <!-- Default Stylesheets -->
	 <link id="lightStyleSheet" rel="stylesheet" href="../bootstrap/css/style.css">


	<link rel="stylesheet" type="text/css" href="side-nav.css">
	<link rel="stylesheet" type="text/css" href="container.css">

	

	
	<style>
	 body {
      font-family: Arial;
      /* text-align: center; */
      padding: 50px;
      margin-left: 50px;
    }
    canvas {
      margin-top: 10px;
      border: 0px solid #000;
    }
    .controls {
      margin-bottom: 20px;
    }
    .pointer {
      position: relative;
      margin: 0 auto;
      width: 20px;
      height: 20px;
      margin-bottom: -20px;
      display:none;
    }
    .pointer:after {
      content: '';
      position: absolute;
      top: 0;
      left: 50%;
      transform: translateX(-50%) rotate(0deg);
      width: 0;
      height: 0;
      border-left: 10px solid transparent;
      border-right: 10px solid transparent;
      border-top: 20px solid rgb(18, 141, 73);
    }
    /* Popup styles */
   /* Hidden checkbox to control the popup visibility */


    /* Popup content */
    .popup {
        display: block;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(0, 0, 0, 1);
        padding: 30px;
        border-radius: 10px;
        color: #fff;
        text-align: center;
        box-shadow: 100px 40px 15px rgba(0, 0, 0, 0.9);
        font-size: 18px;
        max-width: 80%;
        z-index: 900;
        opacity: 0;
        transform-origin: center;
        transition: opacity 1s ease-in-out, transform 3s ease-in-out; /* Increased duration for zoom */
    }

    .popup.show {
        display: block;
        opacity: 1;
        transform: translate(-50%, -50%) scale(2); /* Slower zoom with a slight increase */
    }

    .popup h2 {
      font-size: 24px;
      margin-bottom: 10px;
      color: green;
    }
    .popup .winner {
      font-size: 30px;
      font-weight: bold;
      color: red;
    }
    .popup .amount {
      font-size: 25px;
      color: #ffffff;
      margin-top: 10px;
    }
    .popup .close-btn {
      position: absolute;
      top: 10px;
      right: 10px;
      background-color: transparent;
      border: none;
      color: white;
      font-size: 20px;
      cursor: pointer;
      z-index: 1100;
    }
    .popup .close-btn:hover {
      color: #ff5c5c;
    }
    .popup-btn {
      background-color: #444;
      color: white;
      border: none;
      padding: 10px;
      font-size: 16px;
      cursor: pointer;
      border-radius: 5px;
      margin: 10px;
    }

    .popup-btn:hover {
      background-color: #555;
    }



    /* for ball */
    .ball-selection-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      justify-content: center;
      align-items: center;
      color: black;
      z-index: 1500;
    }

    .ball-selection {
      background-color: #fff;
      padding: 40px;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
      max-width: 95%;
      overflow-y: scroll;
      max-height: 80%;
    }

    #ballButtons {
      display: grid;
      grid-template-columns: repeat(10, 1fr);
      gap: 10px;
    }

    #ballButtons button {
      padding: 10px;
      font-size: 34px;
      cursor: pointer;
    }

    #ballButtons button.selected {
      background-color: #28a745;
      color: white;
    }

    #confirmSelectionBtn {
      margin-top: 10px;
      width: 100%;
      padding: 10px;
      font-size: 36px;
      cursor: pointer;
      background-color:rgb(48, 218, 105,0.8);

    }












    .selected-numbers-container {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 20px;
    }

    .selected-number {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f1c40f;
        border-radius: 5px;
        color: black;
        font-weight: bold;
        font-size: 18px;
    }

    .spin-btn-pause{
      width: 80%;
      height: 50px;
      align-items: center;
      justify-content: center;
      background-color: #f1c40f;
      border-radius: 5px;
      color: black;
      font-weight: bold;
      font-size: 18px;
      display: none;
    }

    .spin-btn-spin{
      width: 80%;
      height: 50px;
      align-items: center;
      justify-content: center;
      background-color:rgb(48, 218, 105);
      border-radius: 5px;
      color: black;
      font-weight: bold;
      font-size: 18px;
      display: none;
    }

    .spin-btn-select{
      width: 120px;
      height: 50px;
      align-items: center;
      justify-content: center;
      background-color:rgb(100, 209, 136);
      border-radius: 5px;
      color: black;
      font-weight: bold;
      font-size: 18px;
    }
	</style>
</head>

<body>


<div id="customAlert" class="custom-alert" style="display: none; position: fixed; top: 20px; right: 20px; z-index: 2000;"></div>

</script>
			
			<!-- ========================================================================== -->
					<p id="ran_voice" hidden><?php echo $sound; ?></p>
					<p id="patt" hidden><?php echo $pattern; ?></p>
					<p id="start_get_mony_from" hidden><?php echo $start_get_mony_from; ?></p>
					<p id="bonus_eligiblity_starts_from" hidden><?php echo $bonus_eligiblity_starts_from; ?></p>
					<p id="lock_round_up_last_digit" hidden><?php echo $lock_round_up_last_digit; ?></p>
					<p id="round" hidden><?php echo $round_number; ?></p>
					<p id="sew" hidden><?php echo $sew; ?></p>
					<p id="birr" hidden><?php echo $birr; ?></p>
					<p id="bala" hidden><?php echo $bala; ?></p>
					<p id="profit" hidden><?php echo $profit; ?></p>
			<!-- ========================================================================== -->

	<div id="user-content" style="display: block;" >
      <?php include_once("side-nav.php"); ?>


	


                


				<div id="main-content" class="main-content">
        <div class="row">
            <!-- First Column for Card Selection -->
            <div class="col-4">
            <h1>Card Selection</h1>
            <div class="controls">
                <label for="ballSelection">Select Card Numbers: </label>
                <button id="selectBallsBtn" class="spin-btn-select">Click Here</button>
                
                <br><br>
                <input type="text" id="cardNumbers" hidden>

                <div id="selectedNumbersContainer" class="selected-numbers-container"></div>

                <br><br>
                <button id="spinWheel" class="spin-btn-spin">Play</button>
                <button id="pauseWheel" class="spin-btn-pause" disabled>Pause</button>
            </div>

            <!-- Ball selection modal or section -->
            <div id="ballSelectionModal" class="ball-selection-modal">
                <div class="ball-selection">
                
                    <h3>Select Balls</h3>
                      <select id = "playingAmountInput" required class="form-control" name="birr" id="birr" style="font-size:32px;font-weight: bold;padding: 7px; width: 30%">
                        <option value="10">በ 10ብር</option>
                        <option value="20">በ 20ብር</option>
                        <option value="30">በ 30ብር</option>
                        <option value="40">በ 40ብር</option>
                        <option value="50">በ 50ብር</option>
                        <option value="60">በ 60ብር</option>
                        <option value="70">በ 70ብር</option>
                        <option value="80">በ 80ብር</option>
                        <option value="100">በ 100ብር</option>
                        <option value="150">በ 150ብር</option>
                        <option value="200">በ 200ብር</option>
                        <option value="250">በ 250ብር</option>
                        <option value="300">በ 300ብር</option>
                        <option value="400">በ 400ብር</option>
                        <option value="500">በ 500ብር</option>
                        <option value="1000">በ 1000ብር</option>
                    </select>
                    <br><br>
                    <div id="ballButtons"></div>
                    <button id="confirmSelectionBtn" class="btn btn-success">Confirm Selection</button>
                </div>
            </div>
        </div><!-- col-4 -->


            <!-- Second Column for Wheel Canvas and Result -->
            <div class="col-8">
                  <!-- <h1>Spin and Win</h1> -->

                  <div class="pointer"></div>
                  <canvas id="wheelCanvas" width="700" height="700"></canvas>
                  <p id="win_result"></p>

                  <!-- Popup Modal for Winner -->
                  <div id="winnerPopup" class="popup">
                    <button class="close-btn" id="closePopup">&times;</button> <!-- Close button -->
                    <h2>እንኳን ደስ አለዎት!</h2>
                    <p class="winner" id="popupWinner">ካርድ : 0</p>
                    <p class="amount" id="popupAmount">Win amount: 0</p>
                  </div>
                </div><!-- col-6 -->
          </div><!-- row -->

				</div><!--main content body -->
		</div><!--user content body -->
		
	






















<script type="text/javascript">
  // Variables for the ball selection
  const selectBallsBtn = document.getElementById('selectBallsBtn');
  const ballSelectionModal = document.getElementById('ballSelectionModal');
  const ballButtonsContainer = document.getElementById('ballButtons');
  const confirmSelectionBtn = document.getElementById('confirmSelectionBtn');
  const selectedBalls = [];

  // Variables for the wheel and user selection
  const canvas = document.getElementById('wheelCanvas');
  const ctx = canvas.getContext('2d');
  
  const winnerPopup = document.getElementById('winnerPopup');
  const popupWinner = document.getElementById('popupWinner');
  const popupAmount = document.getElementById('popupAmount');
  const closePopupButton = document.getElementById('closePopup');
  const spinButton = document.getElementById('spinWheel');
  const pauseButton = document.getElementById('pauseWheel');

  const playingAmountInput = document.getElementById('playingAmountInput');
  const result = document.getElementById('win_result');
  const cashier_profit = 0.3;
  let selectedCards = []; // Default selected card is empty
  let playingAmount = parseFloat(playingAmountInput.value) || 0; // Default to 0
  let winningAmount = parseFloat((playingAmount * cashier_profit).toFixed(2)); // Winning amount
  let angle = 0;
  let spinning = false;
  let isPaused = false;
  let blinkInterval = null;













  // Function to update the winning amount dynamically
  const updateWinningAmount = () => {
    winningAmount =  parseFloat((playingAmount * 0.1).toFixed(2)); // 10% of the playing amount
    drawWheel(); // Redraw the wheel with updated winning amount
  };

  // Event listener for playing amount input change
  playingAmountInput.addEventListener('change', () => {
    playingAmount = parseFloat(playingAmountInput.value);
    updateWinningAmount(); // Update winning amount when playing amount changes
  });

  // Ball selection functions
  const generateBallButtons = () => {
    if (ballButtonsContainer.children.length > 0) return; // Avoid recreating buttons
    for (let i = 1; i <= 75; i++) {
      const button = document.createElement('button');
      button.textContent = i;
      button.classList.add('btn', 'btn-outline-primary');
      button.addEventListener('click', () => toggleBallSelection(i, button));
      ballButtonsContainer.appendChild(button);
    }
  };

  const toggleBallSelection = (ballNumber, button) => {
    if (selectedBalls.includes(ballNumber)) {
      selectedBalls.splice(selectedBalls.indexOf(ballNumber), 1);
      button.classList.remove('selected');
    } else {
      selectedBalls.push(ballNumber);
      button.classList.add('selected');
    }
  };

  selectBallsBtn.addEventListener('click', () => {
    ballSelectionModal.style.display = 'flex';
    generateBallButtons(); // Generate ball buttons on selection
  });

  confirmSelectionBtn.addEventListener('click', () => {
    ballSelectionModal.style.display = 'none';
    document.getElementById('cardNumbers').value = selectedBalls.join(',');
    console.log('Selected Balls:', selectedBalls);
  });

  // Ball selection functions
    selectBallsBtn.addEventListener('click', () => {
        ballSelectionModal.style.display = 'flex';
        generateBallButtons(); // Generate ball buttons on selection
    });


      // Function to update the selected numbers display
      const updateSelectedNumbersDisplay = () => {
          const selectedNumbersContainer = document.getElementById('selectedNumbersContainer');
          selectedNumbersContainer.innerHTML = ''; // Clear previous numbers

          selectedBalls.forEach((ballNumber) => {
              const numberCard = document.createElement('div');
              numberCard.classList.add('selected-number');
              numberCard.textContent = ballNumber;
              selectedNumbersContainer.appendChild(numberCard);
          });
      };


    confirmSelectionBtn.addEventListener('click', () => {
        ballSelectionModal.style.display = 'none';
        document.getElementById('cardNumbers').value = selectedBalls.join(',');
        console.log('Selected Balls:', selectedBalls);

        // Generate the wheel after confirming the ball selection
        if (selectedBalls.length < 3) {
            alert('Please select at least 3 balls!');
            return;
        }

        selectedCards = selectedBalls; // Set selected balls as the cards
        winningAmount = winningAmount =  parseFloat((playingAmount * 0.1).toFixed(1)) || 0;  // Ensure winning amount is calculated
        
        document.getElementById('spinWheel').style.display = "inline-block";
        document.getElementById('pauseWheel').style.display = "none";

       

        // Update the display of selected numbers
        updateSelectedNumbersDisplay();
        // Draw the wheel with the selected cards
        drawWheel(); // This will generate the wheel with the selected cards
    });

  
  
  
  




















  
  // Draw wheel with dynamic sectors
    const drawWheel = (blinkingSector = null, highlightSector = null) => {
        const radius = canvas.width / 2;
        const centerX = canvas.width / 2;
        const centerY = canvas.height / 2;
        const angleStep = (2 * Math.PI) / selectedCards.length;

        // Clear the canvas
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // Define 3 colors for the sectors
        const colors = ['#f1c40f', '#e67e22', '#3498db']; // Yellow, Orange, Blue

        // Draw outer border
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius + 5, 0, 2 * Math.PI); // Slightly larger than the wheel radius
        ctx.lineWidth = 8; // Thickness of the outer border
        ctx.strokeStyle = '#FF0000'; // Outer border color
        ctx.stroke();

        // Draw shadow for spinning effect
        ctx.shadowBlur = 15; // Shadow blur radius
        ctx.shadowOffsetX = 0; // Horizontal shadow offset
        ctx.shadowOffsetY = 5; // Vertical shadow offset
        ctx.shadowColor = 'rgba(0, 0, 0, 0.5)'; // Shadow color

        // Draw sectors
        for (let i = 0; i < selectedCards.length; i++) {
            ctx.beginPath();
            ctx.moveTo(centerX, centerY);
            ctx.arc(centerX, centerY, radius, angleStep * i, angleStep * (i + 1));
            ctx.closePath();

            // Add blinking effect for the selected sector
            if (blinkingSector === i) {
                ctx.fillStyle = (Math.random() > 0.5) ? 'red' : 'green';
            } else {
                ctx.fillStyle = colors[i % 3]; // Alternate between 3 colors
            }
            ctx.fill();

            // Draw sector borders
            ctx.lineWidth = highlightSector === i ? 4 : 2; // Highlighted sector gets thicker border
            ctx.strokeStyle = highlightSector === i ? 'gold' : '#000'; // Highlighted sector gets gold border
            ctx.stroke();

            // Draw the text inside the sectors
            ctx.save();
            ctx.translate(centerX, centerY);
            ctx.rotate(angleStep * (i + 0.5)); // Rotate to align text with the sector
            ctx.textAlign = 'right';
            ctx.fillStyle = '#000'; // Text color
            ctx.font = '36px Arial'; // Font size and style
            ctx.fillText(`${selectedCards[i]}`, radius - 10, 5); // Draw card numbers
            ctx.restore();
        }

        // Reset shadow to avoid affecting other elements
        ctx.shadowBlur = 0;
        ctx.shadowOffsetX = 0;
        ctx.shadowOffsetY = 0;
        ctx.shadowColor = 'transparent';

        // Draw the center circle with gradient effect
        const centerGradient = ctx.createRadialGradient(centerX, centerY, 10, centerX, centerY, 60);
        centerGradient.addColorStop(0, '#FFD700'); // Golden yellow at the center
        centerGradient.addColorStop(1, '#FF4500'); // Outer red gradient

        ctx.beginPath();
        ctx.arc(centerX, centerY, 60, 0, 2 * Math.PI); // Center circle
        ctx.fillStyle = centerGradient;
        ctx.fill();

        // Add text inside the center circle
        ctx.fillStyle = 'white';
        ctx.font = '30px Arial';
        ctx.textAlign = 'center';
        ctx.fillText(`${winningAmount} ብር`, centerX, centerY + 7);
    };



   // Spin wheel function
    const spinWheel = () => {
        if (spinning)
        {
          document.getElementById('spinWheel').style.display = "none";
          return; // Prevent multiple spins
        }
        
        
        spinning = true;
        document.getElementById('spinWheel').style.display = "none";
        document.getElementById('pauseWheel').style.display = "inline-block";

        
        if (blinkInterval) {
            clearInterval(blinkInterval);
            blinkInterval = null;
        }

        spinButton.disabled = true;
        pauseButton.disabled = false;

        const spinAngle = Math.random() * 360 + 720;
        let currentAngle = 0;

        console.log(`Spin starts! Total spin angle: ${spinAngle.toFixed(2)} degrees`);

        spinAnimation = setInterval(() => {
            if (isPaused) return;

            currentAngle += 10; // Increment angle
            if (currentAngle >= spinAngle) {
                clearInterval(spinAnimation);
                angle = (angle + spinAngle) % 360;

                console.log(`Spin ends! Final wheel angle: ${angle.toFixed(2)} degrees`);

                const adjustedAngle = (angle + 90) % 360;
                const sectorAngle = 360 / selectedCards.length;
                const winningIndex = Math.floor(adjustedAngle / sectorAngle);

                console.log(`Winning sector index: ${winningIndex}`);
                console.log(`Winning card: ${selectedCards[winningIndex]}`);

                popupWinner.textContent = `ካርድ : ${selectedCards[winningIndex]}`;
                popupAmount.textContent = `የሽልማት መጠን: ${winningAmount}  ብር`;

                winnerPopup.classList.add('show');

                blinkInterval = setInterval(() => {
                    drawWheel(winningIndex);
                }, 500);

                spinButton.disabled = false;
                pauseButton.disabled = true;

                pauseButton.textContent = "Finished";

                spinning = false;
            } else {
                angle = (angle + 10) % 360;
                console.log(`Current Wheel Angle: ${angle.toFixed(2)} degrees`);
            }

            ctx.save();
            ctx.translate(canvas.width / 2, canvas.height / 2);
            ctx.rotate((angle * Math.PI) / 180);
            ctx.translate(-canvas.width / 2, -canvas.height / 2);
            drawWheel();
            ctx.restore();

            drawCenter(); // Redraw center
        }, 40);
    };


    // Draw the center as a fixed element
    const drawCenter = () => {
      const centerX = canvas.width / 2;
      const centerY = canvas.height / 2;

      ctx.beginPath();
      ctx.arc(centerX, centerY, 50, 0, 2 * Math.PI);
      ctx.fillStyle = '#000';
      ctx.fill();

      ctx.fillStyle = '#fff';
      ctx.font = '20px Arial';
      ctx.textAlign = 'center';
      ctx.fillText(winningAmount, centerX, centerY + 7);
    };


    // Pause/resume spin wheel function
    const pauseWheel = () => {
        if (!spinning) return;
        isPaused = !isPaused;

        if (isPaused) {
            pauseButton.textContent = "Resume";
            console.log("Spin paused!");
        } else {
            pauseButton.textContent = "Pause";
            console.log("Spin resumed!");
        }
    };
    // Close the popup when the close button is clicked
    closePopupButton.addEventListener('click', () => {
        winnerPopup.classList.remove('show');
    });

   

  // Attach event listeners for spin and pause buttons
  spinButton.addEventListener("click", spinWheel);
  pauseButton.addEventListener("click", pauseWheel);

  // Initial render of the wheel
  // drawWheel();
</script>



<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <p>&copy; <?php echo date("Y"); ?> ethiomark. All rights reserved.</p>
        </div>
    </div>
</footer>


</body>
</html>