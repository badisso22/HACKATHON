<?php
require_once '../Configurations/db.php';
requireLogin(); // Ensure user is logged in

// Get user info
$userId = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Player';

// Get user's selected language
$sql = "SELECT selected_language FROM user_onboarding WHERE user_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$selectedLanguage = 'Spanish'; // Default

if ($row = $result->fetch_assoc()) {
    $selectedLanguage = $row['selected_language'];
}

// Get available languages
$availableLanguages = [];
$langSql = "SELECT DISTINCT language FROM game_bosses ORDER BY language";
$langResult = $conn->query($langSql);
while ($langRow = $langResult->fetch_assoc()) {
    $availableLanguages[] = $langRow['language'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mura Language Combat</title>
  <link rel="stylesheet" href="../css/styles.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
  <style>
    .language-selector {
      display: flex;
      align-items: center;
      margin-right: 20px;
    }
    
    .language-selector select {
      padding: 8px 12px;
      border-radius: 4px;
      border: 1px solid #ccc;
      background-color: #fff;
      font-family: 'Inter', sans-serif;
      margin-left: 10px;
    }
    
    .header-controls {
      display: flex;
      align-items: center;
    }
  </style>
</head>
<body>
  <audio id="hitSound" src="https://www.myinstants.com/media/sounds/smb3_kick.mp3" preload="auto"></audio>
  <audio id="damageSound" src="https://www.myinstants.com/media/sounds/hitmarker_2.mp3" preload="auto"></audio>
  
  <!-- Header -->
  <header>
    <div class="logo-title">
      <div class="logo">
        <img src="mura-logo.png" alt="Mura Logo" class="game-logo">
      </div>
      <h1>Mura — Language Combat</h1>
    </div>
    <div class="header-controls">
      <div class="language-selector">
        <label for="language-select">Language:</label>
        <select id="language-select">
          <?php foreach ($availableLanguages as $language): ?>
            <option value="<?php echo $language; ?>" <?php echo ($language === $selectedLanguage) ? 'selected' : ''; ?>>
              <?php echo $language; ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <button id="leave-game" class="leave-button">Leave Game</button>
    </div>
  </header>
  
  <!-- Game Screen -->
  <div id="game" class="game-screen">
    <h2>Daily Language Challenge</h2>
    <h4>— Speak slowly, Learn deeply —</h4>
    
    <div class="progress-container">
      <div id="progress-bar" class="progress-bar"></div>
    </div>
    
    <div id="battle-area">
      <div id="player" class="health">
        <div id="player-hearts" class="hearts">❤️❤️❤️❤️❤️</div>
        <div class="emoji-container">
          <div id="player-emoji" class="character bounce">🤺</div>
        </div>
      </div>
      <div id="enemy" class="health">
        <div id="enemy-hearts" class="hearts">❤️❤️❤️❤️❤️</div>
        <div class="emoji-container">
          <div id="enemy-emoji" class="character bounce">🧙‍♂️</div>
        </div>
      </div>
    </div>
    
    <div id="question-area">
      <div class="parchment">
        <p id="question">Loading question...</p>
        <div id="options">
          <button class="option">Loading...</button>
          <button class="option">Loading...</button>
          <button class="option">Loading...</button>
        </div>
      </div>
    </div>
    
    <div id="log"></div>
  </div>
  
  <!-- Leave Game Confirmation Modal -->
  <div id="leave-modal" class="modal">
    <div class="modal-content">
      <h3>Leave Game?</h3>
      <p>Are you sure you want to leave? Your progress will be lost.</p>
      <div class="modal-buttons">
        <!-- Updated to redirect to dashboard -->
        <a href="../Dashboard/dashboard.php" id="confirm-leave" class="modal-btn leave-btn">LEAVE</a>
        <a href="#" id="cancel-leave" class="modal-btn stay-btn">STAY</a>
      </div>
    </div>
  </div>
  
  <!-- Footer -->
  <footer>
    <div class="footer-content">
      <div class="footer-links">
        <a href="#">About Mura</a>
        <a href="#">How It Works</a>
        <a href="#">Languages</a>
        <a href="#">Support</a>
      </div>
      <div class="footer-copyright">
        © 2025 Mura Language Learning. All rights reserved.
      </div>
    </div>
  </footer>

  <!-- Pass user ID to JavaScript -->
  <script>
    const userId = <?php echo $userId; ?>;
    const username = "<?php echo $username; ?>";
    const currentLanguage = "<?php echo $selectedLanguage; ?>";
  </script>
  <script src="../js/game.js"></script>
  
  <!-- Add language selection script -->
  <script>
    document.getElementById('language-select').addEventListener('change', function() {
      const selectedLanguage = this.value;
      
      // Send AJAX request to change language
      const xhr = new XMLHttpRequest();
      xhr.open('POST', 'game-api.php?action=changeLanguage', true);
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
      xhr.onload = function() {
        if (xhr.status === 200) {
          const response = JSON.parse(xhr.responseText);
          if (response.success) {
            // Reload the page to start game with new language
            window.location.reload();
          }
        }
      };
      xhr.send('language=' + encodeURIComponent(selectedLanguage));
    });
  </script>
</body>
</html>
