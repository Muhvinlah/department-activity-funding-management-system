<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tor;
use App\Models\Lpj;
use App\Models\AnnualBudget;
use App\Models\User;
use App\Models\StatusHist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;


class DashboardController extends Controller
{
    /**
     * Get dashboard summary statistics
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSummary(): JsonResponse
    {
        try {

            // Cache dashboard summary 5 menit
            $summary = Cache::remember('dashboard_summary', 300, function () {

                $user = Auth::guard('api')->user();

                // Total counts
                $totalTor = Tor::count();
                $totalLpj = Lpj::count();
                $totalUsers = User::count();

                // TOR statistics by status
                $torByStatus = Tor::select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->pluck('count', 'status');

                // LPJ statistics by status
                $lpjByStatus = Lpj::select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->pluck('count', 'status');

                // Current year budget
                $currentYear = date('Y');
                $annualBudget = AnnualBudget::where('tahun', $currentYear)->first();

                $budgetInfo = null;
                if ($annualBudget) {
                    // Calculate used budget from approved LPJs (actual spent money)
                    $usedBudget = Lpj::where('status', 'approved_by_head')
                        ->whereHas('tor', function ($query) use ($annualBudget) {
                            $query->where('budget_id', $annualBudget->budget_id);
                        })
                        ->sum('budget_used');

                    $budgetInfo = [
                        'total_budget' => (float) $annualBudget->budget,
                        'used_budget' => (float) $usedBudget,
                        'remaining_budget' => (float) ($annualBudget->budget - $usedBudget),
                        'usage_percentage' => $annualBudget->budget > 0
                            ? round(($usedBudget / $annualBudget->budget) * 100, 2)
                            : 0,
                    ];
                }

                // Recent activities
                $recentActivities = StatusHist::with(['user', 'tor', 'lpj'])
                    ->orderBy('timestamp_aksi', 'desc')
                    ->limit(10)
                    ->get()
                    ->map(function ($history) {
                        return [
                            'id' => $history->hist_id,
                            'type' => $history->tor_id ? 'TOR' : 'LPJ',
                            'activity_name' => $history->tor ? $history->tor->activity_name : ($history->lpj ? $history->lpj->tor->activity_name : 'N/A'),
                            'status' => $history->status,
                            'user' => $history->user ? $history->user->full_name : 'System',
                            'catatan' => $history->catatan,
                            'timestamp' => $history->timestamp_aksi,
                        ];
                    });

                // Pending approvals
                $pendingApprovals = [
                    'tor_submitted' => Tor::where('status', 'submitted')->count(),
                    'tor_reviewed' => Tor::where('status', 'reviewed_by_secretary')->count(),
                    'tor_verified' => Tor::where('status', 'verified_by_admin')->count(),
                    'lpj_submitted' => Lpj::where('status', 'submitted')->count(),
                    'lpj_reviewed' => Lpj::where('status', 'reviewed_by_secretary')->count(),
                    'lpj_verified' => Lpj::where('status', 'verified_by_admin')->count(),
                ];

                // User specific stats
                $userStats = null;
                if ($user) {
                    $userStats = [
                        'my_tors' => Tor::where('user_id', $user->user_id)->count(),
                        'my_lpjs' => Lpj::where('user_id', $user->user_id)->count(),
                        'my_approved_tors' => Tor::where('user_id', $user->user_id)
                            ->where('status', 'approved_by_head')
                            ->count(),
                        'my_approved_lpjs' => Lpj::where('user_id', $user->user_id)
                            ->where('status', 'approved_by_head')
                            ->count(),
                    ];
                }

                // Return summary data
                return [
                    'overview' => [
                        'total_tor' => $totalTor,
                        'total_lpj' => $totalLpj,
                        'total_users' => $totalUsers,
                    ],
                    'tor_statistics' => $torByStatus,
                    'lpj_statistics' => $lpjByStatus,
                    'budget_info' => $budgetInfo,
                    'pending_approvals' => $pendingApprovals,
                    'recent_activities' => $recentActivities,
                    'user_statistics' => $userStats,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $summary,
                'cached_at' => now(),
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to get dashboard summary',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Get filtered dashboard data
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFilteredData(Request $request)
    {
        try {
            $filters = [];

            // TOR filtered data
            $torQuery = Tor::with(['user', 'category', 'annualBudget']);

            if ($request->has('status')) {
                $torQuery->where('status', $request->status);
                $filters['status'] = $request->status;
            }

            if ($request->has('category_id')) {
                $torQuery->where('category_id', $request->category_id);
                $filters['category_id'] = $request->category_id;
            }

            if ($request->has('year')) {
                $torQuery->whereHas('annualBudget', function ($query) use ($request) {
                    $query->where('tahun', $request->year);
                });
                $filters['year'] = $request->year;
            }

            if ($request->has('start_date') && $request->has('end_date')) {
                $torQuery->whereBetween('start_date', [$request->start_date, $request->end_date]);
                $filters['date_range'] = [
                    'start' => $request->start_date,
                    'end' => $request->end_date
                ];
            }

            if ($request->has('budget_min') && $request->has('budget_max')) {
                $torQuery->whereBetween('budget_submitted', [
                    $request->budget_min,
                    $request->budget_max
                ]);
                $filters['budget_range'] = [
                    'min' => $request->budget_min,
                    'max' => $request->budget_max
                ];
            }

            $tors = $torQuery->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 15));

            // Statistics for filtered data
            $statistics = [
                'total_records' => $tors->total(),
                'total_budget' => $torQuery->sum('budget_submitted'),
                'average_budget' => $torQuery->avg('budget_submitted'),
                'status_breakdown' => $torQuery->select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->pluck('count', 'status'),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'tors' => $tors,
                    'statistics' => $statistics,
                    'filters_applied' => $filters,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get filtered data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get chart data for dashboard
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getChartData(Request $request)
    {
        try {
            $year = $request->get('year', date('Y'));
            $cacheKey = "dashboard_charts_{$year}";
            
            // Get annual budget for the year
            $annualBudget = AnnualBudget::where('tahun', $year)->first();

            // Cache hasil chart selama 10 menit
            $chartData = Cache::remember($cacheKey, 600, function () use ($year, $annualBudget) {

                // Monthly TOR and LPJ submissions
                $monthlyTorSubmissions = Tor::selectRaw('EXTRACT(MONTH FROM created_at) as month, COUNT(*) as count')
                    ->whereYear('created_at', $year)
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get()
                    ->pluck('count', 'month');

                $monthlyLpjSubmissions = Lpj::selectRaw('EXTRACT(MONTH FROM created_at) as month, COUNT(*) as count')
                    ->whereYear('created_at', $year)
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get()
                    ->pluck('count', 'month');

                $monthlySubmissionsData = [];
                for ($i = 1; $i <= 12; $i++) {
                    $monthlySubmissionsData[] = [
                        'month' => date('M', mktime(0, 0, 0, $i, 1)),
                        'tor_count' => $monthlyTorSubmissions->get($i, 0),
                        'lpj_count' => $monthlyLpjSubmissions->get($i, 0),
                        'total_count' => $monthlyTorSubmissions->get($i, 0) + $monthlyLpjSubmissions->get($i, 0)
                    ];
                }

                // Budget usage by category (current year only)
                $budgetByCategory = Tor::select('activity_category.category_def', DB::raw('SUM(tor.budget_submitted) as total_budget'))
                    ->join('activity_category', 'tor.category_id', '=', 'activity_category.category_id')
                    ->where('tor.status', 'approved_by_head')
                    ->where('tor.budget_id', $annualBudget->budget_id)
                    ->groupBy('activity_category.category_def')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'category' => $item->category_def,
                            'amount' => (float) $item->total_budget
                        ];
                    });

                // TOR and LPJ status distribution (current year only)
                $torStatusDistribution = Tor::select('status', DB::raw('COUNT(*) as count'))
                    ->whereYear('created_at', $year)
                    ->groupBy('status')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'type' => 'TOR',
                            'status' => $item->status,
                            'count' => $item->count,
                            'label' => 'TOR - ' . ucwords(str_replace('_', ' ', $item->status))
                        ];
                    });

                $lpjStatusDistribution = Lpj::select('status', DB::raw('COUNT(*) as count'))
                    ->whereYear('created_at', $year)
                    ->groupBy('status')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'type' => 'LPJ',
                            'status' => $item->status,
                            'count' => $item->count,
                            'label' => 'LPJ - ' . ucwords(str_replace('_', ' ', $item->status))
                        ];
                    });

                $statusDistribution = $torStatusDistribution->concat($lpjStatusDistribution);

                // Budget vs Realization (current year only)
                $budgetVsRealization = Tor::select('tor.tor_id', 'tor.activity_name', 'tor.budget_submitted', 'lpj.budget_used')
                    ->leftJoin('lpj', 'tor.tor_id', '=', 'lpj.tor_id')
                    ->where('tor.status', 'approved_by_head')
                    ->where('tor.budget_id', $annualBudget->budget_id)
                    ->whereNotNull('lpj.lpj_id')
                    ->limit(10)
                    ->get()
                    ->map(function ($item) {
                        return [
                            'activity' => substr($item->activity_name, 0, 30) . '...',
                            'budget_submitted' => (float) $item->budget_submitted,
                            'budget_used' => (float) $item->budget_used,
                            'variance' => (float) ($item->budget_submitted - $item->budget_used)
                        ];
                    });

                // Approval timeline (current year only)
                // Use subquery to calculate time differences first, then aggregate
                $approvalTimeline = DB::table(DB::raw("(
                    SELECT 
                        sh.status,
                        EXTRACT(EPOCH FROM (sh.timestamp_aksi - LAG(sh.timestamp_aksi) OVER (PARTITION BY sh.tor_id ORDER BY sh.timestamp_aksi)))/86400 as days_diff
                    FROM status_hist sh
                    INNER JOIN tor t ON sh.tor_id = t.tor_id
                    WHERE sh.tor_id IS NOT NULL
                    AND EXTRACT(YEAR FROM t.created_at) = {$year}
                ) as time_diffs"))
                    ->select('status', DB::raw('AVG(days_diff) as avg_days'))
                    ->whereNotNull('days_diff')
                    ->groupBy('status')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'stage' => ucwords(str_replace('_', ' ', $item->status)),
                            'average_days' => round($item->avg_days ?? 0, 1)
                        ];
                    });

                // LPJ budget by category (current year only)
                $lpjBudgetByCategory = Lpj::select('activity_category.category_def', DB::raw('SUM(lpj.budget_used) as total_budget'))
                    ->join('tor', 'lpj.tor_id', '=', 'tor.tor_id')
                    ->join('activity_category', 'tor.category_id', '=', 'activity_category.category_id')
                    ->where('lpj.status', 'approved_by_head')
                    ->where('tor.budget_id', $annualBudget->budget_id)
                    ->groupBy('activity_category.category_def')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'category' => $item->category_def,
                            'amount' => (float) $item->total_budget
                        ];
                    });


                return [
                    'monthly_submissions' => $monthlySubmissionsData,
                    'budget_by_category' => $budgetByCategory,
                    'lpj_budget_by_category' => $lpjBudgetByCategory,
                    'status_distribution' => $statusDistribution,
                    'budget_vs_realization' => $budgetVsRealization,
                    'approval_timeline' => $approvalTimeline,
                    'year' => $year,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $chartData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get chart data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Force refresh dashboard cache (admin only)
     */
    public function refreshCache()
    {
        try {
            Cache::forget('dashboard_summary');
            Cache::forget('dashboard_charts_' . date('Y'));
            Cache::forget('dashboard_annual_budget_' . date('Y'));

            return response()->json([
                'success' => true,
                'message' => 'Dashboard cache refreshed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh cache',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Get annual budget dashboard data
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAnnualBudgetData(Request $request)
    {
        try {
            $year = $request->get('year', date('Y'));

            // Get annual budget for specified year
            $annualBudget = AnnualBudget::where('tahun', $year)->first();

            if (!$annualBudget) {
                return response()->json([
                    'success' => false,
                    'message' => 'Annual budget not found for year ' . $year
                ], 404);
            }

            // Total budget allocated
            $totalBudget = (float) $annualBudget->budget;

            // Budget used (approved TORs)
            $budgetUsed = Tor::where('status', 'approved_by_head')
                ->where('budget_id', $annualBudget->budget_id)
                ->sum('budget_submitted');

            // Budget pending (submitted but not yet approved)
            $budgetPending = Tor::whereIn('status', ['submitted', 'reviewed_by_secretary', 'verified_by_admin'])
                ->where('budget_id', $annualBudget->budget_id)
                ->sum('budget_submitted');

            // Budget remaining
            $budgetRemaining = $totalBudget - $budgetUsed;

            // Budget by category
            $budgetByCategory = Tor::select(
                'activity_category.category_def',
                DB::raw('SUM(tor.budget_submitted) as allocated'),
                DB::raw('COUNT(*) as tor_count')
            )
                ->join('activity_category', 'tor.category_id', '=', 'activity_category.category_id')
                ->where('tor.status', 'approved_by_head')
                ->where('tor.budget_id', $annualBudget->budget_id)
                ->groupBy('activity_category.category_def')
                ->get()
                ->map(function ($item) {
                    return [
                        'category' => $item->category_def,
                        'allocated' => (float) $item->allocated,
                        'tor_count' => $item->tor_count,
                    ];
                });

            // Monthly budget usage
            $monthlyUsage = Tor::selectRaw('EXTRACT(MONTH FROM created_at) as month, SUM(budget_submitted) as amount')
                ->where('status', 'approved_by_head')
                ->where('budget_id', $annualBudget->budget_id)
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->pluck('amount', 'month');

            $monthlyUsageData = [];
            $cumulative = 0;
            for ($i = 1; $i <= 12; $i++) {
                $monthAmount = (float) $monthlyUsage->get($i, 0);
                $cumulative += $monthAmount;
                $monthlyUsageData[] = [
                    'month' => date('M', mktime(0, 0, 0, $i, 1)),
                    'monthly_usage' => $monthAmount,
                    'cumulative_usage' => $cumulative,
                ];
            }

            // Top activities by budget
            $topActivities = Tor::select('activity_name', 'budget_submitted', 'start_date', 'end_date', 'status')
                ->where('budget_id', $annualBudget->budget_id)
                ->where('status', 'approved_by_head')
                ->orderBy('budget_submitted', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'activity' => $item->activity_name,
                        'budget' => (float) $item->budget_submitted,
                        'date_range' => $item->start_date->format('Y-m-d') . ' - ' . $item->end_date->format('Y-m-d'),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'year' => $year,
                    'budget_overview' => [
                        'total_budget' => $totalBudget,
                        'budget_used' => (float) $budgetUsed,
                        'budget_pending' => (float) $budgetPending,
                        'budget_remaining' => $budgetRemaining,
                        'usage_percentage' => $totalBudget > 0 ? round(($budgetUsed / $totalBudget) * 100, 2) : 0,
                        'pending_percentage' => $totalBudget > 0 ? round(($budgetPending / $totalBudget) * 100, 2) : 0,
                    ],
                    'budget_by_category' => $budgetByCategory,
                    'monthly_usage' => $monthlyUsageData,
                    'top_activities' => $topActivities,
                    'statistics' => [
                        'total_approved_tors' => Tor::where('status', 'approved_by_head')
                            ->where('budget_id', $annualBudget->budget_id)
                            ->count(),
                        'total_pending_tors' => Tor::whereIn('status', ['submitted', 'reviewed_by_secretary', 'verified_by_admin'])
                            ->where('budget_id', $annualBudget->budget_id)
                            ->count(),
                        'average_budget_per_activity' => Tor::where('status', 'approved_by_head')
                            ->where('budget_id', $annualBudget->budget_id)
                            ->avg('budget_submitted'),
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get annual budget data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
