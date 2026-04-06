<?php

namespace App\Models;

use CodeIgniter\Model;

class RefreshTokenModel extends Model
{
    protected $table            = 'refresh_tokens';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = false;

    protected $allowedFields = [
        'user_id',
        'token_hash',
        'expires_at',
        'revoked',
        'created_at',
    ];

    /**
     * Find a valid (not revoked, not expired) refresh token.
     */
    public function findValidToken(string $tokenHash): ?array
    {
        return $this->where('token_hash', $tokenHash)
            ->where('revoked', 0)
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->first();
    }

    /**
     * Revoke a specific token.
     */
    public function revokeToken(string $tokenHash): bool
    {
        return $this->where('token_hash', $tokenHash)
            ->set(['revoked' => 1])
            ->update();
    }

    /**
     * Revoke all tokens for a user.
     */
    public function revokeAllForUser(int $userId): bool
    {
        return $this->where('user_id', $userId)
            ->set(['revoked' => 1])
            ->update();
    }

    /**
     * Clean up expired tokens.
     */
    public function purgeExpired(): int
    {
        $this->where('expires_at <', date('Y-m-d H:i:s'))->delete();
        return $this->db->affectedRows();
    }
}
