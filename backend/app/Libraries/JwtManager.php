<?php

namespace App\Libraries;

use Config\Jwt as JwtConfig;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

class JwtManager
{
    private JwtConfig $config;

    public function __construct()
    {
        $this->config = config('Jwt');
    }

    /**
     * Generate an access token for a user.
     */
    public function generateAccessToken(array $user): string
    {
        $now = time();

        $payload = [
            'iss'  => $this->config->issuer,
            'iat'  => $now,
            'exp'  => $now + $this->config->accessExpiry,
            'sub'  => (string) $user['id'],
            'data' => [
                'id'    => (int) $user['id'],
                'email' => $user['email'],
                'name'  => $user['name'],
                'role'  => $user['role'],
            ],
        ];

        return JWT::encode($payload, $this->config->secretKey, $this->config->algorithm);
    }

    /**
     * Generate a refresh token (random string, stored hashed in DB).
     */
    public function generateRefreshToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Get refresh token expiry as DateTime string.
     */
    public function getRefreshTokenExpiry(): string
    {
        return date('Y-m-d H:i:s', time() + $this->config->refreshExpiry);
    }

    /**
     * Decode and validate an access token.
     *
     * @return object|null Decoded payload or null on failure
     */
    public function validateAccessToken(string $token): ?object
    {
        try {
            return JWT::decode($token, new Key($this->config->secretKey, $this->config->algorithm));
        } catch (ExpiredException $e) {
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Hash a refresh token for storage.
     */
    public function hashRefreshToken(string $token): string
    {
        return hash('sha256', $token);
    }

    /**
     * Get access token expiry in seconds.
     */
    public function getAccessExpiry(): int
    {
        return $this->config->accessExpiry;
    }
}
