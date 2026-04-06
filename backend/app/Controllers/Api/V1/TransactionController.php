<?php

namespace App\Controllers\Api\V1;

use App\Models\TransactionModel;
use App\Models\CategoryModel;
use App\Models\TagModel;

class TransactionController extends BaseApiController
{
    private TransactionModel $transactionModel;

    public function __construct()
    {
        $this->transactionModel = model(TransactionModel::class);
    }

    /**
     * GET /api/v1/transactions
     */
    public function index()
    {
        $profileId = $this->getProfileId();

        $filters = [
            'type'        => $this->request->getGet('type'),
            'category_id' => $this->request->getGet('category_id'),
            'date_from'   => $this->request->getGet('date_from'),
            'date_to'     => $this->request->getGet('date_to'),
            'search'      => $this->request->getGet('search'),
            'min_amount'  => $this->request->getGet('min_amount'),
            'max_amount'  => $this->request->getGet('max_amount'),
            'sort'        => $this->request->getGet('sort'),
            'direction'   => $this->request->getGet('direction'),
        ];

        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = (int) ($this->request->getGet('per_page') ?? 20);
        $perPage = min($perPage, 100);

        $result = $this->transactionModel->getFiltered(
            $this->getUserId(),
            $profileId,
            array_filter($filters),
            $page,
            $perPage
        );

        // Attach tags to each transaction
        $tagModel = model(TagModel::class);
        foreach ($result['data'] as &$txn) {
            $txn['tags'] = $tagModel->getByTransaction($txn['id']);
        }

        return $this->success($result);
    }

    /**
     * GET /api/v1/transactions/:id
     */
    public function show($id = null)
    {
        $transaction = $this->transactionModel
            ->select('transactions.*, categories.name as category_name, categories.icon as category_icon, categories.color as category_color')
            ->join('categories', 'categories.id = transactions.category_id', 'left')
            ->where('transactions.user_id', $this->getUserId())
            ->find($id);

        if (! $transaction) {
            return $this->error('Transaction not found', 404);
        }

        // Attach tags
        $tagModel = model(TagModel::class);
        $transaction['tags'] = $tagModel->getByTransaction($id);

        // Attach receipts
        $receiptModel = model(\App\Models\ReceiptModel::class);
        $transaction['receipts'] = $receiptModel->getByTransaction($id, $this->getUserId());

        return $this->success($transaction);
    }

    /**
     * POST /api/v1/transactions
     */
    public function create()
    {
        $rules = [
            'category_id'      => 'required|integer',
            'type'             => 'required|in_list[income,expense]',
            'amount'           => 'required|decimal|greater_than[0]',
            'transaction_date' => 'required|valid_date',
        ];

        if (! $this->validate($rules)) {
            return $this->error('Validation failed', 422, $this->validator->getErrors());
        }

        $profileId = $this->getProfileId();

        // Verify category belongs to user
        $categoryModel = model(CategoryModel::class);
        if (! $categoryModel->belongsToUser($this->getInput('category_id'), $this->getUserId())) {
            return $this->error('Invalid category', 422);
        }

        $data = [
            'user_id'          => $this->getUserId(),
            'profile_id'       => $profileId,
            'category_id'      => $this->getInput('category_id'),
            'type'             => $this->getInput('type'),
            'amount'           => $this->getInput('amount'),
            'currency'         => $this->getInput('currency') ?? 'EUR',
            'description'      => $this->getInput('description'),
            'transaction_date' => $this->getInput('transaction_date'),
            'notes'            => $this->getInput('notes'),
        ];

        $id = $this->transactionModel->insert($data);

        if (! $id) {
            return $this->error('Failed to create transaction', 500);
        }

        // Sync tags if provided
        $tagIds = $this->getInput('tag_ids');
        if (is_array($tagIds) && ! empty($tagIds)) {
            $tagModel = model(TagModel::class);
            $tagModel->syncTransaction($id, $tagIds);
        }

        $transaction = $this->transactionModel->find($id);

        $this->logActivity('transaction_create', 'transaction', $id, "Created {$transaction['type']} of {$transaction['amount']}");

        return $this->success($transaction, 'Transaction created', 201);
    }

    /**
     * PUT /api/v1/transactions/:id
     */
    public function update($id = null)
    {
        $transaction = $this->transactionModel
            ->where('user_id', $this->getUserId())
            ->find($id);

        if (! $transaction) {
            return $this->error('Transaction not found', 404);
        }

        $rules = [
            'type'             => 'if_exist|in_list[income,expense]',
            'amount'           => 'if_exist|decimal|greater_than[0]',
            'transaction_date' => 'if_exist|valid_date',
        ];

        if (! $this->validate($rules)) {
            return $this->error('Validation failed', 422, $this->validator->getErrors());
        }

        $data = array_filter([
            'category_id'      => $this->getInput('category_id'),
            'type'             => $this->getInput('type'),
            'amount'           => $this->getInput('amount'),
            'currency'         => $this->getInput('currency'),
            'description'      => $this->getInput('description'),
            'transaction_date' => $this->getInput('transaction_date'),
            'notes'            => $this->getInput('notes'),
        ], fn($v) => $v !== null);

        if (! empty($data['category_id'])) {
            $categoryModel = model(CategoryModel::class);
            if (! $categoryModel->belongsToUser($data['category_id'], $this->getUserId())) {
                return $this->error('Invalid category', 422);
            }
        }

        $this->transactionModel->update($id, $data);

        // Sync tags if provided
        $tagIds = $this->getInput('tag_ids');
        if (is_array($tagIds)) {
            $tagModel = model(TagModel::class);
            $tagModel->syncTransaction($id, $tagIds);
        }

        $this->logActivity('transaction_update', 'transaction', (int) $id, 'Transaction updated');

        return $this->success($this->transactionModel->find($id), 'Transaction updated');
    }

    /**
     * DELETE /api/v1/transactions/:id
     */
    public function delete($id = null)
    {
        $transaction = $this->transactionModel
            ->where('user_id', $this->getUserId())
            ->find($id);

        if (! $transaction) {
            return $this->error('Transaction not found', 404);
        }

        $this->transactionModel->delete($id);
        $this->logActivity('transaction_delete', 'transaction', (int) $id, 'Transaction deleted');

        return $this->success(null, 'Transaction deleted');
    }
}
