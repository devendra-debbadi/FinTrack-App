<?php

namespace App\Models;

use CodeIgniter\Model;

class ActivityLogModel extends Model
{
    protected $table            = 'activity_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = false;

    protected $allowedFields = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'description',
        'ip_address',
        'created_at',
    ];

    /**
     * Get paginated logs, optionally filtered by user_id and/or action.
     */
    public function getLogs(array $filters = [], int $page = 1, int $perPage = 50): array
    {
        $builder = $this->db->table('activity_logs al')
            ->select('al.*, u.name as user_name, u.email as user_email')
            ->join('users u', 'u.id = al.user_id', 'left')
            ->orderBy('al.created_at', 'DESC');

        if (! empty($filters['user_id'])) {
            $builder->where('al.user_id', (int) $filters['user_id']);
        }

        if (! empty($filters['action'])) {
            $builder->where('al.action', $filters['action']);
        }

        if (! empty($filters['date_from'])) {
            $builder->where('al.created_at >=', $filters['date_from'] . ' 00:00:00');
        }

        if (! empty($filters['date_to'])) {
            $builder->where('al.created_at <=', $filters['date_to'] . ' 23:59:59');
        }

        $total  = $builder->countAllResults(false);
        $offset = ($page - 1) * $perPage;
        $rows   = $builder->limit($perPage, $offset)->get()->getResultArray();

        return [
            'data'       => $rows,
            'total'      => $total,
            'page'       => $page,
            'per_page'   => $perPage,
            'last_page'  => (int) ceil($total / $perPage),
        ];
    }

    /**
     * Get recent logs for a specific user (used in profile/account views).
     */
    public function getByUser(int $userId, int $limit = 20): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Delete logs older than $days days.
     */
    public function deleteOlderThan(int $days = 90): int
    {
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        $this->db->table('activity_logs')->where('created_at <', $cutoff)->delete();
        return $this->db->affectedRows();
    }
}
