<?php

namespace App\Services;

use App\Models\TransactionModel;
use App\Models\CategoryModel;
use App\Models\BudgetModel;

class ReportService
{
    private TransactionModel $transactionModel;

    public function __construct()
    {
        $this->transactionModel = model(TransactionModel::class);
    }

    /**
     * Monthly summary report.
     */
    public function monthlySummary(int $userId, int $profileId, int $year, int $month): array
    {
        $dateFrom = sprintf('%04d-%02d-01', $year, $month);
        $dateTo = date('Y-m-t', strtotime($dateFrom));

        $totals = $this->transactionModel->getPeriodTotals($userId, $profileId, $dateFrom, $dateTo);
        $categoryBreakdown = $this->transactionModel->getCategoryBreakdown($userId, $profileId, $dateFrom, $dateTo, 'expense');
        $incomeBreakdown = $this->transactionModel->getCategoryBreakdown($userId, $profileId, $dateFrom, $dateTo, 'income');
        $dailySpending = $this->transactionModel->getDailySpending($userId, $profileId, $dateFrom, $dateTo);

        // Previous month comparison
        $prevDate = date('Y-m-01', strtotime($dateFrom . ' -1 month'));
        $prevTo = date('Y-m-t', strtotime($prevDate));
        $prevTotals = $this->transactionModel->getPeriodTotals($userId, $profileId, $prevDate, $prevTo);

        return [
            'period'             => ['year' => $year, 'month' => $month, 'from' => $dateFrom, 'to' => $dateTo],
            'totals'             => $totals,
            'previous_totals'    => $prevTotals,
            'expense_categories' => $categoryBreakdown,
            'income_categories'  => $incomeBreakdown,
            'daily_spending'     => $dailySpending,
        ];
    }

    /**
     * Yearly summary report.
     */
    public function yearlySummary(int $userId, int $profileId, int $year): array
    {
        $dateFrom = "{$year}-01-01";
        $dateTo = "{$year}-12-31";

        $totals = $this->transactionModel->getPeriodTotals($userId, $profileId, $dateFrom, $dateTo);
        $monthlyTrend = $this->transactionModel->getMonthlySummary($userId, $profileId, $year);
        $categoryBreakdown = $this->transactionModel->getCategoryBreakdown($userId, $profileId, $dateFrom, $dateTo, 'expense');

        // Previous year
        $prevYear = $year - 1;
        $prevTotals = $this->transactionModel->getPeriodTotals($userId, $profileId, "{$prevYear}-01-01", "{$prevYear}-12-31");

        return [
            'period'             => ['year' => $year, 'from' => $dateFrom, 'to' => $dateTo],
            'totals'             => $totals,
            'previous_totals'    => $prevTotals,
            'monthly_trend'      => $monthlyTrend,
            'expense_categories' => $categoryBreakdown,
        ];
    }

    /**
     * Category detail report.
     */
    public function categoryDetail(int $userId, int $profileId, int $categoryId, string $dateFrom, string $dateTo): array
    {
        $categoryModel = model(CategoryModel::class);
        $category = $categoryModel->find($categoryId);

        if (! $category || $category['user_id'] !== $userId) {
            return ['error' => 'Category not found'];
        }

        $transactions = $this->transactionModel
            ->where('user_id', $userId)
            ->where('profile_id', $profileId)
            ->where('category_id', $categoryId)
            ->where('transaction_date >=', $dateFrom)
            ->where('transaction_date <=', $dateTo)
            ->orderBy('transaction_date', 'DESC')
            ->findAll();

        $total = array_sum(array_column($transactions, 'amount'));
        $average = count($transactions) > 0 ? $total / count($transactions) : 0;

        return [
            'category'     => $category,
            'transactions' => $transactions,
            'summary'      => [
                'total'       => $total,
                'average'     => round($average, 2),
                'count'       => count($transactions),
                'highest'     => ! empty($transactions) ? max(array_column($transactions, 'amount')) : 0,
                'lowest'      => ! empty($transactions) ? min(array_column($transactions, 'amount')) : 0,
            ],
        ];
    }

    /**
     * Income vs Expense comparison across months.
     */
    public function incomeVsExpense(int $userId, int $profileId, int $year): array
    {
        $monthly = $this->transactionModel->getMonthlySummary($userId, $profileId, $year);

        $result = [];
        for ($m = 1; $m <= 12; $m++) {
            $result[$m] = [
                'month'   => $m,
                'income'  => 0,
                'expense' => 0,
                'balance' => 0,
            ];
        }

        foreach ($monthly as $row) {
            $result[(int) $row['month']][$row['type']] = (float) $row['total'];
        }

        foreach ($result as &$month) {
            $month['balance'] = $month['income'] - $month['expense'];
        }

        return array_values($result);
    }

    /**
     * Budget performance report — all budgets for a year with actual vs planned.
     */
    public function budgetPerformance(int $userId, int $profileId, int $year): array
    {
        $budgetModel = model(BudgetModel::class);
        $results = [];

        for ($m = 1; $m <= 12; $m++) {
            $budgets = $budgetModel->getBudgetsWithSpent($userId, $profileId, $year, $m);
            if (! empty($budgets)) {
                $results[] = [
                    'month'   => $m,
                    'budgets' => $budgets,
                ];
            }
        }

        // Calculate yearly summary
        $totalBudgeted = 0;
        $totalSpent = 0;
        $overBudgetCount = 0;
        $totalBudgets = 0;

        foreach ($results as $monthData) {
            foreach ($monthData['budgets'] as $b) {
                $totalBudgeted += (float) $b['amount'];
                $totalSpent += $b['spent'];
                $totalBudgets++;
                if ($b['percentage'] > 100) {
                    $overBudgetCount++;
                }
            }
        }

        return [
            'year'    => $year,
            'months'  => $results,
            'summary' => [
                'total_budgeted'    => $totalBudgeted,
                'total_spent'       => $totalSpent,
                'total_budgets'     => $totalBudgets,
                'over_budget_count' => $overBudgetCount,
                'overall_percentage' => $totalBudgeted > 0 ? round(($totalSpent / $totalBudgeted) * 100, 1) : 0,
            ],
        ];
    }
}
