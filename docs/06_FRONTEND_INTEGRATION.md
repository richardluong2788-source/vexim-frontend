# Frontend Integration Guide

## Overview
This guide explains how to connect your HTML/CSS frontend with the Laravel backend API.

## Authentication Flow

### 1. User Registration

\`\`\`html
<!-- register.html -->
<form id="registerForm">
  <input type="text" name="name" placeholder="Full Name" required>
  <input type="email" name="email" placeholder="Email" required>
  <input type="password" name="password" placeholder="Password" required>
  <input type="password" name="password_confirmation" placeholder="Confirm Password" required>
  <input type="tel" name="phone" placeholder="Phone Number" required>
  
  <select name="role" required>
    <option value="">Select Role</option>
    <option value="supplier">Supplier</option>
    <option value="buyer">Buyer</option>
  </select>
  
  <!-- Show these fields only if role is 'supplier' -->
  <div id="supplierFields" style="display:none;">
    <input type="text" name="company_name" placeholder="Company Name">
    <input type="text" name="business_license" placeholder="Business License">
    <input type="text" name="tax_code" placeholder="Tax Code">
    <input type="text" name="address" placeholder="Address">
    <input type="text" name="country" placeholder="Country">
    <input type="tel" name="company_phone" placeholder="Company Phone">
    <input type="email" name="company_email" placeholder="Company Email">
    <input type="url" name="website" placeholder="Website (optional)">
    <textarea name="description" placeholder="Company Description"></textarea>
  </div>
  
  <button type="submit">Register</button>
</form>

<script>
// Show/hide supplier fields based on role selection
document.querySelector('[name="role"]').addEventListener('change', function() {
  const supplierFields = document.getElementById('supplierFields');
  if (this.value === 'supplier') {
    supplierFields.style.display = 'block';
    // Make fields required
    supplierFields.querySelectorAll('input, textarea').forEach(field => {
      if (!field.name.includes('website') && !field.name.includes('description')) {
        field.required = true;
      }
    });
  } else {
    supplierFields.style.display = 'none';
    supplierFields.querySelectorAll('input, textarea').forEach(field => {
      field.required = false;
    });
  }
});

// Handle form submission
document.getElementById('registerForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const formData = new FormData(this);
  const data = Object.fromEntries(formData);
  
  try {
    const response = await fetch('http://your-domain.com/api/register', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(data)
    });
    
    const result = await response.json();
    
    if (result.success) {
      // Save token to localStorage
      localStorage.setItem('token', result.data.token);
      localStorage.setItem('user', JSON.stringify(result.data.user));
      
      // Redirect based on role
      if (result.data.user.role === 'admin') {
        window.location.href = '/admin/dashboard.html';
      } else if (result.data.user.role === 'supplier') {
        window.location.href = '/supplier/dashboard.html';
      } else {
        window.location.href = '/buyer/dashboard.html';
      }
    } else {
      // Show errors
      alert('Registration failed: ' + JSON.stringify(result.errors));
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Registration failed. Please try again.');
  }
});
</script>
\`\`\`

### 2. User Login

\`\`\`html
<!-- login.html -->
<form id="loginForm">
  <input type="email" name="email" placeholder="Email" required>
  <input type="password" name="password" placeholder="Password" required>
  <button type="submit">Login</button>
</form>

<script>
document.getElementById('loginForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const formData = new FormData(this);
  const data = Object.fromEntries(formData);
  
  try {
    const response = await fetch('http://your-domain.com/api/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(data)
    });
    
    const result = await response.json();
    
    if (result.success) {
      // Save token and user data
      localStorage.setItem('token', result.data.token);
      localStorage.setItem('user', JSON.stringify(result.data.user));
      
      // Redirect based on role
      const user = result.data.user;
      if (user.role === 'admin') {
        window.location.href = '/admin/dashboard.html';
      } else if (user.role === 'supplier') {
        window.location.href = '/supplier/dashboard.html';
      } else {
        window.location.href = '/buyer/dashboard.html';
      }
    } else {
      alert('Login failed: ' + result.message);
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Login failed. Please try again.');
  }
});
</script>
\`\`\`

### 3. Protected Pages (Check Authentication)

\`\`\`html
<!-- Add this script to all protected pages -->
<script>
// Check if user is logged in
function checkAuth() {
  const token = localStorage.getItem('token');
  const user = JSON.parse(localStorage.getItem('user') || '{}');
  
  if (!token) {
    window.location.href = '/login.html';
    return null;
  }
  
  return { token, user };
}

// Check authentication on page load
const auth = checkAuth();

// Helper function to make authenticated API calls
async function apiCall(url, options = {}) {
  const token = localStorage.getItem('token');
  
  const defaultOptions = {
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': `Bearer ${token}`
    }
  };
  
  const mergedOptions = {
    ...defaultOptions,
    ...options,
    headers: {
      ...defaultOptions.headers,
      ...options.headers
    }
  };
  
  try {
    const response = await fetch(url, mergedOptions);
    const result = await response.json();
    
    // Handle unauthorized (token expired)
    if (response.status === 401) {
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      window.location.href = '/login.html';
      return null;
    }
    
    return result;
  } catch (error) {
    console.error('API Error:', error);
    throw error;
  }
}

// Logout function
function logout() {
  apiCall('http://your-domain.com/api/logout', { method: 'POST' })
    .then(() => {
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      window.location.href = '/login.html';
    });
}
</script>
\`\`\`

## Dashboard Examples

### Supplier Dashboard

\`\`\`html
<!-- supplier/dashboard.html -->
<!DOCTYPE html>
<html>
<head>
  <title>Supplier Dashboard</title>
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>
  <div class="dashboard">
    <aside class="sidebar">
      <h2>Supplier Panel</h2>
      <nav>
        <a href="/supplier/dashboard.html">Dashboard</a>
        <a href="/supplier/products.html">Products</a>
        <a href="/supplier/company.html">Company Profile</a>
        <a href="/supplier/certificates.html">Certificates</a>
        <a href="/supplier/messages.html">Messages</a>
        <button onclick="logout()">Logout</button>
      </nav>
    </aside>
    
    <main class="content">
      <h1>Dashboard</h1>
      
      <div class="stats-grid">
        <div class="stat-card">
          <h3>Total Products</h3>
          <p id="totalProducts">-</p>
        </div>
        <div class="stat-card">
          <h3>Profile Views</h3>
          <p id="totalViews">-</p>
        </div>
        <div class="stat-card">
          <h3>Pending Messages</h3>
          <p id="pendingMessages">-</p>
        </div>
        <div class="stat-card">
          <h3>Rating</h3>
          <p id="rating">-</p>
        </div>
      </div>
      
      <div class="verification-status">
        <h2>Verification Status</h2>
        <p id="verificationStatus">-</p>
        <p id="verificationId">-</p>
      </div>
      
      <div class="package-info">
        <h2>Current Package</h2>
        <p id="packageName">-</p>
        <p id="packageExpiry">-</p>
      </div>
      
      <div class="recent-products">
        <h2>Recent Products</h2>
        <div id="productsList"></div>
      </div>
    </main>
  </div>
  
  <script src="/js/auth.js"></script>
  <script>
  // Load dashboard data
  async function loadDashboard() {
    const result = await apiCall('http://your-domain.com/api/supplier/dashboard');
    
    if (result.success) {
      const data = result.data;
      
      // Update stats
      document.getElementById('totalProducts').textContent = data.stats.total_products;
      document.getElementById('totalViews').textContent = data.stats.total_views;
      document.getElementById('pendingMessages').textContent = data.stats.pending_messages;
      document.getElementById('rating').textContent = data.stats.rating + ' / 5.0';
      
      // Update verification status
      const statusText = data.stats.verification_status === 'verified' ? 'Verified ✓' : 
                        data.stats.verification_status === 'pending' ? 'Pending Review' : 
                        'Not Verified';
      document.getElementById('verificationStatus').textContent = statusText;
      
      if (data.company.verification_id) {
        document.getElementById('verificationId').textContent = 
          'Verification ID: ' + data.company.verification_id;
      }
      
      // Update package info
      document.getElementById('packageName').textContent = 
        'Package: ' + data.package_info.current_package.name;
      document.getElementById('packageExpiry').textContent = 
        'Expires: ' + new Date(data.package_info.expires_at).toLocaleDateString() +
        ' (' + data.package_info.days_remaining + ' days remaining)';
      
      // Display recent products
      const productsList = document.getElementById('productsList');
      productsList.innerHTML = data.recent_products.map(product => `
        <div class="product-item">
          <h4>${product.product_name}</h4>
          <p>${product.category} - $${product.price}/${product.unit}</p>
        </div>
      `).join('');
    }
  }
  
  // Load data on page load
  loadDashboard();
  </script>
</body>
</html>
\`\`\`

### Buyer Dashboard

\`\`\`html
<!-- buyer/dashboard.html -->
<!DOCTYPE html>
<html>
<head>
  <title>Buyer Dashboard</title>
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>
  <div class="dashboard">
    <aside class="sidebar">
      <h2>Buyer Panel</h2>
      <nav>
        <a href="/buyer/dashboard.html">Dashboard</a>
        <a href="/buyer/search.html">Search Suppliers</a>
        <a href="/buyer/contacts.html">My Contacts</a>
        <button onclick="logout()">Logout</button>
      </nav>
    </aside>
    
    <main class="content">
      <h1>Dashboard</h1>
      
      <div class="stats-grid">
        <div class="stat-card">
          <h3>Total Contacts</h3>
          <p id="totalContacts">-</p>
        </div>
        <div class="stat-card">
          <h3>Pending</h3>
          <p id="pendingContacts">-</p>
        </div>
        <div class="stat-card">
          <h3>Approved</h3>
          <p id="approvedContacts">-</p>
        </div>
      </div>
      
      <div class="recent-contacts">
        <h2>Recent Contact Requests</h2>
        <div id="contactsList"></div>
      </div>
    </main>
  </div>
  
  <script src="/js/auth.js"></script>
  <script>
  async function loadDashboard() {
    const result = await apiCall('http://your-domain.com/api/buyer/dashboard');
    
    if (result.success) {
      const data = result.data;
      
      document.getElementById('totalContacts').textContent = data.stats.total_contacts;
      document.getElementById('pendingContacts').textContent = data.stats.pending_contacts;
      document.getElementById('approvedContacts').textContent = data.stats.approved_contacts;
      
      const contactsList = document.getElementById('contactsList');
      contactsList.innerHTML = data.recent_contacts.map(contact => `
        <div class="contact-item">
          <h4>${contact.company.company_name}</h4>
          <p>Subject: ${contact.subject}</p>
          <p>Status: ${contact.admin_status}</p>
          <p>Date: ${new Date(contact.created_at).toLocaleDateString()}</p>
        </div>
      `).join('');
    }
  }
  
  loadDashboard();
  </script>
</body>
</html>
\`\`\`

### Search Suppliers Page

\`\`\`html
<!-- buyer/search.html -->
<!DOCTYPE html>
<html>
<head>
  <title>Search Suppliers</title>
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>
  <div class="search-page">
    <h1>Search Verified Suppliers</h1>
    
    <form id="searchForm">
      <input type="text" name="keyword" placeholder="Search by company name or product">
      <select name="country">
        <option value="">All Countries</option>
        <option value="Vietnam">Vietnam</option>
        <option value="China">China</option>
        <option value="Thailand">Thailand</option>
      </select>
      <select name="sort_by">
        <option value="rating">Highest Rated</option>
        <option value="views">Most Viewed</option>
        <option value="newest">Newest</option>
      </select>
      <button type="submit">Search</button>
    </form>
    
    <div id="results" class="suppliers-grid"></div>
    <div id="pagination"></div>
  </div>
  
  <script src="/js/auth.js"></script>
  <script>
  async function searchSuppliers(page = 1) {
    const formData = new FormData(document.getElementById('searchForm'));
    const params = new URLSearchParams(formData);
    params.append('page', page);
    
    const result = await apiCall(`http://your-domain.com/api/suppliers/search?${params}`);
    
    if (result.success) {
      const suppliers = result.data.data;
      const resultsDiv = document.getElementById('results');
      
      resultsDiv.innerHTML = suppliers.map(supplier => `
        <div class="supplier-card">
          <img src="${supplier.logo || '/images/default-logo.png'}" alt="${supplier.company_name}">
          <h3>${supplier.company_name}</h3>
          <p>${supplier.country}</p>
          <p>Rating: ${supplier.rating} / 5.0</p>
          <span class="badge ${supplier.package.badge_color}">${supplier.package.name}</span>
          <a href="/buyer/supplier-details.html?id=${supplier.id}">View Details</a>
        </div>
      `).join('');
      
      // Pagination
      renderPagination(result.data);
    }
  }
  
  function renderPagination(data) {
    const paginationDiv = document.getElementById('pagination');
    let html = '';
    
    for (let i = 1; i <= data.last_page; i++) {
      html += `<button onclick="searchSuppliers(${i})" ${i === data.current_page ? 'class="active"' : ''}>${i}</button>`;
    }
    
    paginationDiv.innerHTML = html;
  }
  
  document.getElementById('searchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    searchSuppliers(1);
  });
  
  // Initial search
  searchSuppliers(1);
  </script>
</body>
</html>
\`\`\`

## File Upload Example

\`\`\`html
<!-- supplier/add-product.html -->
<form id="productForm">
  <input type="text" name="product_name" placeholder="Product Name" required>
  <input type="text" name="category" placeholder="Category" required>
  <textarea name="description" placeholder="Description" required></textarea>
  <input type="number" name="price" placeholder="Price" step="0.01" required>
  <input type="text" name="unit" placeholder="Unit (kg, ton, etc.)" required>
  <input type="number" name="min_order" placeholder="Minimum Order" required>
  <input type="file" name="images" multiple accept="image/*">
  <button type="submit">Add Product</button>
</form>

<script>
document.getElementById('productForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const formData = new FormData(this);
  const token = localStorage.getItem('token');
  
  try {
    const response = await fetch('http://your-domain.com/api/supplier/products', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      },
      body: formData // Don't set Content-Type for FormData
    });
    
    const result = await response.json();
    
    if (result.success) {
      alert('Product added successfully!');
      window.location.href = '/supplier/products.html';
    } else {
      alert('Failed to add product: ' + JSON.stringify(result.errors));
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Failed to add product');
  }
});
</script>
\`\`\`

## API Base URL Configuration

Create a config file for easy API URL management:

\`\`\`javascript
// js/config.js
const API_CONFIG = {
  // Change this to your actual API URL
  BASE_URL: 'http://your-domain.com/api',
  
  // Or use environment-based URLs
  // BASE_URL: window.location.hostname === 'localhost' 
  //   ? 'http://localhost:8000/api' 
  //   : 'https://api.vexim-global.com/api'
};

// Helper to build full API URLs
function apiUrl(endpoint) {
  return `${API_CONFIG.BASE_URL}${endpoint}`;
}
\`\`\`

Then use it in your code:

\`\`\`javascript
const result = await apiCall(apiUrl('/supplier/dashboard'));
\`\`\`

## Error Handling

\`\`\`javascript
// js/error-handler.js
function handleApiError(error, result) {
  if (result && result.errors) {
    // Validation errors
    const errorMessages = Object.values(result.errors).flat().join('\n');
    alert('Validation Error:\n' + errorMessages);
  } else if (result && result.message) {
    // General error message
    alert('Error: ' + result.message);
  } else {
    // Network or unknown error
    alert('An error occurred. Please try again.');
    console.error('Error:', error);
  }
}
\`\`\`

## Deployment on CPanel

### Steps to Deploy:

1. **Upload Laravel Files**
   - Upload all Laravel files to `/home/username/laravel-app`
   - Upload public folder contents to `/public_html` or `/public_html/api`

2. **Configure .htaccess**
   \`\`\`apache
   <IfModule mod_rewrite.c>
       RewriteEngine On
       RewriteRule ^(.*)$ public/$1 [L]
   </IfModule>
   \`\`\`

3. **Set Permissions**
   \`\`\`bash
   chmod -R 755 storage
   chmod -R 755 bootstrap/cache
   \`\`\`

4. **Configure Database**
   - Create MySQL database in CPanel
   - Update `.env` file with database credentials

5. **Run Migrations**
   - Use CPanel Terminal or SSH
   \`\`\`bash
   php artisan migrate
   \`\`\`

6. **Configure CORS** (if frontend is on different domain)
   \`\`\`php
   // config/cors.php
   'allowed_origins' => ['https://your-frontend-domain.com'],
   \`\`\`

This completes the frontend integration guide. Your HTML/CSS frontend can now communicate with the Laravel backend API!
