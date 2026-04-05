<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bingo Card Generator</title>
<style>
    body {
        font-family: Arial, sans-serif;
        text-align: center;
        background-color: #f4f4f4;
    }
    .container {
        margin-top: 20px;
    }
    .bingo-card {
        display: inline-block;
        background-color: rgb(3, 46, 59);
        color: #fff;
        padding: 15px;
        border-radius: 10px;
        text-align: center;
        margin: 10px;
    }
    .bingo-header {
        font-size: 40px;
        font-weight: bold;
        line-height: 1.2;
        letter-spacing: 10px;
        color: #fff;
        margin-bottom: 20px;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .bingo-row {
        display: flex;
        justify-content: center;
    }
    .bingo-cell {
        width: 50px;
        height: 50px;
        margin: 3px;
        background-color: #ffffff;
        color: #003366;
        font-size: 24px;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px;
        border: 2px solid #003366;
    }
    .free-cell {
        background-color: #FFD700;
    }
    #card-number {
        font-size: 20px;
        color: #ffffff;
        margin-top: 10px;
        font-weight: 900;
    }
    @media print {
        body {
            background-color: white;
            margin: 0;
            padding: 0;
        }
        .container, form {
            display: none;
        }
        .bingo-card {
            display: inline-block;
            width: 45%;
            margin: 5px 5px;
            padding: 20px;
            border-radius: 10px;
            box-sizing: border-box;
            border: none;
            height: auto;
        }
    }
</style>
<!-- Include html2canvas from CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
    // JavaScript function to trigger the print dialog
    function printCards() {
        window.print();
    }

    // JavaScript function to print Bingo cards as an image
    function printBingoAsImage() {
        const bingoContainer = document.getElementById('bingo-card-container');

        // Use html2canvas with additional options
        html2canvas(bingoContainer, { 
            useCORS: true,  // Allow cross-origin content
            scale: 2,       // Increase scale for better quality
            logging: true,  // Log for debugging purposes
            allowTaint: true, // Allow canvas taint (use with caution)
            windowWidth: document.body.scrollWidth, // Match viewport width
            windowHeight: document.body.scrollHeight // Match viewport height
        }).then(canvas => {
            // Convert the captured content to an image
            const imgData = canvas.toDataURL('image/png');
            
            // Open a new window to display the image
            const printWindow = window.open('', '_blank');
            printWindow.document.write('<html><head><title>Print Bingo Cards</title></head><body style="margin:0;">');
            printWindow.document.write('<img src="' + imgData + '" style="width:100%;">');
            printWindow.document.write('</body></html>');
            printWindow.document.close();

            // Wait for the image to load before triggering print
            printWindow.onload = function () {
                printWindow.print();
            };
        }).catch(error => {
            console.error('Error generating image for print:', error);
        });
    }
</script>
</head>
<body>

<div class="container">
    <form method="POST">
        <label for="min-cards">Enter Minimum Number of Cards:</label>
        <input type="number" id="min-cards" name="min-cards" min="1" max="100" value="1">
        <br><br>
        <label for="max-cards">Enter Maximum Number of Cards:</label>
        <input type="number" id="max-cards" name="max-cards" min="1" max="100" value="5">
        <br><br>
        <button type="submit">Generate Cards</button>
        <button type="button" onclick="printCards()">Print Cards</button>
        <button type="button" onclick="printBingoAsImage()">Print Cards as Image</button>
    </form>
</div>

<div id="bingo-card-container">



<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Load JSON data from file
    $jsonDataPath = '../assets/file/cartela_300.json';
    $jsonContent = file_get_contents($jsonDataPath);

    if ($jsonContent === false) {
        echo '<p>Error loading JSON data!</p>';
        exit;
    }

    $jsonData = json_decode($jsonContent, true);

    if ($jsonData === null || !isset($jsonData['tables'])) {
        echo '<p>Invalid JSON structure!</p>';
        exit;
    }

    // Get user inputs for card range
    $minCards = intval($_POST['min-cards']);
    $maxCards = intval($_POST['max-cards']);

    if ($minCards > $maxCards) {
        echo '<p>Minimum number of cards cannot be greater than maximum number of cards!</p>';
        exit;
    }

    // Generate and display Bingo cards
    for ($i = $minCards; $i <= $maxCards; $i++) {
        $cardData = array_filter($jsonData['tables'], function($table) use ($i) {
            return $table['cartela_number'] === $i;
        });

        if (empty($cardData)) {
            echo "<p>Card number $i not found!</p>";
            continue;
        }

        // Reset array indexing
        $cardData = array_values($cardData)[0];

        echo '<div class="bingo-card">';
        echo '<div>Ethiomark.com</div>';
        echo '<div class="bingo-header">B   I   N   G   O</div>';

        for ($rowIndex = 0; $rowIndex < 5; $rowIndex++) {
            echo '<div class="bingo-row">';
            for ($colIndex = 0; $colIndex < 5; $colIndex++) {
                $num = $cardData['cartela_data'][$colIndex][$rowIndex];
                $cellClass = ($rowIndex === 2 && $colIndex === 2) ? 'bingo-cell free-cell' : 'bingo-cell';

                $cellContent = ($rowIndex === 2 && $colIndex === 2) ? '★' : $num;
                echo "<div class=\"$cellClass\">$cellContent</div>";
            }
            echo '</div>';
        }

        echo '<div id="card-number">0918101037&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Card No ' . $i . '</div>';
        echo '</div>';
    }
}
?>












</div>

</body>
</html>
