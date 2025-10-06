# Vexim Global - Laravel Project Structure

## Overview
This document outlines the complete folder structure for the Laravel backend.

## Directory Structure

\`\`\`
vexim-backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   ├── LoginController.php
│   │   │   │   ├── RegisterController.php
│   │   │   │   └── LogoutController.php
│   │   │   ├── Admin/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── UserManagementController.php
│   │   │   │   ├── VerificationController.php
│   │   │   │   └── ContactRequestController.php
│   │   │   ├── Supplier/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── CompanyController.php
│   │   │   │   ├── ProductController.php
│   │   │   │   ├── CertificateController.php
│   │   │   │   └── PackageController.php
│   │   │   └── Buyer/
│   │   │       ├── DashboardController.php
│   │   │       ├── SearchController.php
│   │   │       └── ContactRequestController.php
│   │   ├── Middleware/
│   │   │   ├── RoleMiddleware.php
│   │   │   └── VerifiedSupplierMiddleware.php
│   │   └── Requests/
│   │       ├── CompanyRequest.php
│   │       ├── ProductRequest.php
│   │       └── ContactRequestRequest.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Company.php
│   │   ├── Product.php
│   │   ├── Certificate.php
│   │   ├── ContactRequest.php
│   │   ├── Transaction.php
│   │   ├── Package.php
│   │   └── ExportHistory.php
│   ├── Mail/
│   │   ├── ContactRequestNotification.php
│   │   ├── VerificationApproved.php
│   │   └── ContactRequestApproved.php
│   └── Services/
│       ├── VerificationService.php
│       ├── ContactRequestService.php
│       └── PaymentService.php
├── config/
│   ├── database.php
│   ├── mail.php
│   └── services.php
├── database/
│   ├── migrations/
│   └── seeders/
├── routes/
│   ├── web.php
│   └── api.php
├── resources/
│   └── views/
│       └── emails/
├── public/
│   ├── uploads/
│   │   ├── logos/
│   │   ├── certificates/
│   │   └── products/
│   └── index.php
├── .env
├── .env.example
├── composer.json
└── artisan
\`\`\`

## Key Directories Explained

### 1. **app/Http/Controllers/**
Contains all controller logic organized by user role:
- **Auth/**: Login, registration, logout
- **Admin/**: Admin dashboard and management functions
- **Supplier/**: Supplier profile and product management
- **Buyer/**: Buyer search and contact functions

### 2. **app/Models/**
Eloquent models representing database tables with relationships

### 3. **app/Mail/**
Email templates for notifications (contact requests, verification, etc.)

### 4. **app/Services/**
Business logic separated from controllers for reusability

### 5. **routes/**
- **api.php**: RESTful API endpoints for frontend
- **web.php**: Web routes (if using Laravel views)

### 6. **public/uploads/**
File storage for logos, certificates, and product images

## Installation Steps

1. **Clone/Create Laravel Project**
\`\`\`bash
composer create-project laravel/laravel vexim-backend
cd vexim-backend
\`\`\`

2. **Configure Environment**
\`\`\`bash
cp .env.example .env
php artisan key:generate
\`\`\`

3. **Update .env File**
\`\`\`env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vexim_global
DB_USERNAME=root
DB_PASSWORD=your_password

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@vexim.com
MAIL_FROM_NAME="Vexim Global"
\`\`\`

4. **Run Database Migrations**
\`\`\`bash
# Import the SQL files from scripts/ folder
mysql -u root -p vexim_global < scripts/01_create_database_schema.sql
mysql -u root -p vexim_global < scripts/02_insert_default_data.sql
\`\`\`

5. **Install Dependencies**
\`\`\`bash
composer install
\`\`\`

6. **Start Development Server**
\`\`\`bash
php artisan serve
\`\`\`

Your Laravel backend will be available at `http://localhost:8000`
