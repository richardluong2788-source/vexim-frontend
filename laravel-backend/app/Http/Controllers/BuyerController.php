<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Product;
use App\Models\Message;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BuyerController extends Controller
{
    /**
     * Get buyer dashboard data
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();

        $stats = [
            'total_messages' => $user->sentMessages()->count(),
            'pending_messages' => $user->sentMessages()->where('status', 'pending')->count(),
            'approved_messages' => $user->sentMessages()->where('status', 'approved')->count(),
            'total_reviews' => $user->reviews()->count(),
        ];

        $recentMessages = $user->sentMessages()
            ->with(['company', 'receiver'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'stats' => $stats,
                'recent_messages' => $recentMessages
            ]
        ]);
    }

    /**
     * Search suppliers
     */
    public function searchSuppliers(Request $request)
    {
        $query = Company::query()->verified()->active();

        // Search by keyword
        if ($request->has('keyword')) {
            $keyword = $request->keyword;
            $query->where(function($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%")
                  ->orWhere('main_products', 'like', "%{$keyword}%");
            });
        }

        // Filter by country
        if ($request->has('country')) {
            $query->where('country', $request->country);
        }

        // Filter by business type
        if ($request->has('business_type')) {
            $query->where('business_type', $request->business_type);
        }

        // Then by rating, then by recent activity
        $query->orderBy('visibility_level', 'desc')
              ->orderBy('rating', 'desc')
              ->orderBy('updated_at', 'desc');

        $suppliers = $query->with('package')->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $suppliers
        ]);
    }

    /**
     * Get supplier details
     */
    public function getSupplier($id)
    {
        $company = Company::with([
            'products' => function($q) {
                $q->where('status', 'active')->limit(6);
            },
            'certificates',
            'exportHistories',
            'reviews' => function($q) {
                $q->where('status', 'approved')->with('buyer')->latest()->limit(5);
            },
            'package'
        ])->find($id);

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Supplier not found'
            ], 404);
        }

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
        $query = Product::query()
            ->where('status', 'active')
            ->whereHas('company', function($q) {
                $q->verified()->active();
            });

        // Search by keyword
        if ($request->has('keyword')) {
            $keyword = $request->keyword;
            $query->where(function($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%")
                  ->orWhere('category', 'like', "%{$keyword}%");
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

        $products = $query->with('company')->paginate(20);

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
            'contact_email' => 'required|email',
            'contact_phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();

            $message = Message::create([
                'sender_id' => $user->id,
                'company_id' => $request->company_id,
                'subject' => $request->subject,
                'message' => $request->message,
                'contact_email' => $request->contact_email,
                'contact_phone' => $request->contact_phone,
                'status' => 'pending',
            ]);

            // TODO: Send notification to admin

            return response()->json([
                'success' => true,
                'message' => 'Contact request sent successfully. Admin will review and forward to supplier.',
                'data' => $message
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Request failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get buyer's messages
     */
    public function getMessages(Request $request)
    {
        $user = $request->user();

        $messages = $user->sentMessages()
            ->with(['company', 'receiver'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

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
            'comment' => 'required|string|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();

            // Check if already reviewed
            $existingReview = Review::where('company_id', $request->company_id)
                ->where('buyer_id', $user->id)
                ->first();

            if ($existingReview) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already reviewed this supplier'
                ], 400);
            }

            $review = Review::create([
                'company_id' => $request->company_id,
                'buyer_id' => $user->id,
                'rating' => $request->rating,
                'comment' => $request->comment,
                'status' => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Review submitted successfully. Admin will review before publishing.',
                'data' => $review
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Submission failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get featured suppliers (for homepage)
     */
    public function getFeaturedSuppliers(Request $request)
    {
        $limit = $request->get('limit', 8);

        $featured = Company::query()
            ->verified()
            ->active()
            ->where('visibility_level', '>=', 3)
            ->orderBy('visibility_level', 'desc')
            ->orderBy('rating', 'desc')
            ->limit($limit)
            ->with('package')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $featured
        ]);
    }
}
