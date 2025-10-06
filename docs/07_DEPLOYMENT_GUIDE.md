# Deployment Guide for CPanel / Shared Hosting

## Prerequisites

- CPanel hosting account with:
  - PHP 8.1 or higher
  - MySQL 5.7 or higher
  - Composer access (via SSH or CPanel Terminal)
  - SSL certificate (recommended)

## Step-by-Step Deployment

### 1. Prepare Your Laravel Project

**On your local machine:**

\`\`\`bash
# Install dependencies
composer install --optimize-autoloader --no-dev

# Generate production .env
cp .env.example .env

# Generate application key
php artisan key:generate

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
\`\`\`

### 2. Upload Files to CPanel

**Option A: Using File Manager**

1. Compress your Laravel project into a ZIP file
2. Login to CPanel → File Manager
3. Navigate to your home directory (e.g., `/home/username/`)
4. Upload the ZIP file
5. Extract it to create `/home/username/vexim-laravel/`

**Option B: Using FTP/SFTP**

1. Connect via FileZilla or similar FTP client
2. Upload entire Laravel project to `/home/username/vexim-laravel/`

**Important:** Do NOT upload to `public_html` directly!

### 3. Configure Public Directory

**Move public folder contents:**

1. Copy all files from `/home/username/vexim-laravel/public/` 
2. Paste into `/home/username/public_html/` (or subdirectory like `/public_html/api/`)

**Update index.php:**

Edit `/public_html/index.php` (or `/public_html/api/index.php`):

\`\`\`php
<?php

// Change these paths to point to your Laravel installation
require __DIR__.'/../vexim-laravel/vendor/autoload.php';
$app = require_once __DIR__.'/../vexim-laravel/bootstrap/app.php';

// ... rest of the file remains the same
\`\`\`

### 4. Create .htaccess File

Create `/public_html/.htaccess` (or `/public_html/api/.htaccess`):

\`\`\`apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Redirect to HTTPS
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    
    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
    
    # Redirect Trailing Slashes
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]
    
    # Handle Front Controller
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
