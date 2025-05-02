document.addEventListener("DOMContentLoaded", () => {
    // Mobile sidebar toggle
    const menuToggle = document.querySelector(".menu-toggle")
    const sidebar = document.querySelector(".sidebar")
  
    if (menuToggle) {
      menuToggle.addEventListener("click", () => {
        sidebar.classList.toggle("active")
      })
    }
  
    // Navigation active state
    const navItems = document.querySelectorAll(".nav-item")
  
    navItems.forEach((item) => {
      item.addEventListener("click", function () {
        // Remove active class from all items
        navItems.forEach((nav) => nav.classList.remove("active"))
  
        // Add active class to clicked item
        this.classList.add("active")
      })
    })
  
    // Enhanced fire animation
    const fireEmoji = document.querySelector(".fire-emoji")
  
    // Random flicker effect for the fire
    function randomFlicker() {
      const randomScale = 0.9 + Math.random() * 0.2
      const randomRotate = -5 + Math.random() * 10
      const randomShadowBlur = 5 + Math.random() * 5
  
      fireEmoji.style.transform = `scale(${randomScale}) rotate(${randomRotate}deg)`
      fireEmoji.style.textShadow = `0 0 ${randomShadowBlur}px rgba(255, 100, 0, 0.7), 0 0 ${randomShadowBlur * 2}px rgba(255, 200, 0, 0.4)`
  
      setTimeout(randomFlicker, 100 + Math.random() * 200)
    }
  
    // Start the fire animation
    if (fireEmoji) {
      randomFlicker()
    }
  
    // Streak counter and level badge
    const streakNumber = document.querySelector(".streak-number")
    const levelBadge = document.querySelector(".level-badge")
    const levelNumber = document.querySelector(".level-number")
    const languageIndicator = document.querySelector(".language-indicator")
    const languageName = document.querySelector(".language-name")
    const flagImage = document.querySelector(".flag-image")
  
    let currentStreak = Number.parseInt(streakNumber.textContent)
    let currentLevel = Number.parseInt(levelNumber.textContent)
  
    // Language data - mapping language names to flag images
    const languages = {
      Spanish: "flag.jpeg",
      French: "flag-fr.jpeg",
      German: "flag-de.jpeg",
      Italian: "flag-it.jpeg",
      Portuguese: "flag-pt.jpeg",
      Japanese: "flag-jp.jpeg",
      Chinese: "flag-cn.jpeg",
      Korean: "flag-kr.jpeg",
      Russian: "flag-ru.jpeg",
      Arabic: "flag-ar.jpeg",
    }
  
    // Function to change language
    function changeLanguage(language) {
      if (languages[language]) {
        languageName.textContent = language
  
        // Add a subtle transition effect
        flagImage.style.opacity = "0"
        flagImage.style.transform = "scale(0.8)"
  
        setTimeout(() => {
          // Update the flag image source
          flagImage.src = languages[language]
  
          // Fade back in
          flagImage.style.opacity = "1"
          flagImage.style.transform = "scale(1)"
        }, 300)
  
        // Add highlight effect
        languageIndicator.classList.add("highlight")
        setTimeout(() => {
          languageIndicator.classList.remove("highlight")
        }, 1000)
  
        // Show notification
        showNotification(`Learning language changed to ${language}`)
      }
    }
  
    // Add click event to language indicator to demo language change
    if (languageIndicator) {
      languageIndicator.addEventListener("click", () => {
        // Get random language from the languages object
        const languageNames = Object.keys(languages)
        const randomLanguage = languageNames[Math.floor(Math.random() * languageNames.length)]
        changeLanguage(randomLanguage)
      })
    }
  
    // For demo purposes - update streak every 30 seconds
    function updateStreakDemo() {
      currentStreak++
      streakNumber.textContent = currentStreak
  
      // Add a pulse effect when streak updates
      streakNumber.classList.add("pulse")
      setTimeout(() => {
        streakNumber.classList.remove("pulse")
      }, 1000)
  
      // Level up every 5 streaks (for demo)
      if (currentStreak % 5 === 0) {
        levelUp()
      }
    }
  
    // Level up animation
    function levelUp() {
      currentLevel++
      levelNumber.textContent = currentLevel
  
      // Add level up animation
      levelBadge.classList.add("level-up")
      setTimeout(() => {
        levelBadge.classList.remove("level-up")
      }, 1000)
  
      // Show level up notification
      showNotification(`Level up! You're now level ${currentLevel}`)
    }
  
    // Simple notification function
    function showNotification(message) {
      // Create notification element
      const notification = document.createElement("div")
      notification.className = "notification"
      notification.textContent = message
  
      // Style the notification
      notification.style.position = "fixed"
      notification.style.bottom = "20px"
      notification.style.right = "20px"
      notification.style.backgroundColor = "#5a3b5d"
      notification.style.color = "white"
      notification.style.padding = "10px 20px"
      notification.style.borderRadius = "5px"
      notification.style.boxShadow = "0 3px 10px rgba(0,0,0,0.2)"
      notification.style.zIndex = "1000"
      notification.style.opacity = "0"
      notification.style.transform = "translateY(20px)"
      notification.style.transition = "all 0.3s ease"
  
      // Add to document
      document.body.appendChild(notification)
  
      // Show notification
      setTimeout(() => {
        notification.style.opacity = "1"
        notification.style.transform = "translateY(0)"
      }, 10)
  
      // Remove after 3 seconds
      setTimeout(() => {
        notification.style.opacity = "0"
        notification.style.transform = "translateY(20px)"
  
        setTimeout(() => {
          document.body.removeChild(notification)
        }, 300)
      }, 3000)
    }
  
    // Add transition styles for flag image
    const styleFlag = document.createElement("style")
    styleFlag.textContent = `
      .flag-image {
        transition: opacity 0.3s ease, transform 0.3s ease;
      }
    `
    document.head.appendChild(styleFlag)
  
    // Add highlight animation for language change
    const styleHighlight = document.createElement("style")
    styleHighlight.textContent = `
      @keyframes highlight {
        0% { box-shadow: 0 0 5px rgba(126, 87, 194, 0.3); }
        50% { box-shadow: 0 0 15px rgba(126, 87, 194, 0.8); }
        100% { box-shadow: 0 0 5px rgba(126, 87, 194, 0.3); }
      }
      .highlight {
        animation: highlight 1s;
      }
    `
    document.head.appendChild(styleHighlight)
  
    // Add pulse animation
    const stylePulse = document.createElement("style")
    stylePulse.textContent = `
      @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); }
      }
      .pulse {
        animation: pulse 0.5s;
      }
    `
    document.head.appendChild(stylePulse)
  
    // Uncomment to see streak update demo
    // setInterval(updateStreakDemo, 30000);
  })
  
