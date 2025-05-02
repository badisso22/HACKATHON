// Tab switching functionality
document.addEventListener("DOMContentLoaded", () => {
    const tabs = document.querySelectorAll(".tab")
    const tabContents = document.querySelectorAll(".tab-content")
  
    tabs.forEach((tab) => {
      tab.addEventListener("click", () => {
        const tabId = tab.getAttribute("data-tab")
  
        // Update active tab
        tabs.forEach((t) => t.classList.remove("active"))
        tab.classList.add("active")
  
        // Show active content
        tabContents.forEach((content) => {
          content.classList.remove("active")
          if (content.id === tabId) {
            content.classList.add("active")
          }
        })
      })
    })
  
    // Sidebar toggle functionality for mobile
    const sidebar = document.getElementById("sidebar")
    const mainContent = document.getElementById("mainContent")
    const menuToggle = document.getElementById("menuToggle")
  
    if (menuToggle) {
      menuToggle.addEventListener("click", () => {
        sidebar.classList.toggle("active")
      })
    }
  
    // Form submission handling with loading state
    const forms = ["personalForm", "accountForm", "securityForm"]
    const buttons = ["personalSubmitBtn", "accountSubmitBtn", "securitySubmitBtn"]
  
    forms.forEach((formId, index) => {
      const form = document.getElementById(formId)
      const button = document.getElementById(buttons[index])
  
      if (form && button) {
        form.addEventListener("submit", (e) => {
          // Show loading state
          const btnText = button.querySelector(".btn-text")
          const loading = button.querySelector(".loading")
  
          btnText.classList.add("hidden")
          loading.classList.remove("hidden")
          button.disabled = true
  
          // Form will submit normally, no need to prevent default
        })
      }
    })
  
    // Password confirmation validation
    const securityForm = document.getElementById("securityForm")
    if (securityForm) {
      securityForm.addEventListener("submit", (e) => {
        const newPassword = document.getElementById("newPassword").value
        const confirmPassword = document.getElementById("confirmPassword").value
  
        if (newPassword !== confirmPassword) {
          e.preventDefault()
          alert("Passwords do not match")
  
          // Reset loading state
          const button = document.getElementById("securitySubmitBtn")
          const btnText = button.querySelector(".btn-text")
          const loading = button.querySelector(".loading")
  
          btnText.classList.remove("hidden")
          loading.classList.add("hidden")
          button.disabled = false
        }
      })
    }
  
    // Auto-hide notifications after 5 seconds
    const notification = document.querySelector(".notification")
    if (notification) {
      setTimeout(() => {
        notification.style.opacity = "0"
        notification.style.transition = "opacity 0.5s ease-out"
  
        setTimeout(() => {
          notification.style.display = "none"
        }, 500)
      }, 5000)
    }
  
    // Handle responsive sidebar for mobile
    function handleResponsive() {
      if (window.innerWidth <= 768) {
        sidebar.classList.remove("active")
      } else {
        sidebar.classList.remove("active")
      }
    }
  
    // Initial check
    handleResponsive()
  
    // Listen for window resize
    window.addEventListener("resize", handleResponsive)
  })
  
