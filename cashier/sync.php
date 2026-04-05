<?php
// local_server_sync.php - on the offline/local machine
$cashier_id = '@temp1'; // Example cashier ID, you can pass this dynamically

// Remote server URL
$remote_url = "https://admin.bingo.ethiomark.com/admin/sync_cashier_package.php"; // Remote script URL

// Step 1: Fetch cashier_package from the remote server
$data = ['cashier_id' => $cashier_id];
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $remote_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {
    echo "cURL Error: " . curl_error($ch);
    curl_close($ch);
    exit;
}

curl_close($ch);

// Debug: Raw response
echo "Raw Response: " . $response . "\n";

$response_data = json_decode($response, true);

// Check if the JSON decoding is successful
if ($response_data === null) {
    echo "Error decoding JSON response: " . json_last_error_msg() . "\n";
    exit;
}

if ($response_data['status'] == 'success') {
    $remote_package = $response_data['cashier_package'];
    
    if ($remote_package > 0) {
        // Step 2: Update local cashier_package
        include_once '../config/Database.php';
        $db = Database::getInstance();
        $conn = $db->getConnection();

        // Fetch current local package
        $sql = "SELECT cashier_package FROM cashier WHERE cashier_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $cashier_id);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($local_package);
        $stmt->fetch();

        $new_local_package = $local_package + $remote_package;
        $stmt->close();

        // Update local package
        $update_sql = "UPDATE cashier SET cashier_package = ? WHERE cashier_id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ds", $new_local_package, $cashier_id);
        if ($stmt->execute()) {
            echo "Local package updated successfully.\n";

            // Step 3: Send confirmation back to the remote server
            $confirm_data = [
                'cashier_id' => $cashier_id,
                'confirm' => true
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $remote_url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $confirm_data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $confirm_response = curl_exec($ch);
            curl_close($ch);

            $confirm_response_data = json_decode($confirm_response, true);
            if ($confirm_response_data['status'] == 'success') {
                echo "Remote package set to 0 successfully.\n";
            } else {
                echo "Error updating remote package: " . $confirm_response_data['message'] . "\n";
            }
        } else {
            echo "Error updating local package: " . $stmt->error . "\n";
        }
        $stmt->close();
    } else {
        echo "No valid package found on the remote server.\n";
    }
} else {
    echo "Error fetching data from remote server: " . $response_data['message'] . "\n";
}
?>
