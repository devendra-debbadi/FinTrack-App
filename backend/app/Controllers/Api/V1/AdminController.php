<?php

namespace App\Controllers\Api\V1;

use App\Models\UserModel;

class AdminController extends BaseApiController
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = model(UserModel::class);
    }

    /**
     * GET /api/v1/admin/users
     */
    public function users()
    {
        $users = $this->userModel
            ->select('id, email, name, role, is_active, must_change_pwd, created_at, updated_at')
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return $this->success($users);
    }

    /**
     * POST /api/v1/admin/users
     */
    public function createUser()
    {
        $rules = [
            'name'     => 'required|min_length[2]|max_length[100]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]',
            'role'     => 'required|in_list[admin,user]',
        ];

        if (! $this->validate($rules)) {
            return $this->error('Validation failed', 422, $this->validator->getErrors());
        }

        $userId = $this->userModel->insert([
            'email'           => $this->getInput('email'),
            'password_hash'   => password_hash($this->getInput('password'), PASSWORD_ARGON2ID),
            'name'            => $this->getInput('name'),
            'role'            => $this->getInput('role'),
            'is_active'       => 1,
            'must_change_pwd' => 1,
        ]);

        // Create default profile & settings
        $profileModel = model(\App\Models\ProfileModel::class);
        $profileModel->createDefault($userId);

        $settingsModel = model(\App\Models\SettingsModel::class);
        $settingsModel->insert([
            'user_id'  => $userId,
            'currency' => 'EUR',
            'theme'    => 'dark',
        ]);

        $user = $this->userModel->getSafeUser($userId);

        $this->logActivity('admin_user_create', 'user', $userId, "Admin created user: {$this->getInput('email')}");

        return $this->success($user, 'User created', 201);
    }

    /**
     * PUT /api/v1/admin/users/:id
     */
    public function updateUser($id = null)
    {
        $user = $this->userModel->find($id);

        if (! $user) {
            return $this->error('User not found', 404);
        }

        $rules = [
            'name'  => 'if_exist|min_length[2]|max_length[100]',
            'email' => "if_exist|valid_email|is_unique[users.email,id,{$id}]",
            'role'  => 'if_exist|in_list[admin,user]',
        ];

        if (! $this->validate($rules)) {
            return $this->error('Validation failed', 422, $this->validator->getErrors());
        }

        $data = array_filter([
            'name'  => $this->getInput('name'),
            'email' => $this->getInput('email'),
            'role'  => $this->getInput('role'),
        ], fn($v) => $v !== null);

        if (empty($data)) {
            return $this->error('No data to update', 422);
        }

        $updated = $this->userModel->skipValidation(true)->update($id, $data);

        if (! $updated) {
            return $this->error('Failed to update user', 500);
        }

        $this->logActivity('admin_user_update', 'user', (int) $id, "Admin updated user #{$id}");

        return $this->success($this->userModel->getSafeUser($id), 'User updated');
    }

    /**
     * DELETE /api/v1/admin/users/:id
     */
    public function deleteUser($id = null)
    {
        $user = $this->userModel->find($id);

        if (! $user) {
            return $this->error('User not found', 404);
        }

        if ((int) $id === $this->getUserId()) {
            return $this->error('Cannot delete your own account', 409);
        }

        $this->userModel->delete($id);
        $this->logActivity('admin_user_delete', 'user', (int) $id, "Admin deleted user #{$id}");

        return $this->success(null, 'User deleted');
    }

    /**
     * PATCH /api/v1/admin/users/:id/toggle
     */
    public function toggleUser($id = null)
    {
        $user = $this->userModel->find($id);

        if (! $user) {
            return $this->error('User not found', 404);
        }

        if ((int) $id === $this->getUserId()) {
            return $this->error('Cannot deactivate yourself', 409);
        }

        $newState = $user['is_active'] ? 0 : 1;
        $this->userModel->skipValidation(true)->update($id, ['is_active' => $newState]);

        $message = $newState ? 'User activated' : 'User deactivated';
        $action  = $newState ? 'admin_user_activate' : 'admin_user_deactivate';

        $this->logActivity($action, 'user', (int) $id, "Admin {$message} user #{$id}");

        return $this->success($this->userModel->getSafeUser($id), $message);
    }

    /**
     * PATCH /api/v1/admin/users/:id/reset-password
     */
    public function resetPassword($id = null)
    {
        $user = $this->userModel->find($id);

        if (! $user) {
            return $this->error('User not found', 404);
        }

        $rules = [
            'password' => 'required|min_length[8]',
        ];

        if (! $this->validate($rules)) {
            return $this->error('Validation failed', 422, $this->validator->getErrors());
        }

        $this->userModel->skipValidation(true)->update($id, [
            'password_hash'   => password_hash($this->getInput('password'), PASSWORD_ARGON2ID),
            'must_change_pwd' => 1,
        ]);

        $this->logActivity('admin_password_reset', 'user', (int) $id, "Admin reset password for user #{$id}");

        return $this->success(null, 'Password reset successfully');
    }

    /**
     * GET /api/v1/admin/activity-logs
     */
    public function activityLogs()
    {
        $logModel = model(\App\Models\ActivityLogModel::class);

        $filters = array_filter([
            'user_id'   => $this->request->getGet('user_id'),
            'action'    => $this->request->getGet('action'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to'   => $this->request->getGet('date_to'),
        ], fn($v) => $v !== null && $v !== '');

        $page    = max(1, (int) ($this->request->getGet('page') ?? 1));
        $perPage = min(100, max(1, (int) ($this->request->getGet('per_page') ?? 50)));

        $result = $logModel->getLogs($filters, $page, $perPage);

        return $this->success($result);
    }

    /**
     * GET /api/v1/admin/stats
     */
    public function stats()
    {
        $db = \Config\Database::connect();

        $totalUsers = $this->userModel->countAllResults();
        $activeUsers = $this->userModel->where('is_active', 1)->countAllResults();
        $totalTransactions = $db->table('transactions')->countAllResults();
        $totalCategories = $db->table('categories')->countAllResults();

        return $this->success([
            'total_users'        => $totalUsers,
            'active_users'       => $activeUsers,
            'total_transactions' => $totalTransactions,
            'total_categories'   => $totalCategories,
        ]);
    }
}
