-- ============================================
-- VEXIM GLOBAL - DATABASE SCHEMA
-- MySQL Database for B2B Supplier Directory
-- ============================================

-- Drop existing tables if they exist (for clean setup)
DROP TABLE IF EXISTS contact_requests;
DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS export_history;
DROP TABLE IF EXISTS certificates;
DROP TABLE IF EXISTS companies;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS packages;

-- ============================================
-- 1. PACKAGES TABLE
-- Service tiers: Free, Silver, Gold, Premium
-- ============================================
CREATE TABLE packages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    duration_months INT NOT NULL DEFAULT 12,
    max_products INT DEFAULT NULL, -- NULL = unlimited
    max_certificates INT DEFAULT NULL,
    featured_listing BOOLEAN DEFAULT FALSE,
    priority_support BOOLEAN DEFAULT FALSE,
    analytics_access BOOLEAN DEFAULT FALSE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. USERS TABLE
-- Three roles: admin, supplier, buyer
-- ============================================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'supplier', 'buyer') NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    country VARCHAR(100),
    language ENUM('en', 'vi') DEFAULT 'en',
    is_active BOOLEAN DEFAULT TRUE,
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. COMPANIES TABLE
-- Supplier company profiles
-- ============================================
CREATE TABLE companies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    package_id INT NOT NULL DEFAULT 1, -- Default to Free package
    
    -- Basic Information
    company_name VARCHAR(255) NOT NULL,
    business_license VARCHAR(100) UNIQUE,
    tax_code VARCHAR(100),
    
    -- Verification
    verification_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    verification_id VARCHAR(50) UNIQUE, -- Format: VXM-YYYY-XXXXX
    verified_at TIMESTAMP NULL,
    verification_expiry TIMESTAMP NULL,
    
    -- Contact Information (Masked)
    email VARCHAR(255),
    phone VARCHAR(50),
    website VARCHAR(255),
    address TEXT,
    city VARCHAR(100),
    country VARCHAR(100),
    
    -- Business Details
    year_established INT,
    employee_count VARCHAR(50),
    annual_revenue VARCHAR(100),
    main_products TEXT,
    main_markets TEXT,
    business_type ENUM('manufacturer', 'trading_company', 'distributor', 'service_provider'),
    
    -- Description
    description TEXT,
    logo_url VARCHAR(255),
    cover_image_url VARCHAR(255),
    
    -- Package & Subscription
    package_start_date TIMESTAMP NULL,
    package_expiry_date TIMESTAMP NULL,
    
    -- Statistics
    total_products INT DEFAULT 0,
    total_certificates INT DEFAULT 0,
    profile_views INT DEFAULT 0,
    contact_requests_received INT DEFAULT 0,
    rating_average DECIMAL(3, 2) DEFAULT 0.00,
    rating_count INT DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES packages(id),
    INDEX idx_verification_status (verification_status),
    INDEX idx_verification_id (verification_id),
    INDEX idx_country (country)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. CERTIFICATES TABLE
-- Company certifications and documents
-- ============================================
CREATE TABLE certificates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    certificate_name VARCHAR(255) NOT NULL,
    certificate_type VARCHAR(100), -- ISO, CE, FDA, etc.
    issuing_authority VARCHAR(255),
    issue_date DATE,
    expiry_date DATE,
    certificate_number VARCHAR(100),
    file_url VARCHAR(255),
    verification_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    INDEX idx_company_id (company_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. PRODUCTS TABLE
-- Supplier product listings
-- ============================================
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    subcategory VARCHAR(100),
    description TEXT,
    specifications TEXT,
    min_order_quantity VARCHAR(100),
    price_range VARCHAR(100),
    unit VARCHAR(50),
    lead_time VARCHAR(100),
    
    -- Images (JSON array of URLs)
    images JSON,
    
    -- SEO & Visibility
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    views INT DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    INDEX idx_company_id (company_id),
    INDEX idx_category (category),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. EXPORT_HISTORY TABLE
-- Company export records for charts
-- ============================================
CREATE TABLE export_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    year INT NOT NULL,
    month INT NOT NULL,
    export_value DECIMAL(15, 2) NOT NULL,
    destination_country VARCHAR(100),
    product_category VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    INDEX idx_company_year (company_id, year),
    UNIQUE KEY unique_company_month (company_id, year, month, destination_country)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 7. CONTACT_REQUESTS TABLE
-- Admin-mediated contact system
-- ============================================
CREATE TABLE contact_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    buyer_id INT NOT NULL,
    supplier_id INT NOT NULL,
    company_id INT NOT NULL,
    
    -- Request Details
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    product_interest VARCHAR(255),
    
    -- Status Flow: pending -> approved/rejected -> contacted
    status ENUM('pending', 'approved', 'rejected', 'contacted') DEFAULT 'pending',
    
    -- Admin Actions
    admin_id INT,
    admin_notes TEXT,
    reviewed_at TIMESTAMP NULL,
    
    -- Contact Unlock (if premium feature)
    contact_unlocked BOOLEAN DEFAULT FALSE,
    unlock_fee DECIMAL(10, 2) DEFAULT 0.00,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_buyer_id (buyer_id),
    INDEX idx_supplier_id (supplier_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 8. TRANSACTIONS TABLE
-- Payment records for verification & packages
-- ============================================
CREATE TABLE transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    company_id INT,
    
    -- Transaction Details
    transaction_type ENUM('verification_fee', 'package_upgrade', 'package_renewal', 'contact_unlock') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'USD',
    
    -- Payment Status
    payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    payment_gateway VARCHAR(50),
    transaction_id VARCHAR(255) UNIQUE,
    
    -- Related Data
    package_id INT,
    description TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_payment_status (payment_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
