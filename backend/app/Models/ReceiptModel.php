<?php

namespace App\Models;

use CodeIgniter\Model;

class ReceiptModel extends Model
{
    protected $table            = 'receipts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = false;

    protected $allowedFields = [
        'transaction_id',
        'user_id',
        'filename',
        'original_name',
        'mime_type',
        'file_size',
        'created_at',
    ];

    /**
     * Get receipts for a transaction.
     */
    public function getByTransaction(int $transactionId, int $userId): array
    {
        return $this->where('transaction_id', $transactionId)
            ->where('user_id', $userId)
            ->findAll();
    }

    /**
     * Delete receipt file and DB record.
     */
    public function deleteWithFile(int $receiptId, int $userId): bool
    {
        $receipt = $this->where('id', $receiptId)
            ->where('user_id', $userId)
            ->first();

        if (! $receipt) {
            return false;
        }

        $filePath = WRITEPATH . 'uploads/receipts/' . $receipt['filename'];
        if (is_file($filePath)) {
            unlink($filePath);
        }

        return $this->delete($receiptId);
    }
}
