<?php

namespace App\Models;

use CodeIgniter\Model;

class SavingsDepositModel extends Model
{
    protected $table            = 'savings_deposits';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = false;

    protected $allowedFields = [
        'goal_id',
        'amount',
        'note',
        'deposit_date',
        'created_at',
    ];

    protected $validationRules = [
        'goal_id'      => 'required|integer',
        'amount'       => 'required|decimal|greater_than[0]',
        'deposit_date' => 'required|valid_date',
    ];

    /**
     * Get deposits for a goal.
     */
    public function getByGoal(int $goalId): array
    {
        return $this->where('goal_id', $goalId)
            ->orderBy('deposit_date', 'DESC')
            ->findAll();
    }
}
