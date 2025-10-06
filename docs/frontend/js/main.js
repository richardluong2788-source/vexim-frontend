// Main JavaScript file for homepage

// Mobile menu toggle
document.getElementById("mobile-menu-btn")?.addEventListener("click", () => {
  const mobileMenu = document.getElementById("mobile-menu")
  mobileMenu.classList.toggle("hidden")
})

// Declare variables before using them
function getAuthToken() {
  // Implementation for getting auth token
  return localStorage.getItem("auth_token")
}

function getUserData() {
  // Implementation for getting user data
  return JSON.parse(localStorage.getItem("user_data"))
}

function removeAuthToken() {
  // Implementation for removing auth token
  localStorage.removeItem("auth_token")
}

// Check if user is logged in
function checkAuth() {
  const token = getAuthToken()
  const userData = getUserData()

  if (token && userData) {
    // User is logged in
    document.getElementById("auth-buttons")?.classList.add("hidden")
    document.getElementById("user-menu")?.classList.remove("hidden")
  } else {
    // User is not logged in
    document.getElementById("auth-buttons")?.classList.remove("hidden")
    document.getElementById("user-menu")?.classList.add("hidden")
  }
}

// Logout function
function logout() {
  removeAuthToken()
  localStorage.removeItem("user_data")
  window.location.href = "index.html"
}

// Search function
function performSearch() {
  const searchInput = document.getElementById("search-input")
  const query = searchInput.value.trim()

  if (query) {
    window.location.href = `suppliers.html?search=${encodeURIComponent(query)}`
  }
}

// Allow Enter key to trigger search
document.getElementById("search-input")?.addEventListener("keypress", (e) => {
  if (e.key === "Enter") {
    performSearch()
  }
})

// Initialize on page load
document.addEventListener("DOMContentLoaded", () => {
  checkAuth()
})
