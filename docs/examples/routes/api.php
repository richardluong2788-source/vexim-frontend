<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\SupplierController;
use App\Http\Controllers\API\BuyerController;
use App\Http\Controllers\API\AdminController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public supplier search (no auth required)
Route::get('/suppliers/search', [BuyerController::class, 'searchSuppliers']);
Route::get('/suppliers/{id}', [BuyerController::class, 'getSupplierDetails']);
Route::get('/products/search', [BuyerController::class, 'searchProducts']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    
    // Common auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);

    // Supplier routes
    Route::middleware('role:supplier')->prefix('supplier')->group(function () {
        Route::get('/dashboard', [SupplierController::class, 'dashboard']);
        Route::put('/company', [SupplierController::class, 'updateCompany']);
        
        // Products
        Route::get('/products', [SupplierController::class, 'getProducts']);
        Route::post('/products', [SupplierController::class, 'createProduct']);
        Route::put('/products/{id}', [SupplierController::class, 'updateProduct']);
        Route::delete('/products/{id}', [SupplierController::class, 'deleteProduct']);
        
        // Certificates
        Route::get('/certificates', [SupplierController::class, 'getCertificates']);
        Route::post('/certificates', [SupplierController::class, 'addCertificate']);
        
        // Export history
        Route::get('/export-history', [SupplierController::class, 'getExportHistory']);
        Route::post('/export-history', [SupplierController::class, 'addExportHistory']);
    });

    // Buyer routes
    Route::middleware('role:buyer')->prefix('buyer')->group(function () {
        Route::get('/dashboard', [BuyerController::class, 'dashboard']);
        Route::post('/contact-request', [BuyerController::class, 'sendContactRequest']);
        Route::get('/contact-history', [BuyerController::class, 'getContactHistory']);
        Route::post('/reviews', [BuyerController::class, 'submitReview']);
    });

    // Admin routes
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        
        // User management
        Route::get('/users', [AdminController::class, 'getUsers']);
        Route::put('/users/{id}/status', [AdminController::class, 'updateUserStatus']);
        
        // Company management
        Route::get('/companies', [AdminController::class, 'getCompanies']);
        Route::get('/companies/{id}', [AdminController::class, 'getCompanyDetails']);
        Route::put('/companies/{id}/verify', [AdminController::class, 'verifyCompany']);
        
        // Message management
        Route::get('/messages/pending', [AdminController::class, 'getPendingMessages']);
        Route::put('/messages/{id}/review', [AdminController::class, 'reviewContactRequest']);
        
        // Review management
        Route::get('/reviews/pending', [AdminController::class, 'getPendingReviews']);
        Route::put('/reviews/{id}', [AdminController::class, 'reviewReview']);
        
        // Transaction management
        Route::get('/transactions', [AdminController::class, 'getTransactions']);
        
        // Package management
        Route::get('/packages', [AdminController::class, 'getPackages']);
        Route::put('/packages/{id}', [AdminController::class, 'updatePackage']);
    });
});
