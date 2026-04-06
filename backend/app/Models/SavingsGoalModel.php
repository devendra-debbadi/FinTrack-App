<?php

namespace App\Models;

use CodeIgniter\Model;

class SavingsGoalModel extends Model
{
    protected $table            = 'savings_goals';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $allowedFields = [
        'user_id',
        'profile_id',
        'name',
        'target_amount',
        'current_amount',
        'currency',
        'target_date',
        'icon',
        'color',
        'is_completed',
    ];

    protected $validationRules = [
        'user_id'       => 'required|integer',
        'profile_id'    => 'required|integer',
        'name'          => 'required|min_length[2]|max_length[100]',
        'target_amount' => 'required|decimal|greater_than[0]',
    ];

    /**
     * Get all goals for a user/profile with progress.
     */
    public function getByProfile(int $userId, int $profileId, bool $includeCompleted = true): array
    {
        $builder = $this->where('user_id', $userId)
            ->where('profile_id', $profileId);

        if (! $includeCompleted) {
            $builder->where('is_completed', 0);
        }

        $goals = $builder->orderBy('is_completed', 'ASC')
            ->orderBy('target_date', 'ASC')
            ->findAll();

        foreach ($goals as &$goal) {
            $goal['percentage'] = $goal['target_amount'] > 0
                ? round(((float) $goal['current_amount'] / (float) $goal['target_amount']) * 100, 1)
                : 0;
            $goal['remaining'] = max(0, (float) $goal['target_amount'] - (float) $goal['current_amount']);
        }

        return $goals;
    }

    /**
     * Add a deposit to a goal.
     */
    public function addDeposit(int $goalId, float $amount): bool
    {
        $goal = $this->find($goalId);
        if (! $goal) {
            return false;
        }

        $newAmount = (float) $goal['current_amount'] + $amount;
        $isCompleted = $newAmount >= (float) $goal['target_amount'] ? 1 : 0;

        return $this->update($goalId, [
            'current_amount' => $newAmount,
            'is_completed'   => $isCompleted,
        ]);
    }
}
