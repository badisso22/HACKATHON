<?php
session_start();
require_once '../Configurations/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/signin.php");
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
    header("Location: ../Login/signin.php");
    exit();
}

$user = $result->fetch_assoc();

// Check if onboarding is complete, if not redirect to check_onboarding.php
if (!$user['onboarding_complete']) {
    header("Location: check_onboarding.php");
    exit();
}

$username = $user['username'] ?? 'User';
$first_name = $user['first_name'] ?? 'mehdi';
$selected_language = $user['selected_language'] ?? 'Spanish';
$daily_goal = $user['daily_goal'] ?? '3 min';
$proficiency_level = $user['proficiency_level'] ?? 'Beginner';

// Mock data for daily progress - in a real app, this would come from your database
$daily_progress = 35; // percentage of daily goal completed
$user_level = 1; // User's current level

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

// Get current date for greeting
$hour = date('H');
if ($hour < 12) {
    $greeting = "Good morning";
} elseif ($hour < 18) {
    $greeting = "Good afternoon";
} else {
    $greeting = "Good evening";
}

// Mock data for recommended lessons
$recommendedLessons = [
    [
        'title' => 'Basic Greetings',
        'description' => 'Learn how to say hello and introduce yourself',
        'duration' => '10 min',
        'icon' => '👋'
    ],
    [
        'title' => 'Common Phrases',
        'description' => 'Essential phrases for everyday conversations',
        'duration' => '15 min',
        'icon' => '💬'
    ],
    [
        'title' => 'Numbers 1-20',
        'description' => 'Learn to count and use numbers in conversation',
        'duration' => '12 min',
        'icon' => '🔢'
    ]
];

// Function to get random vocabulary words
function getRandomVocabularyWords($language, $count = 5) {
    // In a real app, you would fetch these from a database based on the user's language
    // For now, we'll use a larger predefined list and randomly select from it
    $allWords = [
        'Spanish' => [
            ['word' => 'Hola', 'translation' => 'Hello'],
            ['word' => 'Gracias', 'translation' => 'Thank you'],
            ['word' => 'Por favor', 'translation' => 'Please'],
            ['word' => 'Amigo', 'translation' => 'Friend'],
            ['word' => 'Buenos días', 'translation' => 'Good morning'],
            ['word' => 'Buenas noches', 'translation' => 'Good night'],
            ['word' => 'Adiós', 'translation' => 'Goodbye'],
            ['word' => 'Sí', 'translation' => 'Yes'],
            ['word' => 'No', 'translation' => 'No'],
            ['word' => 'Disculpe', 'translation' => 'Excuse me'],
            ['word' => 'Lo siento', 'translation' => 'I\'m sorry'],
            ['word' => 'Agua', 'translation' => 'Water'],
            ['word' => 'Comida', 'translation' => 'Food'],
            ['word' => 'Casa', 'translation' => 'House'],
            ['word' => 'Familia', 'translation' => 'Family']
        ],
        'French' => [
            ['word' => 'Bonjour', 'translation' => 'Hello'],
            ['word' => 'Merci', 'translation' => 'Thank you'],
            ['word' => 'S\'il vous plaît', 'translation' => 'Please'],
            ['word' => 'Ami', 'translation' => 'Friend'],
            ['word' => 'Au revoir', 'translation' => 'Goodbye']
        ],
        // Add more languages as needed
    ];
    
    // Default to Spanish if the language doesn't exist in our array
    $wordsForLanguage = $allWords[$language] ?? $allWords['Spanish'];
    
    // Shuffle the array to get random words
    shuffle($wordsForLanguage);
    
    // Return the first $count words
    return array_slice($wordsForLanguage, 0, $count);
}

// Get random vocabulary words for the user's selected language
$vocabularyWords = getRandomVocabularyWords($selected_language);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Mura</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
                    <li class="nav-item active">
                        <a href="dashboard.php">
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
                        <a href="#">
                            <i class="fas fa-robot"></i>
                            TutorBot
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#">
                            <i class="fas fa-gamepad"></i>
                            Language Combat
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="translation.php">
                            <i class="fas fa-language"></i>
                            Translation
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#">
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
                        <a href="#">
                            <i class="fas fa-users"></i>
                            Community
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#">
                            <i class="fas fa-cog"></i>
                            Settings
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <a href="logout.php" class="logout-btn">
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
                        <span><?php echo getLanguageFlag($selected_language); ?></span>
                    </div>
                </div>
            </div>

            <div class="content">
                <div class="welcome-section">
                    <h2><?php echo $greeting; ?>, <?php echo htmlspecialchars($first_name); ?>!</h2>
                    <p>Ready to continue your <?php echo htmlspecialchars($selected_language); ?> learning journey?</p>
                </div>

                <div class="daily-goal-section">
                    <div class="daily-goal-header">
                        <h3>Daily Goal Progress</h3>
                        <span class="daily-goal-info"><?php echo htmlspecialchars($daily_progress); ?>% of <?php echo htmlspecialchars($daily_goal); ?></span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress" style="width: <?php echo $daily_progress; ?>%"></div>
                    </div>
                    <button class="continue-btn pulse-animation">Continue Learning</button>
                </div>

                <div class="dashboard-grid">
                    <div class="dashboard-card recommended-lessons">
                        <div class="card-header">
                            <h3><i class="fas fa-star"></i> Recommended Lessons</h3>
                        </div>
                        <div class="card-content">
                            <ul class="lesson-list">
                                <?php foreach ($recommendedLessons as $lesson): ?>
                                <li class="lesson-item">
                                    <div class="lesson-icon"><?php echo $lesson['icon']; ?></div>
                                    <div class="lesson-details">
                                        <h4><?php echo htmlspecialchars($lesson['title']); ?></h4>
                                        <p><?php echo htmlspecialchars($lesson['description']); ?></p>
                                        <span class="lesson-duration"><i class="far fa-clock"></i> <?php echo htmlspecialchars($lesson['duration']); ?></span>
                                    </div>
                                    <button class="lesson-start-btn"><i class="fas fa-play"></i></button>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="dashboard-card vocabulary-review">
        <div class="card-header">
            <h3><i class="fas fa-book"></i> Vocabulary Review</h3>
        </div>
        <div class="card-content">
            <div class="vocabulary-container">
                <div class="flip-card" id="vocabulary-card">
                    <div class="flip-card-inner">
                        <div class="flip-card-front">
                            <span class="vocabulary-word"><?php echo htmlspecialchars($vocabularyWords[0]['word']); ?></span>
                            <p class="vocabulary-hint">Click to flip</p>
                        </div>
                        <div class="flip-card-back">
                            <span class="vocabulary-translation"><?php echo htmlspecialchars($vocabularyWords[0]['translation']); ?></span>
                        </div>
                    </div>
                </div>
                <div class="vocabulary-navigation">
                    <button id="prev-word" class="vocabulary-nav-btn"><i class="fas fa-chevron-left"></i></button>
                    <span id="vocabulary-counter">1/<?php echo count($vocabularyWords); ?></span>
                    <button id="next-word" class="vocabulary-nav-btn"><i class="fas fa-chevron-right"></i></button>
                </div>
            </div>
        </div>
    </div>
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
                document.querySelector('.progress').style.width = '<?php echo $daily_progress; ?>%';
            }, 500);

            // Vocabulary card flip functionality
const vocabularyCard = document.getElementById('vocabulary-card');
if (vocabularyCard) {
    vocabularyCard.addEventListener('click', function() {
        this.classList.toggle('flipped');
    });
}

// Vocabulary navigation
const prevBtn = document.getElementById('prev-word');
const nextBtn = document.getElementById('next-word');
const counter = document.getElementById('vocabulary-counter');
let currentIndex = 0;
const vocabularyWords = <?php echo json_encode($vocabularyWords); ?>;
const totalWords = vocabularyWords.length;

// Update the card content when changing words
function updateVocabularyCard() {
    const word = vocabularyWords[currentIndex];
    const frontContent = vocabularyCard.querySelector('.flip-card-front .vocabulary-word');
    const backContent = vocabularyCard.querySelector('.flip-card-back .vocabulary-translation');
    
    // Update the content
    frontContent.textContent = word.word;
    backContent.textContent = word.translation;
    
    // Reset flip state when changing words
    vocabularyCard.classList.remove('flipped');
    
    // Update counter
    counter.textContent = `${currentIndex + 1}/${totalWords}`;
}

prevBtn.addEventListener('click', function(e) {
    e.stopPropagation(); // Prevent triggering the card flip
    currentIndex = (currentIndex - 1 + totalWords) % totalWords;
    updateVocabularyCard();
});

nextBtn.addEventListener('click', function(e) {
    e.stopPropagation(); // Prevent triggering the card flip
    currentIndex = (currentIndex + 1) % totalWords;
    updateVocabularyCard();
});

            // Add hover effect to practice options
            const practiceOptions = document.querySelectorAll('.practice-option');
            practiceOptions.forEach(option => {
                option.addEventListener('mouseenter', function() {
                    this.classList.add('practice-hover');
                });
                practiceOptions.forEach(option => {
                    option.addEventListener('mouseleave', function() {
                        this.classList.remove('practice-hover');
                    });
                });
                practiceOptions.forEach(option => {
                    option.addEventListener('click', function() {
                        // Add a ripple effect when clicked
                        const ripple = document.createElement('span');
                        ripple.classList.add('ripple');
                        this.appendChild(ripple);
                    
                        // Remove the ripple after animation completes
                        setTimeout(() => {
                            ripple.remove();
                        }, 600);
                    });
                });
            });
        });
    </script>
</body>
</html>
