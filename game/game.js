// Game state variables
let playerHP = 5 // 5 hearts
let enemyHP = 5 // 5 hearts
let level = 0
let score = 0

// DOM elements
const hitSound = document.getElementById("hitSound")
const damageSound = document.getElementById("damageSound")
const progressBar = document.getElementById("progress-bar")
const gameScreen = document.getElementById("game")
const battleArea = document.getElementById("battle-area")
const playerHearts = document.getElementById("player-hearts")
const enemyHearts = document.getElementById("enemy-hearts")
const logElement = document.getElementById("log")

// Enemy data with updated theme
const bosses = [
  {
    name: "Novice Scholar",
    hp: 3,
    img: "https://i.imgur.com/3vc49fQ.png", // Purple scholar character
    question: "Translate: 'Apple'",
    correct: "Manzana",
    options: ["Manzana", "Banana", "Pera"],
  },
  {
    name: "Language Master",
    hp: 4,
    img: "https://i.imgur.com/GHXiQmr.png", // Mage character with purple theme
    question: "Translate: 'Dog'",
    correct: "Perro",
    options: ["Perro", "Gato", "Caballo"],
  },
  {
    name: "Word Wizard",
    hp: 5,
    img: "https://i.imgur.com/esfHP2L.png", // Wizard character with purple theme
    question: "Translate: 'House'",
    correct: "Casa",
    options: ["Casa", "Carro", "Puerta"],
  },
  {
    name: "Linguistics Professor",
    hp: 5,
    img: "https://i.imgur.com/LuLV8eL.png", // Professor character
    question: "Translate: 'Book'",
    correct: "Libro",
    options: ["Libro", "Papel", "Lápiz"],
  },
]

// Player characters
const playerCharacters = [
  "https://i.imgur.com/NLRi5Ob.png", // Student character with purple theme
]

// Initialize the game
function initGame() {
  // Reset game state
  playerHP = 5
  enemyHP = 5
  level = 0
  score = 0

  // Set player character
  document.getElementById("player-img").src = playerCharacters[0]

  // Update hearts display
  updateHearts()

  // Load the first level
  loadLevel()

  // Set up event listeners for options
  setupOptionListeners()

  // Set initial message
  logElement.textContent = "Challenge begins! Choose the correct translation."
  
  // Add initial animations
  document.querySelectorAll(".option").forEach(btn => {
    btn.classList.add("magic-effect")
    setTimeout(() => btn.classList.remove("magic-effect"), 1000)
  })

  // Update progress
  updateProgress()
}

// Load current level
function loadLevel() {
  const boss = bosses[level]
  enemyHP = boss.hp

  // Update boss image and question
  document.getElementById("enemy-img").src = boss.img
  document.getElementById("question").textContent = boss.question
  
  // Add a small shake animation to the enemy when new level loads
  const enemyImg = document.getElementById("enemy-img")
  enemyImg.classList.add("magic-effect")
  setTimeout(() => enemyImg.classList.remove("magic-effect"), 1000)

  // Update options with shuffle
  const optionButtons = document.querySelectorAll(".option")
  const shuffledOptions = shuffleArray([...boss.options])
  
  optionButtons.forEach((btn, i) => {
    btn.textContent = shuffledOptions[i]
    btn.classList.remove("correct-glow")
  })

  // Reset hearts display
  updateHearts()

  // Update progress
  updateProgress()
  
  // Show level info
  logElement.textContent = `Level ${level + 1}: Challenging the ${boss.name}!`
}

// Shuffle array (Fisher-Yates algorithm)
function shuffleArray(array) {
  for (let i = array.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [array[i], array[j]] = [array[j], array[i]]
  }
  return array
}

// Update hearts display
function updateHearts() {
  // Create hearts HTML
  let playerHeartsHTML = ""
  let enemyHeartsHTML = ""

  for (let i = 0; i < 5; i++) {
    if (i < playerHP) {
      playerHeartsHTML += "❤️"
    } else {
      playerHeartsHTML += '<span class="heart-lost">❤️</span>'
    }
  }

  for (let i = 0; i < bosses[level].hp; i++) {
    if (i < enemyHP) {
      enemyHeartsHTML += "❤️"
    } else {
      enemyHeartsHTML += '<span class="heart-lost">❤️</span>'
    }
  }

  // Update the DOM
  playerHearts.innerHTML = playerHeartsHTML
  enemyHearts.innerHTML = enemyHeartsHTML
}

// Update progress bar
function updateProgress() {
  const percent = (level / bosses.length) * 100
  progressBar.style.width = `${percent}%`
}

// Add visual feedback for correct/incorrect answers
function showFeedback(correct, button = null) {
  const parchment = document.querySelector(".parchment")
  
  if (correct) {
    parchment.classList.add("correct-glow")
    if (button) button.classList.add("correct-glow")
    setTimeout(() => {
      parchment.classList.remove("correct-glow")
      if (button) button.classList.remove("correct-glow")
    }, 1000)
  } else {
    battleArea.classList.add("shake")
    setTimeout(() => battleArea.classList.remove("shake"), 500)
  }
}

// Check game state after damage
function checkGameState() {
  if (enemyHP <= 0) {
    score += 100 + (playerHP * 20) // Score based on remaining player health
    
    setTimeout(() => {
      level++
      if (level < bosses.length) {
        logElement.textContent = `Victory! You earned ${score} points. Prepare for the next challenger!`
        loadLevel()
      } else {
        // Game completed
        gameScreen.innerHTML = `
          <h2>🏆 Challenge Complete!</h2>
          <div class="parchment">
            <h3>Congratulations!</h3>
            <p>You've mastered today's language challenge.</p>
            <p>Final Score: ${score} points</p>
            <p>Come back tomorrow for a new challenge!</p>
            <button class="option" onclick="resetGame()">Play Again</button>
          </div>
        `
      }
    }, 1000)
  } else if (playerHP <= 0) {
    setTimeout(() => {
      gameScreen.innerHTML = `
        <h2>Challenge Failed</h2>
        <div class="parchment">
          <h3>Don't give up!</h3>
          <p>Learning takes patience and practice.</p>
          <p>Score: ${score} points</p>
          <p>"Step into the arena. Speak slowly, Learn deeply."</p>
          <button class="option" onclick="resetGame()">Try Again</button>
        </div>
      `
    }, 1000)
  }
}

// Reset the game
function resetGame() {
  // Reload the page to reset everything
  window.location.reload()
}

// Set up event listeners for options
function setupOptionListeners() {
  document.querySelectorAll(".option").forEach((button) => {
    button.addEventListener("click", () => {
      const boss = bosses[level]
      const enemyImg = document.getElementById("enemy-img")
      const playerImg = document.getElementById("player-img")

      if (button.textContent === boss.correct) {
        // Correct answer
        enemyHP -= 1 // Enemy loses one heart
        score += 10 // Add points for correct answer
        
        logElement.textContent = "✅ Correct! Great job!"
        hitSound.play()
        enemyImg.classList.add("shake")
        playerImg.classList.add("attack")
        showFeedback(true, button)
      } else {
        // Wrong answer
        playerHP -= 1 // Player loses one heart
        
        logElement.textContent = `❌ Incorrect! The correct answer was "${boss.correct}".`
        damageSound.play()
        playerImg.classList.add("shake")
        enemyImg.classList.add("attack")
        showFeedback(false)
      }

      // Update hearts display
      updateHearts()

      // Check if game is over
      checkGameState()

      setTimeout(() => {
        enemyImg.classList.remove("shake", "attack")
        playerImg.classList.remove("shake", "attack")
      }, 500)
    })
  })
}

// Start the game when page loads
window.addEventListener("load", initGame)
