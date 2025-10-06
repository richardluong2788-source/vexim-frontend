# Hướng Dẫn Triển Khai Vexim Platform Lên cPanel

## Tổng Quan

Dự án Vexim bao gồm 2 phần:
1. **Backend Laravel** (PHP/MySQL) - Deploy lên cPanel
2. **Frontend Next.js** (React) - Deploy lên Vercel (khuyến nghị) hoặc cPanel với Node.js

---

## PHẦN 1: TRIỂN KHAI BACKEND LARAVEL LÊN CPANEL

### Bước 1: Chuẩn Bị File Backend

1. **Nén thư mục Laravel backend** thành file ZIP:
   \`\`\`
   laravel-backend.zip
   \`\`\`

2. **Các file cần có trong Laravel backend:**
   - app/
   - bootstrap/
   - config/
   - database/
   - public/
   - resources/
   - routes/
   - storage/
   - vendor/ (nếu đã chạy composer install)
   - .env.example
   - composer.json
   - artisan

### Bước 2: Upload File Lên cPanel

1. **Đăng nhập vào cPanel** của bạn
2. Mở **File Manager**
3. Điều hướng đến thư mục gốc (thường là `/home/username/`)
4. **Tạo thư mục mới** tên `laravel-backend` (hoặc tên bạn muốn)
5. Vào thư mục vừa tạo
6. Click **Upload** và upload file `laravel-backend.zip`
7. Sau khi upload xong, click chuột phải vào file ZIP và chọn **Extract**

### Bước 3: Tạo Database MySQL

1. Trong cPanel, mở **MySQL Databases**
2. **Tạo database mới:**
   - Database Name: `vexim_db` (hoặc tên bạn muốn)
   - Click "Create Database"

3. **Tạo MySQL User:**
   - Username: `vexim_user`
   - Password: (tạo password mạnh)
   - Click "Create User"

4. **Gán quyền cho User:**
   - Chọn user vừa tạo
   - Chọn database vừa tạo
   - Tick "ALL PRIVILEGES"
   - Click "Make Changes"

5. **Ghi chú thông tin:**
   \`\`\`
   DB_HOST=localhost
   DB_DATABASE=vexim_db
   DB_USERNAME=vexim_user
   DB_PASSWORD=your_password
   \`\`\`

### Bước 4: Cấu Hình Laravel

1. **Tạo file .env:**
   - Trong File Manager, vào thư mục `laravel-backend`
   - Copy file `.env.example` thành `.env`
   - Click chuột phải vào `.env` và chọn **Edit**

2. **Cập nhật thông tin trong .env:**
   \`\`\`env
   APP_NAME=Vexim
   APP_ENV=production
   APP_KEY=
   APP_DEBUG=false
   APP_URL=https://yourdomain.com

   LOG_CHANNEL=stack
   LOG_LEVEL=error

   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=vexim_db
   DB_USERNAME=vexim_user
   DB_PASSWORD=your_password_here

   BROADCAST_DRIVER=log
   CACHE_DRIVER=file
   FILESYSTEM_DISK=local
   QUEUE_CONNECTION=sync
   SESSION_DRIVER=file
   SESSION_LIFETIME=120

   # CORS Settings
   SANCTUM_STATEFUL_DOMAINS=yourdomain.com,www.yourdomain.com
   SESSION_DOMAIN=.yourdomain.com
   \`\`\`

3. **Lưu file .env**

### Bước 5: Cài Đặt Dependencies (Qua Terminal SSH)

**Nếu cPanel có Terminal/SSH:**

1. Mở **Terminal** trong cPanel
2. Chạy các lệnh sau:

\`\`\`bash
# Di chuyển vào thư mục Laravel
cd laravel-backend

# Cài đặt Composer dependencies (nếu chưa có vendor/)
composer install --optimize-autoloader --no-dev

# Generate application key
php artisan key:generate

# Tạo symbolic link cho storage
php artisan storage:link

# Chạy migrations
php artisan migrate --force

# Chạy seeders (nếu có)
php artisan db:seed --force

# Clear và cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache
\`\`\`

**Nếu KHÔNG có Terminal/SSH:**
- Upload thư mục `vendor/` đã được cài đặt sẵn từ máy local
- Sử dụng phpMyAdmin để import file SQL thay vì chạy migrations

### Bước 6: Cấu Hình Public Directory

Laravel cần trỏ document root đến thư mục `public/`:

**Cách 1: Sử dụng .htaccess (Nếu domain chính)**

1. Trong File Manager, vào thư mục `public_html`
2. Tạo file `.htaccess` với nội dung:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ /laravel-backend/public/$1 [L]
</IfModule>
