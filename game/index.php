<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mura Language Combat</title>
  <link rel="stylesheet" href="styles.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
</head>
<body>
  <audio id="hitSound" src="https://www.myinstants.com/media/sounds/smb3_kick.mp3"></audio>
  <audio id="damageSound" src="https://www.myinstants.com/media/sounds/hitmarker_2.mp3"></audio>
  
  <!-- Header -->
  <header>
    <div class="logo-title">
      <div class="logo">M</div>
      <h1>Mura — Language Combat</h1>
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
        <img id="player-img" class="character bounce" src="https://i.imgur.com/NLRi5Ob.png" alt="Player">
      </div>
      <div id="enemy" class="health">
        <div id="enemy-hearts" class="hearts">❤️❤️❤️❤️❤️</div>
        <img id="enemy-img" class="character bounce" src="https://i.imgur.com/3vc49fQ.png" alt="Boss">
      </div>
    </div>
    
    <div id="question-area">
      <div class="parchment">
        <p id="question">Translate: "Apple"</p>
        <div id="options">
          <button class="option">Manzana</button>
          <button class="option">Banana</button>
          <button class="option">Pera</button>
        </div>
      </div>
    </div>
    
    <div id="log"></div>
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

  <script src="game.js"></script>
</body>
</html>
