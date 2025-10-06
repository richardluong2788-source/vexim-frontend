# Frontend Deployment Guide - Vexim Global

## Overview
This guide explains how to deploy the HTML/CSS/JavaScript frontend to CPanel or shared hosting and connect it with your Laravel backend.

---

## Frontend Structure

\`\`\`
frontend/
├── index.html              # Home page
├── suppliers.html          # Supplier listing page
├── supplier-profile.html   # Supplier detail page
├── login.html             # Login page
├── register.html          # Registration page
├── dashboard.html         # Dashboard (to be created based on role)
├── css/
│   └── style.css          # Custom styles
├── js/
│   ├── config.js          # API configuration
│   ├── i18n.js            # Multi-language support
│   ├── main.js            # Homepage scripts
│   ├── auth.js            # Authentication scripts
│   ├── suppliers.js       # Supplier listing scripts
│   └── supplier-profile.js # Supplier profile scripts
└── assets/
    └── images/            # Images and logos
\`\`\`

---

## Step 1: Configure API Connection

### Edit `js/config.js`

Before uploading, update the API base URL to point to your Laravel backend:

\`\`\`javascript
const API_CONFIG = {
    // Change this to your Laravel backend URL
    BASE_URL: 'https://yourdomain.com/api',
    // For local development: 'http://localhost:8000/api'
    
    // ... rest of config
};
\`\`\`

**Important:** Make sure your Laravel backend has CORS enabled to allow requests from your frontend domain.

---

## Step 2: Upload to CPanel

### Method 1: File Manager

1. **Login to CPanel**
   - Go to your hosting CPanel (usually `yourdomain.com/cpanel`)
   - Enter your credentials

2. **Navigate to File Manager**
   - Click on "File Manager" icon
   - Navigate to `public_html` folder (or your domain's root folder)

3. **Upload Files**
   - Click "Upload" button
   - Select all frontend files and folders
   - Wait for upload to complete

4. **Extract (if uploaded as ZIP)**
   - If you uploaded a ZIP file, right-click and select "Extract"
   - Move extracted files to the correct location

### Method 2: FTP Client (FileZilla)

1. **Connect via FTP**
   - Host: `ftp.yourdomain.com`
   - Username: Your CPanel username
   - Password: Your CPanel password
   - Port: 21

2. **Upload Files**
   - Navigate to `public_html` on remote side
   - Drag and drop all frontend files from local to remote

---

## Step 3: Configure Laravel Backend CORS

Your Laravel backend needs to allow requests from your frontend domain.

### Install Laravel CORS Package (if not already installed)

\`\`\`bash
composer require fruitcake/laravel-cors
\`\`\`

### Configure CORS in `config/cors.php`

\`\`\`php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    
    'allowed_methods' => ['*'],
    
    'allowed_origins' => [
        'https://yourdomain.com',  // Your frontend domain
        'http://localhost:3000',   // For local development
    ],
    
    'allowed_origins_patterns' => [],
    
    'allowed_headers' => ['*'],
    
    'exposed_headers' => [],
    
    'max_age' => 0,
    
    'supports_credentials' => true,
];
\`\`\`

### Add CORS Middleware to `app/Http/Kernel.php`

\`\`\`php
protected $middleware = [
    // ... other middleware
    \Fruitcake\Cors\HandleCors::class,
];
\`\`\`

---

## Step 4: Test the Connection

### 1. Open Your Frontend
- Visit `https://yourdomain.com` in your browser
- Open browser Developer Tools (F12)
- Go to Console tab

### 2. Test API Connection
- Try to register a new user
- Check the Network tab in Developer Tools
- Look for API requests to your Laravel backend
- Verify responses are successful (200 status code)

### 3. Common Issues

**Issue: CORS Error**
\`\`\`
Access to fetch at 'https://api.yourdomain.com' from origin 'https://yourdomain.com' 
has been blocked by CORS policy
\`\`\`

**Solution:**
- Check Laravel CORS configuration
- Make sure your frontend domain is in `allowed_origins`
- Clear Laravel cache: `php artisan config:clear`

**Issue: 404 Not Found**
\`\`\`
GET https://yourdomain.com/api/suppliers 404 (Not Found)
\`\`\`

**Solution:**
- Verify API base URL in `js/config.js`
- Check Laravel routes are correctly defined
- Ensure Laravel is running and accessible

**Issue: 401 Unauthorized**
\`\`\`
GET https://yourdomain.com/api/profile 401 (Unauthorized)
\`\`\`

**Solution:**
- Check if auth token is being sent in headers
- Verify Laravel Sanctum is configured correctly
- Check token is stored in localStorage

---

## Step 5: Environment-Specific Configuration

### For Development (Local)

\`\`\`javascript
// js/config.js
const API_CONFIG = {
    BASE_URL: 'http://localhost:8000/api',
    // ...
};
\`\`\`

### For Production (Live Server)

\`\`\`javascript
// js/config.js
const API_CONFIG = {
    BASE_URL: 'https://api.yourdomain.com/api',
    // or 'https://yourdomain.com/api' if Laravel is in same domain
    // ...
};
\`\`\`

### Using Environment Detection

You can make the config automatically detect the environment:

\`\`\`javascript
// js/config.js
const API_CONFIG = {
    BASE_URL: window.location.hostname === 'localhost' 
        ? 'http://localhost:8000/api'
        : 'https://yourdomain.com/api',
    // ...
};
\`\`\`

---

## Step 6: SSL Certificate (HTTPS)

For production, you should use HTTPS for security.

### Free SSL with Let's Encrypt (CPanel)

1. **Login to CPanel**
2. **Find "SSL/TLS Status"** or "Let's Encrypt SSL"
3. **Select your domain**
4. **Click "Install"** or "Run AutoSSL"
5. **Wait for installation** (usually takes a few minutes)

### Update URLs to HTTPS

After SSL is installed:
- Update `BASE_URL` in `js/config.js` to use `https://`
- Update Laravel `.env` file: `APP_URL=https://yourdomain.com`

---

## Step 7: Performance Optimization

### 1. Enable Gzip Compression

Add to `.htaccess` in your frontend root:

\`\`\`apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>
