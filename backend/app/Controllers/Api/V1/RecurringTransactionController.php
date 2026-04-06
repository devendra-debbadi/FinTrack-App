<?php

namespace App\Controllers\Api\V1;

use App\Models\RecurringTransactionModel;
use App\Models\TransactionModel;

class RecurringTransactionController extends BaseApiController
{
    private RecurringTransactionModel $recurringModel;

    public function __construct()
    {
        $this->recurringModel = model(RecurringTransactionModel::class);
    }

    /**
     * GET /api/v1/recurring
     */
    public function index()
    {
        $profileId = $this->getProfileId();
        $items = $this->recurringModel->getByProfile($this->getUserId(), $profileId);

        return $this->success($items);
    }

    /**
     * POST /api/v1/recurring
     */
    public function create()
    {
        $rules = [
            'type'        => 'required|in_list[income,expense]',
            'amount'      => 'required|decimal|greater_than[0]',
            'category_id' => 'required|integer',
            'frequency'   => 'required|in_list[daily,weekly,monthly,yearly]',
            'next_due'    => 'required|valid_date',
        ];

        if (! $this->validate($rules)) {
            return $this->error('Validation failed', 422, $this->validator->getErrors());
        }

        $profileId = $this->getProfileId();

        $data = [
            'user_id'     => $this->getUserId(),
            'profile_id'  => $profileId,
            'category_id' => $this->getInput('category_id'),
            'type'        => $this->getInput('type'),
            'amount'      => $this->getInput('amount'),
            'currency'    => $this->getInput('currency') ?? 'EUR',
            'description' => $this->getInput('description') ?? '',
            'frequency'   => $this->getInput('frequency'),
            'next_due'    => $this->getInput('next_due'),
            'is_active'   => 1,
        ];

        $id = $this->recurringModel->insert($data);

        return $this->success($this->recurringModel->find($id), 'Recurring transaction created', 201);
    }

    /**
     * PUT /api/v1/recurring/:id
     */
    public function update($id = null)
    {
        $item = $this->recurringModel
            ->where('user_id', $this->getUserId())
            ->find($id);

        if (! $item) {
            return $this->error('Recurring transaction not found', 404);
        }

        $data = array_filter([
            'category_id' => $this->getInput('category_id'),
            'type'        => $this->getInput('type'),
            'amount'      => $this->getInput('amount'),
            'currency'    => $this->getInput('currency'),
            'description' => $this->getInput('description'),
            'frequency'   => $this->getInput('frequency'),
            'next_due'    => $this->getInput('next_due'),
        ], fn($v) => $v !== null);

        $this->recurringModel->update($id, $data);

        return $this->success($this->recurringModel->find($id), 'Recurring transaction updated');
    }

    /**
     * PATCH /api/v1/recurring/:id/toggle
     */
    public function toggle($id = null)
    {
        $item = $this->recurringModel
            ->where('user_id', $this->getUserId())
            ->find($id);

        if (! $item) {
            return $this->error('Recurring transaction not found', 404);
        }

        $this->recurringModel->update($id, [
            'is_active' => $item['is_active'] ? 0 : 1,
        ]);

        $label = $item['is_active'] ? 'paused' : 'activated';
        return $this->success($this->recurringModel->find($id), "Recurring transaction {$label}");
    }

    /**
     * POST /api/v1/recurring/:id/process
     * Manually generate a transaction from a recurring template.
     */
    public function process($id = null)
    {
        $item = $this->recurringModel
            ->where('user_id', $this->getUserId())
            ->find($id);

        if (! $item) {
            return $this->error('Recurring transaction not found', 404);
        }

        $transactionModel = model(TransactionModel::class);

        $txnData = [
            'user_id'          => $item['user_id'],
            'profile_id'       => $item['profile_id'],
            'category_id'      => $item['category_id'],
            'type'             => $item['type'],
            'amount'           => $item['amount'],
            'currency'         => $item['currency'],
            'description'      => $item['description'],
            'transaction_date' => $item['next_due'],
        ];

        $txnId = $transactionModel->insert($txnData);

        // Advance next_due date
        $nextDue = $this->recurringModel->calculateNextDue($item['next_due'], $item['frequency']);
        $this->recurringModel->update($id, ['next_due' => $nextDue]);

        return $this->success([
            'transaction' => $transactionModel->find($txnId),
            'recurring'   => $this->recurringModel->find($id),
        ], 'Transaction generated and next due date advanced');
    }

    /**
     * DELETE /api/v1/recurring/:id
     */
    public function delete($id = null)
    {
        $item = $this->recurringModel
            ->where('user_id', $this->getUserId())
            ->find($id);

        if (! $item) {
            return $this->error('Recurring transaction not found', 404);
        }

        $this->recurringModel->delete($id);

        return $this->success(null, 'Recurring transaction deleted');
    }
}
