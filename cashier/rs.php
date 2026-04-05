<html>
    <head>
        <body>
            <form action="rs.php">
                <input type="text" name="" id="">
                <input type="submit" onclick="checkCard()">
            </form>

            <div id="result">

            </div>
            <script>
                // This function would be called when the form is submitted
                checkCard();
                        async function checkCard() {
                            const cardNo = 1;
                            const round = 1;
                            const cashierId = "@temp1";

                            try {
                                // First fetch the new_bonus_status
                                const bonusResponse = await fetch('rs_back.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                    },
                                    body: JSON.stringify({
                                        game_id: round,
                                        cashier_id: cashierId
                                    })
                                });
                                
                                const bonusData = await bonusResponse.json();
                                const newBonusStatus = bonusData.status || '';
                                console.log(bonusData);  // ✅ logs the actual response content

                                // Then fetch the game data
                                const gameResponse = await fetch('rs_back.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                    },
                                    body: JSON.stringify({
                                        card_no: cardNo,
                                        round: round,
                                        cashier_id: cashierId
                                    })
                                });

                                const gameData = await gameResponse.json();
                                console.log(gameData);  // ✅ logs the actual response content


                                if (gameData.error) {
                                    displayError(gameData.error);
                                    return;
                                }

                                if (gameData.rows && gameData.rows.length > 0) {
                                    const row = gameData.rows[0];
                                    const drawnNumbers = row.result.split(',').map(num => parseInt(num.trim())).filter(num => !isNaN(num));
                                    const pattern = row.pattern;

                                    // Fetch category and color
                                    const categoryResponse = await fetch('rs_back.php', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                        },
                                        body: JSON.stringify({ cashier_id: cashierId })
                                    });
                                    
                                    const categoryData = await categoryResponse.json();
                                    const category = categoryData.category || 'default';
                                    const resultColor = categoryData.color || 'two';
                                    console.log(categoryData);  // ✅ logs the actual response content


                                    // Fetch card data
                                    const cardResponse = await fetch('rs_back.php', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                        },
                                        body: JSON.stringify({
                                            cartela_number: cardNo,
                                            category: category
                                        })
                                    });
                                    const cardData = await cardResponse.json();
                                    console.log(cardData);  // ✅ logs the actual response content

                                    // Process the card data
                                    const card = {
                                        'B': cardData.b.split(','),
                                        'I': cardData.i.split(','),
                                        'N': cardData.n.split(','),
                                        'G': cardData.g.split(','),
                                        'O': cardData.o.split(',')
                                    };

                                    // Check for bingo
                                    const isBingo = checkPattern(card, drawnNumbers, pattern);
                                    let winStatus = 0;
                                    const lastCalled = drawnNumbers[drawnNumbers.length - 1];
                                    let isNewBonusAvailable = "no";
                                    let countWinningLine = 0;
                                    const specialPartnerIdCondition = (resultColor === "two");

                                    // Generate HTML for the card
                                    let html = `<div id="round" style="font-size: 30px; font-weight: bolder; background-color: white; color: black; padding: 5px; margin-bottom: 5px; display: flex; justify-content: space-between; align-items: center;">
                                        <span style="text-align: left;">Card No: ${cardNo}</span>
                                        <span style="text-align: right;color : green;">Any ${pattern} line</span>
                                    </div>`;

                                    html += `<table id="tablefetch" style="width: 90%; margin: 0 auto; position: relative; text-align: center; border-collapse: collapse;">
                                        <thead>
                                        <tr style="line-height: 2.5;">
                                            <th class="b" style="background-color: #00478B; color: white;">B</th>
                                            <th class="i" style="background-color: #00478B; color: white;">I</th>
                                            <th class="n" style="background-color: #00478B; color: white;">N</th>
                                            <th class="g" style="background-color: #00478B; color: white;">G</th>
                                            <th class="o" style="background-color: #00478B; color: white;">O</th>
                                        </tr>
                                        </thead>
                                        <tbody>`;

                                    if (pattern) {
                                        // Define winning corners
                                        const winningCorners = [
                                            card['B'][0], // Top-left corner
                                            card['O'][0], // Top-right corner
                                            card['B'][4], // Bottom-left corner
                                            card['O'][4]  // Bottom-right corner
                                        ];

                                        const winningCenterCorners = [
                                            card['I'][1], // Top-left corner
                                            card['G'][1], // Top-right corner
                                            card['I'][3], // Bottom-left corner
                                            card['G'][3]  // Bottom-right corner
                                        ];

                                        // Check if all winning corners are found
                                        let allCornersFound = winningCorners.every(corner => drawnNumbers.includes(parseInt(corner)));
                                        if (allCornersFound) countWinningLine++;

                                        // Check if all winning center corners are found
                                        let allCenterCornersFound = winningCenterCorners.every(corner => drawnNumbers.includes(parseInt(corner)));
                                        if (allCenterCornersFound) countWinningLine++;

                                        // Initialize arrays to track winning patterns
                                        const winningRows = [];
                                        const winningRowsNum = [];
                                        const winningColumns = {};
                                        const winningColumnsNum = {};
                                        const winningDiagonals = [false, false];
                                        const winningDiagonalsNum = [[], []];

                                        // Check rows
                                        for (let i = 0; i < 5; i++) {
                                            const rowNumbers = [
                                                parseInt(card['B'][i]),
                                                parseInt(card['I'][i]),
                                                parseInt(card['N'][i]),
                                                parseInt(card['G'][i]),
                                                parseInt(card['O'][i])
                                            ];

                                            const countDrawn = rowNumbers.filter(num => drawnNumbers.includes(num)).length;
                                            const expectedCount = (i === 2) ? 4 : 5;
                                            
                                            if (countDrawn >= expectedCount) {
                                                winningRowsNum[i] = rowNumbers.join(", ");
                                            }
                                            
                                            winningRows[i] = countDrawn >= expectedCount;
                                            if (winningRows[i]) countWinningLine++;
                                        }

                                        // Check columns
                                        const columnKeys = ['B', 'I', 'N', 'G', 'O'];
                                        columnKeys.forEach(colKey => {
                                            let count = 0;
                                            const columnNumbers = [];
                                            
                                            for (let i = 0; i < 5; i++) {
                                                const num = parseInt(card[colKey][i]);
                                                if (colKey === 'N' && i === 2) continue; // Skip center
                                                
                                                if (drawnNumbers.includes(num)) {
                                                    count++;
                                                    columnNumbers.push(num);
                                                }
                                            }
                                            
                                            const expectedCount = colKey === 'N' ? 4 : 5;
                                            winningColumns[colKey] = count >= expectedCount;
                                            if (winningColumns[colKey]) {
                                                winningColumnsNum[colKey] = columnNumbers;
                                                countWinningLine++;
                                            }
                                        });

                                        // Check diagonals
                                        const diag1 = [
                                            parseInt(card['B'][0]),
                                            parseInt(card['I'][1]),
                                            parseInt(card['N'][2]),
                                            parseInt(card['G'][3]),
                                            parseInt(card['O'][4])
                                        ];
                                        
                                        const diag2 = [
                                            parseInt(card['B'][4]),
                                            parseInt(card['I'][3]),
                                            parseInt(card['N'][2]),
                                            parseInt(card['G'][1]),
                                            parseInt(card['O'][0])
                                        ];
                                        
                                        winningDiagonals[0] = diag1.filter(num => drawnNumbers.includes(num)).length === 4;
                                        winningDiagonals[1] = diag2.filter(num => drawnNumbers.includes(num)).length === 4;
                                        winningDiagonalsNum[0] = diag1;
                                        winningDiagonalsNum[1] = diag2;
                                        
                                        if (winningDiagonals[0]) countWinningLine++;
                                        if (winningDiagonals[1]) countWinningLine++;

                                        // Generate the card HTML
                                        const lockedCells = {};
                                        const lockedCellsColor = {
                                            0: { B: null, I: null, N: null, G: null, O: null },
                                            1: { B: null, I: null, N: null, G: null, O: null },
                                            2: { B: null, I: null, N: null, G: null, O: null },
                                            3: { B: null, I: null, N: null, G: null, O: null },
                                            4: { B: null, I: null, N: null, G: null, O: null }
                                        };

                                        for (let i = 0; i < 5; i++) {
                                            html += "<tr>";
                                            
                                            columnKeys.forEach(colKey => {
                                                const number = parseInt(card[colKey][i]);
                                                let cellColor = 'white';
                                                let txtColor = 'black';
                                                let blinkClass = '';

                                                if (drawnNumbers.includes(number)) {
                                                    // Check winning corners
                                                    if (winningCorners.includes(number)) {
                                                        if (allCornersFound) {
                                                            if (specialPartnerIdCondition) {
                                                                winStatus = 1;
                                                            }
                                                            
                                                            const found = (lastCalled === number);
                                                            if (found) {
                                                                cellColor = 'green';
                                                                txtColor = 'white';
                                                                if (!lockedCells[i]) lockedCells[i] = {};
                                                                lockedCells[i][colKey] = true;
                                                                lockedCellsColor[i][colKey] = "green";
                                                                
                                                                winStatus = 1;
                                                                isNewBonusAvailable = "yes";
                                                            } else {
                                                                if (!lockedCells[i]) lockedCells[i] = {};
                                                                lockedCells[i][colKey] = true;
                                                                lockedCellsColor[i][colKey] = "#bca106";
                                                                cellColor = '#bca106';
                                                                txtColor = 'white';
                                                            }
                                                        } else {
                                                            cellColor = 'red';
                                                            txtColor = 'white';
                                                        }
                                                    }

                                                    // Check winning center corners
                                                    if (winningCenterCorners.includes(number)) {
                                                        if (allCenterCornersFound) {
                                                            if (specialPartnerIdCondition) {
                                                                winStatus = 1;
                                                            }
                                                            
                                                            const found = (lastCalled === number);
                                                            if (found) {
                                                                cellColor = 'green';
                                                                txtColor = 'white';
                                                                if (!lockedCells[i]) lockedCells[i] = {};
                                                                lockedCells[i][colKey] = true;
                                                                lockedCellsColor[i][colKey] = "green";
                                                                
                                                                winStatus = 1;
                                                                isNewBonusAvailable = "yes";
                                                            } else {
                                                                if (!lockedCells[i]) lockedCells[i] = {};
                                                                lockedCells[i][colKey] = true;
                                                                lockedCellsColor[i][colKey] = "#bca106";
                                                                cellColor = '#bca106';
                                                                txtColor = 'white';
                                                            }
                                                        } else {
                                                            cellColor = 'red';
                                                            txtColor = 'white';
                                                        }
                                                    }

                                                    // Check winning columns
                                                    if (winningColumns[colKey]) {
                                                        const found = winningColumnsNum[colKey].includes(lastCalled);
                                                        
                                                        if (specialPartnerIdCondition) {
                                                            winStatus = 1;
                                                        }
                                                        
                                                        if (found) {
                                                            cellColor = 'green';
                                                            txtColor = 'white';
                                                            if (!lockedCells[i]) lockedCells[i] = {};
                                                            lockedCells[i][colKey] = true;
                                                            lockedCellsColor[i][colKey] = "green";
                                                            
                                                            winStatus = 1;
                                                            isNewBonusAvailable = "yes";
                                                        } else {
                                                            if (!lockedCells[i] || !lockedCells[i][colKey]) {
                                                                if (!lockedCells[i]) lockedCells[i] = {};
                                                                lockedCells[i][colKey] = true;
                                                                lockedCellsColor[i][colKey] = "#bca106";
                                                            }
                                                            cellColor = '#bca106';
                                                            txtColor = 'white';
                                                        }
                                                    } else if (!winningColumns[colKey]) {
                                                        cellColor = 'red';
                                                        txtColor = 'white';
                                                    }

                                                    // Check winning rows
                                                    if (winningRows[i]) {
                                                        const rowNumbers = winningRowsNum[i].split(', ').map(num => parseInt(num));
                                                        const found = rowNumbers.includes(lastCalled);
                                                        
                                                        if (specialPartnerIdCondition) {
                                                            winStatus = 1;
                                                        }
                                                        
                                                        if (found) {
                                                            cellColor = 'green';
                                                            txtColor = 'white';
                                                            if (!lockedCells[i]) lockedCells[i] = {};
                                                            lockedCells[i][colKey] = true;
                                                            lockedCellsColor[i][colKey] = "green";
                                                            
                                                            winStatus = 1;
                                                            isNewBonusAvailable = "yes";
                                                        } else {
                                                            if (!lockedCells[i] || !lockedCells[i][colKey]) {
                                                                if (!lockedCells[i]) lockedCells[i] = {};
                                                                lockedCells[i][colKey] = true;
                                                                lockedCellsColor[i][colKey] = "#bca106";
                                                            }
                                                            cellColor = '#bca106';
                                                            txtColor = 'white';
                                                        }
                                                    } else if (!winningRows[i]) {
                                                        cellColor = 'green';
                                                        txtColor = 'white';
                                                    }

                                                    // Check diagonals
                                                    if ((winningDiagonals[0] && (
                                                        (colKey === 'B' && i === 0) ||
                                                        (colKey === 'I' && i === 1) ||
                                                        (colKey === 'N' && i === 2) ||
                                                        (colKey === 'G' && i === 3) ||
                                                        (colKey === 'O' && i === 4)
                                                    )) || (winningDiagonals[1] && (
                                                        (colKey === 'B' && i === 4) ||
                                                        (colKey === 'I' && i === 3) ||
                                                        (colKey === 'N' && i === 2) ||
                                                        (colKey === 'G' && i === 1) ||
                                                        (colKey === 'O' && i === 0)
                                                    ))) {
                                                        if (specialPartnerIdCondition) {
                                                            winStatus = 1;
                                                        }
                                                        
                                                        const diagToCheck = winningDiagonals[0] ? 0 : 1;
                                                        const found = winningDiagonalsNum[diagToCheck].includes(lastCalled);
                                                        
                                                        if (found) {
                                                            cellColor = 'green';
                                                            txtColor = 'white';
                                                            if (!lockedCells[i]) lockedCells[i] = {};
                                                            lockedCells[i][colKey] = true;
                                                            
                                                            // Set colors for the entire diagonal
                                                            if (diagToCheck === 0) {
                                                                lockedCellsColor[0]['B'] = 'green';
                                                                lockedCellsColor[1]['I'] = 'green';
                                                                lockedCellsColor[2]['N'] = 'green';
                                                                lockedCellsColor[3]['G'] = 'green';
                                                                lockedCellsColor[4]['O'] = 'green';
                                                            } else {
                                                                lockedCellsColor[4]['B'] = 'green';
                                                                lockedCellsColor[3]['I'] = 'green';
                                                                lockedCellsColor[2]['N'] = 'green';
                                                                lockedCellsColor[1]['G'] = 'green';
                                                                lockedCellsColor[0]['O'] = 'green';
                                                            }
                                                            
                                                            winStatus = 1;
                                                            isNewBonusAvailable = "yes";
                                                        } else {
                                                            if (!lockedCells[i] || !lockedCells[i][colKey]) {
                                                                if (!lockedCells[i]) lockedCells[i] = {};
                                                                lockedCells[i][colKey] = true;
                                                                
                                                                // Set colors for the entire diagonal
                                                                if (diagToCheck === 0) {
                                                                    lockedCellsColor[0]['B'] = '#bca106';
                                                                    lockedCellsColor[1]['I'] = '#bca106';
                                                                    lockedCellsColor[2]['N'] = '#bca106';
                                                                    lockedCellsColor[3]['G'] = '#bca106';
                                                                    lockedCellsColor[4]['O'] = '#bca106';
                                                                } else {
                                                                    lockedCellsColor[4]['B'] = '#bca106';
                                                                    lockedCellsColor[3]['I'] = '#bca106';
                                                                    lockedCellsColor[2]['N'] = '#bca106';
                                                                    lockedCellsColor[1]['G'] = '#bca106';
                                                                    lockedCellsColor[0]['O'] = '#bca106';
                                                                }
                                                            }
                                                            cellColor = '#bca106';
                                                            txtColor = 'white';
                                                        }
                                                    } else {
                                                        cellColor = 'red';
                                                        txtColor = 'white';
                                                    }

                                                    // Handle center cell
                                                    if ((winningRows[2] || winningColumns[colKey] || winningDiagonals[0] || winningDiagonals[1]) && 
                                                        (i === 2 && colKey === 'N')) {
                                                        cellColor = '#bca106';
                                                        txtColor = 'white';
                                                        
                                                        if (specialPartnerIdCondition) {
                                                            cellColor = 'green';
                                                        }
                                                        
                                                        let isGreen = false;
                                                        
                                                        if (winningRows[2] && lockedCellsColor[2]['B'] === "green") {
                                                            isGreen = true;
                                                        }
                                                        
                                                        if (!isGreen && winningColumns[colKey] && lockedCellsColor[0][colKey] === "green") {
                                                            isGreen = true;
                                                        }
                                                        
                                                        if (!isGreen) {
                                                            if (winningDiagonals[0] && lockedCellsColor[0]['B'] === "green") {
                                                                isGreen = true;
                                                            }
                                                            
                                                            if (!isGreen && winningDiagonals[1] && lockedCellsColor[0]['O'] === "green") {
                                                                isGreen = true;
                                                            }
                                                        }
                                                        
                                                        if (isGreen) {
                                                            cellColor = 'green';
                                                            txtColor = 'white';
                                                            lockedCellsColor[i][colKey] = 'green';
                                                        } else {
                                                            lockedCellsColor[i][colKey] = cellColor;
                                                        }
                                                    } else if (drawnNumbers.length > 0 && (i === 2 && colKey === 'N')) {
                                                        cellColor = 'red';
                                                        txtColor = 'white';
                                                    }

                                                    // Handle locked cells
                                                    if (lockedCells[i] && lockedCells[i][colKey]) {
                                                        cellColor = specialPartnerIdCondition ? 'green' : lockedCellsColor[i][colKey];
                                                    }

                                                    // Highlight last called number
                                                    if (number === lastCalled) {
                                                        cellColor = 'blue';
                                                        txtColor = 'white';
                                                        blinkClass = 'blink';
                                                    }
                                                }

                                                const displayNumber = (number === 0) ? "★" : number;
                                                html += `<td class="${blinkClass}" style="background-color: ${cellColor}; color: ${txtColor}; border: 2px solid #ccc; font-size: 48px; text-align: center; vertical-align: middle;">${displayNumber}</td>`;
                                            });
                                            
                                            html += "</tr>";
                                        }
                                    }
                                    
                                    html += `</tbody></table><br/><br/><br/>`;

                                    // Display the result
                                    const response = {
                                        status: "success",
                                        message: html,
                                        win_status: winStatus,
                                        last_called: lastCalled,
                                        new_bonus_status: newBonusStatus,
                                        is_new_bonus_available: isNewBonusAvailable,
                                        count_winning_line: countWinningLine,
                                        expected_pattern: pattern
                                    };
                                    
                                    displayResult(response);
                                } else {
                                    displayResult({
                                        status: "success",
                                        message: "No data found."
                                    });
                                }
                            } catch (error) {
                                displayError(error.message);
                            }
                        }

                        // Pattern checking functions
                        function checkPattern(card, drawnNumbers, pattern) {
                            switch (pattern) {
                                case '1': return checkAnyOneLine(card, drawnNumbers);
                                case '2': return checkAnyTwoLines(card, drawnNumbers);
                                case '3': return checkAnyVertical(card, drawnNumbers);
                                case '4': return checkAnyHorizontal(card, drawnNumbers);
                                case '5': return checkTPattern(card, drawnNumbers);
                                case '6': return checkReverseTPattern(card, drawnNumbers);
                                case '7': return checkXPattern(card, drawnNumbers);
                                case '8': return checkLPattern(card, drawnNumbers);
                                case '9': return checkReverseLPattern(card, drawnNumbers);
                                case '10': return checkHalfAbove(card, drawnNumbers);
                                case '11': return checkHalfBelow(card, drawnNumbers);
                                case '12': return checkFullPattern(card, drawnNumbers);
                                default: return false;
                            }
                        }

                        function checkAnyOneLine(card, drawnNumbers) {
                            const columns = ['B', 'I', 'N', 'G', 'O'];
                            return columns.some(col => {
                                return card[col].every(num => drawnNumbers.includes(parseInt(num)));
                            });
                        }

                        function checkAnyTwoLines(card, drawnNumbers) {
                            let lineCount = 0;
                            const columns = ['B', 'I', 'N', 'G', 'O'];
                            
                            // Check horizontal lines
                            columns.forEach(col => {
                                if (card[col].every(num => drawnNumbers.includes(parseInt(num)))) {
                                    lineCount++;
                                }
                            });

                            // Check vertical lines
                            for (let i = 0; i < 5; i++) {
                                const vertical = [
                                    parseInt(card['B'][i]),
                                    parseInt(card['I'][i]),
                                    parseInt(card['N'][i]),
                                    parseInt(card['G'][i]),
                                    parseInt(card['O'][i])
                                ];
                                
                                if (vertical.every(num => drawnNumbers.includes(num))) {
                                    lineCount++;
                                }
                            }

                            return lineCount >= 2;
                        }

                        function checkAnyVertical(card, drawnNumbers) {
                            for (let i = 0; i < 5; i++) {
                                const vertical = [
                                    parseInt(card['B'][i]),
                                    parseInt(card['I'][i]),
                                    parseInt(card['N'][i]),
                                    parseInt(card['G'][i]),
                                    parseInt(card['O'][i])
                                ];
                                
                                if (vertical.every(num => drawnNumbers.includes(num))) {
                                    return true;
                                }
                            }
                            return false;
                        }

                        function checkAnyHorizontal(card, drawnNumbers) {
                            const columns = ['B', 'I', 'N', 'G', 'O'];
                            return columns.some(col => {
                                return card[col].every(num => drawnNumbers.includes(parseInt(num)));
                            });
                        }

                        function checkTPattern(card, drawnNumbers) {
                            const topRow = [
                                parseInt(card['B'][0]),
                                parseInt(card['I'][0]),
                                parseInt(card['N'][0]),
                                parseInt(card['G'][0]),
                                parseInt(card['O'][0])
                            ];
                            
                            const middleColumn = [
                                parseInt(card['N'][0]),
                                parseInt(card['N'][1]),
                                parseInt(card['N'][2]),
                                parseInt(card['N'][3]),
                                parseInt(card['N'][4])
                            ];
                            
                            return topRow.every(num => drawnNumbers.includes(num)) && 
                                middleColumn.every(num => drawnNumbers.includes(num));
                        }

                        function checkReverseTPattern(card, drawnNumbers) {
                            const bottomRow = [
                                parseInt(card['B'][4]),
                                parseInt(card['I'][4]),
                                parseInt(card['N'][4]),
                                parseInt(card['G'][4]),
                                parseInt(card['O'][4])
                            ];
                            
                            const middleColumn = [
                                parseInt(card['N'][0]),
                                parseInt(card['N'][1]),
                                parseInt(card['N'][2]),
                                parseInt(card['N'][3]),
                                parseInt(card['N'][4])
                            ];
                            
                            return bottomRow.every(num => drawnNumbers.includes(num)) && 
                                middleColumn.every(num => drawnNumbers.includes(num));
                        }

                        function checkXPattern(card, drawnNumbers) {
                            const diag1 = [
                                parseInt(card['B'][0]),
                                parseInt(card['I'][1]),
                                parseInt(card['N'][2]),
                                parseInt(card['G'][3]),
                                parseInt(card['O'][4])
                            ];
                            
                            const diag2 = [
                                parseInt(card['B'][4]),
                                parseInt(card['I'][3]),
                                parseInt(card['N'][2]),
                                parseInt(card['G'][1]),
                                parseInt(card['O'][0])
                            ];
                            
                            return diag1.every(num => drawnNumbers.includes(num)) && 
                                diag2.every(num => drawnNumbers.includes(num));
                        }

                        function checkLPattern(card, drawnNumbers) {
                            const leftColumn = [
                                parseInt(card['B'][0]),
                                parseInt(card['B'][1]),
                                parseInt(card['B'][2]),
                                parseInt(card['B'][3]),
                                parseInt(card['B'][4])
                            ];
                            
                            const bottomRow = [
                                parseInt(card['B'][4]),
                                parseInt(card['I'][4]),
                                parseInt(card['N'][4]),
                                parseInt(card['G'][4]),
                                parseInt(card['O'][4])
                            ];
                            
                            return leftColumn.every(num => drawnNumbers.includes(num)) && 
                                bottomRow.every(num => drawnNumbers.includes(num));
                        }

                        function checkReverseLPattern(card, drawnNumbers) {
                            const rightColumn = [
                                parseInt(card['O'][0]),
                                parseInt(card['O'][1]),
                                parseInt(card['O'][2]),
                                parseInt(card['O'][3]),
                                parseInt(card['O'][4])
                            ];
                            
                            const bottomRow = [
                                parseInt(card['B'][4]),
                                parseInt(card['I'][4]),
                                parseInt(card['N'][4]),
                                parseInt(card['G'][4]),
                                parseInt(card['O'][4])
                            ];
                            
                            return rightColumn.every(num => drawnNumbers.includes(num)) && 
                                bottomRow.every(num => drawnNumbers.includes(num));
                        }

                        function checkHalfAbove(card, drawnNumbers) {
                            const halfAbove = [
                                parseInt(card['B'][0]), parseInt(card['B'][1]),
                                parseInt(card['I'][0]), parseInt(card['I'][1]),
                                parseInt(card['N'][0]), parseInt(card['N'][1]),
                                parseInt(card['G'][0]), parseInt(card['G'][1]),
                                parseInt(card['O'][0]), parseInt(card['O'][1])
                            ];
                            
                            return halfAbove.every(num => drawnNumbers.includes(num));
                        }

                        function checkHalfBelow(card, drawnNumbers) {
                            const halfBelow = [
                                parseInt(card['B'][3]), parseInt(card['B'][4]),
                                parseInt(card['I'][3]), parseInt(card['I'][4]),
                                parseInt(card['N'][3]), parseInt(card['N'][4]),
                                parseInt(card['G'][3]), parseInt(card['G'][4]),
                                parseInt(card['O'][3]), parseInt(card['O'][4])
                            ];
                            
                            return halfBelow.every(num => drawnNumbers.includes(num));
                        }

                        function checkFullPattern(card, drawnNumbers) {
                            const fullCard = [
                                ...card['B'].map(num => parseInt(num)),
                                ...card['I'].map(num => parseInt(num)),
                                ...card['N'].map(num => parseInt(num)),
                                ...card['G'].map(num => parseInt(num)),
                                ...card['O'].map(num => parseInt(num))
                            ];
                            
                            return fullCard.every(num => drawnNumbers.includes(num));
                        }

                        // Helper functions
                        function displayResult(response) {
                            const resultDiv = document.getElementById('result');
                            resultDiv.innerHTML = response.message;
                            
                            // You can also use other response properties as needed
                            console.log("Win Status:", response.win_status);
                            console.log("Last Called:", response.last_called);
                            console.log("New Bonus Status:", response.new_bonus_status);
                            console.log("Is New Bonus Available:", response.is_new_bonus_available);
                            console.log("Count Winning Line:", response.count_winning_line);
                            console.log("Expected Pattern:", response.expected_pattern);
                        }

                        function displayError(errorMessage) {
                            const resultDiv = document.getElementById('result');
                            resultDiv.innerHTML = `<div class="error">Error: ${errorMessage}</div>`;
                        }
            </script>
        </body>
    </head>
</html>