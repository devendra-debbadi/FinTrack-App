<?php

namespace App\Models;

use CodeIgniter\Model;

class TagModel extends Model
{
    protected $table            = 'tags';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = false;

    protected $allowedFields = [
        'user_id',
        'name',
        'color',
        'created_at',
    ];

    protected $validationRules = [
        'user_id' => 'required|integer',
        'name'    => 'required|min_length[2]|max_length[50]',
    ];

    /**
     * Get all tags for a user.
     */
    public function getByUser(int $userId): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Get tags for a specific transaction.
     */
    public function getByTransaction(int $transactionId): array
    {
        return $this->select('tags.*')
            ->join('transaction_tags', 'transaction_tags.tag_id = tags.id')
            ->where('transaction_tags.transaction_id', $transactionId)
            ->findAll();
    }

    /**
     * Sync tags for a transaction.
     */
    public function syncTransaction(int $transactionId, array $tagIds): void
    {
        $db = \Config\Database::connect();

        // Remove existing
        $db->table('transaction_tags')
            ->where('transaction_id', $transactionId)
            ->delete();

        // Add new
        foreach ($tagIds as $tagId) {
            $db->table('transaction_tags')->insert([
                'transaction_id' => $transactionId,
                'tag_id'         => (int) $tagId,
            ]);
        }
    }
}
