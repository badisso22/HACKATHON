<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $daily_goal = isset($_POST['daily_goal']) ? (int)$_POST['daily_goal'] : null; // Cast to integer

    // Debugging: Log the POST data
    error_log("POST data: " . print_r($_POST, true));

    if ($daily_goal) {
        $daily_goal_str = $daily_goal . ' min'; // Convert to string with 'min'

        try {
            // Debugging: Log the SQL query and parameters
            error_log("Preparing to update daily goal: user_id=$user_id, daily_goal=$daily_goal_str");

            $stmt = $conn->prepare("UPDATE user_onboarding SET `daily goal` = ? WHERE user_ID = ?");
            if (!$stmt) {
                error_log("SQL Prepare Error: " . $conn->error);
                echo "An error occurred while saving your daily goal. Please try again.";
                exit();
            }

            $stmt->bind_param("si", $daily_goal_str, $user_id);
            if (!$stmt->execute()) {
                error_log("SQL Execution Error: " . $stmt->error);
                echo "An error occurred while saving your daily goal. Please try again.";
                exit();
            }

            // Debugging: Log successful query execution
            error_log("Successfully updated daily goal for user_id=$user_id");

            header("Location: adv4.php");
            exit();
        } catch (Exception $e) {
            error_log("Exception: " . $e->getMessage());
            echo "An error occurred while saving your daily goal. Please try again.";
        }
    } else {
        error_log("Invalid input: user_id=" . $user_id . ", daily_goal=" . $daily_goal);
        echo "Invalid input. Please select a daily goal.";
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
    <link rel="stylesheet" href="lan1.css">
</head>
<body>
    <div class="progress-container">
        <div class="progress-bar"></div>
    </div>
    <header class="header">
        <div class="logo-container">
            <div class="logo-icon">🌎</div>
            <div class="logo-text">Mura</div>
        </div>
        <button class="settings-button">⚙️ Settings</button>
    </header>
    <div class="question-section">
        <div class="mascot">🦉</div>
        <div class="question-text">What is your daily goal?</div>
    </div>
    <form method="POST" action="">
        <div class="options-container">
            <button type="submit" class="option" name="daily_goal" value="3">
                <div class="option-icon icon-1">3</div>
                <div class="option-content">
                    <div class="option-text">3 min/day</div>
                    <div class="option-subtext">Casual</div>
                </div>
            </button>
            <button type="submit" class="option" name="daily_goal" value="10">
                <div class="option-icon icon-2">10</div>
                <div class="option-content">
                    <div class="option-text">10 min/day</div>
                    <div class="option-subtext">Normal</div>
                </div>
            </button>
            <button type="submit" class="option" name="daily_goal" value="15">
                <div class="option-icon icon-3">15</div>
                <div class="option-content">
                    <div class="option-text">15 min/day</div>
                    <div class="option-subtext">Intensive</div>
                </div>
            </button>
            <button type="submit" class="option" name="daily_goal" value="30">
                <div class="option-icon icon-4">30</div>
                <div class="option-content">
                    <div class="option-text">30 min/day</div>
                    <div class="option-subtext">Extreme</div>
                </div>
            </button>
        </div>
    </form>
</body>
</html>