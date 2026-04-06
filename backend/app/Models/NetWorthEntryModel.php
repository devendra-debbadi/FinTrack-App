<?php

namespace App\Models;

use CodeIgniter\Model;

class NetWorthEntryModel extends Model
{
    protected $table            = 'net_worth_entries';
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
        'amount',
        'currency',
        'icon',
        'color',
        'notes',
        'entry_date',
    ];

    protected $validationRules = [
        'user_id'    => 'required|integer',
        'profile_id' => 'required|integer',
        'name'       => 'required|max_length[255]',
        'type'       => 'required|in_list[asset,liability]',
        'amount'     => 'required|decimal|greater_than_equal_to[0]',
        'entry_date' => 'required|valid_date',
    ];

    /**
     * Get all entries for a user/profile grouped by type.
     */
    public function getByProfile(int $userId, int $profileId): array
    {
        return $this->where('user_id', $userId)
            ->where('profile_id', $profileId)
            ->orderBy('type', 'ASC')
            ->orderBy('amount', 'DESC')
            ->findAll();
    }

    /**
     * Get net worth summary for a user/profile.
     */
    public function getSummary(int $userId, int $profileId): array
    {
        $entries = $this->getByProfile($userId, $profileId);

        $assets = 0;
        $liabilities = 0;

        foreach ($entries as $entry) {
            if ($entry['type'] === 'asset') {
                $assets += (float) $entry['amount'];
            } else {
                $liabilities += (float) $entry['amount'];
            }
        }

        return [
            'total_assets'      => $assets,
            'total_liabilities' => $liabilities,
            'net_worth'         => $assets - $liabilities,
            'entries'           => $entries,
        ];
    }
}
