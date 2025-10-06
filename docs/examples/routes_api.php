<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\VerificationController;
use App\Http\Controllers\Admin\ContactRequestController as AdminContactRequest;
use App\Http\Controllers\Supplier\DashboardController as SupplierDashboard;
use App\Http\Controllers\Supplier\CompanyController;
use App\Http\Controllers\Supplier\ProductController;
use App\Http\Controllers\Supplier\CertificateController;
use App\Http\Controllers\Buyer\DashboardController as BuyerDashboard;
use App\Http\Controllers\Buyer\SearchController;
use App\Http\Controllers\Buyer\ContactRequestController as BuyerContactRequest;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ============================================
// PUBLIC ROUTES (No Authentication Required)
// ============================================

// Public supplier directory
Route::prefix('public')->group(function () {
    Route::get('/suppliers', [SearchController::class, 'publicSearch']);
    Route::get('/suppliers/{id}', [SearchController::class, 'publicProfile']);
});

// ============================================
// AUTHENTICATION ROUTES
// ============================================

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

// ============================================
// ADMIN ROUTES
// ============================================

Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminDashboard::class, 'index']);
    
    // User Management
    Route::get('/users', [AdminDashboard::class, 'getUsers']);
    Route::put('/users/{id}/toggle-status', [AdminDashboard::class, 'toggleUserStatus']);
    
    // Verification Management
    Route::get('/verifications/pending', [VerificationController::class, 'getPending']);
    Route::post('/verifications/{companyId}/review', [VerificationController::class, 'review']);
    
    // Contact Request Management
    Route::get('/contact-requests', [AdminContactRequest::class, 'index']);
    Route::post('/contact-requests/{id}/review', [AdminContactRequest::class, 'review']);
});

// ============================================
// SUPPLIER ROUTES
// ============================================

Route::prefix('supplier')->middleware(['auth:sanctum', 'role:supplier'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [SupplierDashboard::class, 'index']);
    
    // Company Profile
    Route::get('/company', [CompanyController::class, 'show']);
    Route::post('/company', [CompanyController::class, 'store']);
    Route::put('/company', [CompanyController::class, 'update']);
    Route::post('/company/logo', [CompanyController::class, 'uploadLogo']);
    Route::post('/company/cover', [CompanyController::class, 'uploadCover']);
    
    // Products
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    Route::post('/products/{id}/images', [ProductController::class, 'uploadImages']);
    
    // Certificates
    Route::get('/certificates', [CertificateController::class, 'index']);
    Route::post('/certificates', [CertificateController::class, 'store']);
    Route::delete('/certificates/{id}', [CertificateController::class, 'destroy']);
    
    // Contact Requests Received
    Route::get('/contact-requests', [SupplierDashboard::class, 'getContactRequests']);
    
    // Package Management
    Route::get('/packages', [CompanyController::class, 'getPackages']);
    Route::post('/package/upgrade', [CompanyController::class, 'upgradePackage']);
});

// ============================================
// BUYER ROUTES
// ============================================

Route::prefix('buyer')->middleware(['auth:sanctum', 'role:buyer'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [BuyerDashboard::class, 'index']);
    
    // Search Suppliers
    Route::get('/search', [SearchController::class, 'search']);
    Route::get('/suppliers/{id}', [SearchController::class, 'show']);
    
    // Contact Requests
    Route::get('/contact-requests', [BuyerContactRequest::class, 'index']);
    Route::post('/contact-requests', [BuyerContactRequest::class, 'store']);
    Route::get('/contact-requests/{id}', [BuyerContactRequest::class, 'show']);
});
