<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../Configurations/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/signin.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$notification = '';
$notification_type = 'success';

// Get theme preference from cookie or session
if (isset($_COOKIE['mura_theme'])) {
    $theme = $_COOKIE['mura_theme'];
} elseif (isset($_SESSION['theme'])) {
    $theme = $_SESSION['theme'];
} else {
    $theme = 'light'; // Default theme
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Process profile information form
    $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
    $lastName = mysqli_real_escape_string($conn, $_POST['lastName']);
    $dateOfBirth = mysqli_real_escape_string($conn, $_POST['dateOfBirth']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $phoneNumber = mysqli_real_escape_string($conn, $_POST['phoneNumber']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    
    try {
        // Start transaction
        mysqli_begin_transaction($conn);
        
        // Check if learner record exists
        $check_sql = "SELECT * FROM learner WHERE user_ID = ?";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "i", $user_id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            // Update existing record
            $sql = "UPDATE learner SET first_name = ?, last_name = ?, date_of_birth = ?, phone_number = ?, gender = ?, address = ? WHERE user_ID = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssssssi", $firstName, $lastName, $dateOfBirth, $phoneNumber, $gender, $address, $user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $affected_rows = mysqli_stmt_affected_rows($stmt);
                $notification = "Profile information updated successfully!";
                $notification_type = "success";
            } else {
                throw new Exception("Error updating profile: " . mysqli_error($conn));
            }
        } else {
            // Insert new record
            $sql = "INSERT INTO learner (user_ID, first_name, last_name, date_of_birth, phone_number, gender, address) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "issssss", $user_id, $firstName, $lastName, $dateOfBirth, $phoneNumber, $gender, $address);
            
            if (mysqli_stmt_execute($stmt)) {
                $notification = "Profile information created successfully!";
                $notification_type = "success";
            } else {
                throw new Exception("Error creating profile: " . mysqli_error($conn));
            }
        }
        
        // Commit transaction
        mysqli_commit($conn);
        
        // Redirect back to settings page
        header("Location: settings.php?success=profile_updated");
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        $notification = $e->getMessage();
        $notification_type = "error";
    }
}

// Get current user data
$sql = "SELECT u.username, u.email, u.onboarding_complete, u.profile_picture,
       l.first_name, l.last_name, l.date_of_birth, l.phone_number, l.gender, l.address
FROM users u 
LEFT JOIN learner l ON u.user_ID = l.user_ID
WHERE u.user_ID = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    header("Location: ../Login/signin.php");
    exit();
}

$user = mysqli_fetch_assoc($result);

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

// Get user's language
$sql = "SELECT selected_language FROM user_onboarding WHERE user_ID = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$language = '';
if ($row = mysqli_fetch_assoc($result)) {
    $language = $row['selected_language'];
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $theme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile | Mura</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #5a3b5d;
            --primary-light: #7e57c2;
            --primary-dark: #3f3d56;
            --accent-color: #b39ddb;
            --text-color: #333;
            --text-light: #666;
            --bg-color: #f5f7fa;
            --card-bg: #fff;
            --border-color: #e5e7eb;
            --success-color: #10b981;
            --error-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #3b82f6;
        }

        [data-theme="dark"] {
            --primary-color: #7e57c2;
            --primary-light: #9575cd;
            --primary-dark: #5e35b1;
            --accent-color: #b39ddb;
            --text-color: #e0e0e0;
            --text-light: #aaa;
            --bg-color: #121212;
            --card-bg: #1e1e1e;
            --border-color: #333;
            --success-color: #10b981;
            --error-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #3b82f6;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            transition: background-color 0.3s, color 0.3s;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
        }

        .app-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--primary-dark) 100%);
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
            color: var(--accent-color);
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
            border-left: 3px solid var(--accent-color);
        }

        .nav-item.active a {
            background-color: rgba(179, 157, 219, 0.2);
            border-left: 3px solid var(--accent-color);
            color: var(--accent-color);
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
            background-color: var(--bg-color);
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
            background-color: var(--card-bg);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .menu-toggle {
            display: none;
            font-size: 24px;
            cursor: pointer;
            margin-right: 15px;
        }

        .language-indicator {
            display: flex;
            align-items: center;
            background-color: var(--card-bg);
            padding: 8px 15px;
            border-radius: 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--border-color);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .language-name {
            font-size: 14px;
            font-weight: 500;
            color: var(--primary-color);
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }

        .subtitle {
            color: var(--text-light);
            font-size: 1rem;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 0.375rem;
            color: var(--text-color);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .back-button:hover {
            background-color: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        .back-button i {
            margin-right: 0.5rem;
        }

        /* Card Styles */
        .card {
            background-color: var(--card-bg);
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            background-color: rgba(90, 59, 93, 0.05);
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }

        .card-description {
            color: var(--text-light);
            font-size: 0.875rem;
        }

        .card-content {
            padding: 1.5rem;
        }

        .card-footer {
            padding: 1.5rem;
            border-top: 1px solid var(--border-color);
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
            color: var(--primary-color);
        }

        .form-control {
            width: 100%;
            padding: 0.625rem;
            font-size: 1rem;
            border: 1px solid var(--border-color);
            border-radius: 0.375rem;
            background-color: var(--card-bg);
            color: var(--text-color);
            transition: border-color 0.15s ease-in-out;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(90, 59, 93, 0.1);
        }

        .form-select {
            width: 100%;
            padding: 0.625rem;
            font-size: 1rem;
            border: 1px solid var(--border-color);
            border-radius: 0.375rem;
            background-color: var(--card-bg);
            color: var(--text-color);
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%235a3b5d' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1rem;
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
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
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--primary-light);
            border-color: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-secondary {
            color: var(--text-color);
            background-color: transparent;
            border-color: var(--border-color);
        }

        .btn-secondary:hover {
            background-color: rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }

        /* Notification Styles */
        .notification {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            animation: slideIn 0.5s ease;
        }

        .notification-success {
            background-color: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: var(--success-color);
        }

        .notification-error {
            background-color: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: var(--error-color);
        }

        .notification i {
            margin-right: 0.5rem;
            font-size: 1.25rem;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
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

            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .back-button {
                margin-top: 1rem;
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
                        <a href="../Lessons/lessons.php">
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
                        <a href="../Achievements/achievements.php">
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
                <div class="language-indicator">
                    <span class="language-name"><?php echo htmlspecialchars($language ?? 'Select Language'); ?></span>
                    <div class="language-flag">
                        <span><?php echo getLanguageFlag($language ?? ''); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="content">
                <div class="header">
                    <div>
                        <h1>Edit Profile Information</h1>
                        <p class="subtitle">Update your personal information</p>
                    </div>
                    <a href="settings.php" class="back-button">
                        <i class="fas fa-arrow-left"></i> Back to Settings
                    </a>
                </div>
                
                <?php if (!empty($notification)): ?>
                <div class="notification notification-<?php echo $notification_type; ?>">
                    <i class="fas <?php echo $notification_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                    <?php echo $notification; ?>
                </div>
                <?php endif; ?>
                
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
                            <button type="submit" class="btn btn-primary" id="profileSubmitBtn">
                                <span class="btn-text">Save Changes</span>
                                <span class="loading hidden">
                                    <svg class="loading-spinner" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="4" stroke-dasharray="32" stroke-dashoffset="8"></circle>
                                    </svg>
                                    Saving...
                                </span>
                            </button>
                            <a href="settings.php" class="btn btn-secondary" style="margin-right: 10px;">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Mobile menu toggle
            const sidebar = document.getElementById("sidebar");
            const menuToggle = document.getElementById("menuToggle");
            
            if (menuToggle) {
                menuToggle.addEventListener("click", () => {
                    sidebar.classList.toggle("active");
                });
            }
            
            // Form submission loading state
            const form = document.getElementById("profileForm");
            const button = document.getElementById("profileSubmitBtn");
            
            if (form && button) {
                form.addEventListener("submit", () => {
                    // Show loading state
                    const btnText = button.querySelector(".btn-text");
                    const loading = button.querySelector(".loading");
                    
                    if (btnText && loading) {
                        btnText.classList.add("hidden");
                        loading.classList.remove("hidden");
                        button.disabled = true;
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
