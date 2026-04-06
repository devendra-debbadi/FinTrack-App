<?php

namespace App\Services;

use App\Models\TransactionModel;
use App\Models\BudgetModel;
use App\Models\SavingsGoalModel;
use App\Models\CategoryModel;

class DashboardService
{
    private TransactionModel $transactionModel;

    public function __construct()
    {
        $this->transactionModel = model(TransactionModel::class);
    }

    /**
     * Get complete dashboard data.
     */
    public function getDashboardData(int $userId, int $profileId, string $period = 'month'): array
    {
        $dates = $this->getPeriodDates($period);

        return [
            'kpis'               => $this->getKPIs($userId, $profileId, $dates['from'], $dates['to']),
            'monthly_trend'      => $this->getMonthlyTrend($userId, $profileId),
            'category_breakdown' => $this->getCategoryBreakdown($userId, $profileId, $dates['from'], $dates['to']),
            'daily_spending'     => $this->getDailySpending($userId, $profileId),
            'budget_status'      => $this->getBudgetStatus($userId, $profileId),
            'savings_progress'   => $this->getSavingsProgress($userId, $profileId),
            'recent_transactions' => $this->getRecentTransactions($userId, $profileId),
            'insights'           => $this->getInsights($userId, $profileId, $dates['from'], $dates['to']),
        ];
    }

    /**
     * KPI cards data.
     */
    public function getKPIs(int $userId, int $profileId, string $dateFrom, string $dateTo): array
    {
        $current = $this->transactionModel->getPeriodTotals($userId, $profileId, $dateFrom, $dateTo);

        // Previous period for comparison
        $daysDiff = (strtotime($dateTo) - strtotime($dateFrom)) / 86400;
        $prevFrom = date('Y-m-d', strtotime($dateFrom . " -{$daysDiff} days"));
        $prevTo = date('Y-m-d', strtotime($dateFrom . ' -1 day'));
        $previous = $this->transactionModel->getPeriodTotals($userId, $profileId, $prevFrom, $prevTo);

        return [
            'income'  => [
                'amount'  => $current['income'],
                'change'  => $this->calculateChange($current['income'], $previous['income']),
                'count'   => $current['income_count'],
            ],
            'expense' => [
                'amount'  => $current['expense'],
                'change'  => $this->calculateChange($current['expense'], $previous['expense']),
                'count'   => $current['expense_count'],
            ],
            'balance' => [
                'amount' => $current['balance'],
                'change' => $this->calculateChange($current['balance'], $previous['balance']),
            ],
            'savings_rate' => [
                'percentage' => $current['savings_rate'],
                'change'     => $current['savings_rate'] - $previous['savings_rate'],
            ],
        ];
    }

    /**
     * Monthly trend (12 months).
     */
    public function getMonthlyTrend(int $userId, int $profileId): array
    {
        $year = (int) date('Y');
        $data = $this->transactionModel->getMonthlySummary($userId, $profileId, $year);

        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = ['month' => $m, 'income' => 0, 'expense' => 0];
        }

        foreach ($data as $row) {
            $months[(int) $row['month']][$row['type']] = (float) $row['total'];
        }

        return array_values($months);
    }

    /**
     * Category breakdown for pie/treemap.
     */
    public function getCategoryBreakdown(int $userId, int $profileId, string $dateFrom, string $dateTo): array
    {
        return [
            'expense' => $this->transactionModel->getCategoryBreakdown($userId, $profileId, $dateFrom, $dateTo, 'expense'),
            'income'  => $this->transactionModel->getCategoryBreakdown($userId, $profileId, $dateFrom, $dateTo, 'income'),
        ];
    }

    /**
     * Daily spending for heatmap (last 365 days).
     */
    public function getDailySpending(int $userId, int $profileId): array
    {
        $dateTo = date('Y-m-d');
        $dateFrom = date('Y-m-d', strtotime('-365 days'));

        return $this->transactionModel->getDailySpending($userId, $profileId, $dateFrom, $dateTo);
    }

    /**
     * Budget status for gauges.
     */
    public function getBudgetStatus(int $userId, int $profileId): array
    {
        $budgetModel = model(BudgetModel::class);
        return $budgetModel->getBudgetsWithSpent($userId, $profileId, (int) date('Y'), (int) date('m'));
    }

    /**
     * Savings goals progress.
     */
    public function getSavingsProgress(int $userId, int $profileId): array
    {
        $goalModel = model(SavingsGoalModel::class);
        return $goalModel->getByProfile($userId, $profileId, false);
    }

    /**
     * Recent transactions (latest 5).
     */
    public function getRecentTransactions(int $userId, int $profileId): array
    {
        $result = $this->transactionModel->getFiltered($userId, $profileId, [], 1, 5);
        return $result['data'];
    }

    /**
     * Smart insights based on spending patterns.
     */
    public function getInsights(int $userId, int $profileId, string $dateFrom, string $dateTo): array
    {
        $insights = [];

        // Current period category breakdown
        $currentBreakdown = $this->transactionModel->getCategoryBreakdown(
            $userId, $profileId, $dateFrom, $dateTo, 'expense'
        );

        // Previous period
        $daysDiff = (strtotime($dateTo) - strtotime($dateFrom)) / 86400;
        $prevFrom = date('Y-m-d', strtotime($dateFrom . " -{$daysDiff} days"));
        $prevTo = date('Y-m-d', strtotime($dateFrom . ' -1 day'));
        $prevBreakdown = $this->transactionModel->getCategoryBreakdown(
            $userId, $profileId, $prevFrom, $prevTo, 'expense'
        );

        // Build previous period lookup
        $prevLookup = [];
        foreach ($prevBreakdown as $cat) {
            $prevLookup[$cat['category_name']] = (float) $cat['total'];
        }

        // Compare categories
        foreach ($currentBreakdown as $cat) {
            $currentAmount = (float) $cat['total'];
            $prevAmount = $prevLookup[$cat['category_name']] ?? 0;

            if ($prevAmount > 0) {
                $change = (($currentAmount - $prevAmount) / $prevAmount) * 100;

                if ($change >= 30) {
                    $insights[] = [
                        'type'     => 'warning',
                        'icon'     => 'pi-arrow-up',
                        'message'  => sprintf(
                            '%s spending up %.0f%% vs last period (€%.2f → €%.2f)',
                            $cat['category_name'], $change, $prevAmount, $currentAmount
                        ),
                        'category' => $cat['category_name'],
                    ];
                } elseif ($change <= -20) {
                    $insights[] = [
                        'type'     => 'success',
                        'icon'     => 'pi-arrow-down',
                        'message'  => sprintf(
                            '%s spending down %.0f%% — great control!',
                            $cat['category_name'], abs($change)
                        ),
                        'category' => $cat['category_name'],
                    ];
                }
            }
        }

        // Top spending category
        if (! empty($currentBreakdown)) {
            $top = $currentBreakdown[0];
            $totals = $this->transactionModel->getPeriodTotals($userId, $profileId, $dateFrom, $dateTo);
            if ($totals['expense'] > 0) {
                $pct = round(((float) $top['total'] / $totals['expense']) * 100);
                $insights[] = [
                    'type'    => 'info',
                    'icon'    => 'pi-chart-pie',
                    'message' => sprintf(
                        'Top spending: %s at €%.2f (%d%% of total expenses)',
                        $top['category_name'], $top['total'], $pct
                    ),
                    'category' => $top['category_name'],
                ];
            }
        }

        return $insights;
    }

    /**
     * Calculate period date range.
     */
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

    /**
     * Calculate percentage change.
     */
    private function calculateChange(float $current, float $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        return round((($current - $previous) / abs($previous)) * 100, 1);
    }
}
