document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("register-form")
  const birthdateInput = document.getElementById("birthdate")
  const ageInput = document.getElementById("age")
  const extensionSelect = document.getElementById("extension-name")
  const otherExtension = document.getElementById("other-extension")
  const passwordInput = document.getElementById("password")
  const rePasswordInput = document.getElementById("re-password")
  const usernameInput = document.getElementById("username")

  // Calculate age from birthdate
  birthdateInput.addEventListener("change", function () {
    const birthDate = new Date(this.value)
    const today = new Date()
    let age = today.getFullYear() - birthDate.getFullYear()
    const monthDiff = today.getMonth() - birthDate.getMonth()

    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
      age--
    }

    ageInput.value = age

    // Validate legal age (18+)
    if (age < 18) {
      document.getElementById("age-error").textContent = "Must be 18 years or older"
    } else {
      document.getElementById("age-error").textContent = ""
    }
  })

  // Show/hide other extension input
  extensionSelect.addEventListener("change", function () {
    if (this.value === "Other") {
      otherExtension.style.display = "block"
      otherExtension.required = true
    } else {
      otherExtension.style.display = "none"
      otherExtension.required = false
      otherExtension.value = ""
    }
  })

  // Password strength checker
  passwordInput.addEventListener("input", function () {
    const password = this.value
    const strengthDiv = document.getElementById("password-strength")
    const strength = checkPasswordStrength(password)

    strengthDiv.textContent = strength.text
    strengthDiv.className = "password-strength " + strength.class
  })

  // Real-time validation
  const inputs = form.querySelectorAll('input[type="text"], input[type="email"]')
  inputs.forEach((input) => {
    input.addEventListener("blur", function () {
      validateField(this)
    })
  })

  // Username availability check
  usernameInput.addEventListener("blur", function () {
    checkUsernameAvailability(this.value)
  })

  // Form submission
  form.addEventListener("submit", (e) => {
    e.preventDefault()

    // clear any previous server message
    showServerMessage('', false)

    if (validateForm()) {
      // Submit form data to server via fetch
      const submitButton = form.querySelector('button[type="submit"]')
      const originalButtonText = submitButton.textContent
      submitButton.disabled = true
      submitButton.textContent = 'Registering...'

      const formData = new FormData(form)

      fetch('php/register.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
      })
        .then((resp) => resp.json())
        .then((data) => {
          if (data && data.success) {
            showServerMessage(data.message || 'Registration successful', true)
            // Redirect to login after short delay
            setTimeout(() => {
              window.location.href = 'login.html'
            }, 1200)
          } else {
            const msg = (data && data.message) ? data.message : 'Registration failed'
            showServerMessage(msg, false)
          }
        })
        .catch((err) => {
          console.error('Registration error:', err)
          showServerMessage('Network or server error. Please try again later.', false)
        })
        .finally(() => {
          submitButton.disabled = false
          submitButton.textContent = originalButtonText
        })
    }
  })

  // Show server message near the form
  function showServerMessage(message, success) {
    let el = document.getElementById('server-message')
    if (!el) {
      el = document.createElement('div')
      el.id = 'server-message'
      el.style.marginTop = '12px'
      el.style.fontWeight = '600'
      form.appendChild(el)
    }
    el.textContent = message
    el.style.color = success ? 'green' : 'crimson'
  }

  function validateField(field) {
    const value = field.value.trim()
    const fieldName = field.name
    const errorElement = document.getElementById(fieldName.replace("_", "") + "-error")

    if (!errorElement) return

    let errorMessage = ""

    // Name fields validation
    if (["first_name", "middle_name", "last_name", "extension_name"].includes(fieldName)) {
      errorMessage = validateName(value, fieldName)
    }

    // ID Number validation
    if (fieldName === "id_no") {
      errorMessage = validateIdNumber(value)
    }

    // Address fields validation
    if (["street", "barangay", "city", "province", "country"].includes(fieldName)) {
      errorMessage = validateAddressField(value, fieldName)
    }

    // Zip code validation
    if (fieldName === "zipcode") {
      errorMessage = validateZipCode(value)
    }

    errorElement.textContent = errorMessage
  }

  function validateName(value, fieldName) {
    if (!value && fieldName !== "middle_name") return "This field is required"
    if (!value) return "" // Middle name is optional

    // Check for special characters
    if (/[^a-zA-Z\s]/.test(value)) {
      return "Special characters are not allowed"
    }

    // Check for numbers followed by letters
    if (/\d[a-zA-Z]/.test(value)) {
      return "Numbers followed by letters are not allowed"
    }

    // Check for double spaces
    if (/\s{2,}/.test(value)) {
      return "Double spaces are not allowed"
    }

    // Check for all capital letters
    if (value === value.toUpperCase() && value.length > 1) {
      return "All capital letters are not allowed"
    }

    // Check for three consecutive same letters
    if (/(.)\1{2,}/i.test(value)) {
      return "Three consecutive same letters are not allowed"
    }

    // Check proper capitalization
    const words = value.split(" ")
    for (const word of words) {
      if (word.length > 0 && word[0] !== word[0].toUpperCase()) {
        return "First letter of each word must be capitalized"
      }
      if (word.length > 1 && word.slice(1) !== word.slice(1).toLowerCase()) {
        return "Only first letter should be capitalized"
      }
    }

    return ""
  }

  function validateIdNumber(value) {
    if (!value) return "ID Number is required"

    const pattern = /^\d{4}-\d{4}$/
    if (!pattern.test(value)) {
      return "ID Number must be in format xxxx-xxxx"
    }

    return ""
  }

  function validateAddressField(value, fieldName) {
    if (!value) return `${fieldName.charAt(0).toUpperCase() + fieldName.slice(1)} is required`

    // Basic validation for address fields
    if (value.length < 2) {
      return `${fieldName.charAt(0).toUpperCase() + fieldName.slice(1)} must be at least 2 characters`
    }

    return ""
  }

  function validateZipCode(value) {
    if (!value) return "Zip code is required"

    if (!/^\d{4,6}$/.test(value)) {
      return "Zip code must be 4-6 digits"
    }

    return ""
  }

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

  function checkUsernameAvailability(username) {
    if (!username) return

    // Simulate username check
    const existingUsernames = ["admin", "user", "test"]
    const errorElement = document.getElementById("username-error")

    if (existingUsernames.includes(username.toLowerCase())) {
      errorElement.textContent = "Username already exists"
    } else {
      errorElement.textContent = ""
    }
  }

  function validateForm() {
    let isValid = true

    // Validate all required fields
    const requiredFields = form.querySelectorAll("input[required], select[required]")
    requiredFields.forEach((field) => {
      if (!field.value.trim()) {
        isValid = false
      }
    })

    // Check password match
    if (passwordInput.value !== rePasswordInput.value) {
      document.getElementById("repass-error").textContent = "Passwords do not match"
      isValid = false
    }

    // Check age
    if (Number.parseInt(ageInput.value) < 18) {
      isValid = false
    }

    return isValid
  }
})
