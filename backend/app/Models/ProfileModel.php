<?php

namespace App\Models;

use CodeIgniter\Model;

class ProfileModel extends Model
{
    protected $table            = 'profiles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $allowedFields = [
        'user_id',
        'name',
        'description',
        'is_default',
    ];

    protected $validationRules = [
        'user_id' => 'required|integer',
        'name'    => 'required|min_length[2]|max_length[100]',
    ];

    /**
     * Get all profiles for a user.
     */
    public function getByUser(int $userId): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('is_default', 'DESC')
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Get the default profile for a user.
     */
    public function getDefault(int $userId): ?array
    {
        return $this->where('user_id', $userId)
            ->where('is_default', 1)
            ->first();
    }

    /**
     * Create a default "Personal" profile for a new user.
     */
    public function createDefault(int $userId): int
    {
        return $this->insert([
            'user_id'    => $userId,
            'name'       => 'Personal',
            'is_default' => 1,
        ]);
    }
}
