<?php

namespace App\Controllers\Api\V1;

use App\Services\DashboardService;

class DashboardController extends BaseApiController
{
    private DashboardService $dashboardService;

    public function __construct()
    {
        $this->dashboardService = new DashboardService();
    }

    /**
     * GET /api/v1/dashboard
     */
    public function index()
    {
        $period = $this->request->getGet('period') ?? 'month';
        if (! in_array($period, ['week', 'month', 'quarter', 'year'], true)) {
            $period = 'month';
        }
        $profileId = $this->getProfileId();

        $data = $this->dashboardService->getDashboardData(
            $this->getUserId(),
            $profileId,
            $period
        );

        return $this->success($data);
    }

    /**
     * GET /api/v1/dashboard/kpis
     */
    public function kpis()
    {
        $period = $this->request->getGet('period') ?? 'month';
        if (! in_array($period, ['week', 'month', 'quarter', 'year'], true)) {
            $period = 'month';
        }
        $profileId = $this->getProfileId();

        $dates = $this->getPeriodDates($period);
        $kpis = $this->dashboardService->getKPIs(
            $this->getUserId(),
            $profileId,
            $dates['from'],
            $dates['to']
        );

        return $this->success($kpis);
    }

    /**
     * GET /api/v1/dashboard/trend
     */
    public function trend()
    {
        $profileId = $this->getProfileId();
        $data = $this->dashboardService->getMonthlyTrend($this->getUserId(), $profileId);

        return $this->success($data);
    }

    /**
     * GET /api/v1/dashboard/heatmap
     */
    public function heatmap()
    {
        $profileId = $this->getProfileId();
        $data = $this->dashboardService->getDailySpending($this->getUserId(), $profileId);

        return $this->success($data);
    }

    /**
     * GET /api/v1/dashboard/insights
     */
    public function insights()
    {
        $period = $this->request->getGet('period') ?? 'month';
        $profileId = $this->getProfileId();
        $dates = $this->getPeriodDates($period);

        $data = $this->dashboardService->getInsights(
            $this->getUserId(),
            $profileId,
            $dates['from'],
            $dates['to']
        );

        return $this->success($data);
    }

    private function getPeriodDates(string $period): array
    {
        return match ($period) {
            'week' => [
                'from' => date('Y-m-d', strtotime('monday this week')),
                'to'   => date('Y-m-d'),
            ],
            'month' => [
                'from' => date('Y-m-01'),
                'to'   => date('Y-m-d'),
            ],
            'quarter' => [
                'from' => date('Y-m-01', strtotime('first day of -2 months')),
                'to'   => date('Y-m-d'),
            ],
            'year' => [
                'from' => date('Y-01-01'),
                'to'   => date('Y-m-d'),
            ],
            default => [
                'from' => date('Y-m-01'),
                'to'   => date('Y-m-d'),
            ],
        };
    }
}
