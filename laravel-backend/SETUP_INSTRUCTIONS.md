# Vexim Global - Laravel Backend Setup Instructions

## System Requirements

- **PHP**: 8.1 or higher
- **MySQL**: 5.7 or higher
- **Composer**: Latest version
- **Extensions**: OpenSSL, PDO, Mbstring, Tokenizer, XML, Ctype, JSON, BCMath, Fileinfo

## Local Development Setup

### Step 1: Install Dependencies

\`\`\`bash
cd laravel-backend
composer install
\`\`\`

### Step 2: Environment Configuration

\`\`\`bash
# Copy the example environment file
cp .env.example .env

# Generate application key
php artisan key:generate
\`\`\`

### Step 3: Configure Database

Edit `.env` file with your database credentials:

\`\`\`env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vexim_global
DB_USERNAME=root
DB_PASSWORD=your_password
\`\`\`

### Step 4: Run Migrations and Seeders

\`\`\`bash
# Run migrations to create tables
php artisan migrate

# Seed the database with default data
php artisan db:seed
\`\`\`

This will create:
- Admin user: `admin@vexim.com` / `admin123`
- 4 packages: Free, Silver, Gold, Premium

### Step 5: Create Storage Link

\`\`\`bash
php artisan storage:link
\`\`\`

This creates a symbolic link from `public/storage` to `storage/app/public` for file uploads.

### Step 6: Start Development Server

\`\`\`bash
php artisan serve
\`\`\`

The API will be available at: `http://localhost:8000`

### Step 7: Test API

Test the API is working:

\`\`\`bash
curl http://localhost:8000/api/health
\`\`\`

Expected response:
\`\`\`json
{
  "status": "ok",
  "message": "API is running"
}
\`\`\`

## cPanel Deployment

### Step 1: Prepare Files

1. Compress the entire `laravel-backend` folder into a ZIP file
2. Upload to cPanel File Manager
3. Extract in your desired location (e.g., `/home/username/vexim-api`)

### Step 2: Install Dependencies

Via cPanel Terminal or SSH:

\`\`\`bash
cd /home/username/vexim-api
composer install --optimize-autoloader --no-dev
\`\`\`

### Step 3: Configure Environment

\`\`\`bash
cp .env.example .env
php artisan key:generate
\`\`\`

Edit `.env` with cPanel database credentials:

\`\`\`env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_cpanel_database
DB_USERNAME=your_cpanel_user
DB_PASSWORD=your_cpanel_password
\`\`\`

### Step 4: Set Up Database

\`\`\`bash
php artisan migrate --force
php artisan db:seed --force
\`\`\`

### Step 5: Set Permissions

\`\`\`bash
chmod -R 755 storage bootstrap/cache
chown -R username:username storage bootstrap/cache
\`\`\`

### Step 6: Configure Domain/Subdomain

**Option A: Subdomain (Recommended)**

1. Create subdomain in cPanel: `api.yourdomain.com`
2. Point document root to: `/home/username/vexim-api/public`

**Option B: Subfolder**

1. Create `.htaccess` in public_html:

\`\`\`apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^api/(.*)$ /vexim-api/public/$1 [L]
</IfModule>
