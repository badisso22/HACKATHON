<?php
session_start();
require_once '../Configurations/db.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

// Check if onboarding is already complete
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT onboarding_complete FROM users WHERE user_ID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if ($user['onboarding_complete']) {
        // If onboarding is already complete, redirect to dashboard
        header("Location: ../Dashboard/dashboard.php");
        exit();
    }
}

// Check if reason is selected
$stmt = $conn->prepare("SELECT reason FROM user_onboarding WHERE user_ID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0 || $result->fetch_assoc()['reason'] === null) {
    // If reason is not selected, redirect to reason selection
    header("Location: Qst.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug: Log the POST data
    error_log("POST data in time.php: " . print_r($_POST, true));
    
    $daily_goal = isset($_POST['daily_goal']) ? $_POST['daily_goal'] : null;
    
    if ($daily_goal) {
        // Format the daily goal with 'min' suffix if it's a number
        if (is_numeric($daily_goal)) {
            $daily_goal_str = $daily_goal . ' min';
        } else {
            $daily_goal_str = $daily_goal;
        }
        
        try {
            // Debug: Log the values being used
            error_log("Updating daily_goal for user_id=$user_id to $daily_goal_str");
            
            // Begin transaction
            $conn->begin_transaction();
            
            // Update the user_onboarding table
            $stmt = $conn->prepare("UPDATE user_onboarding SET daily_goal = ? WHERE user_ID = ?");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("si", $daily_goal_str, $user_id);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            // Commit the transaction
            $conn->commit();
            
            // Debug: Log success
            error_log("Successfully updated daily_goal for user_id=$user_id to $daily_goal_str");
            
            // Redirect to the next step
            header("Location: Adv.php");
            exit();
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            error_log("Exception in time.php: " . $e->getMessage());
            echo "An error occurred: " . $e->getMessage();
            exit();
        }
    } else {
        error_log("Invalid daily_goal value: " . print_r($_POST, true));
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mura - Daily Goal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/main.css">
    <style>
        /* Fix for button styling to ensure they work as proper form buttons */
        .option {
            cursor: pointer;
            border: none;
            text-align: left;
            width: 100%;
            display: flex;
            align-items: center;
        }
        
        /* Ensure the option buttons don't have any styles that might interfere with clicking */
        .option:focus {
            outline: none;
        }
    </style>
</head>
<body>
    <!-- Progress bar -->
    <div class="progress-container">
        <div class="progress-bar" id="progressBar"></div>
    </div>

    <!-- Header -->
    <header class="header">
        <div class="logo-container">
            <div class="logo-icon">🌎</div>
            <div class="logo-text">Mura</div>
        </div>
        <button type="button" class="settings-button">⚙️ Settings</button>
    </header>

    <!-- Question section -->
    <div class="question-section">
        <div class="mascot">🦉</div>
        <div class="question-text">What is your daily goal?</div>
    </div>

    <!-- Options grid -->
    <form method="POST" action="" id="timeForm">
        <div class="options-container">
            <!-- Using links with JavaScript to submit the form instead of buttons -->
            <a href="#" class="option" data-value="3" onclick="submitTimeValue(3); return false;">
                <div class="option-icon icon-1">3</div>
                <div class="option-content">
                    <div class="option-text">3 min/day</div>
                    <div class="option-subtext">Casual</div>
                </div>
            </a>

            <a href="#" class="option" data-value="10" onclick="submitTimeValue(10); return false;">
                <div class="option-icon icon-2">10</div>
                <div class="option-content">
                    <div class="option-text">10 min/day</div>
                    <div class="option-subtext">Normal</div>
                </div>
            </a>

            <a href="#" class="option" data-value="15" onclick="submitTimeValue(15); return false;">
                <div class="option-icon icon-3">15</div>
                <div class="option-content">
                    <div class="option-text">15 min/day</div>
                    <div class="option-subtext">Intensive</div>
                </div>
            </a>

            <a href="#" class="option" data-value="30" onclick="submitTimeValue(30); return false;">
                <div class="option-icon icon-4">30</div>
                <div class="option-content">
                    <div class="option-text">30 min/day</div>
                    <div class="option-subtext">Extreme</div>
                </div>
            </a>

            <div class="option" id="custom-option">
                <div class="option-icon icon-5">❓</div>
                <div class="option-content">
                    <div class="option-text">Other</div>
                    <div class="option-subtext">Custom</div>
                </div>
            </div>
        </div>

        <!-- Hidden input to store the selected value -->
        <input type="hidden" id="daily_goal" name="daily_goal" value="">

        <!-- Custom time input (hidden by default) -->
        <div class="custom-time-container" id="custom-time-container">
            <div class="custom-time-input">
                <input type="number" id="custom-time" min="1" max="120" placeholder="Enter time">
                <span>minutes per day</span>
            </div>
            <button type="button" onclick="submitCustomTime()" class="continue-btn">CONTINUE</button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const progressBar = document.getElementById('progressBar');
            const customOption = document.getElementById('custom-option');
            const customTimeContainer = document.getElementById('custom-time-container');
            const customTimeInput = document.getElementById('custom-time');
            const timeForm = document.getElementById('timeForm');
            
            // Set progress to 75%
            progressBar.style.width = '75%';
            
            // Show custom time input when "Other" is clicked
            customOption.addEventListener('click', function(e) {
                e.preventDefault();
                customTimeContainer.classList.add('active');
                customTimeInput.focus();
            });
        });
        
        // Function to submit the form with a specific time value
        function submitTimeValue(value) {
            console.log('Submitting time value: ' + value);
            document.getElementById('daily_goal').value = value;
            document.getElementById('timeForm').submit();
        }
        
        // Function to submit the custom time value
        function submitCustomTime() {
            const customValue = document.getElementById('custom-time').value;
            if (customValue && !isNaN(customValue) && customValue > 0) {
                console.log('Submitting custom time value: ' + customValue);
                document.getElementById('daily_goal').value = customValue;
                document.getElementById('timeForm').submit();
            } else {
                alert('Please enter a valid time value.');
            }
        }
    </script>
</body>
</html>

