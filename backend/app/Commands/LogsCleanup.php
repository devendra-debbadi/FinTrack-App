<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class LogsCleanup extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'logs:cleanup';
    protected $description = 'Delete activity logs older than 90 days.';

    protected $usage     = 'logs:cleanup [options]';
    protected $arguments = [];
    protected $options   = [
        '--days' => 'Number of days to retain (default: 90)',
    ];

    public function run(array $params): void
    {
        $days = (int) CLI::getOption('days') ?: 90;

        CLI::write("Deleting activity logs older than {$days} days...", 'yellow');

        $logModel = model(\App\Models\ActivityLogModel::class);
        $deleted  = $logModel->deleteOlderThan($days);

        CLI::write("Deleted {$deleted} log record(s).", 'green');
    }
}
