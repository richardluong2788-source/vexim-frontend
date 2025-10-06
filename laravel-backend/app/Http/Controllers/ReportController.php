<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Generate supplier performance report
     */
    public function supplierPerformance(Request $request)
    {
        $companyId = $request->input('company_id');
        $startDate = $request->input('start_date', now()->subMonth());
        $endDate = $request->input('end_date', now());

        $query = Company::query();

        if ($companyId) {
            $query->where('id', $companyId);
        }

        $suppliers = $query->with(['contacts', 'reviews', 'products'])
            ->get()
            ->map(function ($company) use ($startDate, $endDate) {
                return [
                    'company_id' => $company->id,
                    'company_name' => $company->name,
                    'verification_status' => $company->verification_status,
                    'rating' => $company->rating,
                    'total_contacts' => $company->contacts()
                        ->whereBetween('created_at', [$startDate, $endDate])
                        ->count(),
                    'responded_contacts' => $company->contacts()
                        ->where('status', 'responded')
                        ->whereBetween('created_at', [$startDate, $endDate])
                        ->count(),
                    'response_rate' => $this->calculateResponseRate($company, $startDate, $endDate),
                    'total_reviews' => $company->reviews()
                        ->whereBetween('created_at', [$startDate, $endDate])
                        ->count(),
                    'average_rating' => $company->reviews()
                        ->whereBetween('created_at', [$startDate, $endDate])
                        ->avg('rating'),
                    'total_products' => $company->products()->count(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $suppliers
        ]);
    }

    /**
     * Generate revenue report
     */
    public function revenueReport(Request $request)
    {
        $startDate = $request->input('start_date', now()->subMonth());
        $endDate = $request->input('end_date', now());
        $groupBy = $request->input('group_by', 'day'); // day, week, month

        $payments = Payment::completed()
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->with(['company', 'package'])
            ->get();

        $summary = [
            'total_revenue' => $payments->sum('amount'),
            'total_transactions' => $payments->count(),
            'average_transaction' => $payments->avg('amount'),
            'by_package' => $payments->groupBy('package.name')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'revenue' => $group->sum('amount'),
                ];
            }),
            'by_payment_method' => $payments->groupBy('payment_method')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'revenue' => $group->sum('amount'),
                ];
            }),
        ];

        // Time series data
        $timeSeries = $this->groupPaymentsByTime($payments, $groupBy);

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => $summary,
                'time_series' => $timeSeries,
                'transactions' => $payments,
            ]
        ]);
    }

    /**
     * Generate contact activity report
     */
    public function contactReport(Request $request)
    {
        $startDate = $request->input('start_date', now()->subMonth());
        $endDate = $request->input('end_date', now());

        $contacts = Contact::with(['company', 'buyer'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $summary = [
            'total_contacts' => $contacts->count(),
            'pending' => $contacts->where('status', 'pending')->count(),
            'responded' => $contacts->where('status', 'responded')->count(),
            'response_rate' => $contacts->count() > 0 ? 
                ($contacts->where('status', 'responded')->count() / $contacts->count() * 100) : 0,
            'by_country' => $contacts->groupBy('country')->map->count(),
            'top_suppliers' => $contacts->groupBy('company_id')
                ->map(function ($group) {
                    return [
                        'company' => $group->first()->company->name,
                        'contacts' => $group->count(),
                    ];
                })
                ->sortByDesc('contacts')
                ->take(10),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => $summary,
                'contacts' => $contacts,
            ]
        ]);
    }

    /**
     * Generate verification report
     */
    public function verificationReport()
    {
        $stats = [
            'total_companies' => Company::count(),
            'verified' => Company::verified()->count(),
            'pending' => Company::where('verification_status', 'pending')->count(),
            'rejected' => Company::where('verification_status', 'rejected')->count(),
            'verification_rate' => Company::count() > 0 ? 
                (Company::verified()->count() / Company::count() * 100) : 0,
        ];

        // Verification timeline
        $timeline = Company::selectRaw('
                DATE(verified_at) as date,
                COUNT(*) as count
            ')
            ->whereNotNull('verified_at')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();

        // By country
        $byCountry = Company::verified()
            ->select('country', DB::raw('COUNT(*) as count'))
            ->groupBy('country')
            ->orderBy('count', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'timeline' => $timeline,
                'by_country' => $byCountry,
            ]
        ]);
    }

    /**
     * Calculate response rate for a company
     */
    private function calculateResponseRate($company, $startDate, $endDate)
    {
        $total = $company->contacts()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        if ($total === 0) {
            return 0;
        }

        $responded = $company->contacts()
            ->where('status', 'responded')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        return round(($responded / $total) * 100, 2);
    }

    /**
     * Group payments by time period
     */
    private function groupPaymentsByTime($payments, $groupBy)
    {
        return $payments->groupBy(function ($payment) use ($groupBy) {
            switch ($groupBy) {
                case 'week':
                    return $payment->paid_at->format('Y-W');
                case 'month':
                    return $payment->paid_at->format('Y-m');
                default:
                    return $payment->paid_at->format('Y-m-d');
            }
        })->map(function ($group) {
            return [
                'count' => $group->count(),
                'revenue' => $group->sum('amount'),
            ];
        });
    }
}
