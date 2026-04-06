<?php

namespace App\Services;

use App\Libraries\JwtManager;
use App\Models\UserModel;
use App\Models\RefreshTokenModel;
use App\Models\LoginAttemptModel;
use App\Models\ProfileModel;
use App\Models\SettingsModel;

class AuthService
{
    private UserModel $userModel;
    private RefreshTokenModel $refreshTokenModel;
    private LoginAttemptModel $loginAttemptModel;
    private JwtManager $jwt;

    public function __construct()
    {
        $this->userModel = model(UserModel::class);
        $this->refreshTokenModel = model(RefreshTokenModel::class);
        $this->loginAttemptModel = model(LoginAttemptModel::class);
        $this->jwt = new JwtManager();
    }

    /**
     * Register a new user.
     */
    public function register(string $name, string $email, string $password): array
    {
        // Check if email exists
        if ($this->userModel->findByEmail($email)) {
            return ['error' => 'Email already registered'];
        }

        // Create user
        $userId = $this->userModel->insert([
            'email'         => $email,
            'password_hash' => password_hash($password, PASSWORD_ARGON2ID),
            'name'          => $name,
            'role'          => 'user',
            'is_active'     => 1,
        ]);

        if (! $userId) {
            return ['error' => 'Failed to create user'];
        }

        // Create default profile
        $profileModel = model(ProfileModel::class);
        $profileModel->createDefault($userId);

        // Create default settings
        $settingsModel = model(SettingsModel::class);
        $settingsModel->insert([
            'user_id'     => $userId,
            'currency'    => 'EUR',
            'theme'       => 'dark',
            'language'    => 'en',
            'date_format' => 'DD/MM/YYYY',
        ]);

        // Generate tokens
        $user = $this->userModel->getSafeUser($userId);

        return $this->generateTokenPair($user);
    }

    /**
     * Authenticate user and return tokens.
     */
    public function login(string $email, string $password, string $ip): array
    {
        // Check rate limit
        $attempts = $this->loginAttemptModel->recentAttempts($email);
        if ($attempts >= 5) {
            return ['error' => 'Too many failed attempts. Please try again in 15 minutes.'];
        }

        $user = $this->userModel->findByEmail($email);

        if (! $user || ! password_verify($password, $user['password_hash'])) {
            $this->loginAttemptModel->recordAttempt($email, $ip);
            return ['error' => 'Invalid email or password'];
        }

        if (! $user['is_active']) {
            return ['error' => 'Account is deactivated. Contact administrator.'];
        }

        // Clear failed attempts on success
        $this->loginAttemptModel->clearAttempts($email);

        $safeUser = $user;
        unset($safeUser['password_hash']);

        return $this->generateTokenPair($safeUser);
    }

    /**
     * Refresh access token using refresh token.
     */
    public function refresh(string $refreshToken): array
    {
        $tokenHash = $this->jwt->hashRefreshToken($refreshToken);
        $stored = $this->refreshTokenModel->findValidToken($tokenHash);

        if (! $stored) {
            return ['error' => 'Invalid or expired refresh token'];
        }

        // Revoke old token
        $this->refreshTokenModel->revokeToken($tokenHash);

        // Get user
        $user = $this->userModel->getSafeUser($stored['user_id']);

        if (! $user || ! $user['is_active']) {
            return ['error' => 'Account not found or deactivated'];
        }

        return $this->generateTokenPair($user);
    }

    /**
     * Revoke all tokens for a user (logout).
     */
    public function logout(int $userId): void
    {
        $this->refreshTokenModel->revokeAllForUser($userId);
    }

    /**
     * Generate access + refresh token pair.
     */
    private function generateTokenPair(array $user): array
    {
        $accessToken = $this->jwt->generateAccessToken($user);
        $refreshToken = $this->jwt->generateRefreshToken();

        // Store refresh token
        $this->refreshTokenModel->insert([
            'user_id'    => $user['id'],
            'token_hash' => $this->jwt->hashRefreshToken($refreshToken),
            'expires_at' => $this->jwt->getRefreshTokenExpiry(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return [
            'user'          => $user,
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in'    => $this->jwt->getAccessExpiry(),
        ];
    }
}
