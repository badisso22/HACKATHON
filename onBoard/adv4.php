<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'] ?? null;
    $proficiency_level = $_POST['proficiency_level'] ?? null;

    if ($user_id && $proficiency_level) {
        $stmt = $conn->prepare("UPDATE user_onboarding SET proficiency_level = ? WHERE user_ID = ?");
        $stmt->bind_param("si", $proficiency_level, $user_id);
        $stmt->execute();

        header("Location: dashboard.php");
        exit();
    }
}

require_once 'check_onboarding.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Language Selection</title>
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
    <div class="content-section">
        <div class="mascot">🦉</div>
        <div class="content-text">Do you already know some Spanish?</div>
        <form method="POST" action="">
            <button class="button" name="proficiency_level" value="beginner">🐣 Beginner</button>
            <button class="button" name="proficiency_level" value="advanced">🦅 Advanced</button>
        </form>
    </div>
</body>
</html>