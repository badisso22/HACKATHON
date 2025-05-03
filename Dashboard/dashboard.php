<?php
session_start();
require_once '../Configurations/db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/signin.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT u.username, u.onboarding_complete, uo.selected_language, uo.daily_goal, uo.proficiency_level,
           l.first_name, l.last_name, COALESCE(us.level, 1) as user_level, COALESCE(ustreak.current_streak, 0) as current_streak
    FROM users u 
    LEFT JOIN user_onboarding uo ON u.user_ID = uo.user_ID 
    LEFT JOIN learner l ON u.user_ID = l.user_ID
    LEFT JOIN user_stats us ON u.user_ID = us.user_id
    LEFT JOIN user_streaks ustreak ON u.user_ID = ustreak.user_id
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

if (!$user['onboarding_complete']) {
    header("Location: check_onboarding.php");
    exit();
}

$username = $user['username'] ?? 'User';
$first_name = $user['first_name'] ?? 'mehdi';
$selected_language = $user['selected_language'] ?? 'Spanish';
$daily_goal = $user['daily_goal'] ?? '3 min';
$proficiency_level = $user['proficiency_level'] ?? 'Beginner';

$daily_progress = 35; // You might want to calculate this dynamically based on user activity
$user_level = $user['user_level'] ?? 1;
$current_streak = $user['current_streak'] ?? 0;

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
$hour = date('H');
if ($hour < 12) {
    $greeting = "Good morning";
} elseif ($hour < 18) {
    $greeting = "Good afternoon";
} else {
    $greeting = "Good evening";
}

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
function getRandomVocabularyWords($language, $count = 5) {
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
          ['word' => 'Au revoir', 'translation' => 'Goodbye'],
          ['word' => 'Oui', 'translation' => 'Yes'],
          ['word' => 'Non', 'translation' => 'No'],
          ['word' => 'Excusez-moi', 'translation' => 'Excuse me'],
          ['word' => 'Je suis désolé', 'translation' => 'I\'m sorry'],
          ['word' => 'Eau', 'translation' => 'Water'],
          ['word' => 'Nourriture', 'translation' => 'Food'],
          ['word' => 'Maison', 'translation' => 'House'],
          ['word' => 'Famille', 'translation' => 'Family'],
          ['word' => 'Bon matin', 'translation' => 'Good morning'],
          ['word' => 'Bonne nuit', 'translation' => 'Good night']
      ],
      'German' => [
          ['word' => 'Hallo', 'translation' => 'Hello'],
          ['word' => 'Danke', 'translation' => 'Thank you'],
          ['word' => 'Bitte', 'translation' => 'Please'],
          ['word' => 'Freund', 'translation' => 'Friend'],
          ['word' => 'Guten Morgen', 'translation' => 'Good morning'],
          ['word' => 'Gute Nacht', 'translation' => 'Good night'],
          ['word' => 'Auf Wiedersehen', 'translation' => 'Goodbye'],
          ['word' => 'Ja', 'translation' => 'Yes'],
          ['word' => 'Nein', 'translation' => 'No'],
          ['word' => 'Entschuldigung', 'translation' => 'Excuse me'],
          ['word' => 'Es tut mir leid', 'translation' => 'I\'m sorry'],
          ['word' => 'Wasser', 'translation' => 'Water'],
          ['word' => 'Essen', 'translation' => 'Food'],
          ['word' => 'Haus', 'translation' => 'House'],
          ['word' => 'Familie', 'translation' => 'Family']
      ],
      'Italian' => [
          ['word' => 'Ciao', 'translation' => 'Hello'],
          ['word' => 'Grazie', 'translation' => 'Thank you'],
          ['word' => 'Per favore', 'translation' => 'Please'],
          ['word' => 'Amico', 'translation' => 'Friend'],
          ['word' => 'Buongiorno', 'translation' => 'Good morning'],
          ['word' => 'Buonanotte', 'translation' => 'Good night'],
          ['word' => 'Arrivederci', 'translation' => 'Goodbye'],
          ['word' => 'Sì', 'translation' => 'Yes'],
          ['word' => 'No', 'translation' => 'No'],
          ['word' => 'Scusa', 'translation' => 'Excuse me'],
          ['word' => 'Mi dispiace', 'translation' => 'I\'m sorry'],
          ['word' => 'Acqua', 'translation' => 'Water'],
          ['word' => 'Cibo', 'translation' => 'Food'],
          ['word' => 'Casa', 'translation' => 'House'],
          ['word' => 'Famiglia', 'translation' => 'Family']
      ]
  ];
    $wordsForLanguage = $allWords[$language] ?? $allWords['Spanish'];
    shuffle($wordsForLanguage);
    
    return array_slice($wordsForLanguage, 0, $count);
}
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

.welcome-section {
  margin-bottom: 30px;
}

.welcome-section h2 {
  font-size: 24px;
  color: #5a3b5d;
  margin-bottom: 10px;
}

.daily-goal-section {
  background-color: #fff;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  margin-bottom: 30px;
}

.daily-goal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.daily-goal-section h3 {
  font-size: 18px;
  color: #333;
  margin: 0;
}

.daily-goal-info {
  font-size: 14px;
  color: #666;
}

.progress-bar {
  height: 10px;
  background-color: #e0e0e0;
  border-radius: 5px;
  overflow: hidden;
  margin-bottom: 20px;
}

.progress {
  height: 100%;
  background: linear-gradient(90deg, #7e57c2, #b39ddb);
  border-radius: 5px;
  transition: width 1.5s ease-in-out;
  width: 0;
}

.continue-btn {
  background-color: #5a3b5d;
  color: #fff;
  border: none;
  padding: 12px 25px;
  border-radius: 5px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s;
}

.continue-btn:hover {
  background-color: #7e57c2;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.pulse-animation {
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(90, 59, 93, 0.4);
  }
  70% {
    box-shadow: 0 0 0 10px rgba(90, 59, 93, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(90, 59, 93, 0);
  }
}

.dashboard-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 30px;
}

.dashboard-card {
  background-color: #fff;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  overflow: hidden;
  height: 100%;
}

.card-header {
  padding: 15px 20px;
  border-bottom: 1px solid #eee;
  background-color: rgba(90, 59, 93, 0.05);
}

.card-header h3 {
  font-size: 16px;
  color: #5a3b5d;
  margin: 0;
  display: flex;
  align-items: center;
}

.card-header h3 i {
  margin-right: 8px;
}

.card-content {
  padding: 20px;
}

.lesson-list {
  list-style: none;
}

.lesson-item {
  display: flex;
  align-items: center;
  padding: 15px 0;
  border-bottom: 1px solid #eee;
}

.lesson-item:last-child {
  border-bottom: none;
}

.lesson-icon {
  width: 40px;
  height: 40px;
  background-color: rgba(90, 59, 93, 0.1);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  margin-right: 15px;
  flex-shrink: 0;
}

.lesson-details {
  flex: 1;
}

.lesson-details h4 {
  font-size: 16px;
  margin-bottom: 5px;
  color: #333;
}

.lesson-details p {
  font-size: 14px;
  color: #666;
  margin-bottom: 5px;
}

.lesson-duration {
  font-size: 12px;
  color: #888;
  display: flex;
  align-items: center;
}

.lesson-duration i {
  margin-right: 5px;
}

.lesson-start-btn {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background-color: #5a3b5d;
  color: #fff;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s;
}

.lesson-start-btn:hover {
  background-color: #7e57c2;
  transform: scale(1.1);
}

.vocabulary-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 100%;
}

/* Fix for flip-card flipping */
.flip-card {
  background-color: transparent;
  width: 100%;
  height: 220px;
  perspective: 1000px; /* Ensure perspective is applied */
  margin-bottom: 20px;
  cursor: pointer;
}

.flip-card-inner {
  position: relative;
  width: 100%;
  height: 100%;
  text-align: center;
  transition: transform 0.6s; /* Smooth transition */
  transform-style: preserve-3d; /* Enable 3D transform */
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
  border-radius: 15px;
}

.flip-card.flipped .flip-card-inner {
  transform: rotateY(180deg); /* Flip the card */
}

.flip-card-front,
.flip-card-back {
  position: absolute;
  width: 100%;
  height: 100%;
  backface-visibility: hidden; /* Hide back side when not flipped */
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  border-radius: 15px;
  padding: 20px;
}

.flip-card-front {
  background: linear-gradient(145deg, #f9f4ff, #f0e6ff);
  border: 1px solid #e6d9ff;
}

.flip-card-back {
  background: linear-gradient(145deg, #5a3b5d, #7e57c2);
  color: white;
  transform: rotateY(180deg); /* Ensure back is rotated */
}

.vocabulary-word {
  font-size: 32px;
  font-weight: bold;
  color: #5a3b5d;
  margin-bottom: 15px;
  text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
  position: relative;
}

.vocabulary-word::after {
  content: "";
  position: absolute;
  bottom: -8px;
  left: 50%;
  transform: translateX(-50%);
  width: 40px;
  height: 2px;
  background-color: #b39ddb;
}

.vocabulary-translation {
  font-size: 28px;
  font-weight: bold;
  text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
  letter-spacing: 0.5px;
}

.vocabulary-hint {
  font-size: 12px;
  color: #888;
  margin-top: 20px;
  position: absolute;
  bottom: 15px;
  left: 0;
  right: 0;
  opacity: 0.7;
}

.vocabulary-navigation {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: 10px;
}

.vocabulary-nav-btn {
  background: none;
  border: none;
  font-size: 16px;
  color: #5a3b5d;
  cursor: pointer;
  padding: 8px 12px;
  transition: all 0.3s;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.vocabulary-nav-btn:hover {
  color: #7e57c2;
  background-color: rgba(126, 87, 194, 0.1);
}

#vocabulary-counter {
  margin: 0 15px;
  font-size: 14px;
  color: #666;
  font-weight: 500;
  min-width: 40px;
  text-align: center;
}

.quick-practice-section {
  background-color: #fff;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.quick-practice-section h3 {
  font-size: 18px;
  color: #333;
  margin-bottom: 20px;
}

.practice-options {
  display: flex;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 15px;
}

.practice-option {
  flex: 1;
  min-width: 120px;
  background-color: #f9f4ff;
  border-radius: 10px;
  padding: 15px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s;
  position: relative;
  overflow: hidden;
}

.practice-option:hover {
  transform: translateY(-5px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.practice-hover {
  background-color: #efe5ff;
}

.practice-icon {
  width: 50px;
  height: 50px;
  background-color: rgba(90, 59, 93, 0.1);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 10px;
  font-size: 20px;
  color: #5a3b5d;
  transition: all 0.3s;
}

.practice-option:hover .practice-icon {
  background-color: #5a3b5d;
  color: white;
  transform: scale(1.1);
}

.practice-option span {
  font-size: 14px;
  font-weight: 500;
  color: #333;
}

.ripple {
  position: absolute;
  background: rgba(255, 255, 255, 0.7);
  border-radius: 50%;
  transform: scale(0);
  animation: ripple 0.6s linear;
  pointer-events: none;
}

@keyframes ripple {
  to {
    transform: scale(4);
    opacity: 0;
  }
}

@media (max-width: 992px) {
  .dashboard-grid {
    grid-template-columns: 1fr;
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

  .logo-container {
    flex-direction: column;
  }

  .mura-logo {
    margin-right: 0;
    margin-bottom: 5px;
  }

  .practice-options {
    flex-wrap: wrap;
  }

  .practice-option {
    min-width: calc(50% - 10px);
    margin-bottom: 15px;
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

  .welcome-section h2 {
    font-size: 20px;
  }

  .daily-goal-section,
  .dashboard-card,
  .quick-practice-section {
    padding: 15px;
  }

  .practice-option {
    min-width: 100%;
  }
}
</style>
    
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
                        <a href="translation.php">
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
                                    <a href="#" class="lesson-link"> 
                                        <button class="lesson-start-btn"><i class="fas fa-play"></i></button>
                                    </a>
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
            const menuToggle = document.querySelector('.menu-toggle');
            const sidebar = document.querySelector('.sidebar');
            
            if (menuToggle) {
                menuToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                });
            }
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

function updateVocabularyCard() {
    const word = vocabularyWords[currentIndex];
    const frontContent = vocabularyCard.querySelector('.flip-card-front .vocabulary-word');
    const backContent = vocabularyCard.querySelector('.flip-card-back .vocabulary-translation');
    
    frontContent.textContent = word.word;
    backContent.textContent = word.translation;
    
    vocabularyCard.classList.remove('flipped');
    
    counter.textContent = `${currentIndex + 1}/${totalWords}`;
}

prevBtn.addEventListener('click', function(e) {
    e.stopPropagation();
    currentIndex = (currentIndex - 1 + totalWords) % totalWords;
    updateVocabularyCard();
});

nextBtn.addEventListener('click', function(e) {
    e.stopPropagation(); 
    currentIndex = (currentIndex + 1) % totalWords;
    updateVocabularyCard();
});
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
                        const ripple = document.createElement('span');
                        ripple.classList.add('ripple');
                        this.appendChild(ripple);
                    
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
