<?php

namespace App\Listeners;

use App\Events\LpjApproved;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UpdateDashboardStatistics
{
    /**
     * Handle the event.
     */
    public function handle(LpjApproved $event): void
    {
        $lpj = $event->lpj;

        try {
            // Clear dashboard cache
            $this->clearDashboardCache();

            // Log the update
            Log::info('Dashboard statistics updated after LPJ approval', [
                'lpj_id' => $lpj->lpj_id,
                'tor_id' => $lpj->tor_id,
                'budget_used' => $lpj->budget_used,
                'approved_at' => now(),
            ]);

            // Optional: Trigger real-time notification via broadcasting
            // broadcast(new DashboardUpdated($lpj));

        } catch (\Exception $e) {
            Log::error('Failed to update dashboard after LPJ approval', [
                'lpj_id' => $lpj->lpj_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Clear all dashboard-related cache
     */
    private function clearDashboardCache(): void
    {
        $cacheKeys = [
            'dashboard_summary',
            'dashboard_charts',
            'dashboard_annual_budget',
            'dashboard_statistics',
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }

        // Clear cache with year suffix
        $currentYear = date('Y');
        Cache::forget("dashboard_charts_{$currentYear}");
        Cache::forget("dashboard_annual_budget_{$currentYear}");
    }
}
