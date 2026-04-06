<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionModel extends Model
{
    protected $table            = 'transactions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $allowedFields = [
        'user_id',
        'profile_id',
        'category_id',
        'type',
        'amount',
        'currency',
        'description',
        'transaction_date',
        'is_recurring',
        'recurring_id',
        'notes',
    ];

    protected $validationRules = [
        'user_id'          => 'required|integer',
        'profile_id'       => 'required|integer',
        'category_id'      => 'required|integer',
        'type'             => 'required|in_list[income,expense]',
        'amount'           => 'required|decimal|greater_than[0]',
        'transaction_date' => 'required|valid_date',
    ];

    /**
     * Get transactions with filters and pagination.
     */
    public function getFiltered(int $userId, int $profileId, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $builder = $this->select('transactions.*, categories.name as category_name, categories.icon as category_icon, categories.color as category_color')
            ->join('categories', 'categories.id = transactions.category_id', 'left')
            ->where('transactions.user_id', $userId)
            ->where('transactions.profile_id', $profileId);

        if (! empty($filters['type'])) {
            $builder->where('transactions.type', $filters['type']);
        }

        if (! empty($filters['category_id'])) {
            $builder->where('transactions.category_id', $filters['category_id']);
        }

        if (! empty($filters['date_from'])) {
            $builder->where('transactions.transaction_date >=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $builder->where('transactions.transaction_date <=', $filters['date_to']);
        }

        if (! empty($filters['search'])) {
            $builder->like('transactions.description', $filters['search']);
        }

        if (! empty($filters['min_amount'])) {
            $builder->where('transactions.amount >=', $filters['min_amount']);
        }

        if (! empty($filters['max_amount'])) {
            $builder->where('transactions.amount <=', $filters['max_amount']);
        }

        // Get total count before pagination
        $total = $builder->countAllResults(false);

        $sortField = $filters['sort'] ?? 'transaction_date';
        $sortDir = strtoupper($filters['direction'] ?? 'DESC');
        $allowedSorts = ['transaction_date', 'amount', 'created_at', 'description'];

        if (! in_array($sortField, $allowedSorts, true)) {
            $sortField = 'transaction_date';
        }

        if (! in_array($sortDir, ['ASC', 'DESC'], true)) {
            $sortDir = 'DESC';
        }

        $data = $builder->orderBy("transactions.{$sortField}", $sortDir)
            ->limit($perPage, ($page - 1) * $perPage)
            ->findAll();

        return [
            'data'       => $data,
            'total'      => $total,
            'page'       => $page,
            'per_page'   => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
        ];
    }

    /**
     * Get monthly summary (income/expense totals by month).
     */
    public function getMonthlySummary(int $userId, int $profileId, int $year): array
    {
        return $this->select("
                MONTH(transaction_date) as month,
                type,
                SUM(amount) as total
            ")
            ->where('user_id', $userId)
            ->where('profile_id', $profileId)
            ->where('YEAR(transaction_date)', $year)
            ->groupBy(['MONTH(transaction_date)', 'type'])
            ->orderBy('month', 'ASC')
            ->findAll();
    }

    /**
     * Get category-wise spending for a period.
     */
    public function getCategoryBreakdown(int $userId, int $profileId, string $dateFrom, string $dateTo, string $type = 'expense'): array
    {
        return $this->select('
                categories.name as category_name,
                categories.icon as category_icon,
                categories.color as category_color,
                SUM(transactions.amount) as total,
                COUNT(*) as count
            ')
            ->join('categories', 'categories.id = transactions.category_id')
            ->where('transactions.user_id', $userId)
            ->where('transactions.profile_id', $profileId)
            ->where('transactions.type', $type)
            ->where('transactions.transaction_date >=', $dateFrom)
            ->where('transactions.transaction_date <=', $dateTo)
            ->groupBy('transactions.category_id')
            ->orderBy('total', 'DESC')
            ->findAll();
    }

    /**
     * Get daily spending for heatmap.
     */
    public function getDailySpending(int $userId, int $profileId, string $dateFrom, string $dateTo): array
    {
        return $this->select('transaction_date, SUM(amount) as total')
            ->where('user_id', $userId)
            ->where('profile_id', $profileId)
            ->where('type', 'expense')
            ->where('transaction_date >=', $dateFrom)
            ->where('transaction_date <=', $dateTo)
            ->groupBy('transaction_date')
            ->orderBy('transaction_date', 'ASC')
            ->findAll();
    }

    /**
     * Get totals for a period.
     */
    public function getPeriodTotals(int $userId, int $profileId, string $dateFrom, string $dateTo): array
    {
        $result = $this->select('type, SUM(amount) as total, COUNT(*) as count')
            ->where('user_id', $userId)
            ->where('profile_id', $profileId)
            ->where('transaction_date >=', $dateFrom)
            ->where('transaction_date <=', $dateTo)
            ->groupBy('type')
            ->findAll();

        $totals = ['income' => 0, 'expense' => 0, 'income_count' => 0, 'expense_count' => 0];
        foreach ($result as $row) {
            $totals[$row['type']] = (float) $row['total'];
            $totals[$row['type'] . '_count'] = (int) $row['count'];
        }
        $totals['balance'] = $totals['income'] - $totals['expense'];
        $totals['savings_rate'] = $totals['income'] > 0 ? round(($totals['balance'] / $totals['income']) * 100, 1) : 0;

        return $totals;
    }
}
