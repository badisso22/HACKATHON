const backgroundWordsContainer = document.getElementById("backgroundWords")

// Only run this code if the element exists
if (backgroundWordsContainer) {
  const words = [
    "Hello",
    "Hola",
    "Bonjour",
    "Ciao",
    "Hallo",
    "Olá",
    "Namaste",
    "Salaam",
    "Zdravstvuyte",
    "Nǐ hǎo",
    "Konnichiwa",
    "Anyoung",
    "Merhaba",
    "Hej",
    "Ahoj",
    "Szia",
    "Shalom",
    "Yassou",
    "Salve",
    "Mingalaba",
    "Sawubona",
    "Habari",
    "Halo",
    "Language",
    "Learn",
    "Speak",
    "Culture",
    "Connect",
    "Understand",
    "Explore",
    "Global",
    "Journey",
    "Discover",
    "Communicate",
    "MURA",
  ]

  function createFloatingWord(text) {
    const word = document.createElement("span")
    word.textContent = text

    const x = Math.random() * window.innerWidth
    const y = window.innerHeight + Math.random() * 100

    word.style.position = "fixed"
    word.style.left = `${x}px`
    word.style.top = `${y}px`
    word.style.fontSize = `${14 + Math.random() * 20}px`
    word.style.opacity = "0.17"
    word.style.color = "#ffffff"
    word.style.fontWeight = "600"
    word.style.pointerEvents = "none"
    word.style.zIndex = "0"
    word.style.transition = "top 0.1s linear"

    backgroundWordsContainer.appendChild(word)

    let currentY = y
    const speed = 0.8 + Math.random() * 0.6

    function animate() {
      currentY -= speed
      if (currentY < -50) {
        currentY = window.innerHeight + 40
        word.style.left = `${Math.random() * window.innerWidth}px`
      }
      word.style.top = `${currentY}px`
      requestAnimationFrame(animate)
    }

    animate()
  }

  // Create initial words
  for (let i = 0; i < 20; i++) {
    const wordText = words[Math.floor(Math.random() * words.length)]
    createFloatingWord(wordText)
  }

  // Add new words periodically
  setInterval(() => {
    const wordText = words[Math.floor(Math.random() * words.length)]
    createFloatingWord(wordText)
  }, 2000)
}

// Password toggle functionality
document.addEventListener("DOMContentLoaded", () => {
  const passwordToggle = document.getElementById("passwordToggle")
  const passwordField = document.getElementById("password")

  if (passwordToggle && passwordField) {
    passwordToggle.addEventListener("click", () => {
      const type = passwordField.getAttribute("type") === "password" ? "text" : "password"
      passwordField.setAttribute("type", type)
      passwordToggle.textContent = type === "password" ? "👁️" : "🔒"
    })
  }
})
