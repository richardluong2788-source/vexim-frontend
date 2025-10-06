# Vexim Global - API Endpoints Documentation

## Base URL
\`\`\`
http://localhost:8000/api
\`\`\`

## Authentication
All protected endpoints require Bearer token in header:
\`\`\`
Authorization: Bearer {token}
\`\`\`

---

## 1. AUTHENTICATION ENDPOINTS

### 1.1 Register
**POST** `/api/auth/register`

**Request Body:**
\`\`\`json
{
  "email": "supplier@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "full_name": "John Doe",
  "role": "supplier",
  "phone": "+84123456789",
  "country": "Vietnam"
}
\`\`\`

**Response (201):**
\`\`\`json
{
  "success": true,
  "message": "Registration successful",
  "data": {
    "user": {
      "id": 1,
      "email": "supplier@example.com",
      "role": "supplier",
      "full_name": "John Doe"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
  }
}
\`\`\`

### 1.2 Login
**POST** `/api/auth/login`

**Request Body:**
\`\`\`json
{
  "email": "supplier@example.com",
  "password": "password123"
}
\`\`\`

**Response (200):**
\`\`\`json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "email": "supplier@example.com",
      "role": "supplier",
      "full_name": "John Doe"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
  }
}
\`\`\`

### 1.3 Logout
**POST** `/api/auth/logout`

**Headers:** `Authorization: Bearer {token}`

**Response (200):**
\`\`\`json
{
  "success": true,
  "message": "Logged out successfully"
}
\`\`\`

---

## 2. ADMIN ENDPOINTS

### 2.1 Admin Dashboard Stats
**GET** `/api/admin/dashboard`

**Headers:** `Authorization: Bearer {admin_token}`

**Response (200):**
\`\`\`json
{
  "success": true,
  "data": {
    "total_suppliers": 150,
    "pending_verifications": 12,
    "total_buyers": 320,
    "pending_contact_requests": 8,
    "total_revenue": 45000.00,
    "recent_registrations": [...]
  }
}
\`\`\`

### 2.2 Get All Users
**GET** `/api/admin/users?role=supplier&page=1`

**Query Parameters:**
- `role`: Filter by role (supplier, buyer)
- `page`: Pagination
- `search`: Search by name or email

**Response (200):**
\`\`\`json
{
  "success": true,
  "data": {
    "users": [...],
    "pagination": {
      "current_page": 1,
      "total_pages": 5,
      "total_items": 50
    }
  }
}
\`\`\`

### 2.3 Get Pending Verifications
**GET** `/api/admin/verifications/pending`

**Response (200):**
\`\`\`json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "company_name": "ABC Manufacturing",
      "business_license": "BL123456",
      "submitted_at": "2025-01-15T10:30:00Z",
      "documents": [...]
    }
  ]
}
\`\`\`

### 2.4 Approve/Reject Verification
**POST** `/api/admin/verifications/{companyId}/review`

**Request Body:**
\`\`\`json
{
  "action": "approve",
  "notes": "All documents verified"
}
\`\`\`

**Response (200):**
\`\`\`json
{
  "success": true,
  "message": "Verification approved successfully",
  "data": {
    "verification_id": "VXM-2025-00001",
    "expiry_date": "2026-01-15"
  }
}
\`\`\`

### 2.5 Get Contact Requests
**GET** `/api/admin/contact-requests?status=pending`

**Response (200):**
\`\`\`json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "buyer_name": "Jane Smith",
      "buyer_email": "jane@example.com",
      "supplier_name": "ABC Manufacturing",
      "subject": "Product Inquiry",
      "message": "I'm interested in...",
      "created_at": "2025-01-20T14:30:00Z"
    }
  ]
}
\`\`\`

### 2.6 Approve/Reject Contact Request
**POST** `/api/admin/contact-requests/{id}/review`

**Request Body:**
\`\`\`json
{
  "action": "approve",
  "admin_notes": "Legitimate inquiry"
}
\`\`\`

**Response (200):**
\`\`\`json
{
  "success": true,
  "message": "Contact request approved. Email sent to supplier."
}
\`\`\`

---

## 3. SUPPLIER ENDPOINTS

### 3.1 Supplier Dashboard
**GET** `/api/supplier/dashboard`

**Headers:** `Authorization: Bearer {supplier_token}`

**Response (200):**
\`\`\`json
{
  "success": true,
  "data": {
    "company": {
      "id": 1,
      "company_name": "ABC Manufacturing",
      "verification_status": "verified",
      "verification_id": "VXM-2025-00001",
      "package": "Gold",
      "package_expiry": "2026-01-15"
    },
    "stats": {
      "total_products": 25,
      "profile_views": 1250,
      "contact_requests": 15
    }
  }
}
\`\`\`

### 3.2 Create/Update Company Profile
**POST** `/api/supplier/company`

**Request Body:**
\`\`\`json
{
  "company_name": "ABC Manufacturing Ltd.",
  "business_license": "BL123456",
  "tax_code": "TAX789",
  "email": "contact@abc.com",
  "phone": "+84123456789",
  "website": "https://abc.com",
  "address": "123 Industrial Zone",
  "city": "Ho Chi Minh City",
  "country": "Vietnam",
  "year_established": 2010,
  "employee_count": "100-200",
  "business_type": "manufacturer",
  "main_products": "Electronics, Components",
  "main_markets": "USA, Europe, Asia",
  "description": "Leading manufacturer of..."
}
\`\`\`

**Response (200):**
\`\`\`json
{
  "success": true,
  "message": "Company profile updated successfully",
  "data": {
    "company": {...}
  }
}
\`\`\`

### 3.3 Upload Company Logo
**POST** `/api/supplier/company/logo`

**Request:** `multipart/form-data`
- `logo`: File (max 2MB, jpg/png)

**Response (200):**
\`\`\`json
{
  "success": true,
  "message": "Logo uploaded successfully",
  "data": {
    "logo_url": "/uploads/logos/abc-logo.png"
  }
}
\`\`\`

### 3.4 Get All Products
**GET** `/api/supplier/products`

**Response (200):**
\`\`\`json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "product_name": "LED Display Module",
      "category": "Electronics",
      "price_range": "$10-$50",
      "is_active": true,
      "views": 450
    }
  ]
}
\`\`\`

### 3.5 Create Product
**POST** `/api/supplier/products`

**Request Body:**
\`\`\`json
{
  "product_name": "LED Display Module",
  "category": "Electronics",
  "subcategory": "LED Components",
  "description": "High-quality LED display...",
  "specifications": "Size: 10x10cm, Voltage: 5V",
  "min_order_quantity": "100 units",
  "price_range": "$10-$50",
  "unit": "piece",
  "lead_time": "15-30 days"
}
\`\`\`

**Response (201):**
\`\`\`json
{
  "success": true,
  "message": "Product created successfully",
  "data": {
    "product": {...}
  }
}
\`\`\`

### 3.6 Upload Product Images
**POST** `/api/supplier/products/{id}/images`

**Request:** `multipart/form-data`
- `images[]`: Multiple files (max 5 images, 2MB each)

**Response (200):**
\`\`\`json
{
  "success": true,
  "message": "Images uploaded successfully",
  "data": {
    "images": [
      "/uploads/products/product-1.jpg",
      "/uploads/products/product-2.jpg"
    ]
  }
}
\`\`\`

### 3.7 Add Certificate
**POST** `/api/supplier/certificates`

**Request:** `multipart/form-data`
- `certificate_name`: String
- `certificate_type`: String (ISO, CE, FDA, etc.)
- `issuing_authority`: String
- `issue_date`: Date
- `expiry_date`: Date
- `certificate_number`: String
- `file`: File (PDF, max 5MB)

**Response (201):**
\`\`\`json
{
  "success": true,
  "message": "Certificate uploaded successfully",
  "data": {
    "certificate": {...}
  }
}
\`\`\`

### 3.8 Get Contact Requests Received
**GET** `/api/supplier/contact-requests`

**Response (200):**
\`\`\`json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "buyer_name": "Jane Smith",
      "buyer_email": "j***e@example.com",
      "subject": "Product Inquiry",
      "message": "I'm interested in...",
      "status": "approved",
      "contact_unlocked": false,
      "created_at": "2025-01-20T14:30:00Z"
    }
  ]
}
\`\`\`

### 3.9 Upgrade Package
**POST** `/api/supplier/package/upgrade`

**Request Body:**
\`\`\`json
{
  "package_id": 3,
  "payment_method": "credit_card"
}
\`\`\`

**Response (200):**
\`\`\`json
{
  "success": true,
  "message": "Package upgraded successfully",
  "data": {
    "transaction_id": "TXN123456",
    "amount": 599.00,
    "package": "Gold"
  }
}
\`\`\`

---

## 4. BUYER ENDPOINTS

### 4.1 Buyer Dashboard
**GET** `/api/buyer/dashboard`

**Headers:** `Authorization: Bearer {buyer_token}`

**Response (200):**
\`\`\`json
{
  "success": true,
  "data": {
    "user": {...},
    "stats": {
      "total_contact_requests": 8,
      "pending_requests": 3,
      "approved_requests": 5
    },
    "recent_searches": [...]
  }
}
\`\`\`

### 4.2 Search Suppliers
**GET** `/api/buyer/search?keyword=electronics&country=Vietnam&page=1`

**Query Parameters:**
- `keyword`: Search term
- `country`: Filter by country
- `category`: Filter by product category
- `verified_only`: Boolean (true/false)
- `page`: Pagination

**Response (200):**
\`\`\`json
{
  "success": true,
  "data": {
    "suppliers": [
      {
        "id": 1,
        "company_name": "ABC Manufacturing",
        "verification_id": "VXM-2025-00001",
        "verification_status": "verified",
        "country": "Vietnam",
        "main_products": "Electronics, Components",
        "rating_average": 4.5,
        "logo_url": "/uploads/logos/abc-logo.png"
      }
    ],
    "pagination": {...}
  }
}
\`\`\`

### 4.3 Get Supplier Profile
**GET** `/api/buyer/suppliers/{id}`

**Response (200):**
\`\`\`json
{
  "success": true,
  "data": {
    "company": {
      "id": 1,
      "company_name": "ABC Manufacturing",
      "verification_id": "VXM-2025-00001",
      "email": "c***t@abc.com",
      "phone": "+84***6789",
      "website": "https://abc.com",
      "description": "...",
      "year_established": 2010,
      "main_products": "Electronics",
      "certificates": [...],
      "products": [...]
    }
  }
}
\`\`\`

### 4.4 Submit Contact Request
**POST** `/api/buyer/contact-requests`

**Request Body:**
\`\`\`json
{
  "supplier_id": 1,
  "company_id": 1,
  "subject": "Product Inquiry - LED Modules",
  "message": "I'm interested in purchasing LED display modules. Could you provide more details about pricing and MOQ?",
  "product_interest": "LED Display Module"
}
\`\`\`

**Response (201):**
\`\`\`json
{
  "success": true,
  "message": "Contact request submitted. Admin will review and forward to supplier.",
  "data": {
    "request_id": 1,
    "status": "pending"
  }
}
\`\`\`

### 4.5 Get My Contact Requests
**GET** `/api/buyer/contact-requests`

**Response (200):**
\`\`\`json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "supplier_name": "ABC Manufacturing",
      "subject": "Product Inquiry",
      "status": "approved",
      "created_at": "2025-01-20T14:30:00Z",
      "reviewed_at": "2025-01-20T16:00:00Z"
    }
  ]
}
\`\`\`

---

## 5. PUBLIC ENDPOINTS (No Authentication)

### 5.1 Get All Verified Suppliers
**GET** `/api/public/suppliers?page=1`

**Response (200):**
\`\`\`json
{
  "success": true,
  "data": {
    "suppliers": [...],
    "pagination": {...}
  }
}
\`\`\`

### 5.2 Get Supplier Public Profile
**GET** `/api/public/suppliers/{id}`

**Response (200):**
\`\`\`json
{
  "success": true,
  "data": {
    "company": {
      "company_name": "ABC Manufacturing",
      "verification_id": "VXM-2025-00001",
      "country": "Vietnam",
      "main_products": "Electronics",
      "description": "...",
      "products": [...],
      "certificates": [...]
    }
  }
}
\`\`\`

---

## Error Responses

### 400 Bad Request
\`\`\`json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."]
  }
}
\`\`\`

### 401 Unauthorized
\`\`\`json
{
  "success": false,
  "message": "Unauthorized. Please login."
}
\`\`\`

### 403 Forbidden
\`\`\`json
{
  "success": false,
  "message": "You do not have permission to access this resource."
}
\`\`\`

### 404 Not Found
\`\`\`json
{
  "success": false,
  "message": "Resource not found."
}
\`\`\`

### 500 Internal Server Error
\`\`\`json
{
  "success": false,
  "message": "An error occurred. Please try again later."
}
