// nav.js — build header navigation based on current page and login state
(function () {
  function buildNav() {
    const navUl = document.querySelector('.header-content nav ul')
    if (!navUl) return

    const path = window.location.pathname
    const page = path.substring(path.lastIndexOf('/') + 1).toLowerCase()
    const isLoggedIn = localStorage.getItem('isLoggedIn') === 'true'

    // clear existing
    navUl.innerHTML = ''

    // always show Home
    navUl.appendChild(makeLi('Home', 'index.html'))

    if (page === 'login.html' || page === 'login') {
      navUl.appendChild(makeLi('Registered', 'register.html'))
    } else if (page === 'register.html' || page === 'register') {
      navUl.appendChild(makeLi('Log-in', 'login.html'))
    } else {
      // other pages
      if (!isLoggedIn) {
        navUl.appendChild(makeLi('Register', 'register.html'))
        navUl.appendChild(makeLi('Log-in', 'login.html'))
      }
    }

    // show logout if logged in
    if (isLoggedIn) {
      const li = document.createElement('li')
      const a = document.createElement('a')
      a.href = '#'
      a.textContent = 'Log-out'
      a.addEventListener('click', (e) => {
        e.preventDefault()
        localStorage.removeItem('isLoggedIn')
        localStorage.removeItem('username')
        // reload to update nav
        window.location.href = 'index.html'
      })
      li.appendChild(a)
      navUl.appendChild(li)
    }
  }

  function makeLi(text, href) {
    const li = document.createElement('li')
    const a = document.createElement('a')
    a.href = href
    a.textContent = text
    li.appendChild(a)
    return li
  }

  document.addEventListener('DOMContentLoaded', buildNav)
})()
// This file has been deleted as per the patch request.
// Dynamic navigation builder — controls which links appear based on page and login state
(function () {
  function buildNav() {
    const navUl = document.querySelector('.header-content nav ul')
    if (!navUl) return

    // Determine current page
    const path = window.location.pathname
    const page = path.substring(path.lastIndexOf('/') + 1).toLowerCase()

    const isLoggedIn = localStorage.getItem('isLoggedIn') === 'true'

    // Clear existing
    navUl.innerHTML = ''

    // Always show Home
    navUl.appendChild(createLi('HOME', 'index.html'))

    if (page === 'login.html' || page === 'login') {
      // On login page show Home and Registered
      navUl.appendChild(createLi('Registered', 'register.html'))
    } else if (page === 'register.html' || page === 'register') {
      // On register page show Home and Log-in
      navUl.appendChild(createLi('Log-in', 'login.html'))
    } else {
      // Default for other pages
      if (!isLoggedIn) {
        navUl.appendChild(createLi('Register', 'register.html'))
        navUl.appendChild(createLi('Log-in', 'login.html'))
      }
    }

    // Add logout link if logged in
    if (isLoggedIn) {
      const logoutLi = document.createElement('li')
      const logoutA = document.createElement('a')
      logoutA.href = '#'
      logoutA.id = 'logout-link'
      logoutA.textContent = 'Log-out'
      logoutA.addEventListener('click', function (e) {
        e.preventDefault()
        localStorage.removeItem('isLoggedIn')
        localStorage.removeItem('username')
        // reload so nav updates
        window.location.href = 'index.html'
      })
      logoutLi.appendChild(logoutA)
      navUl.appendChild(logoutLi)
    }
  }

  function createLi(text, href) {
    const li = document.createElement('li')
    const a = document.createElement('a')
    a.href = href
    a.textContent = text
    li.appendChild(a)
    return li
  }

  // build on DOM ready
  document.addEventListener('DOMContentLoaded', buildNav)
})()
