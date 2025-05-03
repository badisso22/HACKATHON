<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../Configurations/db.php';

// Enable full error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/signin.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$notification = '';
$notification_type = 'success'; // success, error, warning
$debug_info = []; // For storing debug information

// Helper function to log debug info
function debug_log($message) {
    global $debug_info;
    $debug_info[] = date('H:i:s') . ': ' . $message;
}

// Store theme preference in cookie for persistence
if (isset($_POST['theme_submit'])) {
    $theme = mysqli_real_escape_string($conn, $_POST['theme']);
    setcookie('mura_theme', $theme, time() + (86400 * 365), "/"); // Cookie valid for 1 year
    $_SESSION['theme'] = $theme;
}

// Get theme preference from cookie or session
if (isset($_COOKIE['mura_theme'])) {
    $theme = $_COOKIE['mura_theme'];
} elseif (isset($_SESSION['theme'])) {
    $theme = $_SESSION['theme'];
} else {
    $theme = 'light'; // Default theme
}

// Process form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    debug_log("Form submitted: " . json_encode($_POST));
    
    // Determine which form was submitted
    if (isset($_POST['profile_submit'])) {
        // Process profile information form
        $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
        $lastName = mysqli_real_escape_string($conn, $_POST['lastName']);
        $dateOfBirth = mysqli_real_escape_string($conn, $_POST['dateOfBirth']);
        $gender = mysqli_real_escape_string($conn, $_POST['gender']);
        $phoneNumber = mysqli_real_escape_string($conn, $_POST['phoneNumber']);
        $address = mysqli_real_escape_string($conn, $_POST['address']);
        
        debug_log("Processing profile update: firstName=$firstName, lastName=$lastName");
        
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
                    debug_log("Update successful. Affected rows: $affected_rows");
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
                    debug_log("Insert successful");
                    $notification = "Profile information created successfully!";
                    $notification_type = "success";
                } else {
                    throw new Exception("Error creating profile: " . mysqli_error($conn));
                }
            }
            
            // Commit transaction
            mysqli_commit($conn);
            
        } catch (Exception $e) {
            // Rollback transaction on error
            mysqli_rollback($conn);
            debug_log("Error: " . $e->getMessage());
            $notification = $e->getMessage();
            $notification_type = "error";
        }
    } elseif (isset($_POST['security_submit'])) {
        // Process security form
        $currentPassword = $_POST['currentPassword'];
        $newPassword = $_POST['newPassword'];
        $confirmPassword = $_POST['confirmPassword'];
        
        debug_log("Processing security form");
        
        try {
            if ($newPassword !== $confirmPassword) {
                throw new Exception("New passwords do not match!");
            }
            
            if (strlen($newPassword) < 8) {
                throw new Exception("Password must be at least 8 characters long");
            }
            
            // Verify current password
            $sql = "SELECT password FROM users WHERE user_ID = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user_data = mysqli_fetch_assoc($result);
            
            if (!password_verify($currentPassword, $user_data['password'])) {
                throw new Exception("Current password is incorrect");
            }
            
            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update password
            $sql = "UPDATE users SET password = ? WHERE user_ID = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "si", $hashedPassword, $user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $affected_rows = mysqli_stmt_affected_rows($stmt);
                debug_log("Password update successful. Affected rows: $affected_rows");
                $notification = "Password updated successfully!";
                $notification_type = "success";
            } else {
                throw new Exception("Error updating password: " . mysqli_error($conn));
            }
            
        } catch (Exception $e) {
            debug_log("Error: " . $e->getMessage());
            $notification = $e->getMessage();
            $notification_type = "error";
        }
    } elseif (isset($_POST['language_submit'])) {
        // Process language preferences form
        $selectedLanguage = mysqli_real_escape_string($conn, $_POST['selectedLanguage']);
        $proficiencyLevel = mysqli_real_escape_string($conn, $_POST['proficiencyLevel']);
        $dailyGoal = mysqli_real_escape_string($conn, $_POST['dailyGoal']);
        $reason = mysqli_real_escape_string($conn, $_POST['reason']);
        
        debug_log("Processing language form: language=$selectedLanguage, level=$proficiencyLevel");
        
        try {
            // Start transaction
            mysqli_begin_transaction($conn);
            
            // Check if user_onboarding record exists
            $check_sql = "SELECT * FROM user_onboarding WHERE user_ID = ?";
            $check_stmt = mysqli_prepare($conn, $check_sql);
            mysqli_stmt_bind_param($check_stmt, "i", $user_id);
            mysqli_stmt_execute($check_stmt);
            $check_result = mysqli_stmt_get_result($check_stmt);
            
            if (mysqli_num_rows($check_result) > 0) {
                // Update existing record
                $sql = "UPDATE user_onboarding SET selected_language = ?, proficiency_level = ?, daily_goal = ?, reason = ?, is_complete = 1 WHERE user_ID = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ssssi", $selectedLanguage, $proficiencyLevel, $dailyGoal, $reason, $user_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $affected_rows = mysqli_stmt_affected_rows($stmt);
                    debug_log("Update successful. Affected rows: $affected_rows");
                    
                    // Also update the onboarding_complete flag in users table
                    $sql = "UPDATE users SET onboarding_complete = 1 WHERE user_ID = ?";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "i", $user_id);
                    mysqli_stmt_execute($stmt);
                    
                    $notification = "Language preferences updated successfully!";
                    $notification_type = "success";
                } else {
                    throw new Exception("Error updating language preferences: " . mysqli_error($conn));
                }
            } else {
                // Insert new record
                $sql = "INSERT INTO user_onboarding (user_ID, selected_language, proficiency_level, daily_goal, reason, is_complete) 
                        VALUES (?, ?, ?, ?, ?, 1)";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "issss", $user_id, $selectedLanguage, $proficiencyLevel, $dailyGoal, $reason);
                
                if (mysqli_stmt_execute($stmt)) {
                    debug_log("Insert successful");
                    
                    // Also update the onboarding_complete flag in users table
                    $sql = "UPDATE users SET onboarding_complete = 1 WHERE user_ID = ?";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "i", $user_id);
                    mysqli_stmt_execute($stmt);
                    
                    $notification = "Language preferences created successfully!";
                    $notification_type = "success";
                } else {
                    throw new Exception("Error creating language preferences: " . mysqli_error($conn));
                }
            }
            
            // Commit transaction
            mysqli_commit($conn);
            
        } catch (Exception $e) {
            // Rollback transaction on error
            mysqli_rollback($conn);
            debug_log("Error: " . $e->getMessage());
            $notification = $e->getMessage();
            $notification_type = "error";
        }
    } elseif (isset($_POST['avatar_submit'])) {
        // Process avatar upload
        try {
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['avatar']['name'];
                $filetype = pathinfo($filename, PATHINFO_EXTENSION);
                
                // Verify file extension
                if (!in_array(strtolower($filetype), $allowed)) {
                    throw new Exception("Only JPG, JPEG, PNG, and GIF files are allowed");
                }
                
                // Verify file size - 5MB maximum
                $maxsize = 5 * 1024 * 1024;
                if ($_FILES['avatar']['size'] > $maxsize) {
                    throw new Exception("File size must be less than 5MB");
                }
                
                // Create unique filename
                $new_filename = "avatar_" . $user_id . "_" . time() . "." . $filetype;
                $upload_dir = "../uploads/avatars/";
                
                // Create directory if it doesn't exist
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $upload_path = $upload_dir . $new_filename;
                
                // Move the file
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_path)) {
                    // Update database with new avatar path
                    $avatar_path = "uploads/avatars/" . $new_filename;
                    $sql = "UPDATE users SET profile_picture = ? WHERE user_ID = ?";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "si", $avatar_path, $user_id);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $notification = "Profile picture updated successfully!";
                        $notification_type = "success";
                    } else {
                        throw new Exception("Error updating profile picture in database: " . mysqli_error($conn));
                    }
                } else {
                    throw new Exception("Error uploading file");
                }
            } else {
                throw new Exception("No file uploaded or error in upload");
            }
        } catch (Exception $e) {
            debug_log("Error: " . $e->getMessage());
            $notification = $e->getMessage();
            $notification_type = "error";
        }
    } elseif (isset($_POST['theme_submit'])) {
        // Process theme preference
        $theme = mysqli_real_escape_string($conn, $_POST['theme']);
        
        // Store theme preference in session and cookie
        $_SESSION['theme'] = $theme;
        setcookie('mura_theme', $theme, time() + (86400 * 365), "/"); // Cookie valid for 1 year
        
        $notification = "Theme preference saved!";
        $notification_type = "success";
    }
    
    // Force commit any pending transactions
    mysqli_commit($conn);
    debug_log("Forced commit of any pending transactions");
}

// Get current user data - AFTER any updates to ensure we have the latest data
$sql = "SELECT u.username, u.email, u.onboarding_complete, u.profile_picture,
       uo.selected_language, uo.daily_goal, uo.proficiency_level, uo.reason,
       l.first_name, l.last_name, l.date_of_birth, l.phone_number, l.gender, l.address,
       COALESCE(us.level, 1) as user_level, COALESCE(us.xp, 0) as user_xp
FROM users u 
LEFT JOIN user_onboarding uo ON u.user_ID = uo.user_ID 
LEFT JOIN learner l ON u.user_ID = l.user_ID
LEFT JOIN user_stats us ON u.user_ID = us.user_id
WHERE u.user_ID = ?";

debug_log("Fetching user data with SQL: $sql");
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    debug_log("No user found with ID: $user_id");
    header("Location: ../Login/signin.php");
    exit();
}

$user = mysqli_fetch_assoc($result);
debug_log("User data retrieved: " . json_encode($user));

// Get streak data
$sql = "SELECT current_streak, longest_streak, last_play_date FROM user_streaks WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$streakResult = mysqli_stmt_get_result($stmt);
$streak = 0;
$longest_streak = 0;
$last_play_date = null;
if ($streakRow = mysqli_fetch_assoc($streakResult)) {
    $streak = $streakRow['current_streak'];
    $longest_streak = $streakRow['longest_streak'];
    $last_play_date = $streakRow['last_play_date'];
}

// Get XP level thresholds
$sql = "SELECT level, xp_required FROM xp_level_thresholds ORDER BY level ASC";
$levelResult = mysqli_query($conn, $sql);
$levels = [];
while ($row = mysqli_fetch_assoc($levelResult)) {
    $levels[$row['level']] = $row['xp_required'];
}

// Calculate XP progress to next level
$current_level = $user['user_level'];
$current_xp = $user['user_xp'];
$next_level = $current_level + 1;
$xp_for_current_level = isset($levels[$current_level]) ? $levels[$current_level] : 0;
$xp_for_next_level = isset($levels[$next_level]) ? $levels[$next_level] : $xp_for_current_level + 1000;
$xp_needed = $xp_for_next_level - $xp_for_current_level;
$xp_progress = $current_xp - $xp_for_current_level;
$xp_percentage = ($xp_needed > 0) ? min(100, round(($xp_progress / $xp_needed) * 100)) : 100;

// Get current date for greeting
$hour = date('H');
if ($hour < 12) {
    $greeting = "Good morning";
} elseif ($hour < 18) {
    $greeting = "Good afternoon";
} else {
    $greeting = "Good evening";
}

// Get theme preference
$theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light';

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

// Get user achievements
$sql = "SELECT COUNT(*) as completed_lessons FROM user_lesson_progress WHERE user_id = ? AND completed = 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
$completed_lessons = $row['completed_lessons'];

// Get game stats
$sql = "SELECT total_games_played, total_questions_answered, correct_answers FROM user_stats WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$game_stats = mysqli_fetch_assoc($result);
$accuracy = 0;
if ($game_stats && $game_stats['total_questions_answered'] > 0) {
    $accuracy = round(($game_stats['correct_answers'] / $game_stats['total_questions_answered']) * 100);
}

// Get available languages for learning
$sql = "SELECT DISTINCT language FROM lesson_content ORDER BY language";
$result = mysqli_query($conn, $sql);
$available_languages = [];
while ($row = mysqli_fetch_assoc($result)) {
    $available_languages[] = $row['language'];
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $theme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | Mura</title>
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
            background-color: var(--card-bg);
            padding: 8px 15px;
            border-radius: 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--border-color);
            position: relative;
            overflow: hidden;
        }

        .level-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            background: linear-gradient(135deg, var(--primary-light), var(--primary-color));
            border-radius: 50%;
            margin-right: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            z-index: 1;
        }

        .level-number {
            color: #fff;
            font-weight: bold;
            font-size: 14px;
        }

        .level-label {
            font-size: 14px;
            color: var(--text-light);
            z-index: 1;
        }

        .xp-progress {
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            background: linear-gradient(90deg, rgba(126, 87, 194, 0.1), rgba(126, 87, 194, 0.05));
            z-index: 0;
            transition: width 1s ease-in-out;
        }

        .streak-counter {
            display: flex;
            align-items: center;
            background-color: var(--card-bg);
            padding: 8px 15px;
            border-radius: 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--border-color);
        }

        .streak-number {
            font-size: 18px;
            font-weight: bold;
            color: var(--primary-color);
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
            color: var(--text-light);
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

        .language-indicator:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
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

        /* Theme toggle */
        .theme-toggle {
            background: none;
            border: none;
            color: var(--text-color);
            font-size: 20px;
            cursor: pointer;
            padding: 5px;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .theme-toggle:hover {
            background-color: rgba(0, 0, 0, 0.1);
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
            color: var(--primary-color);
        }

        .subtitle {
            color: var(--text-light);
            font-size: 1rem;
        }

        /* Tabs */
        .tabs {
            display: flex;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 2rem;
            overflow-x: auto;
            scrollbar-width: thin;
        }

        .tab {
            padding: 0.75rem 1.5rem;
            cursor: pointer;
            font-weight: 500;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .tab:hover {
            color: var(--primary-color);
        }

        .tab.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
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

        .form-select:focus {
            outline: none;
            border-color: var(--primary-color);
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
            color: var(--text-light);
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

        .notification-warning {
            background-color: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.2);
            color: var(--warning-color);
        }

        .notification i {
            margin-right: 0.5rem;
            font-size: 1.25rem;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Avatar Upload */
        .avatar-upload {
            position: relative;
            max-width: 200px;
            margin: 0 auto 1.5rem;
        }

        .avatar-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            overflow: hidden;
            margin: 0 auto 1rem;
            border: 3px solid var(--primary-color);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .avatar-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .avatar-edit {
            position: absolute;
            right: 10px;
            bottom: 5px;
            width: 40px;
            height: 40px;
        }

        .avatar-edit input {
            display: none;
        }

        .avatar-edit label {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            margin-bottom: 0;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .avatar-edit label:hover {
            background-color: var(--primary-light);
            transform: scale(1.1);
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background-color: var(--card-bg);
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--text-light);
        }

        /* Debug Info Styles */
        .debug-section {
            margin-top: 2rem;
            padding: 1rem;
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 0.375rem;
        }

        .debug-info {
            font-family: monospace;
            white-space: pre-wrap;
            background-color: rgba(0, 0, 0, 0.05);
            padding: 1rem;
            border-radius: 0.25rem;
            max-height: 300px;
            overflow-y: auto;
            color: var(--text-color);
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

        /* Theme cards */
        .theme-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .theme-card {
            border-radius: 10px;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 2px solid transparent;
        }

        .theme-card.active {
            border-color: var(--primary-color);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .theme-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .theme-preview {
            height: 120px;
            display: flex;
            flex-direction: column;
        }

        .theme-header {
            height: 30%;
            background-color: #5a3b5d;
        }

        .theme-body {
            height: 70%;
            background-color: #f5f7fa;
            padding: 10px;
        }

        .theme-dark .theme-body {
            background-color: #121212;
        }

        .theme-name {
            text-align: center;
            padding: 0.5rem;
            background-color: var(--card-bg);
            color: var(--text-color);
            font-weight: 500;
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
                border-bottom: 1px solid var(--border-color);
                text-align: center;
            }

            .tab.active {
                border-bottom: 1px solid var(--primary-color);
                background-color: rgba(90, 59, 93, 0.05);
            }

            .stats-grid {
                grid-template-columns: 1fr;
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
                <div class="left-stats">
                    <div class="level-indicator" title="<?php echo $xp_progress; ?> / <?php echo $xp_needed; ?> XP to next level">
                        <div class="xp-progress" style="width: <?php echo $xp_percentage; ?>%"></div>
                        <div class="level-badge">
                            <span class="level-number"><?php echo $user['user_level']; ?></span>
                        </div>
                        <span class="level-label">Level</span>
                    </div>
                    <div class="streak-counter" title="Current streak: <?php echo $streak; ?> days">
                        <span class="streak-number"><?php echo $streak; ?></span>
                        <div class="streak-icon">
                            <span class="fire-emoji">🔥</span>
                        </div>
                        <span class="streak-label">Streak</span>
                    </div>
                </div>
                <div class="language-indicator">
                    <span class="language-name"><?php echo htmlspecialchars($user['selected_language'] ?? 'Select Language'); ?></span>
                    <div class="language-flag">
                        <span><?php echo getLanguageFlag($user['selected_language'] ?? ''); ?></span>
                    </div>
                </div>
                <button class="theme-toggle" id="themeToggle" title="Toggle dark/light mode">
                    <i class="fas <?php echo $theme === 'dark' ? 'fa-sun' : 'fa-moon'; ?>"></i>
                </button>
            </div>
            
            <div class="content">
                <div class="header">
                    <h1><?php echo $greeting; ?>, <?php echo htmlspecialchars($user['first_name'] ?? $user['username']); ?>!</h1>
                    <p class="subtitle">Manage your account settings and preferences</p>
                </div>
                
                <?php if (!empty($notification)): ?>
                <div class="notification notification-<?php echo $notification_type; ?>">
                    <i class="fas <?php echo $notification_type === 'success' ? 'fa-check-circle' : ($notification_type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'); ?>"></i>
                    <?php echo $notification; ?>
                </div>
                <?php endif; ?>
                
                <div class="tabs">
                    <div class="tab active" data-tab="profile">Profile Settings</div>
                    <div class="tab" data-tab="security">Privacy & Security</div>
                    <div class="tab" data-tab="language">Language Preferences</div>
                    <div class="tab" data-tab="appearance">Appearance</div>
                    <div class="tab" data-tab="stats">Learning Stats</div>
                    <div class="tab" data-tab="debug">Debug Info</div>
                </div>
                
                <!-- Profile Settings Tab -->
                <div id="profile" class="tab-content active">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Profile Picture</h2>
                            <p class="card-description">Upload a profile picture to personalize your account</p>
                        </div>
                        <form id="avatarForm" method="POST" action="" enctype="multipart/form-data">
                            <div class="card-content">
                                <div class="avatar-upload">
                                    <div class="avatar-preview">
                                        <img id="imagePreview" src="<?php echo !empty($user['profile_picture']) ? '../' . $user['profile_picture'] : '../image/default-avatar.png'; ?>" alt="Profile Picture">
                                        <div class="avatar-edit">
                                            <input type="file" id="avatarUpload" name="avatar" accept=".png, .jpg, .jpeg" onchange="previewImage(this)">
                                            <label for="avatarUpload">
                                                <i class="fas fa-camera"></i>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <p class="form-text text-center">Click the camera icon to upload a new profile picture</p>
                            </div>
                            <div class="card-footer">
                                <button type="submit" name="avatar_submit" class="btn btn-primary" id="avatarSubmitBtn">
                                    <span class="btn-text">Save Profile Picture</span>
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
                                <a href="edit-profile.php" class="btn btn-primary" id="profileEditBtn">
                                    <span class="btn-text">Edit Info</span>
                                    <i class="fas fa-edit ml-2"></i>
                                </a>
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
                
                <!-- Appearance Tab -->
                <div id="appearance" class="tab-content">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Theme Settings</h2>
                            <p class="card-description">Customize the appearance of your Mura experience</p>
                        </div>
                        <form id="themeForm" method="POST" action="">
                            <div class="card-content">
                                <div class="theme-cards">
                                    <div class="theme-card <?php echo $theme === 'light' ? 'active' : ''; ?>" data-theme="light">
                                        <div class="theme-preview">
                                            <div class="theme-header"></div>
                                            <div class="theme-body"></div>
                                        </div>
                                        <div class="theme-name">Light Mode</div>
                                    </div>
                                    <div class="theme-card theme-dark <?php echo $theme === 'dark' ? 'active' : ''; ?>" data-theme="dark">
                                        <div class="theme-preview">
                                            <div class="theme-header"></div>
                                            <div class="theme-body"></div>
                                        </div>
                                        <div class="theme-name">Dark Mode</div>
                                    </div>
                                </div>
                                <input type="hidden" name="theme" id="themeInput" value="<?php echo $theme; ?>">
                            </div>
                            <div class="card-footer">
                                <button type="submit" name="theme_submit" class="btn btn-primary" id="themeSubmitBtn">
                                    <span class="btn-text">Save Theme</span>
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
                
                <!-- Learning Stats Tab -->
                <div id="stats" class="tab-content">
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <div class="stat-value"><?php echo $user['user_level']; ?></div>
                            <div class="stat-label">Current Level</div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-fire"></i>
                            </div>
                            <div class="stat-value"><?php echo $streak; ?></div>
                            <div class="stat-label">Current Streak</div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-award"></i>
                            </div>
                            <div class="stat-value"><?php echo $longest_streak; ?></div>
                            <div class="stat-label">Longest Streak</div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="stat-value"><?php echo $user['user_xp']; ?></div>
                            <div class="stat-label">Total XP</div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Learning Progress</h2>
                            <p class="card-description">Track your language learning journey</p>
                        </div>
                        <div class="card-content">
                            <div class="stats-grid">
                                <div class="stat-card">
                                    <div class="stat-icon">
                                        <i class="fas fa-book"></i>
                                    </div>
                                    <div class="stat-value"><?php echo $completed_lessons; ?></div>
                                    <div class="stat-label">Lessons Completed</div>
                                </div>
                                
                                <div class="stat-card">
                                    <div class="stat-icon">
                                        <i class="fas fa-gamepad"></i>
                                    </div>
                                    <div class="stat-value"><?php echo $game_stats['total_games_played'] ?? 0; ?></div>
                                    <div class="stat-label">Games Played</div>
                                </div>
                                
                                <div class="stat-card">
                                    <div class="stat-icon">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="stat-value"><?php echo $game_stats['correct_answers'] ?? 0; ?></div>
                                    <div class="stat-label">Correct Answers</div>
                                </div>
                                
                                <div class="stat-card">
                                    <div class="stat-icon">
                                        <i class="fas fa-bullseye"></i>
                                    </div>
                                    <div class="stat-value"><?php echo $accuracy; ?>%</div>
                                    <div class="stat-label">Accuracy</div>
                                </div>
                            </div>
                            
                            <div class="form-group" style="margin-top: 20px;">
                                <label>XP Progress to Level <?php echo $next_level; ?></label>
                                <div style="background-color: var(--border-color); height: 20px; border-radius: 10px; overflow: hidden; margin-top: 10px;">
                                    <div style="background: linear-gradient(90deg, var(--primary-light), var(--primary-color)); height: 100%; width: <?php echo $xp_percentage; ?>%; transition: width 1s ease-in-out;"></div>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-top: 5px;">
                                    <small><?php echo $xp_progress; ?> / <?php echo $xp_needed; ?> XP</small>
                                    <small><?php echo $xp_percentage; ?>% Complete</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Debug Info Tab -->
                <div id="debug" class="tab-content">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Debug Information</h2>
                            <p class="card-description">Technical information to help troubleshoot issues</p>
                        </div>
                        <div class="card-content">
                            <div class="debug-section">
                                <h3>Database Connection</h3>
                                <div class="debug-info">
                                    <?php
                                    echo "Connection Status: " . (mysqli_ping($conn) ? "Connected" : "Not Connected") . "\n";
                                    echo "Connection Error: " . mysqli_connect_error() . "\n";
                                    echo "Connection Errno: " . mysqli_connect_errno() . "\n";
                                    ?>
                                </div>
                            </div>
                            
                            <div class="debug-section">
                                <h3>User Information</h3>
                                <div class="debug-info">
                                    <?php
                                    echo "User ID: " . $user_id . "\n";
                                    echo "User Data: " . print_r($user, true) . "\n";
                                    ?>
                                </div>
                            </div>
                            
                            <div class="debug-section">
                                <h3>Debug Log</h3>
                                <div class="debug-info">
                                    <?php
                                    foreach ($debug_info as $info) {
                                        echo htmlspecialchars($info) . "\n";
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
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
            const forms = ["profileForm", "securityForm", "languageForm", "avatarForm", "themeForm"];
            const buttons = ["profileSubmitBtn", "securitySubmitBtn", "languageSubmitBtn", "avatarSubmitBtn", "themeSubmitBtn"];
            
            forms.forEach((formId, index) => {
                const form = document.getElementById(formId);
                const button = document.getElementById(buttons[index]);
                
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
            
            // Theme toggle
            const themeToggle = document.getElementById("themeToggle");
            if (themeToggle) {
                themeToggle.addEventListener("click", () => {
                    const html = document.documentElement;
                    const currentTheme = html.getAttribute("data-theme");
                    const newTheme = currentTheme === "dark" ? "light" : "dark";
                    
                    html.setAttribute("data-theme", newTheme);
                    themeToggle.innerHTML = newTheme === "dark" ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
                    
                    // Update theme input
                    const themeInput = document.getElementById("themeInput");
                    if (themeInput) {
                        themeInput.value = newTheme;
                    }
                    
                    // Update theme cards
                    const themeCards = document.querySelectorAll(".theme-card");
                    themeCards.forEach(card => {
                        card.classList.remove("active");
                        if (card.getAttribute("data-theme") === newTheme) {
                            card.classList.add("active");
                        }
                    });
                });
            }
            
            // Theme card selection
            const themeCards = document.querySelectorAll(".theme-card");
            themeCards.forEach(card => {
                card.addEventListener("click", () => {
                    const theme = card.getAttribute("data-theme");
                    
                    // Update theme input
                    const themeInput = document.getElementById("themeInput");
                    if (themeInput) {
                        themeInput.value = theme;
                    }
                    
                    // Update active card
                    themeCards.forEach(c => c.classList.remove("active"));
                    card.classList.add("active");
                    
                    // Update document theme
                    document.documentElement.setAttribute("data-theme", theme);
                    
                    // Update theme toggle icon
                    const themeToggle = document.getElementById("themeToggle");
                    if (themeToggle) {
                        themeToggle.innerHTML = theme === "dark" ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
                    }
                });
            });
            
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
        
        // Image preview function
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    document.getElementById('imagePreview').src = e.target.result;
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
