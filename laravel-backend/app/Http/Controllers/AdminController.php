<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use App\Models\Message;
use App\Models\Review;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    /**
     * Get admin dashboard statistics
     */
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_suppliers' => User::where('role', 'supplier')->count(),
            'total_buyers' => User::where('role', 'buyer')->count(),
            'total_companies' => Company::count(),
            'verified_companies' => Company::where('verification_status', 'verified')->count(),
            'pending_verifications' => Company::where('verification_status', 'pending')->count(),
            'pending_messages' => Message::where('status', 'pending')->count(),
            'pending_reviews' => Review::where('status', 'pending')->count(),
            'total_revenue' => Transaction::where('payment_status', 'completed')->sum('amount'),
        ];

        $recentUsers = User::orderBy('created_at', 'desc')->limit(5)->get();
        $recentCompanies = Company::with('users')->orderBy('created_at', 'desc')->limit(5)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'recent_users' => $recentUsers,
                'recent_companies' => $recentCompanies
            ]
        ]);
    }

    /**
     * Get all users with filters
     */
    public function getUsers(Request $request)
    {
        $query = User::query();

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->with('company')->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Update user status
     */
    public function updateUserStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:active,inactive,suspended',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $user->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'User status updated successfully',
            'data' => $user
        ]);
    }

    /**
     * Get all companies with filters
     */
    public function getCompanies(Request $request)
    {
        $query = Company::query();

        if ($request->has('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }

        if ($request->has('country')) {
            $query->where('country', $request->country);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('verification_id', 'like', "%{$search}%");
            });
        }

        $companies = $query->with(['users', 'package'])->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $companies
        ]);
    }

    /**
     * Verify company
     */
    public function verifyCompany(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'verification_status' => 'required|in:verified,rejected',
            'admin_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $company = Company::find($id);

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found'
            ], 404);
        }

        try {
            $data = [
                'verification_status' => $request->verification_status,
            ];

            if ($request->verification_status === 'verified') {
                // Generate verification ID
                $data['verification_id'] = $company->generateVerificationId();
                $data['verified_at'] = now();
                
                // Set free package with 1 year expiry
                $data['package_id'] = 1; // Free package
                $data['package_expires_at'] = now()->addYear();
            }

            $company->update($data);

            // TODO: Send email notification to supplier

            return response()->json([
                'success' => true,
                'message' => 'Company verification updated successfully',
                'data' => $company
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Verification failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pending contact requests
     */
    public function getPendingMessages(Request $request)
    {
        $messages = Message::where('status', 'pending')
            ->with(['sender', 'company'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }

    /**
     * Approve and forward message to supplier
     */
    public function approveMessage(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:approve,reject',
            'admin_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $message = Message::with(['sender', 'company'])->find($id);

        if (!$message) {
            return response()->json([
                'success' => false,
                'message' => 'Message not found'
            ], 404);
        }

        try {
            if ($request->action === 'approve') {
                // Get supplier user (company owner)
                $supplier = $message->company->users()->first();

                if (!$supplier) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Supplier not found'
                    ], 404);
                }

                $message->update([
                    'status' => 'approved',
                    'receiver_id' => $supplier->id,
                    'admin_notes' => $request->admin_notes,
                    'forwarded_at' => now(),
                ]);

                // TODO: Send email to supplier with buyer's contact info
                // TODO: Send email to buyer confirming message was forwarded

            } else {
                $message->update([
                    'status' => 'rejected',
                    'admin_notes' => $request->admin_notes,
                ]);

                // TODO: Send email to buyer about rejection
            }

            return response()->json([
                'success' => true,
                'message' => 'Message ' . $request->action . 'd successfully',
                'data' => $message
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Action failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pending reviews
     */
    public function getPendingReviews(Request $request)
    {
        $reviews = Review::where('status', 'pending')
            ->with(['company', 'buyer'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }

    /**
     * Moderate review
     */
    public function moderateReview(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:approve,reject',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $review = Review::with(['company', 'buyer'])->find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found'
            ], 404);
        }

        try {
            $status = $request->action === 'approve' ? 'approved' : 'rejected';
            $review->update(['status' => $status]);

            // If approved, update company rating
            if ($status === 'approved') {
                $company = $review->company;
                $approvedReviews = $company->reviews()->where('status', 'approved')->get();
                
                $totalRating = $approvedReviews->sum('rating');
                $reviewCount = $approvedReviews->count();
                
                $company->update([
                    'rating' => $reviewCount > 0 ? round($totalRating / $reviewCount, 2) : 0,
                    'total_reviews' => $reviewCount,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Review ' . $request->action . 'd successfully',
                'data' => $review
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Moderation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all transactions
     */
    public function getTransactions(Request $request)
    {
        $query = Transaction::with('user');

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }
}
