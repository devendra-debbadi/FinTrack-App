<?php

namespace App\Controllers\Api\V1;

use App\Models\BudgetModel;

class BudgetController extends BaseApiController
{
    private BudgetModel $budgetModel;

    public function __construct()
    {
        $this->budgetModel = model(BudgetModel::class);
    }

    /**
     * GET /api/v1/budgets
     */
    public function index()
    {
        $profileId = $this->getProfileId();
        $year = (int) ($this->request->getGet('year') ?? date('Y'));
        $month = $this->request->getGet('month') ? (int) $this->request->getGet('month') : (int) date('m');

        $budgets = $this->budgetModel->getBudgetsWithSpent(
            $this->getUserId(),
            $profileId,
            $year,
            $month
        );

        return $this->success($budgets);
    }

    /**
     * POST /api/v1/budgets
     */
    public function create()
    {
        $rules = [
            'amount' => 'required|decimal|greater_than[0]',
            'period' => 'required|in_list[monthly,yearly]',
            'year'   => 'required|integer',
        ];

        if (! $this->validate($rules)) {
            return $this->error('Validation failed', 422, $this->validator->getErrors());
        }

        $profileId = $this->getProfileId();

        $data = [
            'user_id'     => $this->getUserId(),
            'profile_id'  => $profileId,
            'category_id' => $this->getInput('category_id') ?: null,
            'amount'      => $this->getInput('amount'),
            'currency'    => $this->getInput('currency') ?? 'EUR',
            'period'      => $this->getInput('period'),
            'month'       => $this->getInput('month'),
            'year'        => $this->getInput('year'),
        ];

        $id = $this->budgetModel->insert($data);

        $this->logActivity('budget_create', 'budget', $id, "Budget created for {$this->getInput('period')}");

        return $this->success($this->budgetModel->find($id), 'Budget created', 201);
    }

    /**
     * PUT /api/v1/budgets/:id
     */
    public function update($id = null)
    {
        $budget = $this->budgetModel
            ->where('user_id', $this->getUserId())
            ->find($id);

        if (! $budget) {
            return $this->error('Budget not found', 404);
        }

        $data = array_filter([
            'amount'      => $this->getInput('amount'),
            'category_id' => $this->getInput('category_id'),
            'period'      => $this->getInput('period'),
            'month'       => $this->getInput('month'),
            'year'        => $this->getInput('year'),
        ], fn($v) => $v !== null);

        $this->budgetModel->update($id, $data);

        $this->logActivity('budget_update', 'budget', (int) $id, 'Budget updated');

        return $this->success($this->budgetModel->find($id), 'Budget updated');
    }

    /**
     * DELETE /api/v1/budgets/:id
     */
    public function delete($id = null)
    {
        $budget = $this->budgetModel
            ->where('user_id', $this->getUserId())
            ->find($id);

        if (! $budget) {
            return $this->error('Budget not found', 404);
        }

        $this->budgetModel->delete($id);
        $this->logActivity('budget_delete', 'budget', (int) $id, 'Budget deleted');

        return $this->success(null, 'Budget deleted');
    }
}
