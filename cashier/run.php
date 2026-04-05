<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'startChrome') {
    echo "oooooooooooooooooooooooooooooo";
    $command = 'start "" chrome.exe --new-window --window-position="0,0" --user-data-dir="C:/tmp/Profiles/1" --kiosk --incognito "http://localhost/bingo/cashier/index.php"';
    exec($command);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Start Chrome Kiosk</title>
</head>
<body>
    <button id="fullscreenBtn" style="position: absolute; top: 20px; right: 2px; z-index: 2100; background-color: transparent; border: none; padding: 0px; cursor: pointer;">
        jjjjj
    </button>

    <script>
        document.getElementById('fullscreenBtn').onclick = function() {
            fetch('run.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=startChrome'
            });
        };
    </script>
</body>
</html>
