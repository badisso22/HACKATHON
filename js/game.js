// Game state variables
let currentBoss = null
let currentQuestion = null
let playerHealth = 5
let bossHealth = 0
let score = 0
let level = 0
let maxLevel = 0
let bosses = []
let usedQuestions = []
let correctAnswers = 0
let totalQuestions = 0
let gameActive = true

// DOM elements
const playerHearts = document.getElementById("player-hearts")
const enemyHearts = document.getElementById("enemy-hearts")
const playerEmoji = document.getElementById("player-emoji")
const enemyEmoji = document.getElementById("enemy-emoji")
const questionElement = document.getElementById("question")
const optionsContainer = document.getElementById("options")
const progressBar = document.getElementById("progress-bar")
const logElement = document.getElementById("log")
const leaveButton = document.getElementById("leave-game")
const leaveModal = document.getElementById("leave-modal")
const confirmLeave = document.getElementById("confirm-leave")
const cancelLeave = document.getElementById("cancel-leave")
const hitSound = document.getElementById("hitSound")
const damageSound = document.getElementById("damageSound")

// Initialize the game
async function initGame() {
  try {
    // Get all bosses for the current language
    const response = await fetch(`game-api.php?action=getBosses`)
    const data = await response.json()

    if (data.success && data.bosses.length > 0) {
      bosses = data.bosses
      maxLevel = bosses.length - 1

      // Get user progress
      const progressResponse = await fetch(`game-api.php?action=getUserProgress`)
      const progressData = await progressResponse.json()

      if (progressData.success) {
        level = progressData.progress.current_level

        // Make sure level is valid
        if (level > maxLevel) {
          level = 0
        }
      }

      // Start the first level
      startLevel(level)
    } else {
      logMessage("No bosses found for this language. Please try another language.")
    }
  } catch (error) {
    console.error("Error initializing game:", error)
    logMessage("Error loading game. Please try again.")
  }
}

// Start a level
async function startLevel(levelIndex) {
  // Reset game state for new level
  playerHealth = 5
  usedQuestions = []
  correctAnswers = 0
  totalQuestions = 0

  // Update UI
  updateHealthDisplay()

  // Get the boss for this level
  currentBoss = bosses[levelIndex]
  bossHealth = currentBoss.hp

  // Update enemy emoji
  enemyEmoji.textContent = currentBoss.emoji

  // Update progress bar
  const progress = (levelIndex / maxLevel) * 100
  progressBar.style.width = `${progress}%`

  // Log message
  logMessage(`Level ${levelIndex + 1}: ${currentBoss.name} appears!`)

  // Get the first question
  await getNextQuestion()
}

// Get the next question
async function getNextQuestion() {
  try {
    const response = await fetch(
      `game-api.php?action=getNextQuestion&bossId=${currentBoss.id}&usedQuestions=${usedQuestions.join(",")}`,
    )
    const data = await response.json()

    if (data.success && data.question) {
      currentQuestion = data.question
      usedQuestions.push(currentQuestion.id)

      // Display the question
      questionElement.textContent = currentQuestion.question

      // Clear previous options
      optionsContainer.innerHTML = ""

      // Shuffle options
      const options = [...currentQuestion.options]
      shuffleArray(options)

      // Create option buttons
      options.forEach((option) => {
        const button = document.createElement("button")
        button.className = "option"
        button.textContent = option
        button.addEventListener("click", () => checkAnswer(option))
        optionsContainer.appendChild(button)
      })
    } else {
      logMessage("Error loading question. Please try again.")
    }
  } catch (error) {
    console.error("Error getting question:", error)
    logMessage("Error loading question. Please try again.")
  }
}

// Check if the answer is correct
function checkAnswer(answer) {
  if (!gameActive) return

  totalQuestions++

  if (answer === currentQuestion.correct) {
    // Correct answer
    correctAnswers++
    score += 10

    // Play hit sound
    hitSound.play()

    // Animate player attack
    playerEmoji.classList.add("attack")
    setTimeout(() => {
      playerEmoji.classList.remove("attack")
    }, 500)

    // Reduce boss health
    bossHealth--
    updateHealthDisplay()

    // Log message
    logMessage(`Correct! ${currentQuestion.correct} is right. Boss takes damage!`)

    // Check if boss is defeated
    if (bossHealth <= 0) {
      defeatBoss()
    } else {
      // Get next question
      getNextQuestion()
    }
  } else {
    // Incorrect answer

    // Play damage sound
    damageSound.play()

    // Animate enemy attack
    enemyEmoji.classList.add("attack")
    setTimeout(() => {
      enemyEmoji.classList.remove("attack")
    }, 500)

    // Reduce player health
    playerHealth--
    updateHealthDisplay()

    // Log message
    logMessage(`Wrong! The correct answer was ${currentQuestion.correct}. You take damage!`)

    // Check if player is defeated
    if (playerHealth <= 0) {
      gameOver()
    } else {
      // Get next question
      getNextQuestion()
    }
  }
}

// Update health display
function updateHealthDisplay() {
  // Update player hearts
  playerHearts.textContent = "❤️".repeat(playerHealth)

  // Update enemy hearts
  enemyHearts.textContent = "❤️".repeat(bossHealth)
}

// Boss defeated
async function defeatBoss() {
  logMessage(`You defeated ${currentBoss.name}!`)

  // Save progress
  await saveProgress(false)

  // Check if this is the last boss
  if (level >= maxLevel) {
    // Game completed
    gameCompleted()
  } else {
    // Move to next level
    level++
    startLevel(level)
  }
}

// Game over
async function gameOver() {
  gameActive = false
  logMessage("Game Over! You were defeated.")

  // Save progress with game over flag
  await saveProgress(false)

  // Disable options
  const options = document.querySelectorAll(".option")
  options.forEach((option) => {
    option.disabled = true
  })

  // Show restart button
  const restartButton = document.createElement("button")
  restartButton.textContent = "Try Again"
  restartButton.className = "restart-button"
  restartButton.addEventListener("click", () => {
    location.reload()
  })

  // Add restart button to question area
  const questionArea = document.getElementById("question-area")
  questionArea.appendChild(restartButton)
}

// Game completed
async function gameCompleted() {
  gameActive = false
  logMessage("Congratulations! You completed all levels!")

  // Save progress with completion flag
  await saveProgress(true)

  // Disable options
  const options = document.querySelectorAll(".option")
  options.forEach((option) => {
    option.disabled = true
  })

  // Show restart button
  const restartButton = document.createElement("button")
  restartButton.textContent = "Play Again"
  restartButton.className = "restart-button"
  restartButton.addEventListener("click", () => {
    location.reload()
  })

  // Add restart button to question area
  const questionArea = document.getElementById("question-area")
  questionArea.appendChild(restartButton)
}

// Save progress
async function saveProgress(completed) {
  try {
    const formData = new FormData()
    formData.append("level", level)
    formData.append("score", score)
    formData.append("completed", completed ? 1 : 0)
    formData.append("endGame", 1)
    formData.append("maxLevel", level)
    formData.append("correctAnswers", correctAnswers)
    formData.append("totalQuestions", totalQuestions)

    const response = await fetch("game-api.php?action=saveProgress", {
      method: "POST",
      body: formData,
    })

    const data = await response.json()

    if (data.success) {
      // Check for level up
      if (data.levelUp) {
        logMessage(`Level Up! You are now level ${data.newLevel}!`)
      }

      // Check for streak rewards
      if (data.streakUpdated) {
        logMessage(`Streak Bonus! ${data.currentStreak} day streak: +${data.streakReward} XP`)
      }
    }
  } catch (error) {
    console.error("Error saving progress:", error)
  }
}

// Log a message
function logMessage(message) {
  const logItem = document.createElement("div")
  logItem.className = "log-item"
  logItem.textContent = message
  logElement.prepend(logItem)

  // Limit log items
  const logItems = logElement.querySelectorAll(".log-item")
  if (logItems.length > 5) {
    logElement.removeChild(logItems[logItems.length - 1])
  }
}

// Shuffle array (Fisher-Yates algorithm)
function shuffleArray(array) {
  for (let i = array.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1))
    ;[array[i], array[j]] = [array[j], array[i]]
  }
  return array
}

// Leave game button
leaveButton.addEventListener("click", () => {
  leaveModal.style.display = "flex"
})

// Confirm leave
confirmLeave.addEventListener("click", async () => {
  // Save progress before leaving
  await saveProgress(false)
})

// Cancel leave
cancelLeave.addEventListener("click", () => {
  leaveModal.style.display = "none"
})

// Initialize the game when the page loads
window.addEventListener("load", initGame)
