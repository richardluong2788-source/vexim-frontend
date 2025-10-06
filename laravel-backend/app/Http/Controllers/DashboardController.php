<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Product;
use App\Models\Contact;
use App\Models\Payment;
use App\Models\Review;
use App\Models\SupplierDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Admin Dashboard Statistics
     */
    public function adminDashboard()
    {
        try {
            $stats = [
                'total_suppliers' => Company::count(),
                'verified_suppliers' => Company::verified()->count(),
                'pending_verification' => Company::where('verification_status', 'pending')->count(),
                'rejected_suppliers' => Company::where('verification_status', 'rejected')->count(),
                
                'total_products' => Product::count(),
                'active_products' => Product::where('status', 'active')->count(),
                
                'total_contacts' => Contact::count(),
                'pending_contacts' => Contact::where('status', 'pending')->count(),
                'responded_contacts' => Contact::where('status', 'responded')->count(),
                
                'total_payments' => Payment::sum('amount'),
                'pending_payments' => Payment::pending()->count(),
                'completed_payments' => Payment::completed()->count(),
                
                'pending_documents' => SupplierDocument::where('status', 'pending')->count(),
                'total_reviews' => Review::count(),
            ];

            // Recent activities
            $recentSuppliers = Company::with('users')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            $recentContacts = Contact::with(['company', 'buyer'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            $recentPayments = Payment::with(['company', 'package'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            // Monthly statistics
            $monthlyStats = $this->getMonthlyStats();

            // Top suppliers by rating
            $topSuppliers = Company::verified()
                ->orderBy('rating', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => $stats,
                    'recent_suppliers' => $recentSuppliers,
                    'recent_contacts' => $recentContacts,
                    'recent_payments' => $recentPayments,
                    'monthly_stats' => $monthlyStats,
                    'top_suppliers' => $topSuppliers,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supplier Dashboard Statistics
     */
    public function supplierDashboard()
    {
        try {
            $user = auth()->user();
            $company = $user->company;

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'No company found'
                ], 404);
            }

            $stats = [
                'verification_status' => $company->verification_status,
                'verification_id' => $company->verification_id,
                'package_name' => $company->package->name ?? 'Free',
                'package_expires_at' => $company->package_expires_at,
                'days_until_expiry' => $company->package_expires_at ? 
                    now()->diffInDays($company->package_expires_at, false) : null,
                
                'total_products' => $company->products()->count(),
                'active_products' => $company->products()->where('status', 'active')->count(),
                
                'total_contacts' => $company->contacts()->count(),
                'pending_contacts' => $company->contacts()->where('status', 'pending')->count(),
                'responded_contacts' => $company->contacts()->where('status', 'responded')->count(),
                
                'total_reviews' => $company->reviews()->count(),
                'average_rating' => $company->rating,
                
                'profile_views' => $this->getProfileViews($company->id),
                'product_views' => $this->getProductViews($company->id),
            ];

            // Recent contacts
            $recentContacts = $company->contacts()
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            // Recent reviews
            $recentReviews = $company->reviews()
                ->with('buyer')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            // Pending documents
            $pendingDocuments = $company->documents()
                ->where('status', 'pending')
                ->get();

            // Monthly contact trends
            $contactTrends = $this->getContactTrends($company->id);

            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => $stats,
                    'recent_contacts' => $recentContacts,
                    'recent_reviews' => $recentReviews,
                    'pending_documents' => $pendingDocuments,
                    'contact_trends' => $contactTrends,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buyer Dashboard Statistics
     */
    public function buyerDashboard()
    {
        try {
            $user = auth()->user();

            $stats = [
                'total_contacts' => Contact::where('buyer_id', $user->id)->count(),
                'pending_contacts' => Contact::where('buyer_id', $user->id)
                    ->where('status', 'pending')->count(),
                'responded_contacts' => Contact::where('buyer_id', $user->id)
                    ->where('status', 'responded')->count(),
                
                'total_reviews' => Review::where('buyer_id', $user->id)->count(),
                'saved_suppliers' => 0, // Implement saved suppliers feature
            ];

            // Recent contacts
            $recentContacts = Contact::with('company')
                ->where('buyer_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            // Recent reviews
            $recentReviews = Review::with('company')
                ->where('buyer_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            // Recommended suppliers
            $recommendedSuppliers = Company::verified()
                ->where('country', $user->country)
                ->orderBy('rating', 'desc')
                ->limit(6)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => $stats,
                    'recent_contacts' => $recentContacts,
                    'recent_reviews' => $recentReviews,
                    'recommended_suppliers' => $recommendedSuppliers,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get monthly statistics for admin
     */
    private function getMonthlyStats()
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = [
                'month' => $date->format('M Y'),
                'suppliers' => Company::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
                'contacts' => Contact::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
                'revenue' => Payment::completed()
                    ->whereYear('paid_at', $date->year)
                    ->whereMonth('paid_at', $date->month)
                    ->sum('amount'),
            ];
        }
        return $months;
    }

    /**
     * Get profile views (placeholder - implement with analytics)
     */
    private function getProfileViews($companyId)
    {
        // This would integrate with an analytics system
        return rand(100, 1000);
    }

    /**
     * Get product views (placeholder - implement with analytics)
     */
    private function getProductViews($companyId)
    {
        // This would integrate with an analytics system
        return rand(50, 500);
    }

    /**
     * Get contact trends for supplier
     */
    private function getContactTrends($companyId)
    {
        $trends = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $trends[] = [
                'month' => $date->format('M Y'),
                'contacts' => Contact::where('company_id', $companyId)
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
            ];
        }
        return $trends;
    }

    /**
     * Get verification queue for admin
     */
    public function getVerificationQueue()
    {
        $pendingCompanies = Company::with(['users', 'documents'])
            ->where('verification_status', 'pending')
            ->orderBy('created_at', 'asc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $pendingCompanies
        ]);
    }

    /**
     * Get analytics data
     */
    public function getAnalytics(Request $request)
    {
        $period = $request->input('period', '30days'); // 7days, 30days, 90days, 1year

        $days = match($period) {
            '7days' => 7,
            '30days' => 30,
            '90days' => 90,
            '1year' => 365,
            default => 30,
        };

        $startDate = now()->subDays($days);

        $analytics = [
            'suppliers_growth' => $this->getSuppliersGrowth($startDate),
            'contacts_growth' => $this->getContactsGrowth($startDate),
            'revenue_growth' => $this->getRevenueGrowth($startDate),
            'top_countries' => $this->getTopCountries(),
            'top_categories' => $this->getTopCategories(),
        ];

        return response()->json([
            'success' => true,
            'data' => $analytics
        ]);
    }

    private function getSuppliersGrowth($startDate)
    {
        return Company::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getContactsGrowth($startDate)
    {
        return Contact::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getRevenueGrowth($startDate)
    {
        return Payment::completed()
            ->where('paid_at', '>=', $startDate)
            ->selectRaw('DATE(paid_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getTopCountries()
    {
        return Company::verified()
            ->select('country', DB::raw('COUNT(*) as count'))
            ->groupBy('country')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();
    }

    private function getTopCategories()
    {
        return Company::verified()
            ->select('business_type', DB::raw('COUNT(*) as count'))
            ->groupBy('business_type')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();
    }
}
