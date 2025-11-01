document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("change-password-form")
  const newPasswordInput = document.getElementById("new-password")
  const confirmPasswordInput = document.getElementById("confirm-password")
  const messageDiv = document.getElementById("message")

  // Check if user came from forgot password
  const resetUsername = localStorage.getItem("resetUsername")
  if (!resetUsername) {
    window.location.href = "login.html"
    return
  }

  // Password strength checker
  newPasswordInput.addEventListener("input", function () {
    const password = this.value
    const strengthDiv = document.getElementById("password-strength")
    const strength = checkPasswordStrength(password)

    strengthDiv.textContent = strength.text
    strengthDiv.className = "password-strength " + strength.class
  })

  form.addEventListener("submit", (e) => {
    e.preventDefault()

    const newPassword = newPasswordInput.value
    const confirmPassword = confirmPasswordInput.value

    // Clear previous messages
    messageDiv.textContent = ""
    document.querySelectorAll(".error-msg").forEach((el) => (el.textContent = ""))

    if (newPassword !== confirmPassword) {
      messageDiv.textContent = "Mismatch Password"
      messageDiv.className = "message error"
      document.getElementById("confirm-error").textContent = "Passwords do not match"
      return
    }

    // Simulate password change
    if (changePassword(resetUsername, newPassword)) {
      messageDiv.textContent = "Successfully Change Password"
      messageDiv.className = "message success"

      localStorage.removeItem("resetUsername")

      setTimeout(() => {
        window.location.href = "login.html"
      }, 2000)
    } else {
      messageDiv.textContent = "Failed to change password"
      messageDiv.className = "message error"
    }
  })

  function checkPasswordStrength(password) {
    let score = 0

    if (password.length >= 8) score++
    if (/[a-z]/.test(password)) score++
    if (/[A-Z]/.test(password)) score++
    if (/\d/.test(password)) score++
    if (/[^a-zA-Z\d]/.test(password)) score++

    if (score < 3) {
      return { text: "Weak", class: "weak" }
    } else if (score < 5) {
      return { text: "Medium", class: "medium" }
    } else {
      return { text: "Strong", class: "strong" }
    }
  }

  function changePassword(username, newPassword) {
    // Demo function - replace with actual PHP
    return true
  }
})
