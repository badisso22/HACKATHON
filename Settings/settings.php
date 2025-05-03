<?php
session_start();
require_once '../Configurations/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/signin.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Initialize variables for notifications
$notification = '';
$notificationType = '';

// Process form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Determine which form was submitted
    if (isset($_POST['profile_submit'])) {
        // Process profile information form
        $firstName = filter_input(INPUT_POST, 'firstName', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $lastName = filter_input(INPUT_POST, 'lastName', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $dateOfBirth = filter_input(INPUT_POST, 'dateOfBirth', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $gender = filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $phoneNumber = filter_input(INPUT_POST, 'phoneNumber', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        
        // Validate inputs
        $errors = [];
        if (empty($firstName)) {
            $errors[] = "First name is required";
        }
        if (empty($lastName)) {
            $errors[] = "Last name is required";
        }
        if (empty($dateOfBirth)) {
            $errors[] = "Date of birth is required";
        }
        if (empty($gender)) {
            $errors[] = "Gender is required";
        }
        
        // If no errors, update user data
        if (empty($errors)) {
            // Update learner table
            $stmt = $conn->prepare("UPDATE learner SET first_name = ?, last_name = ?, date_of_birth = ?, phone_number = ?, gender = ?, address = ? WHERE user_ID = ?");
            $stmt->bind_param("ssssssi", $firstName, $lastName, $dateOfBirth, $phoneNumber, $gender, $address, $user_id);
            
            if ($stmt->execute()) {
                $notification = "Profile information updated successfully!";
                $notificationType = "success";
            } else {
                $notification = "Error updating profile information: " . $conn->error;
                $notificationType = "error";
            }
        } else {
            $notification = "Please fix the following errors: " . implode(", ", $errors);
            $notificationType = "error";
        }
    } elseif (isset($_POST['security_submit'])) {
        // Process security form
        $currentPassword = $_POST['currentPassword'];
        $newPassword = $_POST['newPassword'];
        $confirmPassword = $_POST['confirmPassword'];
        
        // Validate inputs
        $errors = [];
        if (empty($currentPassword)) {
            $errors[] = "Current password is required";
        }
        if (empty($newPassword) || strlen($newPassword) < 8) {
            $errors[] = "New password must be at least 8 characters";
        }
        if ($newPassword !== $confirmPassword) {
            $errors[] = "Passwords do not match";
        }
        
        // If no errors, update password
        if (empty($errors)) {
            // Verify current password
            $stmt = $conn->prepare("SELECT password FROM users WHERE user_ID = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                if (password_verify($currentPassword, $row['password'])) {
                    // Hash new password
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    
                    // Update password
                    $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE user_ID = ?");
                    $updateStmt->bind_param("si", $hashedPassword, $user_id);
                    
                    if ($updateStmt->execute()) {
                        $notification = "Password updated successfully!";
                        $notificationType = "success";
                    } else {
                        $notification = "Error updating password: " . $conn->error;
                        $notificationType = "error";
                    }
                } else {
                    $notification = "Current password is incorrect";
                    $notificationType = "error";
                }
            } else {
                $notification = "User not found";
                $notificationType = "error";
            }
        } else {
            $notification = "Please fix the following errors: " . implode(", ", $errors);
            $notificationType = "error";
        }
    } elseif (isset($_POST['language_submit'])) {
        // Process language preferences form
        $selectedLanguage = filter_input(INPUT_POST, 'selectedLanguage', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $proficiencyLevel = filter_input(INPUT_POST, 'proficiencyLevel', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $dailyGoal = filter_input(INPUT_POST, 'dailyGoal', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $reason = filter_input(INPUT_POST, 'reason', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        
        // Validate inputs
        $errors = [];
        if (empty($selectedLanguage)) {
            $errors[] = "Language selection is required";
        }
        if (empty($proficiencyLevel)) {
            $errors[] = "Proficiency level is required";
        }
        if (empty($dailyGoal)) {
            $errors[] = "Daily goal is required";
        }
        
        // If no errors, update language preferences
        if (empty($errors)) {
            // Update user_onboarding table
            $stmt = $conn->prepare("UPDATE user_onboarding SET selected_language = ?, proficiency_level = ?, daily_goal = ?, reason = ? WHERE user_ID = ?");
            $stmt->bind_param("ssssi", $selectedLanguage, $proficiencyLevel, $dailyGoal, $reason, $user_id);
            
            if ($stmt->execute()) {
                $notification = "Language preferences updated successfully!";
                $notificationType = "success";
            } else {
                $notification = "Error updating language preferences: " . $conn->error;
                $notificationType = "error";
            }
        } else {
            $notification = "Please fix the following errors: " . implode(", ", $errors);
            $notificationType = "error";
        }
    }
}

// Get current user data
$stmt = $conn->prepare("
    SELECT u.username, u.email, u.onboarding_complete, uo.selected_language, uo.daily_goal, uo.proficiency_level, uo.reason,
           l.first_name, l.last_name, l.date_of_birth, l.phone_number, l.gender, l.address,
           COALESCE(us.level, 1) as user_level
    FROM users u 
    LEFT JOIN user_onboarding uo ON u.user_ID = uo.user_ID 
    LEFT JOIN learner l ON u.user_ID = l.user_ID
    LEFT JOIN user_stats us ON u.user_ID = us.user_id
    WHERE u.user_ID = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: ../Login/signin.php");
    exit();
}

$user = $result->fetch_assoc();

// Get streak data
$streakStmt = $conn->prepare("SELECT current_streak FROM user_streaks WHERE user_id = ?");
$streakStmt->bind_param("i", $user_id);
$streakStmt->execute();
$streakResult = $streakStmt->get_result();
$streak = 0;
if ($streakRow = $streakResult->fetch_assoc()) {
    $streak = $streakRow['current_streak'];
}

// Get current date for greeting
$hour = date('H');
if ($hour < 12) {
    $greeting = "Good morning";
} elseif ($hour < 18) {
    $greeting = "Good afternoon";
} else {
    $greeting = "Good evening";
}

// Function to get flag emoji based on language
function getLanguageFlag($language) {
    $flags = [
        'Spanish' => '🇪🇸',
        'French' => '🇫🇷',
        'English' => '🇬🇧',
        'German' => '🇩🇪',
        'Italian' => '🇮🇹',
        'Portuguese' => '🇵🇹',
        'Russian' => '🇷🇺',
        'Japanese' => '🇯🇵',
        'Chinese' => '🇨🇳',
        'Korean' => '🇰🇷',
        'Arabic' => '🇸🇦',
    ];
    
    return $flags[$language] ?? '🌐';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | Mura</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fa;
            color: #333;
        }

        .app-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, #5a3b5d 0%, #3f3d56 100%);
            color: #fff;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            z-index: 100;
        }

        .logo {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Logo container styles */
        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .mura-logo {
            width: 40px;
            height: 40px;
            margin-right: 10px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        }

        .mura-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .logo h1 {
            font-size: 28px;
            font-weight: bold;
            color: #b39ddb;
            letter-spacing: 2px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .sidebar-nav {
            flex: 1;
            padding: 20px 0;
            overflow-y: auto;
        }

        .sidebar-nav ul {
            list-style: none;
        }

        .nav-item {
            margin-bottom: 5px;
        }

        .nav-item a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #e8e8e8;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .nav-item a:hover {
            background-color: rgba(179, 157, 219, 0.1);
            border-left: 3px solid #b39ddb;
        }

        .nav-item.active a {
            background-color: rgba(179, 157, 219, 0.2);
            border-left: 3px solid #b39ddb;
            color: #b39ddb;
        }

        .nav-item i {
            margin-right: 15px;
            font-size: 18px;
            width: 20px;
            text-align: center;
        }

        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logout-btn {
            display: flex;
            align-items: center;
            color: #e8e8e8;
            text-decoration: none;
            padding: 10px;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .logout-btn i {
            margin-right: 10px;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            background-color: #f5f7fa;
            margin-left: 250px;
            position: relative;
            width: calc(100% - 250px);
            transition: margin-left 0.3s ease, width 0.3s ease;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .menu-toggle {
            display: none;
            font-size: 24px;
            cursor: pointer;
            margin-right: 15px;
        }

        /* Left Stats (Level and Streak) */
        .left-stats {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        /* Level Indicator Styles */
        .level-indicator {
            display: flex;
            align-items: center;
            background-color: #fff;
            padding: 8px 15px;
            border-radius: 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            border: 1px solid #eee;
        }

        .level-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            background: linear-gradient(135deg, #7e57c2, #5a3b5d);
            border-radius: 50%;
            margin-right: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .level-number {
            color: #fff;
            font-weight: bold;
            font-size: 14px;
        }

        .level-label {
            font-size: 14px;
            color: #666;
        }

        .streak-counter {
            display: flex;
            align-items: center;
            background-color: #fff;
            padding: 8px 15px;
            border-radius: 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            border: 1px solid #eee;
        }

        .streak-number {
            font-size: 18px;
            font-weight: bold;
            color: #5a3b5d;
            margin-right: 5px;
        }

        .streak-icon {
            position: relative;
            width: 24px;
            height: 24px;
            margin: 0 5px;
        }

        .fire-emoji {
            font-size: 20px;
            position: absolute;
            top: 0;
            left: 0;
            animation: flame 0.8s infinite alternate;
        }

        @keyframes flame {
            0% {
                transform: scale(1) rotate(-5deg);
                text-shadow: 0 0 5px rgba(255, 100, 0, 0.5);
            }
            100% {
                transform: scale(1.1) rotate(5deg);
                text-shadow: 0 0 10px rgba(255, 100, 0, 0.8), 0 0 20px rgba(255, 200, 0, 0.4);
            }
        }

        .streak-label {
            font-size: 14px;
            color: #666;
        }

        .language-indicator {
            display: flex;
            align-items: center;
            background-color: #fff;
            padding: 8px 15px;
            border-radius: 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            border: 1px solid #eee;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .language-indicator:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .language-name {
            font-size: 14px;
            font-weight: 500;
            color: #5a3b5d;
            margin-right: 8px;
        }

        .language-flag {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Content Styles */
        .content {
            padding: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            margin-bottom: 2rem;
        }

        .header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #5a3b5d;
        }

        .subtitle {
            color: #666;
            font-size: 1rem;
        }

        /* Tabs */
        .tabs {
            display: flex;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 2rem;
        }

        .tab {
            padding: 0.75rem 1.5rem;
            cursor: pointer;
            font-weight: 500;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
        }

        .tab:hover {
            color: #5a3b5d;
        }

        .tab.active {
            color: #5a3b5d;
            border-bottom-color: #5a3b5d;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Card Styles */
        .card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }

        .card-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            background-color: rgba(90, 59, 93, 0.05);
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #5a3b5d;
        }

        .card-description {
            color: #6b7280;
            font-size: 0.875rem;
        }

        .card-content {
            padding: 1.5rem;
        }

        .card-footer {
            padding: 1.5rem;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: flex-end;
            background-color: rgba(90, 59, 93, 0.02);
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: -0.5rem;
        }

        .form-col {
            flex: 1 0 100%;
            padding: 0.5rem;
        }

        @media (min-width: 768px) {
            .form-col-md-6 {
                flex: 0 0 50%;
                max-width: 50%;
            }
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #5a3b5d;
        }

        .form-control {
            width: 100%;
            padding: 0.625rem;
            font-size: 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            background-color: #fff;
            transition: border-color 0.15s ease-in-out;
        }

        .form-control:focus {
            outline: none;
            border-color: #5a3b5d;
            box-shadow: 0 0 0 3px rgba(90, 59, 93, 0.1);
        }

        .form-select {
            width: 100%;
            padding: 0.625rem;
            font-size: 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            background-color: #fff;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%235a3b5d' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1rem;
        }

        .form-select:focus {
            outline: none;
            border-color: #5a3b5d;
            box-shadow: 0 0 0 3px rgba(90, 59, 93, 0.1);
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        .form-text {
            display: block;
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: #6b7280;
        }

        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 500;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            user-select: none;
            border: 1px solid transparent;
            padding: 0.625rem 1.25rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 0.375rem;
            transition: all 0.15s ease-in-out;
            cursor: pointer;
        }

        .btn-primary {
            color: #fff;
            background-color: #5a3b5d;
            border-color: #5a3b5d;
        }

        .btn-primary:hover {
            background-color: #7e57c2;
            border-color: #7e57c2;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Notification Styles */
        .notification {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
        }

        .notification-success {
            background-color: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: #10b981;
        }

        .notification-error {
            background-color: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }

        /* Loading Spinner */
        .loading {
            display: inline-flex;
            align-items: center;
        }

        .loading-spinner {
            margin-right: 0.5rem;
            animation: spin 1s linear infinite;
            width: 1rem;
            height: 1rem;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        .hidden {
            display: none;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: -250px;
                height: 100%;
                z-index: 999;
            }

            .sidebar.active {
                left: 0;
            }

            .menu-toggle {
                display: block;
            }

            .main-content {
                width: 100%;
                margin-left: 0;
            }

            .top-bar {
                padding: 15px 20px;
            }

            .left-stats {
                gap: 10px;
            }

            .level-indicator,
            .streak-counter,
            .language-indicator {
                padding: 6px 10px;
            }

            .level-label,
            .streak-label,
            .language-name {
                display: none;
            }

            .logo-container {
                flex-direction: column;
            }

            .mura-logo {
                margin-right: 0;
                margin-bottom: 5px;
            }

            .form-row {
                flex-direction: column;
            }

            .form-col-md-6 {
                max-width: 100%;
            }

            .tabs {
                flex-direction: column;
                border-bottom: none;
            }

            .tab {
                border-bottom: 1px solid #e5e7eb;
                text-align: center;
            }

            .tab.active {
                border-bottom: 1px solid #5a3b5d;
                background-color: rgba(90, 59, 93, 0.05);
            }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="logo">
                <div class="logo-container">
                    <div class="mura-logo">
                        <img src="../image/mura.png" alt="Mura Logo">
                    </div>
                    <h1>Mura</h1>
                </div>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="nav-item">
                        <a href="../Dashboard/dashboard.php">
                            <i class="fas fa-home"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#">
                            <i class="fas fa-book-open"></i>
                            Lessons
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../chatai/chatai.php">
                            <i class="fas fa-robot"></i>
                            TutorBot
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../Game/index.php">
                            <i class="fas fa-gamepad"></i>
                            Language Combat
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../Dashboard/translation.php">
                            <i class="fas fa-language"></i>
                            Translation
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../ChatRoom/videochat.php">
                            <i class="fas fa-video"></i>
                            Video Chat
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#">
                            <i class="fas fa-trophy"></i>
                            Achievements
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../community/community.php">
                            <i class="fas fa-users"></i>
                            Community
                        </a>
                    </li>
                    <li class="nav-item active">
                        <a href="settings.php">
                            <i class="fas fa-cog"></i>
                            Settings
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <a href="../Dashboard/logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content" id="mainContent">
            <div class="top-bar">
                <div class="menu-toggle" id="menuToggle">
                    <i class="fas fa-bars"></i>
                </div>
                <div class="left-stats">
                    <div class="level-indicator">
                        <div class="level-badge">
                            <span class="level-number"><?php echo $user['user_level']; ?></span>
                        </div>
                        <span class="level-label">Level</span>
                    </div>
                    <div class="streak-counter">
                        <span class="streak-number"><?php echo $streak; ?></span>
                        <div class="streak-icon">
                            <span class="fire-emoji">🔥</span>
                        </div>
                        <span class="streak-label">Streak</span>
                    </div>
                </div>
                <div class="language-indicator">
                    <span class="language-name"><?php echo htmlspecialchars($user['selected_language']); ?></span>
                    <div class="language-flag">
                        <span><?php echo getLanguageFlag($user['selected_language']); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="content">
                <div class="header">
                    <h1><?php echo $greeting; ?>, <?php echo htmlspecialchars($user['first_name']); ?>!</h1>
                    <p class="subtitle">Manage your account settings and preferences</p>
                </div>
                
                <?php if (!empty($notification)): ?>
                <div class="notification notification-<?php echo $notificationType; ?>">
                    <?php echo $notification; ?>
                </div>
                <?php endif; ?>
                
                <div class="tabs">
                    <div class="tab active" data-tab="profile">Profile Settings</div>
                    <div class="tab" data-tab="security">Privacy & Security</div>
                    <div class="tab" data-tab="language">Language Preferences</div>
                </div>
                
                <!-- Profile Settings Tab -->
                <div id="profile" class="tab-content active">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Profile Information</h2>
                            <p class="card-description">Update your personal information and profile details</p>
                        </div>
                        <form id="profileForm" method="POST" action="">
                            <div class="card-content">
                                <div class="form-row">
                                    <div class="form-col form-col-md-6">
                                        <div class="form-group">
                                            <label for="firstName">First Name</label>
                                            <input type="text" class="form-control" id="firstName" name="firstName" placeholder="Enter your first name" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-col form-col-md-6">
                                        <div class="form-group">
                                            <label for="lastName">Last Name</label>
                                            <input type="text" class="form-control" id="lastName" name="lastName" placeholder="Enter your last name" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="dateOfBirth">Date of Birth</label>
                                    <input type="date" class="form-control" id="dateOfBirth" name="dateOfBirth" value="<?php echo htmlspecialchars($user['date_of_birth'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="gender">Gender</label>
                                    <select class="form-select" id="gender" name="gender" required>
                                        <option value="">Select your gender</option>
                                        <option value="male" <?php echo (isset($user['gender']) && $user['gender'] === 'male') ? 'selected' : ''; ?>>Male</option>
                                        <option value="female" <?php echo (isset($user['gender']) && $user['gender'] === 'female') ? 'selected' : ''; ?>>Female</option>
                                        <option value="other" <?php echo (isset($user['gender']) && $user['gender'] === 'other') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="phoneNumber">Phone Number</label>
                                    <input type="tel" class="form-control" id="phoneNumber" name="phoneNumber" placeholder="Enter your phone number" value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="3" placeholder="Enter your address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" name="profile_submit" class="btn btn-primary" id="profileSubmitBtn">
                                    <span class="btn-text">Save Changes</span>
                                    <span class="loading hidden">
                                        <svg class="loading-spinner" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="4" stroke-dasharray="32" stroke-dashoffset="8"></circle>
                                        </svg>
                                        Saving...
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Privacy & Security Tab -->
                <div id="security" class="tab-content">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Change Password</h2>
                            <p class="card-description">Update your password to keep your account secure</p>
                        </div>
                        <form id="securityForm" method="POST" action="">
                            <div class="card-content">
                                <div class="form-group">
                                    <label for="currentPassword">Current Password</label>
                                    <input type="password" class="form-control" id="currentPassword" name="currentPassword" placeholder="Enter your current password" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="newPassword">New Password</label>
                                    <input type="password" class="form-control" id="newPassword" name="newPassword" placeholder="Enter your new password" required>
                                    <small class="form-text">Password must be at least 8 characters long.</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirmPassword">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Confirm your new password" required>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" name="security_submit" class="btn btn-primary" id="securitySubmitBtn">
                                    <span class="btn-text">Update Password</span>
                                    <span class="loading hidden">
                                        <svg class="loading-spinner" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="4" stroke-dasharray="32" stroke-dashoffset="8"></circle>
                                        </svg>
                                        Updating...
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Language Preferences Tab -->
                <div id="language" class="tab-content">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Language Preferences</h2>
                            <p class="card-description">Update your language learning preferences</p>
                        </div>
                        <form id="languageForm" method="POST" action="">
                            <div class="card-content">
                                <div class="form-group">
                                    <label for="selectedLanguage">Language to Learn</label>
                                    <select class="form-select" id="selectedLanguage" name="selectedLanguage" required>
                                        <option value="">Select a language</option>
                                        <option value="Spanish" <?php echo (isset($user['selected_language']) && $user['selected_language'] === 'Spanish') ? 'selected' : ''; ?>>Spanish</option>
                                        <option value="French" <?php echo (isset($user['selected_language']) && $user['selected_language'] === 'French') ? 'selected' : ''; ?>>French</option>
                                        <option value="German" <?php echo (isset($user['selected_language']) && $user['selected_language'] === 'German') ? 'selected' : ''; ?>>German</option>
                                        <option value="Italian" <?php echo (isset($user['selected_language']) && $user['selected_language'] === 'Italian') ? 'selected' : ''; ?>>Italian</option>
                                        <option value="Japanese" <?php echo (isset($user['selected_language']) && $user['selected_language'] === 'Japanese') ? 'selected' : ''; ?>>Japanese</option>
                                        <option value="Chinese" <?php echo (isset($user['selected_language']) && $user['selected_language'] === 'Chinese') ? 'selected' : ''; ?>>Chinese</option>
                                        <option value="Russian" <?php echo (isset($user['selected_language']) && $user['selected_language'] === 'Russian') ? 'selected' : ''; ?>>Russian</option>
                                        <option value="Portuguese" <?php echo (isset($user['selected_language']) && $user['selected_language'] === 'Portuguese') ? 'selected' : ''; ?>>Portuguese</option>
                                        <option value="Arabic" <?php echo (isset($user['selected_language']) && $user['selected_language'] === 'Arabic') ? 'selected' : ''; ?>>Arabic</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="proficiencyLevel">Proficiency Level</label>
                                    <select class="form-select" id="proficiencyLevel" name="proficiencyLevel" required>
                                        <option value="">Select your proficiency level</option>
                                        <option value="beginner" <?php echo (isset($user['proficiency_level']) && $user['proficiency_level'] === 'beginner') ? 'selected' : ''; ?>>Beginner</option>
                                        <option value="intermediate" <?php echo (isset($user['proficiency_level']) && $user['proficiency_level'] === 'intermediate') ? 'selected' : ''; ?>>Intermediate</option>
                                        <option value="advanced" <?php echo (isset($user['proficiency_level']) && $user['proficiency_level'] === 'advanced') ? 'selected' : ''; ?>>Advanced</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="dailyGoal">Daily Learning Goal</label>
                                    <select class="form-select" id="dailyGoal" name="dailyGoal" required>
                                        <option value="">Select your daily goal</option>
                                        <option value="3 min" <?php echo (isset($user['daily_goal']) && $user['daily_goal'] === '3 min') ? 'selected' : ''; ?>>3 minutes</option>
                                        <option value="5 min" <?php echo (isset($user['daily_goal']) && $user['daily_goal'] === '5 min') ? 'selected' : ''; ?>>5 minutes</option>
                                        <option value="10 min" <?php echo (isset($user['daily_goal']) && $user['daily_goal'] === '10 min') ? 'selected' : ''; ?>>10 minutes</option>
                                        <option value="15 min" <?php echo (isset($user['daily_goal']) && $user['daily_goal'] === '15 min') ? 'selected' : ''; ?>>15 minutes</option>
                                        <option value="30 min" <?php echo (isset($user['daily_goal']) && $user['daily_goal'] === '30 min') ? 'selected' : ''; ?>>30 minutes</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="reason">Reason for Learning</label>
                                    <select class="form-select" id="reason" name="reason">
                                        <option value="">Select your reason</option>
                                        <option value="travel" <?php echo (isset($user['reason']) && $user['reason'] === 'travel') ? 'selected' : ''; ?>>Travel</option>
                                        <option value="work" <?php echo (isset($user['reason']) && $user['reason'] === 'work') ? 'selected' : ''; ?>>Work</option>
                                        <option value="study" <?php echo (isset($user['reason']) && $user['reason'] === 'study') ? 'selected' : ''; ?>>Study</option>
                                        <option value="connections" <?php echo (isset($user['reason']) && $user['reason'] === 'connections') ? 'selected' : ''; ?>>Family/Friend Connections</option>
                                        <option value="culture" <?php echo (isset($user['reason']) && $user['reason'] === 'culture') ? 'selected' : ''; ?>>Cultural Interest</option>
                                        <option value="hobby" <?php echo (isset($user['reason']) && $user['reason'] === 'hobby') ? 'selected' : ''; ?>>Hobby</option>
                                    </select>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" name="language_submit" class="btn btn-primary" id="languageSubmitBtn">
                                    <span class="btn-text">Save Preferences</span>
                                    <span class="loading hidden">
                                        <svg class="loading-spinner" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="4" stroke-dasharray="32" stroke-dashoffset="8"></circle>
                                        </svg>
                                        Saving...
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Tab switching functionality
        document.addEventListener("DOMContentLoaded", () => {
            const tabs = document.querySelectorAll(".tab");
            const tabContents = document.querySelectorAll(".tab-content");
            
            tabs.forEach((tab) => {
                tab.addEventListener("click", () => {
                    const tabId = tab.getAttribute("data-tab");
                    
                    // Update active tab
                    tabs.forEach((t) => t.classList.remove("active"));
                    tab.classList.add("active");
                    
                    // Show active content
                    tabContents.forEach((content) => {
                        content.classList.remove("active");
                        if (content.id === tabId) {
                            content.classList.add("active");
                        }
                    });
                });
            });
            
            // Mobile menu toggle
            const sidebar = document.getElementById("sidebar");
            const menuToggle = document.getElementById("menuToggle");
            
            if (menuToggle) {
                menuToggle.addEventListener("click", () => {
                    sidebar.classList.toggle("active");
                });
            }
            
            // Form submission handling with loading state
            const forms = ["profileForm", "securityForm", "languageForm"];
            const buttons = ["profileSubmitBtn", "securitySubmitBtn", "languageSubmitBtn"];
            
            forms.forEach((formId, index) => {
                const form = document.getElementById(formId);
                const button = document.getElementById(buttons[index]);
                
                if (form && button) {
                    form.addEventListener("submit", () => {
                        // Show loading state
                        const btnText = button.querySelector(".btn-text");
                        const loading = button.querySelector(".loading");
                        
                        btnText.classList.add("hidden");
                        loading.classList.remove("hidden");
                        button.disabled = true;
                    });
                }
            });
            
            // Password confirmation validation
            const securityForm = document.getElementById("securityForm");
            if (securityForm) {
                securityForm.addEventListener("submit", (e) => {
                    const newPassword = document.getElementById("newPassword").value;
                    const confirmPassword = document.getElementById("confirmPassword").value;
                    
                    if (newPassword !== confirmPassword) {
                        e.preventDefault();
                        alert("Passwords do not match");
                        
                        // Reset loading state
                        const button = document.getElementById("securitySubmitBtn");
                        const btnText = button.querySelector(".btn-text");
                        const loading = button.querySelector(".loading");
                        
                        btnText.classList.remove("hidden");
                        loading.classList.add("hidden");
                        button.disabled = false;
                    }
                });
            }
            
            // Auto-hide notifications after 5 seconds
            const notification = document.querySelector(".notification");
            if (notification) {
                setTimeout(() => {
                    notification.style.opacity = "0";
                    notification.style.transition = "opacity 0.5s ease-out";
                    
                    setTimeout(() => {
                        notification.style.display = "none";
                    }, 500);
                }, 5000);
            }
        });
    </script>
</body>
</html>
