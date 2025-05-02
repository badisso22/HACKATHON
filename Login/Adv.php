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

// Check if daily goal is set
$stmt = $conn->prepare("SELECT daily_goal FROM user_onboarding WHERE user_ID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0 || $result->fetch_assoc()['daily_goal'] === null) {
    // If daily goal is not set, redirect to time selection
    header("Location: time.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug: Log the POST data
    error_log("POST data in Adv.php: " . print_r($_POST, true));
    
    $proficiency_level = $_POST['proficiency_level'] ?? null;

    if ($proficiency_level) {
        try {
            // Begin transaction to ensure all updates are completed together
            $conn->begin_transaction();
            
            // Update the proficiency level in user_onboarding table
            $stmt = $conn->prepare("UPDATE user_onboarding SET proficiency_level = ?, is_complete = 1 WHERE user_ID = ?");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("si", $proficiency_level, $user_id);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            // Mark onboarding as complete in users table
            $stmt = $conn->prepare("UPDATE users SET onboarding_complete = 1 WHERE user_ID = ?");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("i", $user_id);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            // Commit the transaction
            $conn->commit();
            
            // Debug: Log success
            error_log("Successfully completed onboarding for user_id=$user_id");
            
            // Redirect to dashboard
            header("Location: ../Dashboard/dashboard.php");
            exit();
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            error_log("Exception in Adv.php: " . $e->getMessage());
            echo "An error occurred: " . $e->getMessage();
            exit();
        }
    }
}

// Get the selected language for the question
$stmt = $conn->prepare("SELECT selected_language FROM user_onboarding WHERE user_ID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$selected_language = $result->fetch_assoc()['selected_language'] ?? 'this language';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Language Selection</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/main.css">
    <style>
        /* Fix for button styling to ensure they work as proper form buttons */
        .button {
            cursor: pointer;
            border: none;
            text-align: center;
            width: 100%;
        }
        
        /* Ensure the buttons don't have any styles that might interfere with clicking */
        .button:focus {
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

    <!-- Content section -->
    <div class="content-section">
        <div class="mascot">🦉</div>
        <div class="content-text">Do you already know some <?php echo htmlspecialchars($selected_language); ?>?</div>

        <!-- Buttons -->
        <form method="POST" action="" id="advForm">
            <div class="button-container">
                <a href="#" class="button" onclick="submitProficiency('beginner'); return false;">🐣 Beginner</a>
                <a href="#" class="button" onclick="submitProficiency('advanced'); return false;">🦅 Advanced</a>
            </div>
            <input type="hidden" id="proficiency_level" name="proficiency_level" value="">
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const progressBar = document.getElementById('progressBar');
            
            // Set progress to 100%
            progressBar.style.width = '100%';
        });
        
        // Function to submit the form with a specific proficiency level
        function submitProficiency(value) {
            console.log('Submitting proficiency level: ' + value);
            document.getElementById('proficiency_level').value = value;
            document.getElementById('advForm').submit();
        }
    </script>
</body>
</html>
