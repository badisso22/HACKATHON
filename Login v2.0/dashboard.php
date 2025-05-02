<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $conn->prepare("
    SELECT u.username, u.onboarding_complete, uo.selected_language, uo.daily_goal, uo.proficiency_level,
           l.first_name, l.last_name
    FROM users u 
    LEFT JOIN user_onboarding uo ON u.user_ID = uo.user_ID 
    LEFT JOIN learner l ON u.user_ID = l.user_ID
    WHERE u.user_ID = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: signin.php");
    exit();
}

$user = $result->fetch_assoc();

// Check if onboarding is complete, if not redirect to check_onboarding.php
if (!$user['onboarding_complete']) {
    header("Location: check_onboarding.php");
    exit();
}

$username = $user['username'] ?? 'User';
$first_name = $user['first_name'] ?? 'User';
$selected_language = $user['selected_language'] ?? 'Not set';
$daily_goal = $user['daily_goal'] ?? 'Not set';
$proficiency_level = $user['proficiency_level'] ?? 'Not set';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles/dashboard.css">
</head>
<body>
    <div class="app-container">
        <div class="sidebar">
            <div class="logo">
                <div class="logo-container">
                    <div class="mura-logo">🌎</div>
                    <h1>Mura</h1>
                </div>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="nav-item active">
                        <a href="dashboard.php">
                            <i>🏠</i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#">
                            <i>📚</i>
                            Lessons
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#">
                            <i>🏆</i>
                            Achievements
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#">
                            <i>👥</i>
                            Community
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#">
                            <i>⚙️</i>
                            Settings
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <a href="logout.php" class="logout-btn">
                    <i>🚪</i>
                    Logout
                </a>
            </div>
        </div>

        <div class="main-content">
            <div class="top-bar">
                <div class="menu-toggle">☰</div>
                <div class="left-stats">
                    <div class="level-indicator">
                        <div class="level-badge">
                            <span class="level-number">1</span>
                        </div>
                        <span class="level-label">Level</span>
                    </div>
                    <div class="streak-counter">
                        <span class="streak-number">0</span>
                        <div class="streak-icon">
                            <span class="fire-emoji">🔥</span>
                        </div>
                        <span class="streak-label">Streak</span>
                    </div>
                </div>
                <div class="language-indicator">
                    <span class="language-name"><?php echo htmlspecialchars($selected_language); ?></span>
                    <div class="language-flag">
                        <span>🌐</span>
                    </div>
                </div>
            </div>

            <div class="content">
                <div class="welcome-section">
                    <h2>Welcome, <?php echo htmlspecialchars($first_name); ?>!</h2>
                    <p>Ready to continue your language learning journey?</p>
                </div>

                <div class="progress-section">
                    <h3>Your Learning Profile</h3>
                    <p><strong>Language:</strong> <?php echo htmlspecialchars($selected_language); ?></p>
                    <p><strong>Daily Goal:</strong> <?php echo htmlspecialchars($daily_goal); ?></p>
                    <p><strong>Level:</strong> <?php echo htmlspecialchars($proficiency_level); ?></p>
                    
                    <div class="progress-bar">
                        <div class="progress" style="width: 0%"></div>
                    </div>
                    <button class="continue-btn">Start Learning</button>
                </div>

                <div class="achievements-section">
                    <h3>Achievements</h3>
                    <ul class="achievements-list">
                        <li><i>🏆</i> Complete your first lesson</li>
                        <li><i>🔥</i> Reach a 3-day streak</li>
                        <li><i>📚</i> Learn 10 new words</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle
            const menuToggle = document.querySelector('.menu-toggle');
            const sidebar = document.querySelector('.sidebar');
            
            if (menuToggle) {
                menuToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                });
            }
            
            // Animate progress bar
            setTimeout(() => {
                document.querySelector('.progress').style.width = '10%';
            }, 500);
        });
    </script>
</body>
</html>
