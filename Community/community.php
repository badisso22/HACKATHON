<?php
session_start();
require_once '../Configurations/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/signin.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user information
$stmt = $conn->prepare("
    SELECT u.username, u.profile_picture, uo.selected_language, us.level
    FROM users u 
    LEFT JOIN user_onboarding uo ON u.user_ID = uo.user_ID 
    LEFT JOIN user_stats us ON u.user_ID = us.user_id
    WHERE u.user_ID = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$username = $user['username'] ?? 'User';
$profile_picture = $user['profile_picture'] ?? 'default-avatar.png';
$selected_language = $user['selected_language'] ?? 'Spanish';
$user_level = $user['level'] ?? 1;

// Handle post submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_content'])) {
    $content = trim($_POST['post_content']);
    $title = isset($_POST['post_title']) ? trim($_POST['post_title']) : '';
    
    if (!empty($content)) {
        // Insert the new post into the database
        $stmt = $conn->prepare("INSERT INTO posts (user_id, title, content, status) VALUES (?, ?, ?, 'published')");
        $stmt->bind_param("iss", $user_id, $title, $content);
        $stmt->execute();
        
        // Redirect to avoid form resubmission
        header("Location: community.php");
        exit();
    }
}

// Get posts with user information
$posts_query = "
    SELECT p.post_id, p.title, p.content, p.created_at, 
           u.username, u.profile_picture, u.user_ID,
           uo.selected_language, us.level
    FROM posts p
    JOIN users u ON p.user_id = u.user_ID
    LEFT JOIN user_onboarding uo ON u.user_ID = uo.user_ID
    LEFT JOIN user_stats us ON u.user_ID = us.user_id
    WHERE p.status = 'published'
    ORDER BY p.created_at DESC
    LIMIT 50
";
$posts_result = $conn->query($posts_query);

// Get trending topics/tags (placeholder for now)
$trending_topics = [
    'Spanish Grammar', 'French Vocabulary', 'Learning Tips', 
    'Language Exchange', 'Study Habits', 'Pronunciation'
];

// Get active users (placeholder for now)
$active_users_query = "
    SELECT u.username, u.profile_picture, COUNT(p.post_id) as post_count
    FROM users u
    JOIN posts p ON u.user_ID = p.user_id
    WHERE p.created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY u.user_ID
    ORDER BY post_count DESC
    LIMIT 5
";
$active_users_result = $conn->query($active_users_query);

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

// Format time elapsed
function timeElapsed($datetime) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0) {
        return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    }
    if ($diff->m > 0) {
        return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    }
    if ($diff->d > 0) {
        return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    }
    if ($diff->h > 0) {
        return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    }
    if ($diff->i > 0) {
        return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    }
    return 'just now';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community | Mura</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #9d4edd;
            --primary-light: #b76ee8;
            --primary-dark: #7b2cbf;
            --bg: #1f1235;
            --bg-light: #3c1642;
            --bg-lighter: #4e1d54;
            --text: #ffffff;
            --text-dark: #333333;
            --text-muted: #cccccc;
            --border-color: rgba(255, 255, 255, 0.1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
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
            margin-left: 250px;
            display: flex;
            flex-direction: column;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background-color: rgba(60, 22, 66, 0.8);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 90;
        }

        .page-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--text);
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            background-color: var(--bg-lighter);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-size: 14px;
            font-weight: 600;
        }

        .user-language {
            font-size: 12px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .menu-toggle {
            display: none;
            font-size: 24px;
            cursor: pointer;
        }

        /* Content Layout */
        .content-wrapper {
            display: flex;
            flex: 1;
            padding: 20px;
            gap: 20px;
        }

        .feed-container {
            flex: 1;
            max-width: 700px;
        }

        .sidebar-right {
            width: 300px;
            position: sticky;
            top: 80px;
            height: calc(100vh - 80px);
            overflow-y: auto;
        }

        /* Post Creation */
        .post-creation {
            background-color: var(--bg-light);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .post-creation-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .post-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            overflow: hidden;
            background-color: var(--bg-lighter);
        }

        .post-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .post-prompt {
            font-size: 16px;
            color: var(--text-muted);
        }

        .post-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .post-title-input {
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background-color: var(--bg-lighter);
            color: var(--text);
            font-size: 16px;
            width: 100%;
            transition: all 0.3s ease;
        }

        .post-content-input {
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background-color: var(--bg-lighter);
            color: var(--text);
            font-size: 16px;
            width: 100%;
            min-height: 120px;
            resize: vertical;
            transition: all 0.3s ease;
        }

        .post-title-input:focus,
        .post-content-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(157, 78, 221, 0.2);
        }

        .post-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .post-button {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .post-button.primary {
            background-color: var(--primary);
            color: white;
        }

        .post-button.primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .post-button.secondary {
            background-color: transparent;
            color: var(--text);
            border: 1px solid var(--border-color);
        }

        .post-button.secondary:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }

        /* Post Feed */
        .post-feed {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .post-card {
            background-color: var(--bg-light);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .post-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
        }

        .post-header {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .post-user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            margin-right: 12px;
            background-color: var(--bg-lighter);
        }

        .post-user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .post-user-info {
            flex: 1;
        }

        .post-user-name {
            font-size: 16px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .user-badge {
            display: flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            background-color: var(--primary-dark);
            color: white;
        }

        .post-meta {
            font-size: 12px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .post-language {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .post-time {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .post-options {
            font-size: 18px;
            color: var(--text-muted);
            cursor: pointer;
            padding: 5px;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .post-options:hover {
            color: var(--text);
            background-color: rgba(255, 255, 255, 0.1);
        }

        .post-body {
            padding: 20px;
        }

        .post-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--text);
        }

        .post-content {
            font-size: 15px;
            line-height: 1.6;
            color: var(--text);
            margin-bottom: 15px;
            white-space: pre-line;
        }

        .post-footer {
            display: flex;
            align-items: center;
            padding: 10px 20px;
            border-top: 1px solid var(--border-color);
        }

        .post-actions-bar {
            display: flex;
            gap: 20px;
        }

        .post-action {
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--text-muted);
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .post-action:hover {
            color: var(--primary-light);
        }

        .post-action i {
            font-size: 16px;
        }

        /* Right Sidebar Components */
        .sidebar-section {
            background-color: var(--bg-light);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .sidebar-section-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 15px;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sidebar-section-title i {
            color: var(--primary);
        }

        .trending-topics {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .topic-tag {
            padding: 6px 12px;
            border-radius: 20px;
            background-color: var(--bg-lighter);
            color: var(--text);
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .topic-tag:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .active-users-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .active-user-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .active-user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            overflow: hidden;
            background-color: var(--bg-lighter);
        }

        .active-user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .active-user-info {
            flex: 1;
        }

        .active-user-name {
            font-size: 14px;
            font-weight: 600;
            color: var(--text);
        }

        .active-user-posts {
            font-size: 12px;
            color: var(--text-muted);
        }

        .community-guidelines {
            font-size: 14px;
            line-height: 1.6;
            color: var(--text-muted);
        }

        .community-guidelines ul {
            margin-left: 20px;
            margin-top: 10px;
        }

        .community-guidelines li {
            margin-bottom: 8px;
        }

        .community-guidelines a {
            color: var(--primary-light);
            text-decoration: none;
        }

        .community-guidelines a:hover {
            text-decoration: underline;
        }

        /* Empty State */
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            text-align: center;
            background-color: var(--bg-light);
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .empty-state-icon {
            font-size: 48px;
            color: var(--primary);
            margin-bottom: 20px;
        }

        .empty-state-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--text);
        }

        .empty-state-description {
            font-size: 16px;
            color: var(--text-muted);
            margin-bottom: 20px;
            max-width: 400px;
        }

        /* Responsive Styles */
        @media (max-width: 1200px) {
            .content-wrapper {
                flex-direction: column;
            }

            .sidebar-right {
                width: 100%;
                position: static;
                height: auto;
            }
        }

        @media (max-width: 992px) {
            .feed-container {
                max-width: 100%;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: -250px;
                height: 100%;
                z-index: 999;
                transition: left 0.3s ease;
            }

            .sidebar.active {
                left: 0;
            }

            .main-content {
                margin-left: 0;
                width: 100%;
            }

            .menu-toggle {
                display: block;
            }

            .top-bar {
                padding: 15px 20px;
            }

            .user-info {
                display: none;
            }

            .post-header {
                flex-wrap: wrap;
            }
        }

        @media (max-width: 576px) {
            .post-creation-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .post-actions {
                flex-direction: column;
                width: 100%;
            }

            .post-button {
                width: 100%;
            }

            .post-actions-bar {
                width: 100%;
                justify-content: space-between;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .post-card {
            animation: fadeIn 0.5s ease forwards;
        }

        .post-card:nth-child(2) {
            animation-delay: 0.1s;
        }

        .post-card:nth-child(3) {
            animation-delay: 0.2s;
        }

        .post-card:nth-child(4) {
            animation-delay: 0.3s;
        }

        .post-card:nth-child(5) {
            animation-delay: 0.4s;
        }
    </style>
</head>
<body>
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
                    <a href="../dashboard/dashboard.php">
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
                    <a href="../dashboard/translation.php">
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
                <li class="nav-item active">
                    <a href="community.php">
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
            <a href="../dashboard/logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div class="menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </div>
            <h1 class="page-title">Community</h1>
            <div class="user-profile">
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($username); ?></div>
                    <div class="user-language">
                        <span><?php echo getLanguageFlag($selected_language); ?></span>
                        <span><?php echo htmlspecialchars($selected_language); ?></span>
                    </div>
                </div>
                <div class="user-avatar">
                    <?php if ($profile_picture && file_exists("../uploads/$profile_picture")): ?>
                        <img src="../uploads/<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture">
                    <?php else: ?>
                        <i class="fas fa-user"></i>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="content-wrapper">
            <div class="feed-container">
                <div class="post-creation">
                    <div class="post-creation-header">
                        <div class="post-avatar">
                            <?php if ($profile_picture && file_exists("../uploads/$profile_picture")): ?>
                                <img src="../uploads/<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture">
                            <?php else: ?>
                                <i class="fas fa-user"></i>
                            <?php endif; ?>
                        </div>
                        <div class="post-prompt">Share your language learning journey with the community...</div>
                    </div>
                    <form class="post-form" method="POST" action="community.php">
                        <input type="text" name="post_title" class="post-title-input" placeholder="Title (optional)">
                        <textarea name="post_content" class="post-content-input" placeholder="What's on your mind?" required></textarea>
                        <div class="post-actions">
                            <button type="button" class="post-button secondary">Cancel</button>
                            <button type="submit" class="post-button primary">Post</button>
                        </div>
                    </form>
                </div>

                <div class="post-feed">
                    <?php if ($posts_result && $posts_result->num_rows > 0): ?>
                        <?php while ($post = $posts_result->fetch_assoc()): ?>
                            <div class="post-card">
                                <div class="post-header">
                                    <div class="post-user-avatar">
                                        <?php if ($post['profile_picture'] && file_exists("../uploads/{$post['profile_picture']}")): ?>
                                            <img src="../uploads/<?php echo htmlspecialchars($post['profile_picture']); ?>" alt="User Avatar">
                                        <?php else: ?>
                                            <i class="fas fa-user"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="post-user-info">
                                        <div class="post-user-name">
                                            <?php echo htmlspecialchars($post['username']); ?>
                                            <span class="user-badge">Lvl <?php echo (int)($post['level'] ?? 1); ?></span>
                                        </div>
                                        <div class="post-meta">
                                            <div class="post-language">
                                                <span><?php echo getLanguageFlag($post['selected_language'] ?? 'Spanish'); ?></span>
                                                <span><?php echo htmlspecialchars($post['selected_language'] ?? 'Spanish'); ?></span>
                                            </div>
                                            <div class="post-time">
                                                <i class="far fa-clock"></i>
                                                <span><?php echo timeElapsed($post['created_at']); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="post-options">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </div>
                                </div>
                                <div class="post-body">
                                    <?php if (!empty($post['title'])): ?>
                                        <h3 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                                    <?php endif; ?>
                                    <div class="post-content"><?php echo nl2br(htmlspecialchars($post['content'])); ?></div>
                                </div>
                                <div class="post-footer">
                                    <div class="post-actions-bar">
                                        <div class="post-action">
                                            <i class="far fa-heart"></i>
                                            <span>Like</span>
                                        </div>
                                        <div class="post-action">
                                            <i class="far fa-comment"></i>
                                            <span>Comment</span>
                                        </div>
                                        <div class="post-action">
                                            <i class="far fa-share-square"></i>
                                            <span>Share</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="far fa-comments"></i>
                            </div>
                            <h3 class="empty-state-title">No posts yet</h3>
                            <p class="empty-state-description">Be the first to share your language learning journey with the community!</p>
                            <button class="post-button primary">Create a Post</button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="sidebar-right">
                <div class="sidebar-section">
                    <h3 class="sidebar-section-title">
                        <i class="fas fa-fire"></i>
                        Trending Topics
                    </h3>
                    <div class="trending-topics">
                        <?php foreach ($trending_topics as $topic): ?>
                            <div class="topic-tag"><?php echo htmlspecialchars($topic); ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="sidebar-section">
                    <h3 class="sidebar-section-title">
                        <i class="fas fa-users"></i>
                        Active Members
                    </h3>
                    <div class="active-users-list">
                        <?php if ($active_users_result && $active_users_result->num_rows > 0): ?>
                            <?php while ($user = $active_users_result->fetch_assoc()): ?>
                                <div class="active-user-item">
                                    <div class="active-user-avatar">
                                        <?php if ($user['profile_picture'] && file_exists("../uploads/{$user['profile_picture']}")): ?>
                                            <img src="../uploads/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="User Avatar">
                                        <?php else: ?>
                                            <i class="fas fa-user"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="active-user-info">
                                        <div class="active-user-name"><?php echo htmlspecialchars($user['username']); ?></div>
                                        <div class="active-user-posts"><?php echo $user['post_count']; ?> posts this week</div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>No active users found.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="sidebar-section">
                    <h3 class="sidebar-section-title">
                        <i class="fas fa-info-circle"></i>
                        Community Guidelines
                    </h3>
                    <div class="community-guidelines">
                        <p>Welcome to the Mura community! Please follow these guidelines:</p>
                        <ul>
                            <li>Be respectful and supportive of other learners</li>
                            <li>Share your language learning experiences</li>
                            <li>Ask questions and provide helpful answers</li>
                            <li>No spam, offensive content, or harassment</li>
                        </ul>
                        <p>For more information, see our <a href="#">full guidelines</a>.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle
            const menuToggle = document.getElementById('menuToggle');
            const sidebar = document.getElementById('sidebar');
            
            if (menuToggle) {
                menuToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                });
            }

            // Post options dropdown (placeholder)
            const postOptions = document.querySelectorAll('.post-options');
            postOptions.forEach(option => {
                option.addEventListener('click', function() {
                    alert('Options menu is not implemented in this demo');
                });
            });

            // Cancel button for post creation
            const cancelButton = document.querySelector('.post-button.secondary');
            const postForm = document.querySelector('.post-form');
            
            if (cancelButton && postForm) {
                cancelButton.addEventListener('click', function() {
                    postForm.reset();
                });
            }

            // Empty state create post button
            const emptyStateButton = document.querySelector('.empty-state .post-button');
            const postContentInput = document.querySelector('.post-content-input');
            
            if (emptyStateButton && postContentInput) {
                emptyStateButton.addEventListener('click', function() {
                    postContentInput.focus();
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                });
            }

            // Like, comment, share actions (placeholder)
            const postActions = document.querySelectorAll('.post-action');
            postActions.forEach(action => {
                action.addEventListener('click', function() {
                    const actionType = this.querySelector('span').textContent;
                    alert(`${actionType} feature is not implemented in this demo`);
                });
            });

            // Topic tags (placeholder)
            const topicTags = document.querySelectorAll('.topic-tag');
            topicTags.forEach(tag => {
                tag.addEventListener('click', function() {
                    alert(`Filter by "${this.textContent}" is not implemented in this demo`);
                });
            });
        });
    </script>
</body>
</html>
