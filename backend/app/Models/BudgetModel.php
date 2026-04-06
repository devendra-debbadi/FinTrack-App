<?php

namespace App\Models;

use CodeIgniter\Model;

class BudgetModel extends Model
{
    protected $table            = 'budgets';
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
        'amount',
        'currency',
        'period',
        'month',
        'year',
    ];

    protected $validationRules = [
        'user_id'    => 'required|integer',
        'profile_id' => 'required|integer',
        'amount'     => 'required|decimal|greater_than[0]',
        'period'     => 'required|in_list[monthly,yearly]',
        'year'       => 'required|integer',
    ];

    /**
     * Get budgets for a user/profile/period with spent amounts.
     */
    public function getBudgetsWithSpent(int $userId, int $profileId, int $year, ?int $month = null): array
    {
        $builder = $this->select('budgets.*, categories.name as category_name, categories.icon as category_icon, categories.color as category_color')
            ->join('categories', 'categories.id = budgets.category_id', 'left')
            ->where('budgets.user_id', $userId)
            ->where('budgets.profile_id', $profileId)
            ->where('budgets.year', $year);

        if ($month !== null) {
            $builder->where('budgets.month', $month);
        }

        $budgets = $builder->findAll();

        // Calculate spent amount for each budget
        $transactionModel = model(TransactionModel::class);
        foreach ($budgets as &$budget) {
            $dateFrom = sprintf('%04d-%02d-01', $budget['year'], $budget['month'] ?? 1);
            if ($budget['period'] === 'monthly' && $budget['month']) {
                $dateTo = date('Y-m-t', strtotime($dateFrom));
            } else {
                $dateTo = $budget['year'] . '-12-31';
            }

            $query = $transactionModel
                ->where('user_id', $userId)
                ->where('profile_id', $profileId)
                ->where('type', 'expense')
                ->where('transaction_date >=', $dateFrom)
                ->where('transaction_date <=', $dateTo);

            if ($budget['category_id']) {
                $query->where('category_id', $budget['category_id']);
            }

            $spent = $query->selectSum('amount', 'total')->first();
            $budget['spent'] = (float) ($spent['total'] ?? 0);
            $budget['remaining'] = max(0, (float) $budget['amount'] - $budget['spent']);
            $budget['percentage'] = $budget['amount'] > 0
                ? round(($budget['spent'] / (float) $budget['amount']) * 100, 1)
                : 0;
        }

        return $budgets;
    }
}
