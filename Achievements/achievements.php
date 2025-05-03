<?php
session_start();
require_once '../Configurations/db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/signin.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $conn->prepare("
    SELECT u.username, u.onboarding_complete, uo.selected_language, uo.proficiency_level,
           l.first_name, l.last_name, us.level, us.xp, us.total_games_played, 
           us.total_questions_answered, us.correct_answers
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

// Fetch streak data
$streakStmt = $conn->prepare("
    SELECT current_streak, longest_streak, last_play_date 
    FROM user_streaks 
    WHERE user_id = ?
");
$streakStmt->bind_param("i", $user_id);
$streakStmt->execute();
$streakResult = $streakStmt->get_result();
$streakData = $streakResult->fetch_assoc();

$current_streak = $streakData['current_streak'] ?? 0;
$longest_streak = $streakData['longest_streak'] ?? 0;
$last_play_date = $streakData['last_play_date'] ?? null;

// Fetch game progress
$gameStmt = $conn->prepare("
    SELECT ugp.highest_level, ugp.total_score, 
           (SELECT COUNT(*) FROM game_sessions WHERE user_id = ? AND completed = 1) as completed_games
    FROM user_game_progress ugp
    WHERE ugp.user_id = ? AND ugp.language = ?
");
$language = $user['selected_language'] ?? 'Spanish';
$gameStmt->bind_param("iis", $user_id, $user_id, $language);
$gameStmt->execute();
$gameResult = $gameStmt->get_result();
$gameData = $gameResult->fetch_assoc();

$highest_level = $gameData['highest_level'] ?? 0;
$total_score = $gameData['total_score'] ?? 0;
$completed_games = $gameData['completed_games'] ?? 0;

// Calculate accuracy percentage
$accuracy = 0;
if ($user['total_questions_answered'] > 0) {
    $accuracy = round(($user['correct_answers'] / $user['total_questions_answered']) * 100);
}

// Define achievements based on user progress
$achievements = [
    [
        'title' => 'First Steps',
        'description' => 'Complete your first lesson',
        'icon' => '🏆',
        'unlocked' => $user['level'] >= 1,
        'progress' => 100
    ],
    [
        'title' => 'Vocabulary Master',
        'description' => 'Learn 50 new words',
        'icon' => '📚',
        'unlocked' => $user['level'] >= 3,
        'progress' => min(100, ($user['level'] / 3) * 100)
    ],
    [
        'title' => 'Streak Warrior',
        'description' => 'Maintain a 7-day streak',
        'icon' => '🔥',
        'unlocked' => $current_streak >= 7,
        'progress' => min(100, ($current_streak / 7) * 100)
    ],
    [
        'title' => 'Combat Champion',
        'description' => 'Defeat all bosses in Language Combat',
        'icon' => '⚔️',
        'unlocked' => $completed_games > 0,
        'progress' => min(100, ($highest_level / 4) * 100)
    ],
    [
        'title' => 'Perfect Accuracy',
        'description' => 'Achieve 90% accuracy in exercises',
        'icon' => '🎯',
        'unlocked' => $accuracy >= 90,
        'progress' => min(100, ($accuracy / 90) * 100)
    ],
    [
        'title' => 'Dedicated Learner',
        'description' => 'Reach level 5',
        'icon' => '🧠',
        'unlocked' => $user['level'] >= 5,
        'progress' => min(100, ($user['level'] / 5) * 100)
    ],
    [
        'title' => 'Social Butterfly',
        'description' => 'Participate in 5 video chats',
        'icon' => '🦋',
        'unlocked' => false,
        'progress' => 20
    ],
    [
        'title' => 'Grammar Guru',
        'description' => 'Master all grammar lessons',
        'icon' => '📝',
        'unlocked' => false,
        'progress' => 30
    ]
];

// Define badges based on achievements
$badges = [
    [
        'name' => 'Novice',
        'icon' => '🥉',
        'unlocked' => $user['level'] >= 1
    ],
    [
        'name' => 'Intermediate',
        'icon' => '🥈',
        'unlocked' => $user['level'] >= 5
    ],
    [
        'name' => 'Advanced',
        'icon' => '🥇',
        'unlocked' => $user['level'] >= 10
    ],
    [
        'name' => 'Streak Master',
        'icon' => '🔥',
        'unlocked' => $current_streak >= 7
    ],
    [
        'name' => 'Combat Hero',
        'icon' => '🛡️',
        'unlocked' => $completed_games > 0
    ],
    [
        'name' => 'Sharpshooter',
        'icon' => '🎯',
        'unlocked' => $accuracy >= 90
    ],
    [
        'name' => 'Scholar',
        'icon' => '🎓',
        'unlocked' => $user['level'] >= 15
    ],
    [
        'name' => 'Polyglot',
        'icon' => '🌍',
        'unlocked' => false
    ]
];

// Get current day of week for streak calendar
$today = date('N'); // 1 (Monday) to 7 (Sunday)
$days_of_week = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

// Function to get language flag emoji
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

$username = $user['username'] ?? 'User';
$first_name = $user['first_name'] ?? 'User';
$selected_language = $user['selected_language'] ?? 'Spanish';
$user_level = $user['level'] ?? 1;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Achievements | Mura</title>
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

        .left-stats {
            display: flex;
            align-items: center;
            gap: 15px;
        }

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

        .content {
            padding: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-title {
            margin-bottom: 30px;
        }

        .page-title h2 {
            font-size: 28px;
            color: #5a3b5d;
            margin-bottom: 10px;
        }

        .page-title p {
            color: #666;
            font-size: 16px;
        }

        /* Streak Calendar */
        .streak-section {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 25px;
            margin-bottom: 30px;
            text-align: center;
        }

        .streak-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .streak-header h3 {
            font-size: 20px;
            color: #5a3b5d;
        }

        .streak-stats {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-bottom: 20px;
        }

        .streak-stat {
            background: linear-gradient(135deg, #7e57c2, #5a3b5d);
            color: white;
            border-radius: 10px;
            padding: 15px 25px;
            text-align: center;
            min-width: 150px;
        }

        .streak-stat h4 {
            font-size: 14px;
            margin-bottom: 5px;
            opacity: 0.9;
        }

        .streak-stat .value {
            font-size: 32px;
            font-weight: bold;
        }

        .streak-calendar {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        .day {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 50px;
        }

        .day-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }

        .day-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            background-color: #f0f0f0;
            color: #666;
            transition: all 0.3s ease;
        }

        .day-circle.active {
            background: linear-gradient(135deg, #7e57c2, #5a3b5d);
            color: white;
            box-shadow: 0 3px 8px rgba(126, 87, 194, 0.3);
        }

        .day-circle.today {
            border: 2px solid #7e57c2;
            color: #7e57c2;
            font-weight: bold;
        }

        /* Achievements Section */
        .achievements-section {
            margin-bottom: 30px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-header h3 {
            font-size: 20px;
            color: #5a3b5d;
        }

        .achievements-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .achievement-card {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 20px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .achievement-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .achievement-icon {
            font-size: 36px;
            margin-bottom: 15px;
        }

        .achievement-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .achievement-description {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
        }

        .achievement-progress {
            height: 8px;
            background-color: #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 5px;
        }

        .achievement-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #7e57c2, #b39ddb);
            border-radius: 4px;
            transition: width 1s ease;
        }

        .achievement-progress-text {
            font-size: 12px;
            color: #888;
            text-align: right;
        }

        .achievement-locked {
            opacity: 0.7;
        }

        .achievement-locked::after {
            content: "🔒";
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 20px;
        }

        .achievement-unlocked::after {
            content: "✅";
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 20px;
        }

        /* Badges Section */
        .badges-section {
            margin-bottom: 30px;
        }

        .badges-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 20px;
        }

        .badge-card {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 15px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .badge-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .badge-icon {
            font-size: 40px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }

        .badge-card:hover .badge-icon {
            transform: scale(1.1);
        }

        .badge-name {
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }

        .badge-locked {
            opacity: 0.5;
            filter: grayscale(1);
        }

        .badge-locked .badge-name::after {
            content: " 🔒";
            font-size: 12px;
        }

        /* Stats Section */
        .stats-section {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 25px;
            margin-bottom: 30px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }

        .stat-card {
            background-color: #f9f4ff;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
        }

        .stat-value {
            font-size: 28px;
            font-weight: bold;
            color: #5a3b5d;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 14px;
            color: #666;
        }

        /* Responsive Styles */
        @media (max-width: 992px) {
            .achievements-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

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

            .streak-stats {
                flex-direction: column;
                gap: 10px;
            }

            .streak-stat {
                min-width: auto;
            }

            .achievements-grid {
                grid-template-columns: 1fr;
            }

            .badges-grid {
                grid-template-columns: repeat(3, 1fr);
            }

            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 480px) {
            .top-bar {
                padding: 15px 10px;
            }

            .left-stats {
                gap: 5px;
            }

            .content {
                padding: 20px 15px;
            }

            .streak-calendar {
                flex-wrap: wrap;
                justify-content: center;
            }

            .badges-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <div class="sidebar">
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
                        <a href="../Chatai/chatai.php">
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
                    <li class="nav-item active">
                        <a href="../Achievements/achievements.php">
                            <i class="fas fa-trophy"></i>
                            Achievements
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../Community/community.php">
                            <i class="fas fa-users"></i>
                            Community
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../Settings/settings.php">
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

        <div class="main-content">
            <div class="top-bar">
                <div class="menu-toggle">
                    <i class="fas fa-bars"></i>
                </div>
                <div class="left-stats">
                    <div class="level-indicator">
                        <div class="level-badge">
                            <span class="level-number"><?php echo $user_level; ?></span>
                        </div>
                        <span class="level-label">Level</span>
                    </div>
                    <div class="streak-counter">
                        <span class="streak-number"><?php echo $current_streak; ?></span>
                        <div class="streak-icon">
                            <span class="fire-emoji">🔥</span>
                        </div>
                        <span class="streak-label">Streak</span>
                    </div>
                </div>
                <div class="language-indicator">
                    <span class="language-name"><?php echo htmlspecialchars($selected_language); ?></span>
                    <div class="language-flag">
                        <span><?php echo getLanguageFlag($selected_language); ?></span>
                    </div>
                </div>
            </div>

            <div class="content">
                <div class="page-title">
                    <h2>Your Achievements</h2>
                    <p>Track your progress and unlock rewards as you learn <?php echo htmlspecialchars($selected_language); ?></p>
                </div>

                <!-- Streak Calendar Section -->
                <div class="streak-section">
                    <div class="streak-header">
                        <h3>Your Learning Streak</h3>
                    </div>
                    
                    <div class="streak-stats">
                        <div class="streak-stat">
                            <h4>Current Streak</h4>
                            <div class="value"><?php echo $current_streak; ?></div>
                        </div>
                        <div class="streak-stat">
                            <h4>Longest Streak</h4>
                            <div class="value"><?php echo $longest_streak; ?></div>
                        </div>
                    </div>
                    
                    <div class="streak-calendar">
                        <?php foreach ($days_of_week as $index => $day): 
                            $day_num = $index + 1;
                            $is_today = $day_num == $today;
                            $is_active = $current_streak > 0 && (
                                // If today is active
                                ($is_today && $last_play_date == date('Y-m-d')) ||
                                // If day is before today and within streak range
                                ($day_num < $today && $today - $day_num <= $current_streak) ||
                                // If day is after today but part of previous week's streak
                                ($day_num > $today && $current_streak > (7 - ($day_num - $today)))
                            );
                        ?>
                            <div class="day">
                                <div class="day-label"><?php echo $day; ?></div>
                                <div class="day-circle <?php echo $is_today ? 'today' : ''; ?> <?php echo $is_active ? 'active' : ''; ?>">
                                    <?php echo $is_active ? '✓' : ''; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Achievements Section -->
                <div class="achievements-section">
                    <div class="section-header">
                        <h3>Learning Achievements</h3>
                    </div>
                    
                    <div class="achievements-grid">
                        <?php foreach ($achievements as $achievement): ?>
                            <div class="achievement-card <?php echo $achievement['unlocked'] ? 'achievement-unlocked' : 'achievement-locked'; ?>">
                                <div class="achievement-icon"><?php echo $achievement['icon']; ?></div>
                                <div class="achievement-title"><?php echo htmlspecialchars($achievement['title']); ?></div>
                                <div class="achievement-description"><?php echo htmlspecialchars($achievement['description']); ?></div>
                                <div class="achievement-progress">
                                    <div class="achievement-progress-bar" style="width: <?php echo $achievement['progress']; ?>%"></div>
                                </div>
                                <div class="achievement-progress-text"><?php echo $achievement['progress']; ?>% complete</div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Badges Section -->
                <div class="badges-section">
                    <div class="section-header">
                        <h3>Your Badges</h3>
                    </div>
                    
                    <div class="badges-grid">
                        <?php foreach ($badges as $badge): ?>
                            <div class="badge-card <?php echo $badge['unlocked'] ? '' : 'badge-locked'; ?>">
                                <div class="badge-icon"><?php echo $badge['icon']; ?></div>
                                <div class="badge-name"><?php echo htmlspecialchars($badge['name']); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Stats Section -->
                <div class="stats-section">
                    <div class="section-header">
                        <h3>Your Learning Stats</h3>
                    </div>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-value"><?php echo $user['total_games_played']; ?></div>
                            <div class="stat-label">Games Played</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value"><?php echo $user['total_questions_answered']; ?></div>
                            <div class="stat-label">Questions Answered</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value"><?php echo $accuracy; ?>%</div>
                            <div class="stat-label">Accuracy</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value"><?php echo $user['xp']; ?></div>
                            <div class="stat-label">Total XP</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.querySelector('.menu-toggle');
            const sidebar = document.querySelector('.sidebar');
            
            if (menuToggle) {
                menuToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                });
            }

            // Animate achievement progress bars
            setTimeout(() => {
                const progressBars = document.querySelectorAll('.achievement-progress-bar');
                progressBars.forEach(bar => {
                    const width = bar.style.width;
                    bar.style.width = '0';
                    setTimeout(() => {
                        bar.style.width = width;
                    }, 100);
                });
            }, 500);

            // Add hover effects to badges
            const badgeCards = document.querySelectorAll('.badge-card:not(.badge-locked)');
            badgeCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    const icon = this.querySelector('.badge-icon');
                    icon.style.transform = 'scale(1.2) rotate(10deg)';
                });
                
                card.addEventListener('mouseleave', function() {
                    const icon = this.querySelector('.badge-icon');
                    icon.style.transform = 'scale(1) rotate(0)';
                });
            });
        });
    </script>
</body>
</html>
