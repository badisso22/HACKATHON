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
           l.first_name, l.last_name, us.level
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

$username = $user['username'] ?? 'User';
$first_name = $user['first_name'] ?? 'User';
$selected_language = $user['selected_language'] ?? 'Spanish';
$proficiency_level = $user['proficiency_level'] ?? 'Beginner';
$user_level = $user['level'] ?? 1;

// Fetch streak data
$streakStmt = $conn->prepare("
    SELECT current_streak
    FROM user_streaks 
    WHERE user_id = ?
");
$streakStmt->bind_param("i", $user_id);
$streakStmt->execute();
$streakResult = $streakStmt->get_result();
$streakData = $streakResult->fetch_assoc();

$current_streak = $streakData['current_streak'] ?? 0;

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

// Define lesson categories
$lessonCategories = [
    [
        'title' => 'Numbers 1-10',
        'description' => 'Learn to count from 1 to 10',
        'icon' => '🔢',
        'color' => '#7e57c2',
        'url' => 'lesson.php?type=numbers_basic'
    ],
    [
        'title' => 'Numbers 20-1000',
        'description' => 'Learn larger numbers and counting',
        'icon' => '📊',
        'color' => '#5c6bc0',
        'url' => 'lesson.php?type=numbers_advanced'
    ],
    [
        'title' => 'Colors',
        'description' => 'Learn the names of common colors',
        'icon' => '🎨',
        'color' => '#26a69a',
        'url' => 'lesson.php?type=colors'
    ],
    [
        'title' => 'Animals',
        'description' => 'Learn the names of common animals',
        'icon' => '🐾',
        'color' => '#ec407a',
        'url' => 'lesson.php?type=animals'
    ],
    [
        'title' => 'Greetings',
        'description' => 'Learn common greetings and introductions',
        'icon' => '👋',
        'color' => '#ffa726',
        'url' => 'lesson.php?type=greetings'
    ],
    [
        'title' => 'Food & Drinks',
        'description' => 'Learn vocabulary for food and beverages',
        'icon' => '🍽️',
        'color' => '#66bb6a',
        'url' => 'lesson.php?type=food'
    ],
    [
        'title' => 'Family Members',
        'description' => 'Learn words for family relationships',
        'icon' => '👪',
        'color' => '#8d6e63',
        'url' => 'lesson.php?type=family'
    ],
    [
        'title' => 'Common Phrases',
        'description' => 'Learn essential everyday phrases',
        'icon' => '💬',
        'color' => '#42a5f5',
        'url' => 'lesson.php?type=phrases'
    ]
];

// Group lessons into rows (2 per row for desktop)
$lessonRows = array_chunk($lessonCategories, 2);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lessons | Mura</title>
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

        /* Lessons Grid */
        .lessons-grid {
            display: flex;
            flex-direction: column;
            gap: 30px;
            margin-bottom: 30px;
        }

        .lessons-row {
            display: flex;
            gap: 30px;
        }

        .lesson-card {
            flex: 1;
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            position: relative;
        }

        .lesson-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .lesson-card-inner {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .lesson-icon-container {
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            position: relative;
            overflow: hidden;
        }

        .lesson-icon-container::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0));
            z-index: 1;
        }

        .lesson-icon-container::after {
            content: "";
            position: absolute;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.3) 0%, rgba(255, 255, 255, 0) 70%);
            border-radius: 50%;
            bottom: -100px;
            right: -100px;
        }

        .lesson-content {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .lesson-title {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }

        .lesson-description {
            font-size: 14px;
            color: #666;
            margin-bottom: 20px;
            flex: 1;
        }

        .lesson-button {
            background-color: #5a3b5d;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: auto;
        }

        .lesson-button:hover {
            background-color: #7e57c2;
            transform: translateY(-2px);
        }

        .lesson-button i {
            font-size: 16px;
        }

        /* Responsive Styles */
        @media (max-width: 992px) {
            .lessons-row {
                flex-direction: column;
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

            .content {
                padding: 20px 15px;
            }

            .page-title h2 {
                font-size: 24px;
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
                padding: 15px 10px;
            }

            .lessons-grid {
                gap: 20px;
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
                    <li class="nav-item active">
                        <a href="lessons.php">
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
                    <h2>Language Lessons</h2>
                    <p>Choose a lesson category to start learning <?php echo htmlspecialchars($selected_language); ?></p>
                </div>

                <div class="lessons-grid">
                    <?php foreach ($lessonRows as $row): ?>
                        <div class="lessons-row">
                            <?php foreach ($row as $lesson): ?>
                                <a href="<?php echo $lesson['url']; ?>" class="lesson-card">
                                    <div class="lesson-card-inner">
                                        <div class="lesson-icon-container" style="background-color: <?php echo $lesson['color']; ?>">
                                            <span class="lesson-icon"><?php echo $lesson['icon']; ?></span>
                                        </div>
                                        <div class="lesson-content">
                                            <h3 class="lesson-title"><?php echo htmlspecialchars($lesson['title']); ?></h3>
                                            <p class="lesson-description"><?php echo htmlspecialchars($lesson['description']); ?></p>
                                            <button class="lesson-button">
                                                Start Learning <i class="fas fa-arrow-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
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
        });
    </script>
</body>
</html>
