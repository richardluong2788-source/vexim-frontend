// Authentication JavaScript

// Declare variables before using them
const API_CONFIG = {
  ENDPOINTS: {
    LOGIN: "/api/login",
    REGISTER: "/api/register",
  },
}

async function apiCall(url, options) {
  const response = await fetch(url, options)
  if (!response.ok) {
    throw new Error("Network response was not ok")
  }
  return response.json()
}

function getApiUrl(endpoint) {
  return endpoint // Assuming API base URL is already set
}

function setAuthToken(token) {
  localStorage.setItem("authToken", token)
}

function setUserData(user) {
  localStorage.setItem("userData", JSON.stringify(user))
}

// Handle Login
async function handleLogin(event) {
  event.preventDefault()

  const email = document.getElementById("email").value
  const password = document.getElementById("password").value
  const loginBtn = document.getElementById("login-btn")

  // Disable button and show loading
  loginBtn.disabled = true
  loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Logging in...'

  try {
    const response = await apiCall(getApiUrl(API_CONFIG.ENDPOINTS.LOGIN), {
      method: "POST",
      body: JSON.stringify({ email, password }),
    })

    // Save token and user data
    setAuthToken(response.token)
    setUserData(response.user)

    // Show success message
    showSuccess("Login successful! Redirecting...")

    // Redirect based on role
    setTimeout(() => {
      window.location.href = "dashboard.html"
    }, 1000)
  } catch (error) {
    showError(error.message || "Login failed. Please check your credentials.")
    loginBtn.disabled = false
    loginBtn.innerHTML = '<span data-i18n="login_btn">Login</span>'
  }
}

// Handle Register
async function handleRegister(event) {
  event.preventDefault()

  const name = document.getElementById("name").value
  const email = document.getElementById("email").value
  const password = document.getElementById("password").value
  const passwordConfirmation = document.getElementById("password_confirmation").value
  const role = document.getElementById("role").value
  const registerBtn = document.getElementById("register-btn")

  // Validate passwords match
  if (password !== passwordConfirmation) {
    showError("Passwords do not match")
    return
  }

  // Disable button and show loading
  registerBtn.disabled = true
  registerBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creating account...'

  try {
    const response = await apiCall(getApiUrl(API_CONFIG.ENDPOINTS.REGISTER), {
      method: "POST",
      body: JSON.stringify({
        name,
        email,
        password,
        password_confirmation: passwordConfirmation,
        role,
      }),
    })

    // Save token and user data
    setAuthToken(response.token)
    setUserData(response.user)

    // Show success message
    showSuccess("Registration successful! Redirecting...")

    // Redirect to dashboard
    setTimeout(() => {
      window.location.href = "dashboard.html"
    }, 1000)
  } catch (error) {
    showError(error.message || "Registration failed. Please try again.")
    registerBtn.disabled = false
    registerBtn.innerHTML = '<span data-i18n="register_btn">Register</span>'
  }
}

// Show error message
function showError(message) {
  const errorDiv = document.getElementById("error-message")
  const errorText = document.getElementById("error-text")

  if (errorDiv && errorText) {
    errorText.textContent = message
    errorDiv.classList.remove("hidden")

    // Hide after 5 seconds
    setTimeout(() => {
      errorDiv.classList.add("hidden")
    }, 5000)
  }
}

// Show success message
function showSuccess(message) {
  const successDiv = document.getElementById("success-message")
  const successText = document.getElementById("success-text")

  if (successDiv && successText) {
    successText.textContent = message
    successDiv.classList.remove("hidden")
  }
}

// Check URL parameters for pre-selected role
document.addEventListener("DOMContentLoaded", () => {
  const urlParams = new URLSearchParams(window.location.search)
  const role = urlParams.get("role")

  if (role && document.getElementById("role")) {
    document.getElementById("role").value = role
  }
})
