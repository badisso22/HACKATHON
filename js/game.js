// Game state variables
let playerHP = 5 // 5 hearts
let enemyHP = 5 // 5 hearts
let level = 0
let score = 0
let bosses = [] // Will be loaded from the database
let currentBoss = null // Current boss object
let currentQuestion = null // Current question object
let userProgress = null // Will store user's progress
let userStats = null // Will store user's level and XP
let userStreak = null // Will store user's streak info
let usedQuestions = [] // Track used questions for current boss
let correctAnswers = 0 // Track correct answers in current session
let totalQuestions = 0 // Track total questions in current session

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

// Player character emoji - Knight
const playerEmoji = "🤺" // Knight emoji

// Initialize the game
async function initGame() {
  logElement.textContent = "Loading game data..."

  try {
    // Load bosses from the database
    await loadBosses()

    // Load user progress
    await loadUserProgress()

    // Load user stats
    await loadUserStats()

    // Load user streak
    await loadUserStreak()

    // Set level from user progress if available
    if (userProgress && userProgress.current_level < bosses.length) {
      level = userProgress.current_level
    } else {
      level = 0
    }

    // Reset game state
    playerHP = 5
    currentBoss = bosses[level]

    // Check if currentBoss exists before accessing its properties
    if (currentBoss) {
      enemyHP = currentBoss.hp

      // Set enemy emoji
      document.getElementById("enemy-emoji").textContent = currentBoss.emoji
    } else {
      console.error("No boss data available at level:", level)
      logElement.textContent = "Error: Could not load boss data. Please try again."
      return
    }

    score = 0
    usedQuestions = [] // Reset used questions
    correctAnswers = 0 // Reset correct answers counter
    totalQuestions = 0 // Reset total questions counter

    // Set player character emoji - always knight
    document.getElementById("player-emoji").textContent = playerEmoji

    // Update hearts display
    updateHearts()

    // Load the first question for the current boss
    await loadNextQuestion()

    // Set up event listeners for options
    setupOptionListeners()

    // Set up leave game functionality
    setupLeaveGame()

    // Display user level and streak info
    updateUserStatsDisplay()

    // Add initial animations
    document.querySelectorAll(".option").forEach((btn) => {
      btn.classList.add("magic-effect")
      setTimeout(() => btn.classList.remove("magic-effect"), 1000)
    })

    // Update progress
    updateProgress()
  } catch (error) {
    console.error("Error initializing game:", error)
    logElement.textContent = "Error loading game data. Please try again."
  }
}

// Load bosses from the database
async function loadBosses() {
  try {
    // Use the correct path to the API - use the current directory
    const response = await fetch("game-api.php?action=getBosses")

    // Log the response for debugging
    console.log("Bosses API response:", await response.clone().text())

    const data = await response.json()

    if (data.success && data.bosses && data.bosses.length > 0) {
      bosses = data.bosses
      console.log("Loaded bosses:", bosses)
    } else {
      throw new Error("Failed to load boss data")
    }
  } catch (error) {
    console.error("Error loading bosses:", error)
    throw error
  }
}

// Load user stats (level, XP)
async function loadUserStats() {
  try {
    const response = await fetch("game-api.php?action=getUserStats")
    const data = await response.json()

    if (data.success) {
      userStats = data.stats
    } else {
      throw new Error("Failed to load user stats")
    }
  } catch (error) {
    console.error("Error loading user stats:", error)
    // Don't throw error, just continue with null stats
    userStats = null
  }
}

// Load user streak information
async function loadUserStreak() {
  try {
    const response = await fetch("game-api.php?action=getUserStreak")
    const data = await response.json()

    if (data.success) {
      userStreak = data.streak
    } else {
      throw new Error("Failed to load user streak")
    }
  } catch (error) {
    console.error("Error loading user streak:", error)
    // Don't throw error, just continue with null streak
    userStreak = null
  }
}

// Update user stats display
function updateUserStatsDisplay() {
  // Check if the stats elements exist
  const statsContainer = document.getElementById("user-stats")
  if (!statsContainer) return

  // Update level and XP
  const levelElement = document.getElementById("user-level")
  const xpElement = document.getElementById("user-xp")
  const xpBarElement = document.getElementById("xp-bar")

  if (levelElement && userStats) {
    levelElement.textContent = `Level ${userStats.level}`
  }

  if (xpElement && userStats) {
    xpElement.textContent = `XP: ${userStats.xp}/${userStats.xpForNextLevel}`
  }

  if (xpBarElement && userStats) {
    xpBarElement.style.width = `${userStats.xpProgress}%`
  }

  // Update streak
  const streakElement = document.getElementById("user-streak")
  if (streakElement && userStreak) {
    streakElement.textContent = `🔥 ${userStreak.currentStreak} day streak`

    // Hide streak if it's 0
    if (userStreak.currentStreak === 0) {
      streakElement.style.display = "none"
    } else {
      streakElement.style.display = "block"
    }
  }
}

// Load the next question for the current boss
async function loadNextQuestion() {
  try {
    // Check if currentBoss exists
    if (!currentBoss) {
      logElement.textContent = "Error: No boss data available."
      return
    }

    // Get the current boss ID
    const bossId = currentBoss.id

    // Get a new question for this boss
    const response = await fetch(
      `game-api.php?action=getNextQuestion&bossId=${bossId}&usedQuestions=${usedQuestions.join(",")}`,
    )

    // Log the response for debugging
    console.log("Question API response:", await response.clone().text())

    const data = await response.json()

    if (data.success && data.question) {
      currentQuestion = data.question

      // Add this question to used questions
      usedQuestions.push(currentQuestion.id)

      // Update the question and options in the UI
      document.getElementById("question").textContent = currentQuestion.question

      // Get option buttons
      const optionButtons = document.querySelectorAll("#options .option")

      // Check if we have enough option buttons
      if (currentQuestion.options.length > optionButtons.length) {
        // Need to add more option buttons
        const optionsContainer = document.getElementById("options")
        for (let i = optionButtons.length; i < currentQuestion.options.length; i++) {
          const newButton = document.createElement("button")
          newButton.className = "option"
          optionsContainer.appendChild(newButton)
        }
      }

      // Get updated option buttons
      const updatedOptionButtons = document.querySelectorAll("#options .option")

      // Update options with shuffle
      const shuffledOptions = shuffleArray([...currentQuestion.options])

      updatedOptionButtons.forEach((btn, i) => {
        if (i < shuffledOptions.length) {
          btn.textContent = shuffledOptions[i]
          btn.classList.remove("correct-glow")
          btn.style.display = "block" // Make sure button is visible
        } else {
          btn.style.display = "none" // Hide extra buttons
        }
      })

      // Show level info
      logElement.textContent = `Level ${level + 1}: Challenging the ${currentBoss.name}!`
    } else {
      throw new Error("Failed to load question data")
    }
  } catch (error) {
    console.error("Error loading question:", error)
    logElement.textContent = "Error loading question. Please try again."
  }
}

// Load user progress from the database
async function loadUserProgress() {
  try {
    const response = await fetch("game-api.php?action=getUserProgress")
    const data = await response.json()

    if (data.success) {
      userProgress = data.progress
    } else {
      throw new Error("Failed to load user progress")
    }
  } catch (error) {
    console.error("Error loading user progress:", error)
    throw error
  }
}

// Save user progress to the database
async function saveProgress(isEndGame = false, isCompleted = false) {
  try {
    const formData = new FormData()
    formData.append("level", level)
    formData.append("score", score)

    if (isEndGame) {
      formData.append("endGame", 1)
      formData.append("maxLevel", level)
      formData.append("completed", isCompleted ? 1 : 0)
      formData.append("correctAnswers", correctAnswers)
      formData.append("totalQuestions", totalQuestions)
    }

    const response = await fetch("game-api.php?action=saveProgress", {
      method: "POST",
      body: formData,
    })

    const data = await response.json()

    // If this is the end of the game, check for level up and streak updates
    if (isEndGame && data.success) {
      if (data.levelUp) {
        showLevelUpNotification(data.newLevel)
      }

      if (data.streakUpdated && data.streakReward > 0) {
        showStreakRewardNotification(data.currentStreak, data.streakReward)
      }
    }

    return data.success
  } catch (error) {
    console.error("Error saving progress:", error)
    return false
  }
}

// Show level up notification
function showLevelUpNotification(newLevel) {
  // Create notification element
  const notification = document.createElement("div")
  notification.className = "level-up-notification"
  notification.innerHTML = `
    <div class="level-up-content">
      <h3>🎉 Level Up! 🎉</h3>
      <p>You've reached level ${newLevel}!</p>
    </div>
  `

  // Add to document
  document.body.appendChild(notification)

  // Remove after animation
  setTimeout(() => {
    notification.classList.add("show")

    setTimeout(() => {
      notification.classList.remove("show")
      setTimeout(() => notification.remove(), 500)
    }, 3000)
  }, 100)
}

// Show streak reward notification
function showStreakRewardNotification(streak, reward) {
  // Create notification element
  const notification = document.createElement("div")
  notification.className = "streak-notification"
  notification.innerHTML = `
    <div class="streak-content">
      <h3>🔥 ${streak} Day Streak! 🔥</h3>
      <p>You earned ${reward} bonus XP!</p>
    </div>
  `

  // Add to document
  document.body.appendChild(notification)

  // Remove after animation
  setTimeout(() => {
    notification.classList.add("show")

    setTimeout(() => {
      notification.classList.remove("show")
      setTimeout(() => notification.remove(), 500)
    }, 3000)
  }, 100)
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

  // Save progress before leaving
  confirmLeave.addEventListener("click", async (e) => {
    // Save progress before leaving
    await saveProgress(true, false)
  })

  // Close modal if clicked outside
  window.addEventListener("click", (event) => {
    if (event.target === leaveModal) {
      leaveModal.style.display = "none"
    }
  })
}

// Load a new level
async function loadLevel() {
  if (level >= bosses.length) {
    logElement.textContent = "Error: No more bosses available."
    return
  }

  currentBoss = bosses[level]
  enemyHP = currentBoss.hp
  usedQuestions = [] // Reset used questions for the new boss

  // Update boss emoji
  document.getElementById("enemy-emoji").textContent = currentBoss.emoji

  // Add a small shake animation to the enemy when new level loads
  const enemyEmoji = document.getElementById("enemy-emoji")
  enemyEmoji.classList.add("magic-effect")
  setTimeout(() => enemyEmoji.classList.remove("magic-effect"), 1000)

  // Load the first question for this boss
  await loadNextQuestion()

  // Reset hearts display
  updateHearts()

  // Update progress
  updateProgress()

  // Save progress when loading a new level
  saveProgress()
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
  // Check if currentBoss exists
  if (!currentBoss) {
    console.error("No boss data available for updating hearts")
    return
  }

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

  for (let i = 0; i < currentBoss.hp; i++) {
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
    button.addEventListener("click", async () => {
      // If already processing an answer, ignore the click
      if (isProcessingAnswer) return

      // Check if currentQuestion exists
      if (!currentQuestion) {
        logElement.textContent = "Error: No question data available."
        return
      }

      isProcessingAnswer = true
      totalQuestions++ // Increment total questions counter

      const enemyEmoji = document.getElementById("enemy-emoji")
      const playerEmoji = document.getElementById("player-emoji")

      if (button.textContent === currentQuestion.correct) {
        // Correct answer
        enemyHP -= 1 // Enemy loses one heart
        score += 10 // Add points for correct answer
        correctAnswers++ // Increment correct answers counter

        logElement.textContent = "✅ Correct! Great job!"
        hitSound.play()
        enemyEmoji.classList.add("shake")
        playerEmoji.classList.add("attack")
        showFeedback(true, button)

        // Load a new question after a correct answer
        setTimeout(async () => {
          if (enemyHP > 0) {
            // Only load new question if boss isn't defeated
            await loadNextQuestion()
          }
        }, 1000)
      } else {
        // Wrong answer
        playerHP -= 1 // Player loses one heart

        logElement.textContent = `❌ Incorrect! The correct answer was "${currentQuestion.correct}".`
        damageSound.play()
        playerEmoji.classList.add("shake")
        enemyEmoji.classList.add("attack")
        showFeedback(false)

        // Load a new question after an incorrect answer
        setTimeout(async () => {
          if (playerHP > 0) {
            // Only load new question if player isn't defeated
            await loadNextQuestion()
          }
        }, 1000)
      }

      // Update hearts display
      updateHearts()

      // Check if game is over
      await checkGameState()

      setTimeout(() => {
        enemyEmoji.classList.remove("shake", "attack")
        playerEmoji.classList.remove("shake", "attack")
        isProcessingAnswer = false // Reset the processing flag
      }, 1000)
    })
  })
}

// Update checkGameState to prevent skipping bosses
async function checkGameState() {
  if (enemyHP <= 0) {
    isProcessingAnswer = true // Prevent further clicks
    score += 100 + playerHP * 20 // Score based on remaining player health

    setTimeout(async () => {
      level++
      if (level < bosses.length) {
        logElement.textContent = `Victory! You earned ${score} points. Prepare for the next challenger!`

        // Save progress after defeating a boss
        await saveProgress()

        // Add a delay before loading the next level
        setTimeout(async () => {
          await loadLevel()
          isProcessingAnswer = false // Allow clicks again
        }, 1500)
      } else {
        // Game completed - save final progress
        await saveProgress(true, true)

        // Game completed
        gameScreen.innerHTML = `
          <h2>🏆 Challenge Complete!</h2>
          <div class="parchment">
            <h3>Congratulations!</h3>
            <p>You've mastered today's language challenge.</p>
            <p>Final Score: ${score} points</p>
            <p>Accuracy: ${Math.round((correctAnswers / totalQuestions) * 100)}% (${correctAnswers}/${totalQuestions})</p>
            <p>Come back tomorrow for a new challenge!</p>
            <button class="option" onclick="resetGame()">Play Again</button>
            <button class="option leave-option" onclick="window.location.href='../Dashboard/dashboard.php'">Return to Dashboard</button>
          </div>
        `
        isProcessingAnswer = false // Allow clicks again
      }
    }, 1000)
  } else if (playerHP <= 0) {
    isProcessingAnswer = true // Prevent further clicks

    // Save progress when game is failed
    await saveProgress(true, false)

    setTimeout(() => {
      gameScreen.innerHTML = `
        <h2>Challenge Failed</h2>
        <div class="parchment">
          <h3>Don't give up!</h3>
          <p>Learning takes patience and practice.</p>
          <p>Score: ${score} points</p>
          <p>Accuracy: ${Math.round((correctAnswers / totalQuestions) * 100)}% (${correctAnswers}/${totalQuestions})</p>
          <p>"Step into the arena. Speak slowly, Learn deeply."</p>
          <button class="option" onclick="resetGame()">Try Again</button>
          <button class="option leave-option" onclick="window.location.href='../Dashboard/dashboard.php'">Return to Dashboard</button>
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

// Add console logging for debugging
console.log("Game script loaded")

// Start the game when page loads
window.addEventListener("load", () => {
  console.log("Window loaded, initializing game...")
  initGame()
})
