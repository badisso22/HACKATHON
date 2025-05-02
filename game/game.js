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
const leaveButton = document.getElementById("leave-game")
const leaveModal = document.getElementById("leave-modal")
const confirmLeave = document.getElementById("confirm-leave")
const cancelLeave = document.getElementById("cancel-leave")

// Enemy data with updated theme and emojis
const bosses = [
  {
    name: "Novice Scholar",
    hp: 3,
    emoji: "🧙‍♂️", // Wizard emoji
    question: "Translate: 'Apple'",
    correct: "Manzana",
    options: ["Manzana", "Banana", "Pera"],
  },
  {
    name: "Language Master",
    hp: 4,
    emoji: "🐉", // Dragon emoji
    question: "Translate: 'Dog'",
    correct: "Perro",
    options: ["Perro", "Gato", "Caballo"],
  },
  {
    name: "Word Wizard",
    hp: 5,
    emoji: "🧛", // Vampire emoji
    question: "Translate: 'House'",
    correct: "Casa",
    options: ["Casa", "Carro", "Puerta"],
  },
  {
    name: "Linguistics Professor",
    hp: 5,
    emoji: "🧙‍♂️", // Wizard emoji
    question: "Translate: 'Book'",
    correct: "Libro",
    options: ["Libro", "Papel", "Lápiz"],
  },
]

// Player character emoji - Knight
const playerEmoji = "🤺" // Knight emoji

// Initialize the game
function initGame() {
  // Reset game state
  playerHP = 5
  enemyHP = 5
  level = 0
  score = 0

  // Set player character emoji - always knight
  document.getElementById("player-emoji").textContent = playerEmoji

  // Update hearts display
  updateHearts()

  // Load the first level
  loadLevel()

  // Set up event listeners for options
  setupOptionListeners()

  // Set up leave game functionality
  setupLeaveGame()

  // Set initial message
  logElement.textContent = "Challenge begins! Choose the correct translation."

  // Add initial animations
  document.querySelectorAll(".option").forEach((btn) => {
    btn.classList.add("magic-effect")
    setTimeout(() => btn.classList.remove("magic-effect"), 1000)
  })

  // Update progress
  updateProgress()
}

// Fix the leave game functionality to prevent HP loss when clicking "Stay"
function setupLeaveGame() {
  leaveButton.addEventListener("click", (e) => {
    e.preventDefault() // Prevent any default action
    leaveModal.style.display = "flex"
  })

  // Using anchor tags now, but still need to handle the cancel action
  cancelLeave.addEventListener("click", (e) => {
    e.preventDefault() // Prevent any default action
    leaveModal.style.display = "none"
  })

  // Close modal if clicked outside
  window.addEventListener("click", (event) => {
    if (event.target === leaveModal) {
      leaveModal.style.display = "none"
    }
  })
}

// Load current level
function loadLevel() {
  const boss = bosses[level]
  enemyHP = boss.hp

  // Update boss emoji and question
  document.getElementById("enemy-emoji").textContent = boss.emoji
  document.getElementById("question").textContent = boss.question

  // Add a small shake animation to the enemy when new level loads
  const enemyEmoji = document.getElementById("enemy-emoji")
  enemyEmoji.classList.add("magic-effect")
  setTimeout(() => enemyEmoji.classList.remove("magic-effect"), 1000)

  // Update options with shuffle
  const optionButtons = document.querySelectorAll("#options .option")
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
    const j = Math.floor(Math.random() * (i + 1))
    ;[array[i], array[j]] = [array[j], array[i]]
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

// Fix the boss progression issue by adding a cooldown
let isProcessingAnswer = false

// Completely separate the option button click handlers from the modal
function setupOptionListeners() {
  const optionButtons = document.querySelectorAll("#options .option")

  optionButtons.forEach((button) => {
    button.addEventListener("click", () => {
      // If already processing an answer, ignore the click
      if (isProcessingAnswer) return

      isProcessingAnswer = true

      const boss = bosses[level]
      const enemyEmoji = document.getElementById("enemy-emoji")
      const playerEmoji = document.getElementById("player-emoji")

      if (button.textContent === boss.correct) {
        // Correct answer
        enemyHP -= 1 // Enemy loses one heart
        score += 10 // Add points for correct answer

        logElement.textContent = "✅ Correct! Great job!"
        hitSound.play()
        enemyEmoji.classList.add("shake")
        playerEmoji.classList.add("attack")
        showFeedback(true, button)
      } else {
        // Wrong answer
        playerHP -= 1 // Player loses one heart

        logElement.textContent = `❌ Incorrect! The correct answer was "${boss.correct}".`
        damageSound.play()
        playerEmoji.classList.add("shake")
        enemyEmoji.classList.add("attack")
        showFeedback(false)
      }

      // Update hearts display
      updateHearts()

      // Check if game is over
      checkGameState()

      setTimeout(() => {
        enemyEmoji.classList.remove("shake", "attack")
        playerEmoji.classList.remove("shake", "attack")
        isProcessingAnswer = false // Reset the processing flag
      }, 1000)
    })
  })
}

// Update checkGameState to prevent skipping bosses
function checkGameState() {
  if (enemyHP <= 0) {
    isProcessingAnswer = true // Prevent further clicks
    score += 100 + playerHP * 20 // Score based on remaining player health

    setTimeout(() => {
      level++
      if (level < bosses.length) {
        logElement.textContent = `Victory! You earned ${score} points. Prepare for the next challenger!`

        // Add a delay before loading the next level
        setTimeout(() => {
          loadLevel()
          isProcessingAnswer = false // Allow clicks again
        }, 1500)
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
            <button class="option leave-option" onclick="window.location.href='index.html'">Exit Game</button>
          </div>
        `
        isProcessingAnswer = false // Allow clicks again
      }
    }, 1000)
  } else if (playerHP <= 0) {
    isProcessingAnswer = true // Prevent further clicks
    setTimeout(() => {
      gameScreen.innerHTML = `
        <h2>Challenge Failed</h2>
        <div class="parchment">
          <h3>Don't give up!</h3>
          <p>Learning takes patience and practice.</p>
          <p>Score: ${score} points</p>
          <p>"Step into the arena. Speak slowly, Learn deeply."</p>
          <button class="option" onclick="resetGame()">Try Again</button>
          <button class="option leave-option" onclick="window.location.href='index.html'">Exit Game</button>
        </div>
      `
      isProcessingAnswer = false // Allow clicks again
    }, 1000)
  }
}

// Reset the game
function resetGame() {
  // Reload the page to reset everything
  window.location.reload()
}

// Start the game when page loads
window.addEventListener("load", initGame)
