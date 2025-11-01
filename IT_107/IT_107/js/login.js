document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("login-form")
  const usernameInput = document.getElementById("username")
  const passwordInput = document.getElementById("password")
  const loginBtn = document.getElementById("login-btn")
  const forgotLink = document.getElementById("forgot-link")
  const registerLink = document.getElementById("register-link")
  const lockoutTimer = document.getElementById("lockout-timer")
  const timerSpan = document.getElementById("timer")

  let failedAttempts = Number.parseInt(localStorage.getItem("failedAttempts") || "0")
  let lockoutEndTime = Number.parseInt(localStorage.getItem("lockoutEndTime") || "0")

  // Check if currently locked out
  checkLockoutStatus()

  // Show forgot password link after 2 failed attempts
  if (failedAttempts >= 2) {
    forgotLink.style.display = "inline"
  }

  form.addEventListener("submit", (e) => {
    e.preventDefault()

    if (isLockedOut()) {
      return
    }

    // Client-side validation before sending to server
    if (!clientValidate()) {
      return
    }

    const username = usernameInput.value.trim()
    const password = passwordInput.value

    // Send credentials to server
    fetch('php/login.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`,
      credentials: 'same-origin'
    })
      .then((resp) => resp.json())
      .then((data) => {
        if (data && data.success) {
          // Successful login
          localStorage.removeItem("failedAttempts")
          localStorage.removeItem("lockoutEndTime")
          localStorage.setItem("isLoggedIn", "true")
          localStorage.setItem("username", username)

          // Redirect to index or dashboard
          window.location.href = 'index.html'
        } else {
          // Handle server-provided info (failedAttempts, lockout)
          if (data.failedAttempts !== undefined) {
            failedAttempts = Number.parseInt(data.failedAttempts || '0')
            localStorage.setItem("failedAttempts", failedAttempts.toString())
          } else {
            // fall back to increment
            failedAttempts++
            localStorage.setItem("failedAttempts", failedAttempts.toString())
          }

          if (failedAttempts >= 2) {
            forgotLink.style.display = "inline"
          }

          if (data.lockout && data.remaining) {
            const lockoutMs = Date.now() + Number(data.remaining) * 1000
            localStorage.setItem('lockoutEndTime', lockoutMs.toString())
            // sync local var and start timer
            lockoutEndTime = lockoutMs
            startLockoutTimer()
          }

          const msg = data && data.message ? data.message : 'Invalid username or password'
          document.getElementById("username-error").textContent = msg
          document.getElementById("password-error").textContent = msg
        }
      })
      .catch((err) => {
        console.error('Login error:', err)
        document.getElementById("username-error").textContent = 'Network or server error'
      })
  })

  // Client-side validation rules
  function clientValidate() {
    let ok = true
    const u = usernameInput.value.trim()
    const p = passwordInput.value

    // Username: required, 3-50 chars, letters and numbers only
    if (!u) {
      document.getElementById("username-error").textContent = 'Username is required'
      ok = false
    } else if (u.length < 3) {
      document.getElementById("username-error").textContent = 'Username must be at least 3 characters'
      ok = false
    } else if (u.length > 50) {
      document.getElementById("username-error").textContent = 'Username must be at most 50 characters'
      ok = false
    } else if (!/^[a-zA-Z0-9_]+$/.test(u)) {
      document.getElementById("username-error").textContent = 'Only letters, numbers, and underscore are allowed'
      ok = false
    } else {
      document.getElementById("username-error").textContent = ''
    }

    // Password: required, 8-100 chars, must contain letters and numbers
    if (!p) {
      document.getElementById("password-error").textContent = 'Password is required'
      ok = false
    } else if (p.length < 8) {
      document.getElementById("password-error").textContent = 'Password must be at least 8 characters'
      ok = false
    } else if (p.length > 100) {
      document.getElementById("password-error").textContent = 'Password must be at most 100 characters'
      ok = false
    } else if (!/[a-zA-Z]/.test(p) || !/\d/.test(p)) {
      document.getElementById("password-error").textContent = 'Password must contain letters and numbers'
      ok = false
    } else {
      document.getElementById("password-error").textContent = ''
    }

    return ok
  }

  function validateLogin(username, password) {
    // removed demo validation; authentication is handled server-side via php/login.php
    return false
  }

  function setLockout(seconds) {
    lockoutEndTime = Date.now() + seconds * 1000
    localStorage.setItem("lockoutEndTime", lockoutEndTime.toString())
    startLockoutTimer()
  }

  function startLockoutTimer() {
    lockoutTimer.style.display = "block"
    loginBtn.disabled = true
    registerLink.style.pointerEvents = "none"
    registerLink.style.opacity = "0.5"

    const interval = setInterval(() => {
      const remaining = Math.ceil((lockoutEndTime - Date.now()) / 1000)

      if (remaining <= 0) {
        clearInterval(interval)
        lockoutTimer.style.display = "none"
        loginBtn.disabled = false
        registerLink.style.pointerEvents = "auto"
        registerLink.style.opacity = "1"
        localStorage.removeItem("lockoutEndTime")
      } else {
        timerSpan.textContent = remaining
      }
    }, 1000)
  }

  function checkLockoutStatus() {
    if (lockoutEndTime > Date.now()) {
      startLockoutTimer()
    }
  }

  function isLockedOut() {
    return lockoutEndTime > Date.now()
  }

  // Disable back button
  history.pushState(null, null, location.href)
  window.onpopstate = () => {
    history.go(1)
  }
})

function togglePassword() {
  const passwordInput = document.getElementById("password")
  const showPassword = document.querySelector(".show-password")

  if (passwordInput.type === "password") {
    passwordInput.type = "text"
    showPassword.textContent = "🙈"
  } else {
    passwordInput.type = "password"
    showPassword.textContent = "👁️"
  }
}
