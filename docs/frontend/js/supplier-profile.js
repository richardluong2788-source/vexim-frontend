// Supplier profile page JavaScript

let currentSupplier = null

// Declare necessary variables and functions
const API_CONFIG = {
  ENDPOINTS: {
    SUPPLIER_DETAIL: "https://api.example.com/supplier/detail",
    SUPPLIER_PRODUCTS: "https://api.example.com/supplier/products",
    CONTACT_REQUEST: "https://api.example.com/contact/request",
  },
}

function getApiUrl(endpoint, params) {
  const url = new URL(endpoint)
  url.search = new URLSearchParams(params).toString()
  return url.toString()
}

async function apiCall(url, options) {
  const response = await fetch(url, options)
  if (!response.ok) {
    throw new Error("Network response was not ok")
  }
  return response.json()
}

function getAuthToken() {
  return localStorage.getItem("authToken")
}

// Load supplier profile on page load
document.addEventListener("DOMContentLoaded", () => {
  const urlParams = new URLSearchParams(window.location.search)
  const supplierId = urlParams.get("id")

  if (supplierId) {
    loadSupplierProfile(supplierId)
  } else {
    alert("Supplier not found")
    window.location.href = "suppliers.html"
  }
})

// Load supplier profile from API
async function loadSupplierProfile(supplierId) {
  try {
    const url = getApiUrl(API_CONFIG.ENDPOINTS.SUPPLIER_DETAIL, { id: supplierId })
    const response = await apiCall(url, { method: "GET" })

    currentSupplier = response.data || response
    displaySupplierProfile(currentSupplier)

    // Load products
    loadSupplierProducts(supplierId)

    // Load certificates
    loadSupplierCertificates(supplierId)
  } catch (error) {
    console.error("Error loading supplier profile:", error)
    alert("Failed to load supplier profile")
    window.location.href = "suppliers.html"
  }
}

// Display supplier profile
function displaySupplierProfile(supplier) {
  // Hide loading, show content
  document.getElementById("loading").classList.add("hidden")
  document.getElementById("profile-content").classList.remove("hidden")

  // Company header
  document.getElementById("company-name").textContent = supplier.company_name
  document.getElementById("industry").textContent = supplier.industry || "N/A"
  document.getElementById("location").textContent = `${supplier.city || ""}, ${supplier.country || ""}`.trim()
  document.getElementById("year-established").textContent = supplier.year_established || "N/A"
  document.getElementById("review-count").textContent = supplier.review_count || 0

  // Logo
  if (supplier.logo_url) {
    document.getElementById("company-logo").src = supplier.logo_url
  }

  // Verified badge
  if (supplier.verification_status === "verified") {
    document.getElementById("verified-badge").classList.remove("hidden")
  }

  // About company
  document.getElementById("company-description").textContent = supplier.description || "No description available."

  // Company details
  document.getElementById("business-type").textContent = supplier.business_type || "N/A"
  document.getElementById("employees").textContent = supplier.number_of_employees || "N/A"
  document.getElementById("revenue").textContent = supplier.annual_revenue || "N/A"
  document.getElementById("markets").textContent = supplier.main_markets || "N/A"

  // Masked contact info
  if (supplier.email) {
    const maskedEmail = maskEmail(supplier.email)
    document.getElementById("masked-email").textContent = maskedEmail
  }

  if (supplier.phone) {
    const maskedPhone = maskPhone(supplier.phone)
    document.getElementById("masked-phone").textContent = maskedPhone
  }

  // Load export history chart
  if (supplier.export_history) {
    loadExportChart(supplier.export_history)
  }
}

// Load supplier products
async function loadSupplierProducts(supplierId) {
  try {
    const url = getApiUrl(API_CONFIG.ENDPOINTS.SUPPLIER_PRODUCTS, { id: supplierId })
    const response = await apiCall(url, { method: "GET" })

    const products = response.data || response
    displayProducts(products)
  } catch (error) {
    console.error("Error loading products:", error)
  }
}

// Display products
function displayProducts(products) {
  const grid = document.getElementById("products-grid")

  if (!products || products.length === 0) {
    grid.innerHTML = '<p class="text-gray-600">No products available</p>'
    return
  }

  grid.innerHTML = products
    .map(
      (product) => `
        <div class="border rounded-lg p-4 hover:shadow-md transition">
            <div class="w-full h-40 bg-gray-200 rounded-lg mb-3 overflow-hidden">
                ${
                  product.image_url
                    ? `<img src="${product.image_url}" alt="${product.name}" class="w-full h-full object-cover">`
                    : `<div class="w-full h-full flex items-center justify-center"><i class="fas fa-box text-4xl text-gray-400"></i></div>`
                }
            </div>
            <h4 class="font-semibold text-gray-800 mb-1">${product.name}</h4>
            <p class="text-sm text-gray-600 line-clamp-2">${product.description || ""}</p>
            ${product.price ? `<p class="text-primary font-bold mt-2">$${product.price}</p>` : ""}
        </div>
    `,
    )
    .join("")
}

// Load supplier certificates
async function loadSupplierCertificates(supplierId) {
  try {
    const url = getApiUrl(API_CONFIG.ENDPOINTS.SUPPLIER_CERTIFICATES, { id: supplierId })
    const response = await apiCall(url, { method: "GET" })

    const certificates = response.data || response
    displayCertificates(certificates)
  } catch (error) {
    console.error("Error loading certificates:", error)
  }
}

// Display certificates
function displayCertificates(certificates) {
  const grid = document.getElementById("certificates-grid")

  if (!certificates || certificates.length === 0) {
    grid.innerHTML = '<p class="text-gray-600">No certificates available</p>'
    return
  }

  grid.innerHTML = certificates
    .map(
      (cert) => `
        <div class="border rounded-lg p-4 text-center hover:shadow-md transition">
            <i class="fas fa-certificate text-4xl text-gold mb-2"></i>
            <p class="font-semibold text-sm">${cert.name}</p>
            <p class="text-xs text-gray-600">${cert.issued_by}</p>
        </div>
    `,
    )
    .join("")
}

// Load export history chart
function loadExportChart(exportHistory) {
  const ctx = document.getElementById("export-chart")

  if (typeof window.Chart === "undefined") {
    console.error("Chart.js is not loaded")
    return
  }

  // Sample data - replace with actual export history
  const years = ["2020", "2021", "2022", "2023", "2024"]
  const values = [1200000, 1500000, 1800000, 2100000, 2400000]

  new window.Chart(ctx, {
    type: "line",
    data: {
      labels: years,
      datasets: [
        {
          label: "Export Value (USD)",
          data: values,
          borderColor: "#003366",
          backgroundColor: "rgba(0, 51, 102, 0.1)",
          tension: 0.4,
          fill: true,
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          display: true,
          position: "top",
        },
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: (value) => "$" + value / 1000000 + "M",
          },
        },
      },
    },
  })
}

// Mask email
function maskEmail(email) {
  const [username, domain] = email.split("@")
  const maskedUsername = username.charAt(0) + "***" + username.charAt(username.length - 1)
  const [domainName, tld] = domain.split(".")
  const maskedDomain = domainName.charAt(0) + "***" + "." + tld
  return maskedUsername + "@" + maskedDomain
}

// Mask phone
function maskPhone(phone) {
  return phone.replace(/\d(?=\d{4})/g, "*")
}

// Show contact form modal
function showContactForm() {
  const token = getAuthToken()
  if (!token) {
    alert("Please login to contact suppliers")
    window.location.href = "login.html"
    return
  }

  document.getElementById("contact-modal").classList.remove("hidden")
}

// Close contact form modal
function closeContactForm() {
  document.getElementById("contact-modal").classList.add("hidden")
}

// Submit contact form
async function submitContactForm(event) {
  event.preventDefault()

  const form = event.target
  const formData = new FormData(form)

  const data = {
    supplier_id: currentSupplier.id,
    company_name: formData.get("company_name"),
    product_interest: formData.get("product_interest"),
    quantity: formData.get("quantity"),
    message: formData.get("message"),
  }

  try {
    await apiCall(getApiUrl(API_CONFIG.ENDPOINTS.CONTACT_REQUEST), {
      method: "POST",
      body: JSON.stringify(data),
    })

    alert("Your inquiry has been sent successfully! The admin will review and forward it to the supplier.")
    closeContactForm()
    form.reset()
  } catch (error) {
    alert("Failed to send inquiry. Please try again.")
    console.error("Error:", error)
  }
}
