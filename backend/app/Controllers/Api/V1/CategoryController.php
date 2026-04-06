<?php

namespace App\Controllers\Api\V1;

use App\Models\CategoryModel;

class CategoryController extends BaseApiController
{
    private CategoryModel $categoryModel;

    public function __construct()
    {
        $this->categoryModel = model(CategoryModel::class);
    }

    /**
     * GET /api/v1/categories
     */
    public function index()
    {
        $profileId = $this->getProfileId();
        $type = $this->request->getGet('type');
        $includeArchived = (bool) $this->request->getGet('include_archived');

        $categories = $this->categoryModel->getByProfile(
            $this->getUserId(),
            $profileId,
            $type,
            $includeArchived
        );

        return $this->success($categories);
    }

    /**
     * POST /api/v1/categories
     */
    public function create()
    {
        $rules = [
            'name' => 'required|min_length[2]|max_length[100]',
            'type' => 'required|in_list[income,expense]',
        ];

        if (! $this->validate($rules)) {
            return $this->error('Validation failed', 422, $this->validator->getErrors());
        }

        $profileId = $this->getProfileId();

        $data = [
            'user_id'    => $this->getUserId(),
            'profile_id' => $profileId,
            'name'       => $this->getInput('name'),
            'type'       => $this->getInput('type'),
            'icon'       => $this->getInput('icon') ?? 'pi-tag',
            'color'      => $this->getInput('color') ?? '#6366f1',
            'sort_order' => (int) ($this->getInput('sort_order') ?? 0),
        ];

        $id = $this->categoryModel->insert($data);

        if (! $id) {
            return $this->error('Failed to create category', 500);
        }

        $this->logActivity('category_create', 'category', $id, "Created category: {$this->getInput('name')}");

        return $this->success($this->categoryModel->find($id), 'Category created', 201);
    }

    /**
     * PUT /api/v1/categories/:id
     */
    public function update($id = null)
    {
        if (! $this->categoryModel->belongsToUser($id, $this->getUserId())) {
            return $this->error('Category not found', 404);
        }

        $data = array_filter([
            'name'       => $this->getInput('name'),
            'icon'       => $this->getInput('icon'),
            'color'      => $this->getInput('color'),
            'sort_order' => $this->getInput('sort_order'),
        ], fn($v) => $v !== null);

        if (empty($data)) {
            return $this->error('No data to update', 422);
        }

        $this->categoryModel->update($id, $data);

        $this->logActivity('category_update', 'category', (int) $id, 'Category updated');

        return $this->success($this->categoryModel->find($id), 'Category updated');
    }

    /**
     * PATCH /api/v1/categories/:id/archive
     */
    public function archive($id = null)
    {
        if (! $this->categoryModel->belongsToUser($id, $this->getUserId())) {
            return $this->error('Category not found', 404);
        }

        $category = $this->categoryModel->find($id);
        $newState = $category['is_archived'] ? 0 : 1;

        $this->categoryModel->update($id, ['is_archived' => $newState]);

        $message = $newState ? 'Category archived' : 'Category restored';
        $action  = $newState ? 'category_archive' : 'category_restore';

        $this->logActivity($action, 'category', (int) $id, $message);

        return $this->success($this->categoryModel->find($id), $message);
    }

    /**
     * DELETE /api/v1/categories/:id
     */
    public function delete($id = null)
    {
        if (! $this->categoryModel->belongsToUser($id, $this->getUserId())) {
            return $this->error('Category not found', 404);
        }

        // Check if category has transactions
        $txnCount = model(\App\Models\TransactionModel::class)
            ->where('category_id', $id)
            ->countAllResults();

        if ($txnCount > 0) {
            return $this->error("Cannot delete: category has {$txnCount} transactions. Archive it instead.", 409);
        }

        $this->categoryModel->delete($id);
        $this->logActivity('category_delete', 'category', (int) $id, 'Category deleted');

        return $this->success(null, 'Category deleted');
    }
}
