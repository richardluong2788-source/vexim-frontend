<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use App\Models\Message;
use App\Models\Review;
use App\Models\Transaction;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationApproved;
use App\Mail\ContactRequestForwarded;

class AdminController extends Controller
{
    /**
     * Get admin dashboard statistics
     */
    public function dashboard()
    {
        $stats = [
            'total_suppliers' => Company::count(),
            'verified_suppliers' => Company::where('verification_status', 'verified')->count(),
            'pending_verifications' => Company::where('verification_status', 'pending')->count(),
            'total_buyers' => User::where('role', 'buyer')->count(),
            'pending_messages' => Message::where('admin_status', 'pending')->count(),
            'pending_reviews' => Review::where('status', 'pending')->count(),
            'total_revenue' => Transaction::where('payment_status', 'completed')->sum('amount'),
            'monthly_revenue' => Transaction::where('payment_status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->sum('amount'),
        ];

        $recentActivities = [
            'new_suppliers' => Company::latest()->take(5)->get(),
            'pending_verifications' => Company::where('verification_status', 'pending')->latest()->take(5)->get(),
            'recent_messages' => Message::with(['sender', 'company'])->latest()->take(5)->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'activities' => $recentActivities
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

        $users = $query->with('company')->paginate(20);

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

        $user->status = $request->status;
        $user->save();

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

        $companies = $query->with(['package', 'users'])->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $companies
        ]);
    }

    /**
     * Get company details for verification
     */
    public function getCompanyDetails($id)
    {
        $company = Company::with([
            'users',
            'package',
            'products',
            'certificates',
            'exportHistories'
        ])->find($id);

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $company
        ]);
    }

    /**
     * Verify company
     */
    public function verifyCompany(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'verification_status' => 'required|in:verified,rejected',
            'admin_note' => 'nullable|string',
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
            $company->verification_status = $request->verification_status;

            if ($request->verification_status === 'verified') {
                // Generate unique verification ID
                $company->verification_id = 'VXM-' . strtoupper(substr(md5($company->id . time()), 0, 8));
                $company->verification_date = now();

                // Set package dates if not set
                if (!$company->package_start_date) {
                    $company->package_start_date = now();
                    $company->package_end_date = now()->addMonths($company->package->duration_months);
                }
            }

            $company->save();

            // Send email notification to supplier
            $supplier = $company->users()->first();
            if ($supplier) {
                // Mail::to($supplier->email)->send(new VerificationApproved($company, $request->verification_status, $request->admin_note));
            }

            return response()->json([
                'success' => true,
                'message' => 'Company verification updated successfully',
                'data' => $company
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Verification update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pending contact requests
     */
    public function getPendingMessages(Request $request)
    {
        $messages = Message::where('admin_status', 'pending')
            ->with(['sender', 'receiver', 'company'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }

    /**
     * Review and forward contact request
     */
    public function reviewContactRequest(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'admin_status' => 'required|in:approved,rejected',
            'admin_note' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $message = Message::with(['sender', 'receiver', 'company'])->find($id);

        if (!$message) {
            return response()->json([
                'success' => false,
                'message' => 'Message not found'
            ], 404);
        }

        try {
            $message->admin_status = $request->admin_status;
            $message->admin_note = $request->admin_note;

            if ($request->admin_status === 'approved') {
                $message->status = 'approved';
                
                // Send email to supplier with buyer's contact info
                // Mail::to($message->receiver->email)->send(new ContactRequestForwarded($message));
                
                // Send email to buyer confirming approval
                // Mail::to($message->sender->email)->send(new ContactRequestApproved($message));
            } else {
                $message->status = 'rejected';
            }

            $message->save();

            return response()->json([
                'success' => true,
                'message' => 'Contact request reviewed successfully',
                'data' => $message
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Review failed',
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
     * Approve or reject review
     */
    public function reviewReview(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found'
            ], 404);
        }

        try {
            $review->status = $request->status;
            $review->save();

            // If approved, update company rating
            if ($request->status === 'approved') {
                $company = $review->company;
                $avgRating = $company->reviews()->where('status', 'approved')->avg('rating');
                $company->rating = round($avgRating, 2);
                $company->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Review updated successfully',
                'data' => $review
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Review update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all transactions
     */
    public function getTransactions(Request $request)
    {
        $query = Transaction::with(['user', 'company', 'package']);

        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }

    /**
     * Get packages
     */
    public function getPackages()
    {
        $packages = Package::all();

        return response()->json([
            'success' => true,
            'data' => $packages
        ]);
    }

    /**
     * Update package
     */
    public function updatePackage(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:100',
            'price' => 'sometimes|numeric|min:0',
            'duration_months' => 'sometimes|integer|min:1',
            'max_products' => 'sometimes|integer|min:-1',
            'priority_listing' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $package = Package::find($id);

        if (!$package) {
            return response()->json([
                'success' => false,
                'message' => 'Package not found'
            ], 404);
        }

        $package->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Package updated successfully',
            'data' => $package
        ]);
    }
}
