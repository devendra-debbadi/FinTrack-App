<?php

namespace App\Controllers\Api\V1;

use App\Services\ReportService;
use App\Services\ExportService;

class ReportController extends BaseApiController
{
    private ReportService $reportService;

    public function __construct()
    {
        $this->reportService = new ReportService();
    }

    /**
     * GET /api/v1/reports/monthly
     */
    public function monthly()
    {
        $profileId = $this->getProfileId();
        $year = (int) ($this->request->getGet('year') ?? date('Y'));
        $month = (int) ($this->request->getGet('month') ?? date('m'));

        $data = $this->reportService->monthlySummary(
            $this->getUserId(),
            $profileId,
            $year,
            $month
        );

        return $this->success($data);
    }

    /**
     * GET /api/v1/reports/yearly
     */
    public function yearly()
    {
        $profileId = $this->getProfileId();
        $year = (int) ($this->request->getGet('year') ?? date('Y'));

        $data = $this->reportService->yearlySummary(
            $this->getUserId(),
            $profileId,
            $year
        );

        return $this->success($data);
    }

    /**
     * GET /api/v1/reports/category/:id
     */
    public function category($id = null)
    {
        $profileId = $this->getProfileId();
        $dateFrom = $this->request->getGet('date_from') ?? date('Y-m-01');
        $dateTo = $this->request->getGet('date_to') ?? date('Y-m-d');

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom) || ! strtotime($dateFrom)) {
            $dateFrom = date('Y-m-01');
        }
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo) || ! strtotime($dateTo)) {
            $dateTo = date('Y-m-d');
        }

        $data = $this->reportService->categoryDetail(
            $this->getUserId(),
            $profileId,
            (int) $id,
            $dateFrom,
            $dateTo
        );

        if (isset($data['error'])) {
            return $this->error($data['error'], 404);
        }

        return $this->success($data);
    }

    /**
     * GET /api/v1/reports/income-vs-expense
     */
    public function incomeVsExpense()
    {
        $profileId = $this->getProfileId();
        $year = (int) ($this->request->getGet('year') ?? date('Y'));

        $data = $this->reportService->incomeVsExpense(
            $this->getUserId(),
            $profileId,
            $year
        );

        return $this->success($data);
    }

    /**
     * GET /api/v1/reports/budget-performance
     */
    public function budgetPerformance()
    {
        $profileId = $this->getProfileId();
        $year = (int) ($this->request->getGet('year') ?? date('Y'));

        $data = $this->reportService->budgetPerformance(
            $this->getUserId(),
            $profileId,
            $year
        );

        return $this->success($data);
    }

    /**
     * GET /api/v1/reports/export/csv
     */
    public function exportCsv()
    {
        $profileId = $this->getProfileId();
        $exportService = new ExportService();

        $filters = [
            'type'      => $this->request->getGet('type'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to'   => $this->request->getGet('date_to'),
        ];

        $csv = $exportService->exportCsv(
            $this->getUserId(),
            $profileId,
            array_filter($filters)
        );

        $filename = 'fintrack_export_' . date('Y-m-d') . '.csv';

        return $this->response
            ->setHeader('Content-Type', 'text/csv')
            ->setHeader('Content-Disposition', "attachment; filename=\"{$filename}\"")
            ->setBody($csv);
    }

    /**
     * POST /api/v1/reports/import/csv
     */
    public function importCsv()
    {
        $file = $this->request->getFile('file');

        if (! $file || ! $file->isValid()) {
            return $this->error('Valid CSV file required', 422);
        }

        $allowedMimes = ['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'];
        if (! in_array($file->getMimeType(), $allowedMimes) && strtolower($file->getClientExtension()) !== 'csv') {
            return $this->error('File must be a CSV', 422);
        }

        $profileId = $this->getProfileId();
        $exportService = new ExportService();

        $result = $exportService->importCsv(
            $this->getUserId(),
            $profileId,
            file_get_contents($file->getTempName())
        );

        return $this->success($result, "Imported {$result['imported']} of {$result['total']} transactions");
    }
}
