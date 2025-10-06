<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\BuyerController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\SupportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public supplier/product search (no auth required)
Route::get('/suppliers/search', [BuyerController::class, 'searchSuppliers']);
Route::get('/suppliers/{id}', [BuyerController::class, 'getSupplier']);
Route::get('/products/search', [BuyerController::class, 'searchProducts']);
Route::get('/suppliers/featured', [BuyerController::class, 'getFeaturedSuppliers']);

// Payment webhook route (must be outside auth middleware)
Route::post('/payments/callback', [PaymentController::class, 'handleCallback']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    
    // Common auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);

    // Supplier routes
    Route::middleware('role:supplier')->prefix('supplier')->group(function () {
        Route::get('/dashboard', [SupplierController::class, 'dashboard']);
        Route::put('/company', [SupplierController::class, 'updateCompany']);
        Route::post('/company/logo', [SupplierController::class, 'uploadLogo']);
        
        Route::get('/products', [SupplierController::class, 'getProducts']);
        Route::post('/products', [SupplierController::class, 'createProduct']);
        Route::put('/products/{id}', [SupplierController::class, 'updateProduct']);
        Route::delete('/products/{id}', [SupplierController::class, 'deleteProduct']);
        
        Route::get('/certificates', [SupplierController::class, 'getCertificates']);
        Route::post('/certificates', [SupplierController::class, 'uploadCertificate']);
        
        Route::get('/export-history', [SupplierController::class, 'getExportHistory']);
        Route::post('/export-history', [SupplierController::class, 'addExportHistory']);
    });

    // Buyer routes
    Route::middleware('role:buyer')->prefix('buyer')->group(function () {
        Route::get('/dashboard', [BuyerController::class, 'dashboard']);
        Route::post('/contact-request', [BuyerController::class, 'sendContactRequest']);
        Route::get('/messages', [BuyerController::class, 'getMessages']);
        Route::post('/reviews', [BuyerController::class, 'submitReview']);
    });

    // Payment routes
    Route::prefix('payments')->group(function () {
        Route::get('/packages', [PaymentController::class, 'getPackages']);
        Route::post('/initiate', [PaymentController::class, 'initiatePayment']);
        Route::get('/history', [PaymentController::class, 'getPaymentHistory']);
        Route::get('/status', [PaymentController::class, 'checkPackageStatus']);
    });

    // Verification routes
    Route::prefix('verification')->group(function () {
        Route::post('/documents', [VerificationController::class, 'uploadDocument']);
        Route::get('/documents', [VerificationController::class, 'getDocuments']);
        Route::delete('/documents/{id}', [VerificationController::class, 'deleteDocument']);
    });

    // Contact routes
    Route::prefix('contacts')->group(function () {
        Route::post('/', [ContactController::class, 'requestContact']);
        Route::get('/supplier', [ContactController::class, 'getSupplierContacts']);
        Route::get('/buyer', [ContactController::class, 'getBuyerContacts']);
        Route::post('/{id}/respond', [ContactController::class, 'respondToContact']);
        Route::get('/masked/{companyId}', [ContactController::class, 'getMaskedContact']);
        Route::post('/toggle-visibility', [ContactController::class, 'toggleContactVisibility']);
        Route::get('/limit-status', [ContactController::class, 'getContactLimitStatus']);
    });

    // Support ticket routes
    Route::prefix('support')->group(function () {
        Route::post('/tickets', [SupportController::class, 'createTicket']);
        Route::get('/tickets', [SupportController::class, 'getUserTickets']);
        Route::get('/tickets/{id}', [SupportController::class, 'getTicket']);
    });

    // Admin routes
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        
        Route::get('/users', [AdminController::class, 'getUsers']);
        Route::put('/users/{id}/status', [AdminController::class, 'updateUserStatus']);
        
        Route::get('/companies', [AdminController::class, 'getCompanies']);
        Route::put('/companies/{id}/verify', [AdminController::class, 'verifyCompany']);
        
        Route::get('/messages/pending', [AdminController::class, 'getPendingMessages']);
        Route::put('/messages/{id}/approve', [AdminController::class, 'approveMessage']);
        
        Route::get('/reviews/pending', [AdminController::class, 'getPendingReviews']);
        Route::put('/reviews/{id}/moderate', [AdminController::class, 'moderateReview']);
        
        Route::get('/transactions', [AdminController::class, 'getTransactions']);

        // Admin verification routes
        Route::prefix('verification')->group(function () {
            Route::get('/pending', [VerificationController::class, 'getPendingVerifications']);
            Route::post('/{id}/approve', [VerificationController::class, 'approveVerification']);
            Route::post('/{id}/reject', [VerificationController::class, 'rejectVerification']);
            Route::post('/{id}/request-documents', [VerificationController::class, 'requestDocuments']);
        });

        // Admin payment routes
        Route::get('/payments', [PaymentController::class, 'getAllPayments']);

        // Admin dashboard routes
        Route::get('/dashboard/stats', [DashboardController::class, 'adminDashboard']);
        Route::get('/dashboard/reports', [DashboardController::class, 'generateReports']);

        // Audit log routes
        Route::prefix('audit-logs')->group(function () {
            Route::get('/', [AuditLogController::class, 'index']);
            Route::get('/stats', [AuditLogController::class, 'stats']);
            Route::get('/timeline', [AuditLogController::class, 'timeline']);
            Route::get('/export', [AuditLogController::class, 'export']);
        });

        // Admin support ticket routes
        Route::prefix('support')->group(function () {
            Route::get('/tickets', [SupportController::class, 'getAllTickets']);
            Route::get('/tickets/pending-count', [SupportController::class, 'getPendingCount']);
            Route::post('/tickets/{id}/reply', [SupportController::class, 'replyToTicket']);
            Route::put('/tickets/{id}/status', [SupportController::class, 'updateStatus']);
        });
    });
});
