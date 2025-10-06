<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Product;
use App\Models\Message;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Notifications\ContactRequestNotification;
use App\Models\User;

class BuyerController extends Controller
{
    /**
     * Get buyer dashboard data
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();

        $data = [
            'stats' => [
                'total_contacts' => $user->sentMessages()->count(),
                'pending_contacts' => $user->sentMessages()->where('admin_status', 'pending')->count(),
                'approved_contacts' => $user->sentMessages()->where('admin_status', 'approved')->count(),
                'saved_suppliers' => 0, // Can implement favorites later
            ],
            'recent_contacts' => $user->sentMessages()
                ->with(['company', 'receiver'])
                ->latest()
                ->take(5)
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Search suppliers
     */
    public function searchSuppliers(Request $request)
    {
        $query = Company::where('verification_status', 'verified');

        // Search by keyword
        if ($request->has('keyword')) {
            $keyword = $request->keyword;
            $query->where(function($q) use ($keyword) {
                $q->where('company_name', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        // Filter by country
        if ($request->has('country')) {
            $query->where('country', $request->country);
        }

        // Filter by package (premium suppliers first)
        if ($request->has('premium_only')) {
            $query->whereHas('package', function($q) {
                $q->where('priority_listing', true);
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'rating');
        switch ($sortBy) {
            case 'rating':
                $query->orderBy('rating', 'desc');
                break;
            case 'views':
                $query->orderBy('view_count', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            default:
                $query->orderBy('rating', 'desc');
        }

        $suppliers = $query->with('package')->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $suppliers
        ]);
    }

    /**
     * Get supplier details
     */
    public function getSupplierDetails($id)
    {
        $company = Company::with([
            'package',
            'products' => function($q) {
                $q->where('status', 'active')->latest()->take(10);
            },
            'certificates',
            'exportHistories',
            'reviews' => function($q) {
                $q->where('status', 'approved')->latest()->take(5);
            }
        ])->find($id);

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Supplier not found'
            ], 404);
        }

        // Increment view count
        $company->incrementViews();

        return response()->json([
            'success' => true,
            'data' => $company
        ]);
    }

    /**
     * Search products
     */
    public function searchProducts(Request $request)
    {
        $query = Product::where('status', 'active')
            ->whereHas('company', function($q) {
                $q->where('verification_status', 'verified');
            });

        // Search by keyword
        if ($request->has('keyword')) {
            $keyword = $request->keyword;
            $query->where(function($q) use ($keyword) {
                $q->where('product_name', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Filter by price range
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $products = $query->with('company')->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Send contact request to supplier
     */
    public function sendContactRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $company = Company::find($request->company_id);

        // Get supplier user (first user of the company)
        $supplier = $company->users()->first();

        if (!$supplier) {
            return response()->json([
                'success' => false,
                'message' => 'Supplier not found'
            ], 404);
        }

        try {
            // Mask buyer contact info
            $maskedEmail = $this->maskEmail($user->email);
            $maskedPhone = $this->maskPhone($user->phone);

            // Create message
            $message = Message::create([
                'sender_id' => $user->id,
                'receiver_id' => $supplier->id,
                'company_id' => $company->id,
                'subject' => $request->subject,
                'message' => $request->message,
                'admin_status' => 'pending',
                'buyer_email_masked' => $maskedEmail,
                'buyer_phone_masked' => $maskedPhone,
                'status' => 'pending',
            ]);

            // Notify admin
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                // Send notification (implement notification system)
                // $admin->notify(new ContactRequestNotification($message));
            }

            return response()->json([
                'success' => true,
                'message' => 'Contact request sent successfully. Admin will review and forward to supplier.',
                'data' => $message
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send contact request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get buyer's contact history
     */
    public function getContactHistory(Request $request)
    {
        $user = $request->user();

        $messages = $user->sentMessages()
            ->with(['company', 'receiver'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }

    /**
     * Submit review for supplier
     */
    public function submitReview(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Check if buyer has already reviewed this company
        $existingReview = Review::where('company_id', $request->company_id)
            ->where('buyer_id', $user->id)
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this supplier'
            ], 422);
        }

        try {
            $review = Review::create([
                'company_id' => $request->company_id,
                'buyer_id' => $user->id,
                'rating' => $request->rating,
                'comment' => $request->comment,
                'status' => 'pending', // Admin approval required
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Review submitted successfully. Waiting for admin approval.',
                'data' => $review
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit review',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper: Mask email
     */
    private function maskEmail($email)
    {
        $parts = explode('@', $email);
        $name = $parts[0];
        $domain = $parts[1];
        
        $maskedName = substr($name, 0, 2) . str_repeat('*', strlen($name) - 2);
        return $maskedName . '@' . $domain;
    }

    /**
     * Helper: Mask phone
     */
    private function maskPhone($phone)
    {
        $length = strlen($phone);
        return substr($phone, 0, 3) . str_repeat('*', $length - 6) . substr($phone, -3);
    }
}
