<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingsModel extends Model
{
    protected $table            = 'settings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $allowedFields = [
        'user_id',
        'currency',
        'theme',
        'language',
        'date_format',
    ];

    /**
     * Get settings for a user, creating defaults if not found.
     */
    public function getByUser(int $userId): array
    {
        $settings = $this->where('user_id', $userId)->first();

        if (! $settings) {
            $this->insert([
                'user_id'     => $userId,
                'currency'    => 'EUR',
                'theme'       => 'dark',
                'language'    => 'en',
                'date_format' => 'DD/MM/YYYY',
            ]);
            $settings = $this->where('user_id', $userId)->first();
        }

        return $settings;
    }

    /**
     * Update settings for a user.
     */
    public function updateByUser(int $userId, array $data): bool
    {
        $settings = $this->where('user_id', $userId)->first();

        if ($settings) {
            return $this->update($settings['id'], $data);
        }

        $data['user_id'] = $userId;
        return (bool) $this->insert($data);
    }
}
