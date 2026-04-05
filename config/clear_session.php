<?php
if (session_status() === PHP_SESSION_NONE)
{
	session_start();
}
// Clear session data
session_unset();
session_destroy();

// Send a response back to the JavaScript
echo json_encode(['status' => 'success']);
?>
