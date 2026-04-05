<?php

// Load the CSV file
$filename = '../assets/file/table_300.csv'; // Make sure your CSV file is named table.csv

// Initialize the array to hold the final output
$output = [
    "tables" => []
];

// Open the CSV file for reading
if (($handle = fopen($filename, 'r')) !== FALSE) {
    // Read each row in the CSV file
    while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
        $cartela_number = (int)$data[0]; // Get the cartela_number
        $cartela_data = [];

        // Get the remaining columns and convert to array of integers
        for ($i = 1; $i < count($data); $i++) {
            $dataRow = array_map('intval', explode(',', $data[$i])); // Convert string to array of integers
            $cartela_data[] = $dataRow; // Add the row to cartela_data
        }

        // Add the cartela_number and cartela_data to the output
        $output["tables"][] = [
            "cartela_number" => $cartela_number,
            "cartela_data" => $cartela_data
        ];
    }
    fclose($handle); // Close the CSV file
}

// Convert the output array to JSON format
$jsonOutput = json_encode($output, JSON_PRETTY_PRINT);

// Print the JSON output
header('Content-Type: application/json');
echo $jsonOutput;

?>
