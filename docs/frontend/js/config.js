// API Configuration
const API_CONFIG = {
  // Change this to your Laravel backend URL
  BASE_URL: "http://localhost:8000/api",
  // Or for production: 'https://yourdomain.com/api'

  // API Endpoints
  ENDPOINTS: {
    // Auth
    LOGIN: "/login",
    REGISTER: "/register",
    LOGOUT: "/logout",
    PROFILE: "/profile",

    // Supplier
    SUPPLIERS: "/suppliers",
    SUPPLIER_DETAIL: "/suppliers/:id",
    SUPPLIER_PRODUCTS: "/suppliers/:id/products",

    // Buyer
    CONTACT_REQUEST: "/buyer/contact-requests",
    REVIEWS: "/buyer/reviews",

    // Admin
    ADMIN_DASHBOARD: "/admin/dashboard",
    ADMIN_VERIFY: "/admin/companies/:id/verify",
    ADMIN_MESSAGES: "/admin/messages",

    // Public
    SEARCH: "/search",
    PACKAGES: "/packages",
  },
}

// Helper function to get full API URL
function getApiUrl(endpoint, params = {}) {
  let url = API_CONFIG.BASE_URL + endpoint

  // Replace URL parameters
  Object.keys(params).forEach((key) => {
    url = url.replace(`:${key}`, params[key])
  })

  return url
}

// Helper function to get auth token
function getAuthToken() {
  return localStorage.getItem("auth_token")
}

// Helper function to set auth token
function setAuthToken(token) {
  localStorage.setItem("auth_token", token)
}

// Helper function to remove auth token
function removeAuthToken() {
  localStorage.removeItem("auth_token")
}

// Helper function to get user data
function getUserData() {
  const userData = localStorage.getItem("user_data")
  return userData ? JSON.parse(userData) : null
}

// Helper function to set user data
function setUserData(data) {
  localStorage.setItem("user_data", JSON.stringify(data))
}

// Helper function to make authenticated API calls
async function apiCall(endpoint, options = {}) {
  const token = getAuthToken()

  const headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
    ...options.headers,
  }

  if (token) {
    headers["Authorization"] = `Bearer ${token}`
  }

  try {
    const response = await fetch(endpoint, {
      ...options,
      headers,
    })

    const data = await response.json()

    if (!response.ok) {
      throw new Error(data.message || "API request failed")
    }

    return data
  } catch (error) {
    console.error("API Error:", error)
    throw error
  }
}
