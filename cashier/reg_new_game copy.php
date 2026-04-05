<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['loggedin'])) {
    header("Location: ../config/logout.php");
    exit;
}

include_once '../config/Database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

$cashier_id = $_SESSION['cashier_id'] ?? '';
$cartela_number = '';
$round_number = 1;

if ($cashier_id) {
    $query = "SELECT game.`round_number`, game.`cartela_number`, game.`iscompleted`
              FROM `game` 
              WHERE game.`cashier_id` = ?
              ORDER BY game.`round_number` DESC
              LIMIT 1";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $cashier_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $cartela_number = str_replace(' ', '', $row['cartela_number']);
        $round_number = ($row['iscompleted'] == 0) ? $row['round_number'] : $row['round_number'] + 1;
    }
    $stmt->close();
}

// Get total card count
$query_cartela = "SELECT COUNT(cartela.cartela_number) 
                 FROM cartela 
                 JOIN cashier ON cartela.category = cashier.category 
                 WHERE cashier.cashier_id = ?";
$stmt = $conn->prepare($query_cartela);
$stmt->bind_param("s", $cashier_id);
$stmt->execute();
$stmt->bind_result($count_cartela_amount);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Ethiomark Bingo</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="../bootstrap/css/style.css">
    <link rel="stylesheet" href="../bootstrap/css/register_new_game.css">
    <script src="../bootstrap/js/jquery.js"></script>
    <script src="../bootstrap/js/bootstrap.min.js"></script>
    <link rel="stylesheet" type="text/css" href="side-nav.css">
    
    <link rel="stylesheet" href="css/bingo-interface.css">
    <style>
        /* Base Styles */
body {
    /* background: linear-gradient(135deg, #1a2a3a, #2c3e50); */
    background-size: 400% 400%;
    margin: 0;
    display: flex;
    justify-content: flex-start;
    height: 100vh;
    position: relative;
    overflow: hidden;
    color: #ffffff;
    overflow-y: auto;
    font-family: 'Arial', sans-serif;
}

/* Main Content */
.main-content {
    margin-top: 0;
    padding-top: 0;
    width: 100%;
}

.content {
    display: flex;
    justify-content: space-between;
    width: 100%;
    gap: 20px;
}

/* Game Round Title */
.game-round-title {
    margin-top: 1px;
    text-align: center;
    color: white;
    font-size: 38px;
}

.game-round-title span {
    color: gray;
    font-size: 30px;
}

.help-btn {
    margin-left: 20px;
    font-size: 20px;
    background: #077C6C;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 5px 10px;
    cursor: pointer;
    transition: all 0.3s;
}

.help-btn:hover {
    background: #066156;
}

/* Section Divider */
.section-divider {
    border: 0;
    height: 3px;
    background-color: #077C6C;
    margin: 5px 0;
    width: 95%;
    opacity: 0.9;
    padding: 5px 0;
}

/* View Toggle */
.view-toggle {
    display: flex;
    justify-content: center;
    margin: 15px 0;
    gap: 10px;
    align-items: center;
}

.toggle-btn {
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
    transition: all 0.3s;
    background: #2c3e50;
    color: white;
}

.toggle-btn.active {
    background-color: #077C6C;
    color: white;
}

.toggle-btn:hover {
    background: #34495e;
}

.box-size-controls {
    display: flex;
    gap: 5px;
    align-items: center;
    margin-left: 20px;
}

.box-size-controls button {
    padding: 5px 10px;
    border: none;
    border-radius: 3px;
    cursor: pointer;
    background: #3498db;
    color: white;
}

.box-size-controls button:hover {
    background: #2980b9;
}

/* Number Boxes */
:root {
    --box-size: 60px;
}

.box, .number-box {
    width: var(--box-size);
    height: var(--box-size);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s;
    margin: 5px;
    font-size: calc(var(--box-size) * 0.5);
}

.box {
    background: radial-gradient(circle, #ffffff 0%, #f0f0f0 40%, #012e3a 80%, #0c578e 100%);
    border: 1px solid #000000;
    color: #000000;
}

.number-box {
    background: #f0f0f0;
    color: #000000;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    padding: 10px;
}

.box:hover, .number-box:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(0,0,0,0.3);
}

.snum, .selected {
    background: #28a745 !important;
    color: white !important;
    opacity: 0.9;
}

/* Container Styles */
.container {
    display: flex;
    flex-wrap: wrap;
    width: 100%;
    margin: 0 auto;
    justify-content: center;
}

.next {
    display: flex;
    flex-wrap: wrap;
    width: 80%;
    margin: 20px auto;
    justify-content: center;
    gap: 10px;
}

.next button {
    font-weight: 900;
    height: 3rem;
    width: 6rem;
    margin: 0 4px;
    border: 2px solid #ffffff;
    background-color: #0e8cc7;
    color: #ffffff;
    cursor: pointer;
    transition: background-color 0.3s;
    border-radius: 4px;
}

.next button:hover {
    background-color: #162536;
}

.next button.active-page {
    background-color: #077C6C;
}

/* Resizable Container */
.resizable-container {
    position: relative;
    width: 120%;
    height: 700px;
    min-width: 800px;
    max-width: 98%;
    min-height: 300px;
    max-height: 1000px;
    border: 2px solid #077C6C;
    border-radius: 8px;
    overflow: hidden;
    resize: both;
    background-color: rgba(255, 255, 255, 0.1);
    margin-bottom: 20px;
}

.numbers-wrapper {
    padding: 15px;
    height: calc(100% - 30px);
    overflow-y: auto;
}

.numbers-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(var(--box-size), 1fr));
    gap: 20px;
    width: 100%;
}

/* Registration Card */
#regcard {
    display: none;
    width: 35%;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.selected-numbers-title {
    letter-spacing: 2px;
    color: white;
    margin-top: 10px;
    font-size: 38px;
    text-align: center;
}

#tablereg {
    width: 100%;
    margin: 20px 0;
    border-collapse: separate;
    border-spacing: 10px;
}

.custom-cell {
    background: radial-gradient(circle, #000000, #5a6503);
    border: 2px solid rgb(0, 0, 0);
    color: rgb(255, 255, 255);
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: inline-block;
    vertical-align: middle;
    text-align: center;
    font-size: 24px;
    font-weight: 600;
    line-height: 60px;
}

.form-controls {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.form-control {
    font-size: 24px;
    font-weight: bold;
    padding: 10px;
    border-radius: 4px;
    border: 1px solid #077C6C;
    background: rgba(255,255,255,0.9);
}

.action-buttons {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.login-btn {
    cursor: pointer;
    transition: all 0.3s;
    border: none;
    border-radius: 4px;
    font-size: 24px;
    padding: 10px;
    font-weight: bold;
}

.play-btn {
    width: 70%;
    background: #077C6C;
    color: white;
}

.play-btn:hover {
    background: #066156;
}

.clear-btn {
    width: 30%;
    background-color: #BB191A;
    color: white;
}

.clear-btn:hover {
    background-color: #9e1516;
}

/* Help Modal */
.help-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.6);
    z-index: 9999;
}

.help-modal-content {
    background: #275762;
    color: white;
    width: 90%;
    max-width: 900px;
    margin: 10% auto;
    padding: 20px;
    border-radius: 10px;
    position: relative;
}

.close-modal {
    position: absolute;
    top: 10px;
    right: 15px;
    cursor: pointer;
    font-size: 34px;
}

.help-modal h2 {
    text-align: center;
    margin-bottom: 20px;
}

.help-modal ul {
    font-size: 20px;
    line-height: 1.6;
    padding-left: 20px;
}

.help-modal li {
    margin-bottom: 10px;
}

/* Typing Helper */
.typing-helper {
    position: fixed;
    top: 20px;
    left: 80px;
    background: #222;
    color: white;
    padding: 15px 20px;
    border-radius: 10px;
    font-size: 20px;
    display: none;
    z-index: 9999;
    box-shadow: 0 4px 8px rgba(0,0,0,0.3);
}

#typingHelperNumber {
    color: yellow;
    font-size: 24px;
}

#typingHelperAction {
    font-size: 18px;
    margin-top: 5px;
    color: gray;
}

/* Alerts */
.custom-alert {
    display: block;
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1000;
    padding: 15px 25px;
    border-radius: 4px;
    font-size: 16px;
    opacity: 0;
    transform: translateX(100%);
    transition: all 0.3s ease;
}

.custom-alert-success {
    background: #28a745;
    color: white;
}

.custom-alert-danger {
    background: #dc3545;
    color: white;
}

.slide-in {
    opacity: 1;
    transform: translateX(0);
}

.slide-out {
    opacity: 0;
    transform: translateX(100%);
}

/* Responsive Design */
@media (max-width: 1200px) {
    .content {
        flex-direction: column;
    }
    
    .balls, #regcard {
        width: 100%;
    }
    
    #regcard {
        margin-top: 20px;
    }
}

@media (max-width: 768px) {
    .game-round-title {
        font-size: 28px;
    }
    
    .game-round-title span {
        font-size: 20px;
    }
    
    .help-btn {
        font-size: 16px;
    }
    
    .view-toggle {
        flex-direction: column;
    }
    
    .box-size-controls {
        margin-left: 0;
        margin-top: 10px;
    }
    
    .resizable-container {
        min-width: 100%;
    }
    
    :root {
        --box-size: 50px;
    }
    
    .form-control {
        font-size: 18px;
    }
    
    .login-btn {
        font-size: 18px;
    }
}

@media (max-width: 480px) {
    :root {
        --box-size: 40px;
    }
    
    .next button {
        width: 4rem;
        font-size: 14px;
    }
    
    .selected-numbers-title {
        font-size: 24px;
    }
    
    .custom-cell {
        width: 40px;
        height: 40px;
        font-size: 18px;
        line-height: 40px;
    }
}
    </style>
</head>
<body>
    <div id="customAlert" class="custom-alert"></div>
    <div id="user-content">
        <?php include_once("side-nav.php"); ?>

        <div class="main-content">
            <p style="display: none;" id="cardnum"><?php echo $cartela_number; ?></p>
            <p style="display: none;" id="totalcard"><?php echo !empty($count_cartela_amount) ? $count_cartela_amount : 200; ?></p>
            
            <div class='content'>
                <div class="balls">
                    <div class="balls-line">
                        <h1 class="game-round-title">
                            Game Round <span id="register_new_game_round"><?php echo $round_number; ?></span>
                            <br>
                            <span id="previw_typed_number">[በ ኪቦርድ መመዝገብ ይችላሉ ⌨️⌨️]</span>
                            <button onclick="openHelpModal()" class="help-btn">❓ Help</button>
                        </h1>
                        
                        <!-- Help Modal -->
                        <div id="helpModal" class="help-modal">
                            <div class="help-modal-content">
                                <span onclick="closeHelpModal()" class="close-modal">&times;</span>
                                <h2>🆘 እርዳታ</h2>
                                <ul>
                                    <li>⌫ በ Backspace የተጻፉትን ቁጥሮች ያስወግዱ</li>
                                    <li>⏎ ኪቦርድ ላይ የሚፈልጉትን ቁጥር ከጻፉ በኋላ Enter ወይም space ሲጫኑ ቀጥታ ይመዘገባል።</li>
                                    <li>❗ ቁጥሩ ከተዘረዘሩት ካርድ ቁትሮች መካከል መሆን አለበት</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Typing Helper -->
                        <div id="typingHelper" class="typing-helper">
                            <div><b>⌨️ Typing:</b> <span id="typingHelperNumber"></span></div>
                            <div id="typingHelperAction"></div>
                        </div>

                        <hr class="section-divider">
                        
                        <!-- View Toggle -->
                        <div class="view-toggle">
                            <button class="toggle-btn active" onclick="switchView('paginated')">Paginated View</button>
                            <button class="toggle-btn" onclick="switchView('scrollable')">Scrollable View</button>
                        </div>
                        
                        <!-- Paginated View (Default) -->
                        <div id="paginatedView">
                            <div class="container" id="circledNumbers"></div>
                            <div class="next" id="pagination">
                                <button onclick="previousPage()">Previous</button>
                                <div id="pageButtons"></div>
                                <button onclick="nextPage()">Next</button>
                            </div>
                        </div>
                        
                        <!-- Scrollable View -->
                        <div id="scrollableView">
                            <div class="resizable-container" id="resizableContainer">
                                <div class="numbers-wrapper">
                                    <div class="numbers-grid" id="numbersGrid"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Registration Card Section -->
                <div id="regcard">
                    <h1 class="selected-numbers-title">እየተመዘገቡ ያሉ ካርድ ቁጥሮች!</h1>
                    <table id="tablereg"></table>
                    
                    <div class="form-controls">
                        <select required class="form-control" name="birr" id="birr">
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

                        <select required class="form-control" name="pattern" id="pattern">
                            <option value="1">any one line</option>
                            <option value="2">any two lines</option>
                            <option value="3">any three lines</option>
                            <option value="5" hidden disabled>any vertical</option>
                            <option value="6" hidden disabled>any horizontal</option>
                            <option value="7" hidden disabled>T</option>
                            <option value="8" hidden disabled>reverse T</option>
                            <option value="9" hidden disabled>X</option>
                            <option value="10" hidden disabled>L</option>
                            <option value="11" hidden disabled>reverse L</option>
                            <option value="12" hidden disabled>half above</option>
                            <option value="13" hidden disabled>half below</option>
                            <option value="14" hidden disabled>full</option>
                        </select>

                        <div class="action-buttons">
                            <button class="login-btn play-btn" id="play">PLAY</button>
                            <button class="login-btn clear-btn" id="clearBtn">Clear</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div id="resultContainer"></div>
        </div>
    </div>

    <script>
    // Global variables
    let selectedNum = [];
    let typedNumber = "";
    let currentView = localStorage.getItem('bingoView') || 'paginated'; // Load saved view preference
    let currentPage = 1;
    const numbersPerPage = 200;
    let totalNumbers = parseInt(document.getElementById("totalcard").innerText);
    let totalPages = Math.ceil(totalNumbers / numbersPerPage);
    
    // Initialize on DOM load
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize views
        initPaginatedView();
        initScrollableView();
        
        // Load saved selections from localStorage
        loadSavedSelections();
        
        // Initialize card numbers from PHP if they exist
        loadInitialCards();
        
        // Set up event listeners
        setupEventListeners();
        
        // Show saved view
        switchView(currentView);
    });
    
    // Initialize paginated view
    function initPaginatedView() {
        showPage(1);
    }
    
    // Initialize scrollable view
    function initScrollableView() {
        // Load saved container size and box size
        const savedSize = localStorage.getItem('bingoContainerSize');
        const savedBoxSize = localStorage.getItem('bingoBoxSize') || '60px';
        
        if (savedSize) {
            const size = JSON.parse(savedSize);
            const container = document.getElementById('resizableContainer');
            container.style.width = size.width || '100%';
            container.style.height = size.height || '400px';
        }
        
        // Apply saved box size
        document.documentElement.style.setProperty('--box-size', savedBoxSize);
        
        // Generate all numbers
        generateAllNumbers();
        
        // Save size when container is resized
        let resizeTimer;
        const container = document.getElementById('resizableContainer');
        
        container.addEventListener('mouseup', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                localStorage.setItem('bingoContainerSize', JSON.stringify({
                    width: this.style.width,
                    height: this.style.height
                }));
            }, 300);
        });
    }
    
    // Switch between views
    function switchView(view) {
        currentView = view;
        localStorage.setItem('bingoView', view); // Save view preference
        
        // Update toggle buttons
        document.querySelectorAll('.toggle-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector(`.toggle-btn[onclick="switchView('${view}')"]`).classList.add('active');
        
        // Show/hide views
        document.getElementById('paginatedView').style.display = (view === 'paginated') ? 'block' : 'none';
        document.getElementById('scrollableView').style.display = (view === 'scrollable') ? 'block' : 'none';
        
        // Update pagination visibility
        document.getElementById('pagination').style.display = (view === 'paginated' && totalPages > 1) ? 'flex' : 'none';
    }
    
    // Paginated view functions
    function showPage(page) {
        currentPage = page;
        const startIndex = (page - 1) * numbersPerPage + 1;
        generateNumbers(startIndex);
        updatePagination();
    }
    
    function generateNumbers(startIndex) {
        const container = document.getElementById("circledNumbers");
        container.innerHTML = "";
        
        for (let i = startIndex; i < startIndex + numbersPerPage && i <= totalNumbers; i++) {
            const number = document.createElement("div");
            number.className = "box";
            number.textContent = i;
            number.setAttribute("numb", i);
            
            if (selectedNum.includes(i)) {
                number.classList.add("snum");
            }
            
            number.onclick = () => selecting(i);
            container.appendChild(number);
        }
    }
    
    function updatePagination() {
        const pageButtonsContainer = document.getElementById("pageButtons");
        pageButtonsContainer.innerHTML = "";
        
        for (let i = 1; i <= totalPages; i++) {
            const button = document.createElement("button");
            button.textContent = i;
            button.onclick = () => showPage(i);
            if (i === currentPage) {
                button.disabled = true;
                button.classList.add('active-page');
            }
            pageButtonsContainer.appendChild(button);
        }
    }
    
    function previousPage() {
        if (currentPage > 1) {
            showPage(currentPage - 1);
        }
    }
    
    function nextPage() {
        if (currentPage < totalPages) {
            showPage(currentPage + 1);
        }
    }
    
    // Scrollable view functions
    function generateAllNumbers() {
        const container = document.getElementById("numbersGrid");
        container.innerHTML = "";
        
        for (let i = 1; i <= totalNumbers; i++) {
            const number = document.createElement("div");
            number.className = "number-box";
            number.textContent = i;
            number.setAttribute("numb", i);
            
            if (selectedNum.includes(i)) {
                number.classList.add("selected");
            }
            
            number.onclick = () => selecting(i);
            container.appendChild(number);
        }
    }
    
    // Common functions
    function selecting(number) {
        const index = selectedNum.indexOf(number);
        
        if (index === -1) {
            selectedNum.push(number);
        } else {
            selectedNum.splice(index, 1);
        }
        
        // Update the specific number box in both views
        updateNumberSelection(number);
        updateSelectedNumbers();
    }
    
    function updateNumberSelection(number) {
        // Update in paginated view
        const paginatedElement = document.querySelector(`#paginatedView [numb="${number}"]`);
        if (paginatedElement) {
            paginatedElement.classList.toggle('snum', selectedNum.includes(number));
        }
        
        // Update in scrollable view
        const scrollableElement = document.querySelector(`#scrollableView [numb="${number}"]`);
        if (scrollableElement) {
            scrollableElement.classList.toggle('selected', selectedNum.includes(number));
        }
    }
    
    function updateSelectedNumbers() {
        const table = document.getElementById("tablereg");
        table.innerHTML = "";
        
        let countrow = 0;
        for (let i = 0; i < selectedNum.length; i++) {
            if (i % 6 === 0) {
                const row = table.insertRow(countrow);
                countrow++;
            }
            const cell = table.rows[countrow - 1].insertCell(i % 6);
            cell.innerHTML = selectedNum[i];
            cell.className = "custom-cell";
        }
        
        // Show/hide registration card based on selections
        document.getElementById('regcard').style.display = selectedNum.length > 0 ? 'block' : 'none';
    }
    
    function clearSelectedNumbers() {
        // Clear selections array
        selectedNum = [];
        
        // Update both views
        document.querySelectorAll('.snum, .selected').forEach(el => {
            el.classList.remove('snum', 'selected');
        });
        
        updateSelectedNumbers();
    }
    
    // Load saved selections from localStorage
    function loadSavedSelections() {
        const birrSelect = document.getElementById('birr');
        const patternSelect = document.getElementById('pattern');
        
        const savedBirr = localStorage.getItem('selectedBirr');
        const savedPattern = localStorage.getItem('selectedPattern');
        
        if (savedBirr) birrSelect.value = savedBirr;
        if (savedPattern) patternSelect.value = savedPattern;
        
        birrSelect.addEventListener('change', () => localStorage.setItem('selectedBirr', birrSelect.value));
        patternSelect.addEventListener('change', () => localStorage.setItem('selectedPattern', patternSelect.value));
    }
    
    // Load initial cards from PHP
    function loadInitialCards() {
        const cardnum = document.getElementById('cardnum').innerText.trim();
        if (cardnum !== "") {
            const cards = cardnum.split(',').map(Number);
            cards.forEach(card => selecting(card));
        }
    }
    
    // Typing helper functions
    function updateHelper() {
        document.getElementById("typingHelperNumber").innerText = typedNumber || "...";
        document.getElementById("typingHelper").style.display = "block";
    }
    
    function hideHelper(delay = 3000) {
        setTimeout(() => {
            document.getElementById("typingHelper").style.display = "none";
            document.getElementById("typingHelperAction").innerText = "";
        }, delay);
    }
    
    // Keyboard input handling
    function setupEventListeners() {
        // Keyboard input
        document.addEventListener("keydown", function(event) {
            document.getElementById("typingHelperAction").innerText = "🖊️ Number typed";
            updateHelper();
            
            if (event.key >= "0" && event.key <= "9") {
                typedNumber += event.key;
                updateHelper();
            } 
            else if (event.key === "Backspace") {
                typedNumber = typedNumber.slice(0, -1);
                document.getElementById("typingHelperAction").innerText = "❌ Deleted last digit";
                updateHelper();
            } 
            else if (event.key === "Enter" || event.key === " ") {
                event.preventDefault();
                
                if (typedNumber !== "") {
                    const number = parseInt(typedNumber);
                    
                    if (!isNaN(number) && number >= 1 && number <= totalNumbers) {
                        selecting(number);
                        document.getElementById("typingHelperAction").innerText = "✅ Selected with Enter/Space";
                    } else {
                        showAlert(`${number} <i style='color:white;'>❌</i> የተሳሳተ ቁጥር ነው የነኩ እባክዎትን ከ 1 እስከ ${totalNumbers} ቁጥር ብቻ ይንኩ !`, "danger");
                    }
                    
                    updateHelper();
                    hideHelper();
                    typedNumber = "";
                }
            }
        });
        
        // Clear button
        document.getElementById('clearBtn').addEventListener('click', clearSelectedNumbers);
        
        // Play button
        document.getElementById('play').addEventListener('click', function() {
            const cashier_id = localStorage.getItem('cashier_id');
            const selectedcard = selectedNum.join(', ');
            const price = document.getElementById("birr").value;
            const pattern = document.getElementById("pattern").value;
            const round = document.getElementById("register_new_game_round").innerHTML;
            
            if (price && selectedcard && pattern && cashier_id) {
                $.ajax({
                    type: 'POST',
                    url: '../config/DbFunction.php',
                    data: {
                        register_new_game_round: round,
                        selectedcard: selectedcard,
                        price: price,
                        pattern: pattern,
                        cashier_id: cashier_id
                    },
                    dataType: 'JSON',
                    success: function(resp) {
                        showAlert(resp.message, resp.status);
                        if (resp.status === "success") {
                            setTimeout(() => window.location.href = '../cashier', 1000);
                        }
                    },
                    error: function(xhr) {
                        console.error("AJAX Error:", xhr.responseText);
                        showAlert("An error occurred", "danger");
                    }
                });
            } else {
                showAlert("Please fill in all fields", "danger");
            }
        });
        
        // Help modal functions
        window.openHelpModal = function() {
            document.getElementById("helpModal").style.display = "block";
        };
        
        window.closeHelpModal = function() {
            document.getElementById("helpModal").style.display = "none";
        };
        
        window.onclick = function(event) {
            if (event.target === document.getElementById("helpModal")) {
                closeHelpModal();
            }
        };
    }
    
    // Alert function
    function showAlert(message, type) {
        const alertDiv = $('#customAlert');
        alertDiv.html(message)
            .removeClass('custom-alert-success custom-alert-danger slide-out')
            .addClass(`custom-alert-${type} slide-in`)
            .show();
        
        setTimeout(() => alertDiv.removeClass('slide-in'), 50);
        setTimeout(() => {
            alertDiv.addClass('slide-out');
            setTimeout(() => alertDiv.fadeOut(), 300);
        }, 3000);
    }
    
    // Login/logout handling
    if (localStorage.getItem('loggedin') !== 'true') {
        window.location.href = '../config/logout.php';
    }
    
    document.getElementById('logoutbtn').addEventListener('click', function() {
        localStorage.removeItem('loggedin');
        localStorage.removeItem('cashier_id');
        showAlert('Successfully logged out!', 'success');
        setTimeout(() => window.location.href = '../config/logout.php', 1000);
    });
    
    // Box size adjustment (added functionality)
    function adjustBoxSize(size) {
        const boxSize = size + 'px';
        document.documentElement.style.setProperty('--box-size', boxSize);
        localStorage.setItem('bingoBoxSize', boxSize);
        
        // Refresh the scrollable view if it's active
        if (currentView === 'scrollable') {
            generateAllNumbers();
        }
    }
    
    // Add box size controls to the UI
    function addBoxSizeControls() {
        const sizeControls = document.createElement('div');
        sizeControls.className = 'box-size-controls';
        sizeControls.innerHTML = `
            <span>Box Size: </span>
            <button onclick="adjustBoxSize(40)">Small</button>
            <button onclick="adjustBoxSize(60)">Medium</button>
            <button onclick="adjustBoxSize(80)">Large</button>
        `;
        document.querySelector('.view-toggle').appendChild(sizeControls);
    }
    
    // Initialize box size controls
    addBoxSizeControls();
    </script>
</body>
</html>