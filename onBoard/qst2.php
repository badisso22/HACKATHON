<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'] ?? null;
    $reason = $_POST['reason'] ?? null;

    if ($user_id && $reason) {
        $stmt = $conn->prepare("UPDATE user_onboarding SET reason = ? WHERE user_ID = ?");
        $stmt->bind_param("si", $reason, $user_id);
        $stmt->execute();

        // Redirect to time3.php after successfully updating the reason
        header("Location: time3.php");
        exit();
    }
}

// Ensure the page does not reload unnecessarily
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mura - Learn Languages</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="lan1.css">
</head>
<body>
    <div class="progress-container">
        <div class="progress-bar" id="progressBar"></div>
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
        <div class="question-text">Why do you want to learn <span id="selected-language">this language</span>?</div>
    </div>
    <form method="POST" action="">
        <div class="options-container">
            <button class="option" name="reason" value="fun">
                <div class="option-icon icon-1">🎉</div>
                <div class="option-content">
                    <div class="option-text">Have fun</div>
                </div>
            </button>
            <button class="option" name="reason" value="connections">
                <div class="option-icon icon-2">👥</div>
                <div class="option-content">
                    <div class="option-text">Make connections</div>
                </div>
            </button>
            <button class="option" name="reason" value="travel">
                <div class="option-icon icon-3">✈️</div>
                <div class="option-content">
                    <div class="option-text">Prepare for travel</div>
                </div>
            </button>
            <button class="option" name="reason" value="brain">
                <div class="option-icon icon-4">🧠</div>
                <div class="option-content">
                    <div class="option-text">Exercise my brain</div>
                </div>
            </button>
            <button class="option" name="reason" value="studies">
                <div class="option-icon icon-5">📚</div>
                <div class="option-content">
                    <div class="option-text">Help with my studies</div>
                </div>
            </button>
            <button class="option" name="reason" value="career">
                <div class="option-icon icon-1">💼</div>
                <div class="option-content">
                    <div class="option-text">Boost my career</div>
                </div>
            </button>
            <button class="option" name="reason" value="other">
                <div class="option-icon icon-2">❓</div>
                <div class="option-content">
                    <div class="option-text">Other</div>
                </div>
            </button>
        </div>
    </form>
    <script src="qst2.js"></script>
</body>
</html>