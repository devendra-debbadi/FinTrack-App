<?php

namespace App\Controllers\Api\V1;

use App\Models\ReceiptModel;
use App\Models\TransactionModel;

class ReceiptController extends BaseApiController
{
    private ReceiptModel $receiptModel;

    public function __construct()
    {
        $this->receiptModel = model(ReceiptModel::class);
    }

    /**
     * GET /api/v1/receipts/:transaction_id
     */
    public function index($transactionId = null)
    {
        // Verify transaction belongs to user
        $txnModel = model(TransactionModel::class);
        $txn = $txnModel->where('user_id', $this->getUserId())->find($transactionId);

        if (! $txn) {
            return $this->error('Transaction not found', 404);
        }

        $receipts = $this->receiptModel->getByTransaction($transactionId, $this->getUserId());

        return $this->success($receipts);
    }

    /**
     * POST /api/v1/receipts/:transaction_id
     */
    public function upload($transactionId = null)
    {
        $txnModel = model(TransactionModel::class);
        $txn = $txnModel->where('user_id', $this->getUserId())->find($transactionId);

        if (! $txn) {
            return $this->error('Transaction not found', 404);
        }

        $file = $this->request->getFile('receipt');

        if (! $file || ! $file->isValid()) {
            return $this->error('Valid file required', 422);
        }

        // Validate file type (MIME + extension)
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
        if (! in_array($file->getMimeType(), $allowedMimes) || ! in_array(strtolower($file->getExtension()), $allowedExts)) {
            return $this->error('File must be JPEG, PNG, WebP, or PDF', 422);
        }

        // Max 5MB
        if ($file->getSize() > 5 * 1024 * 1024) {
            return $this->error('File must be under 5MB', 422);
        }

        // Generate unique filename
        $filename = bin2hex(random_bytes(16)) . '.' . $file->getExtension();

        // Ensure upload directory exists
        $uploadPath = WRITEPATH . 'uploads/receipts/';
        if (! is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $file->move($uploadPath, $filename);

        $receiptId = $this->receiptModel->insert([
            'transaction_id' => $transactionId,
            'user_id'        => $this->getUserId(),
            'filename'       => $filename,
            'original_name'  => $file->getClientName(),
            'mime_type'      => $file->getMimeType(),
            'file_size'      => $file->getSize(),
            'created_at'     => date('Y-m-d H:i:s'),
        ]);

        return $this->success($this->receiptModel->find($receiptId), 'Receipt uploaded', 201);
    }

    /**
     * GET /api/v1/receipts/download/:id
     */
    public function download($id = null)
    {
        $receipt = $this->receiptModel
            ->where('user_id', $this->getUserId())
            ->find($id);

        if (! $receipt) {
            return $this->error('Receipt not found', 404);
        }

        $filePath = WRITEPATH . 'uploads/receipts/' . basename($receipt['filename']);
        $realPath = realpath($filePath);
        $uploadsDir = realpath(WRITEPATH . 'uploads/receipts');

        if (! $realPath || ! $uploadsDir || strpos($realPath, $uploadsDir) !== 0) {
            return $this->error('Receipt file not found', 404);
        }

        return $this->response->download($filePath, null)
            ->setFileName($receipt['original_name']);
    }

    /**
     * DELETE /api/v1/receipts/:id
     */
    public function delete($id = null)
    {
        $deleted = $this->receiptModel->deleteWithFile($id, $this->getUserId());

        if (! $deleted) {
            return $this->error('Receipt not found', 404);
        }

        return $this->success(null, 'Receipt deleted');
    }
}
