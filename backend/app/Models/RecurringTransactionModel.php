<?php

namespace App\Models;

use CodeIgniter\Model;

class RecurringTransactionModel extends Model
{
    protected $table            = 'recurring_transactions';
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
        'frequency',
        'next_due',
        'is_active',
    ];

    protected $validationRules = [
        'user_id'    => 'required|integer',
        'profile_id' => 'required|integer',
        'category_id' => 'required|integer',
        'type'       => 'required|in_list[income,expense]',
        'amount'     => 'required|decimal|greater_than[0]',
        'frequency'  => 'required|in_list[daily,weekly,monthly,yearly]',
        'next_due'   => 'required|valid_date',
    ];

    /**
     * Get active recurring transactions due today or earlier.
     */
    public function getDueTransactions(): array
    {
        return $this->where('is_active', 1)
            ->where('next_due <=', date('Y-m-d'))
            ->findAll();
    }

    /**
     * Get all recurring transactions for a user/profile.
     */
    public function getByProfile(int $userId, int $profileId): array
    {
        return $this->select('recurring_transactions.*, categories.name as category_name, categories.icon as category_icon, categories.color as category_color')
            ->join('categories', 'categories.id = recurring_transactions.category_id', 'left')
            ->where('recurring_transactions.user_id', $userId)
            ->where('recurring_transactions.profile_id', $profileId)
            ->orderBy('recurring_transactions.next_due', 'ASC')
            ->findAll();
    }

    /**
     * Calculate the next due date based on frequency.
     */
    public function calculateNextDue(string $currentDue, string $frequency): string
    {
        $date = new \DateTime($currentDue);

        return match ($frequency) {
            'daily'   => $date->modify('+1 day')->format('Y-m-d'),
            'weekly'  => $date->modify('+1 week')->format('Y-m-d'),
            'monthly' => $date->modify('+1 month')->format('Y-m-d'),
            'yearly'  => $date->modify('+1 year')->format('Y-m-d'),
            default   => $date->modify('+1 month')->format('Y-m-d'),
        };
    }
}
