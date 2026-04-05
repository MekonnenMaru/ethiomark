<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ethiomark</title>
    <link rel="stylesheet" href="bootstrap/css/style.css">
    <script src="bootstrap/js/jquery.js"></script>
    <link rel="icon" href="../assets/images/logo.png" type="image/jpg">

    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#007bff">
    <style>
      /* Modal Styles */
      .modal {
            display: none;
            position: fixed;
            z-index: 1001;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            border-radius: 10px;
            width: 50%;
            max-width: 500px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            animation: slideDown 0.5s ease;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #000;
        }

        .modal-header {
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }

        .modal-body {
            padding: 20px 0;
        }

        .modal-footer {
            padding: 10px 0;
            border-top: 1px solid #ddd;
            text-align: right;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        .form-group textarea {
            resize: vertical;
            height: 100px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .btn-primary {
            background-color: #007bff;
            color: #fff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: #fff;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .btn:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }


        .disable-pointer {
            pointer-events: none;
            opacity: 0.6; /* Optional for a visual indication */
        }

        button:disabled {
            pointer-events: none; /* Disable pointer interactions */
            opacity: 0.6; /* Optional: To visually indicate the button is disabled */
        }
        #loadingSpinner {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 2000;
            font-size: 1.5rem;
            color: #007bff;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        #loadingSpinner .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid rgba(0, 123, 255, 0.2);
            border-top-color: #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        #loadingSpinner .loading-text {
            margin-top: 10px;
            font-size: 1rem;
            color: #007bff;
        }
</style>

</head>
<body>
    <div id="customAlert" class="custom-alert" style="display: none; position: fixed; top: 20px; right: 20px; z-index: 1000;"></div>

    <!-- Loading spinner -->
    <div id="loadingSpinner">
        <div class="spinner"></div>
        <div class="loading-text">Loading...</div>
    </div>


    <!-- Login Section -->
    <div class="login-center-wrapper" id="login-part" >
        <div class="login-container" style="background-color: black;">
            <div class="logo-container">
                <div class="login-logo">
                    <img style="user-drag: none; -webkit-user-drag: none;" src="assets/images/logo.png" alt="Logo">
                </div>
            </div>
            <span class="login-text">ኢትዮ ማርክ ቢንጎ </span> <br>
            <form id="loginform" method="post">
                <div class="login-input-group">
                    <label class="login-label" for="cashier_id">Cashier ID</label>
                    <input class="login-input" type="text" name="cashier_id" id="cashier_ids" placeholder="cashier id" required>
                    <input type="text" name="login_form_submitted" hidden>
                </div>
                <div class="login-input-group">
                    <label class="login-label" for="password">Password</label>
                    <input class="login-input" type="password" name="password" id="passwords" placeholder="Password" required>
                </div>
                <button class="login-btn" type="submit">Login</button>
            </form>
            <p class="login-p">
                Don't have an account? <a class="login-a" href="#" id="signUpLink">ይመዝገቡ</a>
            </p>
        </div>
    </div>

    <!-- Sign Up Modal -->
    <div id="signUpModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close" id="closeModal">&times;</span>
                <h2>Sign Up - ይመዝገቡ</h2>
            </div>
            <div class="modal-body">
                <form id="signUpForm">
                    <div class="form-group">
                        <label for="name">Name - ስም</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="location">Location - የስራ አካባቢ ስም</label>
                        <input type="text" id="location" name="location" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone - ስልክ </label>
                        <input type="text" id="phone" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Message - መልክት</label>
                        <textarea id="message" name="message" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
                <button class="btn btn-primary" id="submitBtn">
                    <span id="submitText">Submit</span>
                    <div class="loading-spinner" id="loadingSpinner"></div>
                </button>
            </div>
        </div>
    </div>

    <script>
        const loginform = document.getElementById('loginform');
        const loginButton = document.querySelector('.login-btn');
        const loadingSpinner = document.getElementById('loadingSpinner');
        resetLoginFormState();
        // Check login status
        const isLoggedIn = localStorage.getItem('loggedin') === 'true';
        const loginPart = document.getElementById('login-part');

        if (isLoggedIn) {
            window.location.href = "cashier/index.php"; // Redirect based on position
        } else {
            loginPart.style.display = '';
        }

        // Handle login form submission
        loginform.addEventListener('submit', function (e) {
            e.preventDefault(); // Prevent default form submission

            const cashier_id = document.getElementById('cashier_ids').value.trim();
            const password = document.getElementById('passwords').value.trim();
            const form_data = $(this).serialize();

            // Validate inputs
            if (cashier_id !== '' && password !== '') {
                // Disable interactions and show loader
                loginButton.disabled = true;
                loginButton.innerHTML = 'Logging in...';
                loginform.classList.add('disable-pointer');
                loadingSpinner.style.display = 'block';

                $.ajax({
                    url: "config/DbFunction.php",
                    method: "POST",
                    data: form_data,
                    dataType: "JSON",
                    success: function (resp) 
                    {
                        if (resp.login_status === 'success') 
                        {
                            showAlert('Successfully logged in!', 'success');
                            localStorage.setItem('loggedin', 'true');
                            localStorage.setItem('cashier_id', cashier_id);

                            setTimeout(function () {
                                window.location.href = "cashier/index.php";
                            }, 1000);
                        } else if (resp.login_status != '') {
                            setTimeout(function () {
                                resetLoginFormState();
                                showAlert(resp.login_status, 'danger');
                            }, 5000);

                        } else if (resp.error) {
                            setTimeout(function () {
                                resetLoginFormState();
                                showAlert(resp.login_status, 'danger');
                            }, 5000);
                        } else {
                            setTimeout(function () {
                                resetLoginFormState();
                                 showAlert('Unexpected error occurred!', 'danger');
                            }, 5000);

                        }


                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.error('Error Details:');
                        console.error('Status: ' + textStatus);
                        console.error('Error Thrown: ' + errorThrown);
                        console.error('Response Text: ' + jqXHR.responseText);
                        showAlert("An error occurred while logging in. Check console for details.", "danger");

                        resetLoginFormState();
                    }
                });
            } else {
                alert('Please enter both cashier_id and password');
            }
        });

        // Function to reset the form state after response
        function resetLoginFormState() {
            loginButton.disabled = false;
            loginButton.innerHTML = 'Login';
            loginform.classList.remove('disable-pointer');
            loadingSpinner.style.display = 'none';
        }

        // Function to show alert
        function showAlert(message, type) {
            const alertDiv = $('#customAlert');
            alertDiv.html(message);
            alertDiv.removeClass('custom-alert-success custom-alert-danger slide-out'); // Clear previous alert classes
            alertDiv.addClass('custom-alert-' + type + ' slide-in'); // Add appropriate class and slide-in

            alertDiv.show();

            setTimeout(function () {
                alertDiv.removeClass('slide-in');
            }, 50);

            setTimeout(function () {
                alertDiv.addClass('slide-out');
                setTimeout(function () {
                    alertDiv.fadeOut();
                }, 300);
            }, 3000);
        }
    

        // Get the modal
        var modal = document.getElementById("signUpModal");

        // Get the link that opens the modal
        var signUpLink = document.getElementById("signUpLink");

        // Get the <span> element that closes the modal
        var closeModal = document.getElementById("closeModal");

        // Get the cancel button
        var cancelBtn = document.getElementById("cancelBtn");

        // Get the submit button and loading spinner
        var submitBtn = document.getElementById("submitBtn");
        var submitText = document.getElementById("submitText");

        // When the user clicks the link, open the modal
        signUpLink.onclick = function() {
            modal.style.display = "block";
        }

        // When the user clicks on <span> (x), close the modal
        closeModal.onclick = function() {
            modal.style.display = "none";
        }

        // When the user clicks on cancel button, close the modal
        cancelBtn.onclick = function() {
            modal.style.display = "none";
        }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        // Handle form submission
        submitBtn.onclick = function(event)
        {
            event.preventDefault(); // Prevent default form submission

            // Disable the submit button and show the loading spinner
            submitBtn.disabled = true;
            submitText.textContent = "Wait...";
            loadingSpinner.style.display = "inline-block";

            // Simulate form submission with a delay (replace with actual AJAX call)
            setTimeout(function() {
                // Re-enable the submit button and hide the loading spinner
                submitBtn.disabled = false;
                submitText.textContent = "Submit";
                loadingSpinner.style.display = "none";

                // Show success message
                alert("Form submitted successfully!");
                modal.style.display = "none"; // Close the modal
            }, 2000); // Simulate a 2-second delay for submission
        }


        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/service-worker.js')
                .then(registration => {
                    console.log('Service Worker registered with scope:', registration.scope);
                })
                .catch(error => {
                    console.log('Service Worker registration failed:', error);
                });
        }


        var offset = new Date().getTimezoneOffset();
			function showWarning(message, timeout = 1000000) 
			{
				// Create a container if it doesn't exist
				let container = document.getElementById('warningContainer');
				if (!container) {
					container = document.createElement('div');
					container.id = 'warningContainer';
					container.style.position = 'fixed';
					container.style.bottom = '5px';
					container.style.left = '50%';
                    container.style.transform = 'translateX(-50%)';
					container.style.display = 'flex';
					container.style.flexDirection = 'column';
					container.style.gap = '5px';
					container.style.zIndex = '10000';
					document.body.appendChild(container);
				}

				// Create the warning box
				const warning = document.createElement('div');
				warning.style.background = ' rgb(255, 255, 0)';
				warning.style.color = 'black';
				warning.style.padding = '10px 15px';
				warning.style.borderRadius = '8px';
				warning.style.boxShadow = '0 4px 8px rgba(0, 0, 0, 0.3)';
				warning.style.fontSize = '16px';
				warning.style.maxWidth = '700px';
				warning.style.position = 'relative';
				warning.style.animation = 'fadeIn 0.5s';

				// Close button
				const closeBtn = document.createElement('span');
				closeBtn.innerHTML = '&times;';
				closeBtn.style.position = 'absolute';
				closeBtn.style.top = '-30px';
				closeBtn.style.right = '5px';
				closeBtn.style.color = 'red';
				closeBtn.style.cursor = 'pointer';
				closeBtn.style.fontSize = '40px';
				closeBtn.addEventListener('click', () => {
					container.removeChild(warning);
				});

				warning.innerHTML = message;
				warning.appendChild(closeBtn);
				container.appendChild(warning);

				// Auto remove after timeout
				setTimeout(() => {
					if (container.contains(warning)) {
						container.removeChild(warning);
					}
				}, timeout);
			}
        if(true) {
					// Check time with internet instead of transaction
					fetch('https://timeapi.io/api/time/current/zone?timeZone=Africa%2FNairobi', {
						method: 'GET',
						headers: {
							'Accept': 'application/json'
						}
					})
					.then(response => response.json())
					.then(data => {
						try {
							const internetTime = new Date(data.dateTime); // Extract datetime from API response
							const localTime = new Date();
							const timeDifferenceMs = Math.abs(localTime - internetTime);
							const timeDifferenceHours = (timeDifferenceMs / (1000 * 60 * 60)); // Convert to hours
							
							if (timeDifferenceHours > 6 || timeDifferenceHours < -6) { 
								showWarning('Time difference exceeds 6 hours! Your computer time is significantly incorrect. Please adjust it immediately.', 15000);
							} else if (timeDifferenceHours > 0.1 || timeDifferenceHours < -0.1) { 
								let direction = timeDifferenceHours > 0 ? 'ahead' : 'behind';
								showWarning(`Your system clock is ${Math.abs(timeDifferenceHours).toFixed(1)} hours ${direction} of the correct time. Please sync it.`, 15000);
							}
						} catch (error) {
							console.error('Error processing the time difference:', error);
						}
					})
					.catch(error => {
						console.error('Error fetching internet time:', error);
					});
				}
    </script>
</body>
</html>
