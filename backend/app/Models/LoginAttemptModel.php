<?php

namespace App\Models;

use CodeIgniter\Model;

class LoginAttemptModel extends Model
{
    protected $table            = 'login_attempts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = false;

    protected $allowedFields = [
        'email',
        'ip_address',
        'attempted_at',
    ];

    /**
     * Record a failed login attempt.
     */
    public function recordAttempt(string $email, string $ip): void
    {
        $this->insert([
            'email'        => $email,
            'ip_address'   => $ip,
            'attempted_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Count recent attempts for an email.
     */
    public function recentAttempts(string $email, int $minutes = 15): int
    {
        $cutoff = date('Y-m-d H:i:s', time() - ($minutes * 60));

        return $this->where('email', $email)
            ->where('attempted_at >=', $cutoff)
            ->countAllResults();
    }

    /**
     * Clear attempts for an email (on successful login).
     */
    public function clearAttempts(string $email): void
    {
        $this->where('email', $email)->delete();
    }
}
