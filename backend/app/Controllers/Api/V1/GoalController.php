<?php

namespace App\Controllers\Api\V1;

use App\Models\SavingsGoalModel;
use App\Models\SavingsDepositModel;

class GoalController extends BaseApiController
{
    private SavingsGoalModel $goalModel;
    private SavingsDepositModel $depositModel;

    public function __construct()
    {
        $this->goalModel = model(SavingsGoalModel::class);
        $this->depositModel = model(SavingsDepositModel::class);
    }

    /**
     * GET /api/v1/goals
     */
    public function index()
    {
        $profileId = $this->getProfileId();
        $includeCompleted = (bool) ($this->request->getGet('include_completed') ?? true);

        $goals = $this->goalModel->getByProfile(
            $this->getUserId(),
            $profileId,
            $includeCompleted
        );

        return $this->success($goals);
    }

    /**
     * GET /api/v1/goals/:id
     */
    public function show($id = null)
    {
        $goal = $this->goalModel
            ->where('user_id', $this->getUserId())
            ->find($id);

        if (! $goal) {
            return $this->error('Goal not found', 404);
        }

        $goal['deposits'] = $this->depositModel->getByGoal($id);
        $goal['percentage'] = $goal['target_amount'] > 0
            ? round(((float) $goal['current_amount'] / (float) $goal['target_amount']) * 100, 1)
            : 0;

        return $this->success($goal);
    }

    /**
     * POST /api/v1/goals
     */
    public function create()
    {
        $rules = [
            'name'          => 'required|min_length[2]|max_length[100]',
            'target_amount' => 'required|decimal|greater_than[0]',
        ];

        if (! $this->validate($rules)) {
            return $this->error('Validation failed', 422, $this->validator->getErrors());
        }

        $profileId = $this->getProfileId();

        $data = [
            'user_id'        => $this->getUserId(),
            'profile_id'     => $profileId,
            'name'           => $this->getInput('name'),
            'target_amount'  => $this->getInput('target_amount'),
            'current_amount' => 0,
            'currency'       => $this->getInput('currency') ?? 'EUR',
            'target_date'    => $this->getInput('target_date'),
            'icon'           => $this->getInput('icon') ?? 'pi-star',
            'color'          => $this->getInput('color') ?? '#10b981',
        ];

        $id = $this->goalModel->insert($data);

        $this->logActivity('goal_create', 'goal', $id, "Savings goal created: {$this->getInput('name')}");

        return $this->success($this->goalModel->find($id), 'Goal created', 201);
    }

    /**
     * PUT /api/v1/goals/:id
     */
    public function update($id = null)
    {
        $goal = $this->goalModel
            ->where('user_id', $this->getUserId())
            ->find($id);

        if (! $goal) {
            return $this->error('Goal not found', 404);
        }

        $data = array_filter([
            'name'          => $this->getInput('name'),
            'target_amount' => $this->getInput('target_amount'),
            'currency'      => $this->getInput('currency'),
            'target_date'   => $this->getInput('target_date'),
            'icon'          => $this->getInput('icon'),
            'color'         => $this->getInput('color'),
        ], fn($v) => $v !== null);

        $this->goalModel->update($id, $data);

        $this->logActivity('goal_update', 'goal', (int) $id, 'Savings goal updated');

        return $this->success($this->goalModel->find($id), 'Goal updated');
    }

    /**
     * POST /api/v1/goals/:id/deposit
     */
    public function deposit($id = null)
    {
        $goal = $this->goalModel
            ->where('user_id', $this->getUserId())
            ->find($id);

        if (! $goal) {
            return $this->error('Goal not found', 404);
        }

        $rules = [
            'amount'       => 'required|decimal|greater_than[0]',
            'deposit_date' => 'required|valid_date',
        ];

        if (! $this->validate($rules)) {
            return $this->error('Validation failed', 422, $this->validator->getErrors());
        }

        $amount = (float) $this->getInput('amount');

        // Create deposit record
        $this->depositModel->insert([
            'goal_id'      => $id,
            'amount'       => $amount,
            'note'         => $this->getInput('note'),
            'deposit_date' => $this->getInput('deposit_date'),
            'created_at'   => date('Y-m-d H:i:s'),
        ]);

        // Update goal amount
        $this->goalModel->addDeposit($id, $amount);

        $updatedGoal = $this->goalModel->find($id);
        $updatedGoal['deposits'] = $this->depositModel->getByGoal($id);

        $this->logActivity('goal_deposit', 'goal', (int) $id, "Deposited {$amount} to savings goal");

        return $this->success($updatedGoal, 'Deposit added');
    }

    /**
     * DELETE /api/v1/goals/:id
     */
    public function delete($id = null)
    {
        $goal = $this->goalModel
            ->where('user_id', $this->getUserId())
            ->find($id);

        if (! $goal) {
            return $this->error('Goal not found', 404);
        }

        $this->goalModel->delete($id);
        $this->logActivity('goal_delete', 'goal', (int) $id, 'Savings goal deleted');

        return $this->success(null, 'Goal deleted');
    }
}
