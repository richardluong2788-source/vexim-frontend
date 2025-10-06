// Suppliers listing page JavaScript

let allSuppliers = []

// Declare variables before using them
const API_CONFIG = {
  ENDPOINTS: {
    SUPPLIERS: "/api/suppliers",
  },
}

function getApiUrl(endpoint) {
  return endpoint
}

async function apiCall(url, options) {
  const response = await fetch(url, options)
  return response.json()
}

function getAuthToken() {
  return localStorage.getItem("authToken")
}

// Load suppliers on page load
document.addEventListener("DOMContentLoaded", () => {
  loadSuppliers()
})

// Load suppliers from API
async function loadSuppliers() {
  showLoading()

  try {
    const urlParams = new URLSearchParams(window.location.search)
    const searchQuery = urlParams.get("search") || ""

    let url = getApiUrl(API_CONFIG.ENDPOINTS.SUPPLIERS)
    if (searchQuery) {
      url += `?search=${encodeURIComponent(searchQuery)}`
    }

    const response = await apiCall(url, { method: "GET" })
    allSuppliers = response.data || response

    displaySuppliers(allSuppliers)
  } catch (error) {
    console.error("Error loading suppliers:", error)
    showEmptyState()
  }
}

// Display suppliers in grid
function displaySuppliers(suppliers) {
  hideLoading()

  const grid = document.getElementById("suppliers-grid")

  if (!suppliers || suppliers.length === 0) {
    showEmptyState()
    return
  }

  hideEmptyState()

  grid.innerHTML = suppliers
    .map(
      (supplier) => `
        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
            <div class="p-6">
                 Company Logo 
                <div class="w-20 h-20 bg-gray-200 rounded-lg mb-4 flex items-center justify-center overflow-hidden">
                    ${
                      supplier.logo_url
                        ? `<img src="${supplier.logo_url}" alt="${supplier.company_name}" class="w-full h-full object-cover">`
                        : `<i class="fas fa-building text-3xl text-gray-400"></i>`
                    }
                </div>
                
                 Company Name & Badge 
                <div class="flex items-start justify-between mb-2">
                    <h3 class="text-xl font-bold text-primary">${supplier.company_name}</h3>
                    ${
                      supplier.verification_status === "verified"
                        ? `<span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-semibold" data-i18n="listing_verified">Verified</span>`
                        : ""
                    }
                </div>
                
                 Industry 
                <p class="text-gray-600 mb-2">
                    <i class="fas fa-industry mr-2"></i>${supplier.industry || "N/A"}
                </p>
                
                 Location 
                <p class="text-gray-600 mb-2">
                    <i class="fas fa-map-marker-alt mr-2"></i>${supplier.country || "N/A"}
                </p>
                
                 Main Products 
                <p class="text-gray-700 mb-4 line-clamp-2">
                    ${supplier.main_products || "Various products available"}
                </p>
                
                 Rating 
                <div class="flex items-center mb-4">
                    <div class="flex text-gold text-sm">
                        ${generateStars(supplier.average_rating || 0)}
                    </div>
                    <span class="text-gray-600 text-sm ml-2">(${supplier.review_count || 0})</span>
                </div>
                
                 Actions 
                <div class="flex gap-2">
                    <a href="supplier-profile.html?id=${supplier.id}" class="flex-1 bg-primary text-white text-center px-4 py-2 rounded-lg hover:bg-blue-900 transition">
                        <span data-i18n="listing_view_profile">View Profile</span>
                    </a>
                    <button onclick="quickContact(${supplier.id})" class="bg-gold text-primary px-4 py-2 rounded-lg hover:bg-yellow-500 transition">
                        <i class="fas fa-envelope"></i>
                    </button>
                </div>
            </div>
        </div>
    `,
    )
    .join("")
}

// Generate star rating HTML
function generateStars(rating) {
  const fullStars = Math.floor(rating)
  const hasHalfStar = rating % 1 >= 0.5
  const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0)

  let html = ""
  for (let i = 0; i < fullStars; i++) {
    html += '<i class="fas fa-star"></i>'
  }
  if (hasHalfStar) {
    html += '<i class="fas fa-star-half-alt"></i>'
  }
  for (let i = 0; i < emptyStars; i++) {
    html += '<i class="far fa-star"></i>'
  }
  return html
}

// Apply filters
function applyFilters() {
  const industry = document.getElementById("industry-filter").value
  const country = document.getElementById("country-filter").value
  const verified = document.getElementById("verified-filter").value

  let filtered = allSuppliers

  if (industry) {
    filtered = filtered.filter((s) => s.industry === industry)
  }

  if (country) {
    filtered = filtered.filter((s) => s.country === country)
  }

  if (verified === "verified") {
    filtered = filtered.filter((s) => s.verification_status === "verified")
  }

  displaySuppliers(filtered)
}

// Quick contact
function quickContact(supplierId) {
  const token = getAuthToken()
  if (!token) {
    alert("Please login to contact suppliers")
    window.location.href = "login.html"
    return
  }

  window.location.href = `supplier-profile.html?id=${supplierId}#contact`
}

// Show/hide loading state
function showLoading() {
  document.getElementById("loading")?.classList.remove("hidden")
  document.getElementById("suppliers-grid").classList.add("hidden")
}

function hideLoading() {
  document.getElementById("loading")?.classList.add("hidden")
  document.getElementById("suppliers-grid").classList.remove("hidden")
}

// Show/hide empty state
function showEmptyState() {
  hideLoading()
  document.getElementById("empty-state")?.classList.remove("hidden")
  document.getElementById("suppliers-grid").classList.add("hidden")
}

function hideEmptyState() {
  document.getElementById("empty-state")?.classList.add("hidden")
}
