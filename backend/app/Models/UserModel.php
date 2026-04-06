<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $allowedFields = [
        'email',
        'password_hash',
        'name',
        'role',
        'is_active',
        'must_change_pwd',
        'avatar',
    ];

    protected $validationRules = [
        'email' => 'required|valid_email|is_unique[users.email,id,{id}]',
        'name'  => 'required|min_length[2]|max_length[100]',
    ];

    protected $validationMessages = [
        'email' => [
            'is_unique' => 'This email is already registered.',
        ],
    ];

    /**
     * Find user by email.
     */
    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }

    /**
     * Get user data safe for JWT/response (no password hash).
     */
    public function getSafeUser(int $id): ?array
    {
        $user = $this->find($id);
        if ($user) {
            unset($user['password_hash']);
        }
        return $user;
    }
}
