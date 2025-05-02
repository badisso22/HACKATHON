<?php
session_start();
require_once '../Configurations/db.php';

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
        header("Location: dashboard.php");
        exit();
    }
}

// Check if language is selected
$stmt = $conn->prepare("SELECT selected_language FROM user_onboarding WHERE user_ID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0 || $result->fetch_assoc()['selected_language'] === null) {
    // If language is not selected, redirect to language selection
    header("Location: lan.php");
    exit();
}

// Get the selected language
$stmt = $conn->prepare("SELECT selected_language FROM user_onboarding WHERE user_ID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$selected_language = $result->fetch_assoc()['selected_language'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason = $_POST['reason'] ?? null;

    if ($reason) {
        $stmt = $conn->prepare("UPDATE user_onboarding SET reason = ? WHERE user_ID = ?");
        $stmt->bind_param("si", $reason, $user_id);
        $stmt->execute();

        header("Location: time.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mura - Learn Languages</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/main.css">
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
        <button class="settings-button">⚙️ Settings</button>
    </header>

    <!-- Question section -->
    <div class="question-section">
        <div class="mascot">🦉</div>
        <div class="question-text">Why do you want to learn <span id="selected-language"><?php echo htmlspecialchars($selected_language); ?></span>?</div>
    </div>

    <!-- Options grid -->
    <form method="POST" action="">
        <div class="options-container">
            <button type="submit" name="reason" value="fun" class="option">
                <div class="option-icon icon-1">🎉</div>
                <div class="option-content">
                    <div class="option-text">Have fun</div>
                </div>
            </button>

            <button type="submit" name="reason" value="connections" class="option">
                <div class="option-icon icon-2">👥</div>
                <div class="option-content">
                    <div class="option-text">Make connections</div>
                </div>
            </button>

            <button type="submit" name="reason" value="travel" class="option">
                <div class="option-icon icon-3">✈️</div>
                <div class="option-content">
                    <div class="option-text">Prepare for travel</div>
                </div>
            </button>

            <button type="submit" name="reason" value="brain" class="option">
                <div class="option-icon icon-4">🧠</div>
                <div class="option-content">
                    <div class="option-text">Exercise my brain</div>
                </div>
            </button>

            <button type="submit" name="reason" value="studies" class="option">
                <div class="option-icon icon-5">📚</div>
                <div class="option-content">
                    <div class="option-text">Help with my studies</div>
                </div>
            </button>

            <button type="submit" name="reason" value="career" class="option">
                <div class="option-icon icon-1">💼</div>
                <div class="option-content">
                    <div class="option-text">Boost my career</div>
                </div>
            </button>

            <button type="submit" name="reason" value="other" class="option">
                <div class="option-icon icon-2">❓</div>
                <div class="option-content">
                    <div class="option-text">Other</div>
                </div>
            </button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const progressBar = document.getElementById('progressBar');
            // Set progress to 50%
            progressBar.style.width = '50%';
        });
    </script>
</body>
</html>
