<?php

namespace App\Services;

use App\Models\TransactionModel;

class ExportService
{
    private TransactionModel $transactionModel;

    public function __construct()
    {
        $this->transactionModel = model(TransactionModel::class);
    }

    /**
     * Export transactions as CSV.
     */
    public function exportCsv(int $userId, int $profileId, array $filters = []): string
    {
        $filters['sort'] = 'transaction_date';
        $filters['direction'] = 'DESC';

        // Get all transactions (no pagination)
        $result = $this->transactionModel->getFiltered($userId, $profileId, $filters, 1, 10000);
        $transactions = $result['data'];

        $output = fopen('php://temp', 'r+');

        // Header row
        fputcsv($output, [
            'Date',
            'Type',
            'Category',
            'Description',
            'Amount',
            'Currency',
            'Notes',
        ]);

        foreach ($transactions as $txn) {
            fputcsv($output, [
                $txn['transaction_date'],
                ucfirst($txn['type']),
                $this->escapeCsvValue($txn['category_name'] ?? 'Uncategorized'),
                $this->escapeCsvValue($txn['description'] ?? ''),
                $txn['amount'],
                $txn['currency'],
                $this->escapeCsvValue($txn['notes'] ?? ''),
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Escape CSV value to prevent formula injection when opened in Excel.
     */
    private function escapeCsvValue(string $value): string
    {
        if ($value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $value;
        }

        return $value;
    }

    /**
     * Import transactions from CSV.
     */
    public function importCsv(int $userId, int $profileId, string $csvContent): array
    {
        $lines = str_getcsv($csvContent, "\n");
        $header = str_getcsv(array_shift($lines));

        $imported = 0;
        $errors = [];
        $categoryModel = model(\App\Models\CategoryModel::class);

        foreach ($lines as $lineNum => $line) {
            $row = str_getcsv($line);

            if (count($row) < 5) {
                $errors[] = "Line " . ($lineNum + 2) . ": insufficient columns";
                continue;
            }

            $date = $row[0] ?? '';
            $type = strtolower(trim($row[1] ?? ''));
            $categoryName = trim($row[2] ?? '');
            $description = trim($row[3] ?? '');
            $amount = (float) ($row[4] ?? 0);
            $currency = trim($row[5] ?? 'EUR');

            // Validate
            if (! in_array($type, ['income', 'expense'])) {
                $errors[] = "Line " . ($lineNum + 2) . ": invalid type '{$type}'";
                continue;
            }

            if ($amount <= 0) {
                $errors[] = "Line " . ($lineNum + 2) . ": invalid amount";
                continue;
            }

            if (! strtotime($date)) {
                $errors[] = "Line " . ($lineNum + 2) . ": invalid date '{$date}'";
                continue;
            }

            // Find or create category
            $category = $categoryModel->where('user_id', $userId)
                ->where('profile_id', $profileId)
                ->where('name', $categoryName)
                ->where('type', $type)
                ->first();

            if (! $category) {
                $catId = $categoryModel->insert([
                    'user_id'    => $userId,
                    'profile_id' => $profileId,
                    'name'       => $categoryName ?: 'Imported',
                    'type'       => $type,
                ]);
            } else {
                $catId = $category['id'];
            }

            $this->transactionModel->insert([
                'user_id'          => $userId,
                'profile_id'       => $profileId,
                'category_id'      => $catId,
                'type'             => $type,
                'amount'           => $amount,
                'currency'         => $currency,
                'description'      => $description,
                'transaction_date' => date('Y-m-d', strtotime($date)),
            ]);

            $imported++;
        }

        return [
            'imported' => $imported,
            'errors'   => $errors,
            'total'    => count($lines),
        ];
    }
}
