<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MURA - Language Learning</title>
    <link rel="stylesheet" href="mainstyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <!-- Added logo image next to MURA text -->
                <div class="logo-container">
                    <img src="logo.png" alt="MURA Logo" class="mura-logo">
                    <h1>MURA</h1>
                </div>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="nav-item active">
                        <a href="#profile">
                            <i class="fas fa-user"></i>
                            <span>Profile</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#learn">
                            <i class="fas fa-book"></i>
                            <span>Learn</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#translator">
                            <i class="fas fa-language"></i>
                            <span>Translator</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#combat">
                            <i class="fas fa-gamepad"></i>
                            <span>Language Combat</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#ai-buddy">
                            <i class="fas fa-robot"></i>
                            <span>AI Buddy</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#levels">
                            <i class="fas fa-layer-group"></i>
                            <span>Levels</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#settings">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <a href="#logout" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Bar with Stats -->
            <div class="top-bar">
                <div class="left-stats">
                    <div class="menu-toggle">
                        <i class="fas fa-bars"></i>
                    </div>
                    
                    <!-- Level Indicator -->
                    <div class="level-indicator">
                        <div class="level-badge">
                            <span class="level-number">12</span>
                        </div>
                        <div class="level-label">Level</div>
                    </div>
                    
                    <!-- Streak Counter -->
                    <div class="streak-counter">
                        <div class="streak-number">7</div>
                        <div class="streak-icon">
                            <div class="fire-emoji">🔥</div>
                        </div>
                        <div class="streak-label">day streak</div>
                    </div>
                </div>
                
                <!-- Language Indicator with flag image -->
                <div class="language-indicator">
                    <div class="language-name">Spanish</div>
                    <div class="language-flag">
                        <!-- Replaced emoji with actual image -->
                        <img src="Flag.jpg" alt="Spanish Flag" class="flag-image">
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="content">
                <div class="welcome-section">
                    <h2>Welcome back, learner! 🌟</h2>
                </div>

                <div class="progress-section">
                    <h3>Your Daily Progress</h3>
                    <div class="progress-bar">
                        <div class="progress" style="width: 65%"></div>
                    </div>
                    <button class="continue-btn">Continue Lesson</button>
                </div>

                <div class="achievements-section">
                    <h3>Achievements</h3>
                    <ul class="achievements-list">
                        <li><i class="fas fa-star"></i> 7-day streak</li>
                        <li><i class="fas fa-bullseye"></i> 90% quiz accuracy</li>
                        <li><i class="fas fa-trophy"></i> 3 new skills unlocked</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="main.js"></script>
</body>
</html>
