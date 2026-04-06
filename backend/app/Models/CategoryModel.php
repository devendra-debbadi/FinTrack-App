<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table            = 'categories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $allowedFields = [
        'user_id',
        'profile_id',
        'name',
        'type',
        'icon',
        'color',
        'is_archived',
        'sort_order',
    ];

    protected $validationRules = [
        'user_id'    => 'required|integer',
        'profile_id' => 'required|integer',
        'name'       => 'required|min_length[2]|max_length[100]',
        'type'       => 'required|in_list[income,expense]',
    ];

    /**
     * Get categories for a user/profile, optionally filtered by type.
     */
    public function getByProfile(int $userId, int $profileId, ?string $type = null, bool $includeArchived = false): array
    {
        $builder = $this->where('user_id', $userId)
            ->where('profile_id', $profileId);

        if ($type) {
            $builder->where('type', $type);
        }

        if (! $includeArchived) {
            $builder->where('is_archived', 0);
        }

        return $builder->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Check if a category belongs to a user.
     */
    public function belongsToUser(int $categoryId, int $userId): bool
    {
        return $this->where('id', $categoryId)
            ->where('user_id', $userId)
            ->countAllResults() > 0;
    }
}
