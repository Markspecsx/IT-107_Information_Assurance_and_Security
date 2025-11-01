// Main JavaScript file
document.addEventListener("DOMContentLoaded", () => {
  // Check if user is logged in
  const isLoggedIn = localStorage.getItem("isLoggedIn")
  const logoutLink = document.getElementById("logout-link")

  if (isLoggedIn === "true" && logoutLink) {
    logoutLink.style.display = "block"
    logoutLink.addEventListener("click", (e) => {
      e.preventDefault()
      localStorage.removeItem("isLoggedIn")
      localStorage.removeItem("username")
      window.location.href = "index.html"
    })
  }

  // Disable back button
  history.pushState(null, null, location.href)
  window.onpopstate = () => {
    history.go(1)
  }
})
