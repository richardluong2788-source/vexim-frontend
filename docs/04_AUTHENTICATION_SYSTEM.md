# Vexim Global - Authentication & Role System

## Overview
Laravel Sanctum-based authentication with three user roles: Admin, Supplier, and Buyer.

---

## Installation

### 1. Install Laravel Sanctum
\`\`\`bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
\`\`\`

### 2. Configure Sanctum

**config/sanctum.php**
\`\`\`php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
    '%s%s',
    'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
    env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : ''
))),

'expiration' => null, // Tokens never expire (or set to minutes)
\`\`\`

### 3. Update User Model

**app/Models/User.php**
\`\`\`php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'email',
        'password',
        'role',
        'full_name',
        'phone',
        'country',
        'language',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is supplier
     */
    public function isSupplier()
    {
        return $this->role === 'supplier';
    }

    /**
     * Check if user is buyer
     */
    public function isBuyer()
    {
        return $this->role === 'buyer';
    }

    /**
     * Get user's company (for suppliers)
     */
    public function company()
    {
        return $this->hasOne(Company::class);
    }

    /**
     * Get contact requests sent (for buyers)
     */
    public function contactRequestsSent()
    {
        return $this->hasMany(ContactRequest::class, 'buyer_id');
    }

    /**
     * Get contact requests received (for suppliers)
     */
    public function contactRequestsReceived()
    {
        return $this->hasMany(ContactRequest::class, 'supplier_id');
    }
}
\`\`\`

---

## Role-Based Middleware

### Create Middleware

\`\`\`bash
php artisan make:middleware RoleMiddleware
\`\`\`

**app/Http/Middleware/RoleMiddleware.php**
\`\`\`php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Please login.'
            ], 401);
        }

        if ($request->user()->role !== $role) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to access this resource.'
            ], 403);
        }

        return $next($request);
    }
}
\`\`\`

### Register Middleware

**app/Http/Kernel.php**
\`\`\`php
protected $middlewareAliases = [
    // ... existing middleware
    'role' => \App\Http\Middleware\RoleMiddleware::class,
];
\`\`\`

---

## Authentication Controller

**app/Http/Controllers/Auth/AuthController.php**
\`\`\`php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Register new user
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'full_name' => 'required|string|max:255',
            'role' => 'required|in:supplier,buyer',
            'phone' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        // Create user
        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'full_name' => $request->full_name,
            'role' => $request->role,
            'phone' => $request->phone,
            'country' => $request->country,
            'language' => $request->language ?? 'en',
        ]);

        // If supplier, create company profile
        if ($user->role === 'supplier') {
            Company::create([
                'user_id' => $user->id,
                'package_id' => 1, // Default to Free package
                'verification_status' => 'pending',
            ]);
        }

        // Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'role' => $user->role,
                    'full_name' => $user->full_name,
                ],
                'token' => $token
            ]
        ], 201);
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated'
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'role' => $user->role,
                    'full_name' => $user->full_name,
                ],
                'token' => $token
            ]
        ], 200);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ], 200);
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request)
    {
        $user = $request->user();

        $data = [
            'id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'full_name' => $user->full_name,
            'phone' => $user->phone,
            'country' => $user->country,
        ];

        // If supplier, include company info
        if ($user->isSupplier() && $user->company) {
            $data['company'] = [
                'id' => $user->company->id,
                'company_name' => $user->company->company_name,
                'verification_status' => $user->company->verification_status,
                'verification_id' => $user->company->verification_id,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => ['user' => $data]
        ], 200);
    }
}
\`\`\`

---

## Protected Routes Example

**routes/api.php**
\`\`\`php
// Public routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    
    // Admin only routes
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminDashboard::class, 'index']);
        Route::get('/users', [AdminDashboard::class, 'getUsers']);
    });
    
    // Supplier only routes
    Route::middleware('role:supplier')->prefix('supplier')->group(function () {
        Route::get('/dashboard', [SupplierDashboard::class, 'index']);
        Route::post('/company', [CompanyController::class, 'store']);
    });
    
    // Buyer only routes
    Route::middleware('role:buyer')->prefix('buyer')->group(function () {
        Route::get('/dashboard', [BuyerDashboard::class, 'index']);
        Route::post('/contact-requests', [ContactRequestController::class, 'store']);
    });
});
\`\`\`

---

## Frontend Integration

### Login Request (JavaScript/Fetch)

\`\`\`javascript
async function login(email, password) {
    const response = await fetch('http://localhost:8000/api/auth/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ email, password })
    });
    
    const data = await response.json();
    
    if (data.success) {
        // Store token in localStorage
        localStorage.setItem('auth_token', data.data.token);
        localStorage.setItem('user', JSON.stringify(data.data.user));
        
        // Redirect based on role
        if (data.data.user.role === 'admin') {
            window.location.href = '/admin/dashboard.html';
        } else if (data.data.user.role === 'supplier') {
            window.location.href = '/supplier/dashboard.html';
        } else {
            window.location.href = '/buyer/dashboard.html';
        }
    } else {
        alert(data.message);
    }
}
\`\`\`

### Making Authenticated Requests

\`\`\`javascript
async function getSupplierDashboard() {
    const token = localStorage.getItem('auth_token');
    
    const response = await fetch('http://localhost:8000/api/supplier/dashboard', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`
        }
    });
    
    const data = await response.json();
    
    if (data.success) {
        // Display dashboard data
        console.log(data.data);
    } else {
        // Token expired or invalid
        if (response.status === 401) {
            localStorage.removeItem('auth_token');
            window.location.href = '/login.html';
        }
    }
}
\`\`\`

### Logout

\`\`\`javascript
async function logout() {
    const token = localStorage.getItem('auth_token');
    
    await fetch('http://localhost:8000/api/auth/logout', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`
        }
    });
    
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');
    window.location.href = '/login.html';
}
\`\`\`

---

## Security Best Practices

1. **HTTPS Only in Production**
   - Never send tokens over HTTP
   - Use SSL certificates

2. **Token Storage**
   - Store in `localStorage` or `sessionStorage`
   - Never store in cookies without `httpOnly` flag

3. **CORS Configuration**
   - Configure allowed origins in `config/cors.php`

4. **Rate Limiting**
   - Add rate limiting to login endpoint
   - Prevent brute force attacks

5. **Password Requirements**
   - Minimum 8 characters
   - Include uppercase, lowercase, numbers

---

## Testing Authentication

### Using Postman

1. **Register**
   - POST `http://localhost:8000/api/auth/register`
   - Body: JSON with email, password, etc.

2. **Login**
   - POST `http://localhost:8000/api/auth/login`
   - Copy token from response

3. **Access Protected Route**
   - GET `http://localhost:8000/api/supplier/dashboard`
   - Headers: `Authorization: Bearer {token}`

---

## Summary

✅ Laravel Sanctum for token-based authentication  
✅ Three roles: Admin, Supplier, Buyer  
✅ Role-based middleware for route protection  
✅ Secure token generation and validation  
✅ Frontend integration with localStorage  
✅ Complete authentication flow
