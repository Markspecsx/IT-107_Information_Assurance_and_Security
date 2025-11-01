document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("forgot-form")
  const answerInput = document.getElementById("answer")
  const reAnswerInput = document.getElementById("re-answer")

  form.addEventListener("submit", (e) => {
    e.preventDefault()

    const username = document.getElementById("username").value.trim()
    const question = document.getElementById("question").value
    const answer = answerInput.value.trim()
    const reAnswer = reAnswerInput.value.trim()

    // Clear previous errors
    document.querySelectorAll(".error-msg").forEach((el) => (el.textContent = ""))

    // Validate answers match
    if (answer !== reAnswer) {
      document.getElementById("reanswer-error").textContent = "Answers do not match"
      return
    }

    // Simulate verification
    if (verifySecurityAnswer(username, question, answer)) {
      alert("Verification successful! Redirecting to change password...")
      localStorage.setItem("resetUsername", username)
      window.location.href = "change-password.html"
    } else {
      document.getElementById("answer-error").textContent = "Incorrect answer"
    }
  })

  function verifySecurityAnswer(username, question, answer) {
    // Demo verification - replace with actual PHP verification
    return answer.toLowerCase().includes("demo")
  }
})
