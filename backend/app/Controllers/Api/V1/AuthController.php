<?php

namespace App\Controllers\Api\V1;

use App\Services\AuthService;

class AuthController extends BaseApiController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    /**
     * POST /api/v1/auth/register
     */
    public function register()
    {
        $rules = [
            'name'     => 'required|min_length[2]|max_length[100]',
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[8]',
        ];

        if (! $this->validate($rules)) {
            return $this->error('Validation failed', 422, $this->validator->getErrors());
        }

        $result = $this->authService->register(
            $this->getInput('name'),
            $this->getInput('email'),
            $this->getInput('password')
        );

        if (isset($result['error'])) {
            return $this->error($result['error'], 409);
        }

        $this->logActivity('register', 'user', (int) $result['user']['id'], 'New user registered', (int) $result['user']['id']);

        return $this->success($result, 'Registration successful', 201);
    }

    /**
     * POST /api/v1/auth/login
     */
    public function login()
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required',
        ];

        if (! $this->validate($rules)) {
            return $this->error('Validation failed', 422, $this->validator->getErrors());
        }

        $result = $this->authService->login(
            $this->getInput('email'),
            $this->getInput('password'),
            $this->request->getIPAddress()
        );

        if (isset($result['error'])) {
            return $this->error($result['error'], 401);
        }

        $this->logActivity('login', 'user', (int) $result['user']['id'], 'User logged in', (int) $result['user']['id']);

        return $this->success($result, 'Login successful');
    }

    /**
     * POST /api/v1/auth/refresh
     */
    public function refresh()
    {
        $refreshToken = $this->getInput('refresh_token');

        if (empty($refreshToken)) {
            return $this->error('Refresh token required', 422);
        }

        $result = $this->authService->refresh($refreshToken);

        if (isset($result['error'])) {
            return $this->error($result['error'], 401);
        }

        return $this->success($result, 'Token refreshed');
    }

    /**
     * POST /api/v1/auth/logout
     */
    public function logout()
    {
        $this->authService->logout($this->getUserId());
        $this->logActivity('logout', '', null, 'User logged out');

        return $this->success(null, 'Logged out successfully');
    }

    /**
     * GET /api/v1/auth/me
     */
    public function me()
    {
        $userModel = model(\App\Models\UserModel::class);
        $user = $userModel->getSafeUser($this->getUserId());

        $settingsModel = model(\App\Models\SettingsModel::class);
        $settings = $settingsModel->getByUser($this->getUserId());

        $profileModel = model(\App\Models\ProfileModel::class);
        $profiles = $profileModel->getByUser($this->getUserId());

        return $this->success([
            'user'     => $user,
            'settings' => $settings,
            'profiles' => $profiles,
        ]);
    }

    /**
     * PUT /api/v1/auth/profile
     * Update the authenticated user's own name and/or email.
     */
    public function updateProfile()
    {
        $id = $this->getUserId();

        $rules = [
            'name'  => "if_exist|min_length[2]|max_length[100]",
            'email' => "if_exist|valid_email|is_unique[users.email,id,{$id}]",
        ];

        if (! $this->validate($rules)) {
            return $this->error('Validation failed', 422, $this->validator->getErrors());
        }

        $data = array_filter([
            'name'  => $this->getInput('name'),
            'email' => $this->getInput('email'),
        ], fn($v) => $v !== null);

        if (empty($data)) {
            return $this->error('No data to update', 422);
        }

        $userModel = model(\App\Models\UserModel::class);
        $updated = $userModel->skipValidation(true)->update($id, $data);

        if (! $updated) {
            return $this->error('Failed to update profile', 500);
        }

        $this->logActivity('profile_update', 'user', $id, 'User updated own profile');

        return $this->success($userModel->getSafeUser($id), 'Profile updated');
    }

    /**
     * PUT /api/v1/auth/password
     */
    public function changePassword()
    {
        $rules = [
            'current_password' => 'required',
            'new_password'     => 'required|min_length[8]',
        ];

        if (! $this->validate($rules)) {
            return $this->error('Validation failed', 422, $this->validator->getErrors());
        }

        $userModel = model(\App\Models\UserModel::class);
        $user = $userModel->find($this->getUserId());

        if (! password_verify($this->getInput('current_password'), $user['password_hash'])) {
            return $this->error('Current password is incorrect', 401);
        }

        $userModel->skipValidation(true)->update($this->getUserId(), [
            'password_hash'   => password_hash($this->getInput('new_password'), PASSWORD_ARGON2ID),
            'must_change_pwd' => 0,
        ]);

        $this->logActivity('password_change', 'user', $this->getUserId(), 'Password changed');

        return $this->success(null, 'Password changed successfully');
    }
}
