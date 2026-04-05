// Inactivity timeout (set to 2 hours for production)
const inactivityTime = 7200000; // 2 hours in milliseconds
let timeout;

// Function to handle inactivity
function handleInactivity() {
    console.log("Handling inactivity. Checking if user is logged in...");

    // Calculate remaining time
    const remainingTime = timeout ? timeout - Date.now() : inactivityTime; // Calculate remaining time
    const hours = Math.floor(remainingTime / 3600000);
    const minutes = Math.floor((remainingTime % 3600000) / 60000);
    
    console.log(`Remaining time until session closure: ${hours} hours and ${minutes} minutes`);

    // Send a request to PHP to clear the session
    fetch('../config/clear_session.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                console.log('Session cleared due to inactivity.');
                localStorage.clear();
                // Reload the page to reflect the logged-out state
                window.location.reload();
            } else {
                console.log('Session could not be cleared. User might still be logged in.');
            }
        })
        .catch(error => console.error('Error clearing session:', error));
}

// Reset inactivity timer when there is user activity
function resetInactivityTimer() {
    // console.log("User activity detected. Resetting inactivity timer.");
    clearTimeout(timeout);
    timeout = setTimeout(handleInactivity, inactivityTime);
}

// Add event listeners to track user activities and reset the inactivity timer
function initInactivityTimer() {
    console.log("Initializing inactivity timer. Adding event listeners for user activity.");

    ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'].forEach(event => {
        document.addEventListener(event, resetInactivityTimer);
    });

    // Start inactivity timer on page load
    resetInactivityTimer();
}

// Call this function after a successful login
function userLoggedIn() {
    console.log("User logged in. Storing session status in localStorage.");
    localStorage.setItem('loggedIn', 'true');
}

// Handle page close or tab close (not refresh) using beforeunload event
window.addEventListener('beforeunload', function (e) {
    console.log("Page is being closed. Checking if user is logged in...");
    if (localStorage.getItem('loggedIn') === 'true') 
        {
            console.log("User is logged in. Sending request to clear session on page close.");
            navigator.sendBeacon('../config/logout.php');
    } else {
        console.log("User is not logged in. No action taken.");
    }
});

// Check server-side session on page load (e.g., if user manually cleared localStorage)
function checkSessionStatus() {
    // fetch('check_session.php')
    //     .then(response => response.json())
    //     .then(data => {
    //         if (data.loggedIn) {
    //             console.log('User is still logged in (server-side).');
    //             localStorage.setItem('loggedIn', 'true');
    //         } else {
    //             console.log('User is not logged in (server-side). Clearing localStorage.');
    //             localStorage.removeItem('loggedIn');
    //         }
    //     })
    //     .catch(error => console.error('Error checking session status:', error));
}

// Initialize the inactivity timer and check session status on page load
window.onload = function() {
    console.log("Page loaded. Checking session status and initializing inactivity timer.");
    checkSessionStatus(); // Check server session first
    initInactivityTimer(); // Start inactivity timer
};
