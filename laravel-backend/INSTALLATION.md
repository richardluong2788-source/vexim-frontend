# Vexim Global B2B Directory - Laravel Backend Installation Guide

## Prerequisites

- PHP 8.1 or higher
- Composer
- MySQL 5.7 or higher
- Apache/Nginx web server

## Installation Steps

### 1. Create Laravel Project Base

If you don't have Laravel installed, first install it:

\`\`\`bash
composer create-project laravel/laravel vexim-backend
cd vexim-backend
\`\`\`

### 2. Copy Backend Files

Copy all files from the `laravel-backend` folder to your Laravel project:

- Copy `app/Models/*` to your Laravel `app/Models/` directory
- Copy `app/Http/Controllers/*` to your Laravel `app/Http/Controllers/` directory
- Copy `app/Http/Middleware/RoleMiddleware.php` to your Laravel `app/Http/Middleware/` directory
- Copy `routes/api.php` to your Laravel `routes/` directory
- Copy `composer.json` (merge dependencies if needed)
- Copy `.env.example` to `.env`

### 3. Install Dependencies

\`\`\`bash
composer install
\`\`\`

### 4. Configure Environment

Edit your `.env` file:

\`\`\`env
APP_NAME="Vexim Global"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vexim_b2b
DB_USERNAME=your_username
DB_PASSWORD=your_password

FRONTEND_URL=http://localhost:8080
\`\`\`

### 5. Generate Application Key

\`\`\`bash
php artisan key:generate
\`\`\`

### 6. Create Database

Create a MySQL database named `vexim_b2b`:

\`\`\`sql
CREATE DATABASE vexim_b2b CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
\`\`\`

### 7. Run SQL Schema

Import the SQL schema files from the `scripts` folder:

\`\`\`bash
mysql -u your_username -p vexim_b2b < scripts/01_create_database_schema.sql
mysql -u your_username -p vexim_b2b < scripts/02_insert_default_data.sql
\`\`\`

Or use a MySQL client like phpMyAdmin to import the SQL files.

### 8. Configure Laravel Sanctum

Publish Sanctum configuration:

\`\`\`bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
\`\`\`

### 9. Register Middleware

Edit `app/Http/Kernel.php` and add the RoleMiddleware:

\`\`\`php
protected $middlewareAliases = [
    // ... existing middleware
    'role' => \App\Http\Middleware\RoleMiddleware::class,
];
\`\`\`

### 10. Configure CORS

Edit `config/cors.php`:

\`\`\`php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:8080')],
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
'supports_credentials' => true,
\`\`\`

### 11. Create Storage Link

\`\`\`bash
php artisan storage:link
\`\`\`

This creates a symbolic link from `public/storage` to `storage/app/public` for file uploads.

### 12. Set Permissions (Linux/Mac)

\`\`\`bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
\`\`\`

### 13. Start Development Server

\`\`\`bash
php artisan serve
\`\`\`

Your API will be available at `http://localhost:8000`

## Testing the API

### Test Registration

\`\`\`bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Buyer",
    "email": "buyer@test.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "buyer"
  }'
\`\`\`

### Test Login

\`\`\`bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@vexim.com",
    "password": "admin123"
  }'
\`\`\`

## Deployment to CPanel

### 1. Prepare Files

- Zip your entire Laravel project
- Upload to CPanel File Manager

### 2. Extract Files

- Extract the zip file in your home directory (not public_html)
- Move only the `public` folder contents to `public_html`

### 3. Update Index.php

Edit `public_html/index.php`:

\`\`\`php
require __DIR__.'/../vexim-backend/bootstrap/app.php';
\`\`\`

### 4. Create Database

- Use CPanel MySQL Database Wizard
- Create database, user, and grant privileges
- Import SQL schema files

### 5. Configure .env

Update `.env` with production settings:

\`\`\`env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_HOST=localhost
DB_DATABASE=your_cpanel_db_name
DB_USERNAME=your_cpanel_db_user
DB_PASSWORD=your_cpanel_db_password
\`\`\`

### 6. Set Permissions

\`\`\`bash
chmod -R 755 storage bootstrap/cache
\`\`\`

### 7. Configure .htaccess

Ensure `public_html/.htaccess` has:

\`\`\`apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
\`\`\`

## Additional Configuration

### Step 10: Configure Payment Webhooks

#### Stripe Webhook:
1. Go to Stripe Dashboard → Developers → Webhooks
2. Add endpoint: `https://yourdomain.com/api/payments/callback`
3. Select events: `payment_intent.succeeded`, `payment_intent.payment_failed`
4. Copy webhook secret to `.env` as `STRIPE_WEBHOOK_SECRET`

#### PayPal Webhook:
1. Go to PayPal Developer Dashboard → Webhooks
2. Add webhook: `https://yourdomain.com/api/payments/callback`
3. Select events: `PAYMENT.CAPTURE.COMPLETED`, `PAYMENT.CAPTURE.DENIED`
4. Copy webhook ID to `.env` as `PAYPAL_WEBHOOK_ID`

### Step 11: Setup Queue Worker

The application uses queues for sending emails and processing background jobs.

#### Development (Local):

Run the queue worker in a separate terminal:

\`\`\`bash
php artisan queue:work --tries=3 --timeout=90
\`\`\`

#### Production (cPanel):

**Option 1: Using Supervisor (Recommended)**

If your hosting supports Supervisor, create a configuration file:

\`\`\`ini
[program:vexim-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/username/vexim-backend/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=username
numprocs=2
redirect_stderr=true
stdout_logfile=/home/username/vexim-backend/storage/logs/worker.log
stopwaitsecs=3600
\`\`\`

**Option 2: Using Cron Job**

Add this cron job in cPanel → Cron Jobs:

\`\`\`bash
* * * * * cd /home/username/vexim-backend && php artisan schedule:run >> /dev/null 2>&1
\`\`\`

Then add this to `app/Console/Kernel.php`:

\`\`\`php
protected function schedule(Schedule $schedule)
{
    $schedule->command('queue:work --stop-when-empty')
             ->everyMinute()
             ->withoutOverlapping();
}
\`\`\`

**Option 3: Manual Queue Processing**

If neither option works, you can process queued jobs manually:

\`\`\`bash
php artisan queue:work --once
\`\`\`

Add this as a cron job that runs every minute.

#### Create Failed Jobs Table:

\`\`\`bash
php artisan queue:failed-table
php artisan migrate
\`\`\`

#### Monitor Queue:

\`\`\`bash
# Check queue status
php artisan queue:monitor

# Retry failed jobs
php artisan queue:retry all

# Clear failed jobs
php artisan queue:flush
\`\`\`

### Step 12: SSL Configuration

**Important:** Always use HTTPS in production for security.

1. Install SSL certificate in cPanel → SSL/TLS
2. Update `.env`:
   \`\`\`env
   APP_URL=https://yourdomain.com
   SESSION_SECURE_COOKIE=true
   \`\`\`
3. Force HTTPS in `app/Providers/AppServiceProvider.php`:
   \`\`\`php
   public function boot()
   {
       if ($this->app->environment('production')) {
           \URL::forceScheme('https');
       }
   }
   \`\`\`

### Step 13: API Endpoint Testing

Test all critical endpoints:

\`\`\`bash
# Health check
curl https://yourdomain.com/api/health

# Get packages
curl https://yourdomain.com/api/payments/packages

# Test webhook (with proper signature)
curl -X POST https://yourdomain.com/api/payments/callback \
  -H "Content-Type: application/json" \
  -H "Stripe-Signature: your_signature" \
  -d '{"transaction_id":"TEST123","status":"completed"}'
\`\`\`

### Step 14: Performance Optimization

\`\`\`bash
# Cache everything
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Enable OPcache in cPanel → PHP Settings
# Enable Redis/Memcached if available
\`\`\`

## Security Checklist

- [ ] APP_DEBUG=false in production
- [ ] Strong APP_KEY generated
- [ ] Database credentials secure
- [ ] File permissions correct (755/775)
- [ ] SSL certificate installed and forced
- [ ] Payment webhook secrets configured
- [ ] reCAPTCHA keys configured
- [ ] Queue worker running
- [ ] Failed jobs table created
- [ ] Regular backups enabled in cPanel
- [ ] Sensitive data encrypted (buyer emails/phones)
- [ ] Rate limiting enabled on contact forms
- [ ] CORS properly configured
- [ ] Session cookies secure (HTTPS only)

## Monitoring & Maintenance

**Check Logs Regularly:**

\`\`\`bash
# Application logs
tail -f storage/logs/laravel.log

# Queue worker logs
tail -f storage/logs/worker.log

# Failed jobs
php artisan queue:failed
\`\`\`

**Database Backups:**

Set up automatic daily backups in cPanel → Backup Wizard

**Performance Monitoring:**

- Monitor queue length: `php artisan queue:monitor`
- Check failed jobs: `php artisan queue:failed`
- Review audit logs in admin panel

## Troubleshooting

**Queue Jobs Not Processing:**
- Verify queue worker is running: `ps aux | grep queue:work`
- Check queue connection in `.env`: `QUEUE_CONNECTION=database`
- Verify jobs table exists: `php artisan queue:table` then `php artisan migrate`
- Check failed jobs: `php artisan queue:failed`
- Manually process: `php artisan queue:work --once`
- Check worker logs in `storage/logs/`

**Webhook Verification Failing:**
- Verify webhook secrets are correct in `.env`
- Check webhook URL is publicly accessible (not localhost)
- Ensure SSL is properly configured
- Check webhook logs in payment gateway dashboard
- Test with webhook testing tools (Stripe CLI, PayPal Sandbox)

**Email Not Sending:**
- Verify MAIL_* settings in `.env`
- Check queue is processing: `php artisan queue:work`
- Test with: `php artisan tinker` then `Mail::raw('Test', function($m) { $m->to('test@example.com')->subject('Test'); });`
- Check cPanel email logs
- Verify SMTP credentials and ports
