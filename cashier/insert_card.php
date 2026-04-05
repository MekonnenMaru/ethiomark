<?php

// File path to the JSON data
$filePath = '../assets/file/tables.json';
include_once '../config/Database.php';

// Check if the file exists
if (!file_exists($filePath)) {
    die("File not found: $filePath");
}

// Read the JSON data from the file
$jsonData = file_get_contents($filePath);

// Decode the JSON data
$data = json_decode($jsonData, true);

// Check if decoding was successful
if (json_last_error() !== JSON_ERROR_NONE) {
    die("Error decoding JSON: " . json_last_error_msg());
}

// Database connection
$db = Database::getInstance();
$conn = $db->getConnection(); // Get the connection object

// Define a date and partner_id for the insert statements
$currentDate = date('Y-m-d H:i:s');
$partnerId = 0; // Example partner_id, adjust as needed

// Prepare the SQL statement with placeholders
$stmt = $conn->prepare("INSERT INTO `cartela` (`cartela_number`, `b`, `i`, `n`, `g`, `o`, `date`, `partner_id`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

// Check if the statement was prepared successfully
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

// Loop through the data and bind parameters for each row
foreach ($data['tables'] as $table) {
    $cartelaNumber = $table['cartela_number'];

    // Extract the values for b, i, n, g, o from cartela_data
    if (isset($table['cartela_data'])) {
        $cartelaData = $table['cartela_data'];

        // Prepare comma-separated strings for b, i, n, g, o
        $b = implode(',', $cartelaData[0]);
        $i = implode(',', $cartelaData[1]);
        $n = implode(',', $cartelaData[2]);
        $g = implode(',', $cartelaData[3]);
        $o = implode(',', $cartelaData[4]);

        // Bind parameters
        $stmt->bind_param("issssssi", $cartelaNumber, $b, $i, $n, $g, $o, $currentDate, $partnerId);

        // Execute the statement
        if (!$stmt->execute()) {
            echo "Error executing statement for cartela number $cartelaNumber: " . $stmt->error . "<br>";
        }
    } else {
        echo "No cartela_data found for cartela number $cartelaNumber.<br>";
    }
}

// Close the prepared statement
$stmt->close();

// Close the database connection
$conn->close();

echo "Records inserted successfully.";
?>
