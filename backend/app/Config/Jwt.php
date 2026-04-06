<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Jwt extends BaseConfig
{
    /**
     * Secret key for signing JWT tokens.
     * Override via .env: jwt.secretKey
     */
    public string $secretKey = 'CHANGE_ME_TO_A_RANDOM_64_CHAR_STRING_IN_PRODUCTION';

    /**
     * Access token expiry in seconds (default: 15 minutes)
     */
    public int $accessExpiry = 900;

    /**
     * Refresh token expiry in seconds (default: 7 days)
     */
    public int $refreshExpiry = 604800;

    /**
     * Token issuer
     */
    public string $issuer = 'fintrack-app';

    /**
     * Signing algorithm
     */
    public string $algorithm = 'HS256';
}
