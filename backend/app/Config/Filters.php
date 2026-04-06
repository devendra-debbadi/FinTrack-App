<?php

namespace Config;

use CodeIgniter\Config\Filters as BaseFilters;
use CodeIgniter\Filters\Cors;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\ForceHTTPS;
use CodeIgniter\Filters\PageCache;
use CodeIgniter\Filters\PerformanceMetrics;
use App\Filters\JwtAuthFilter;
use App\Filters\AdminFilter;
use App\Filters\RateLimitFilter;

class Filters extends BaseFilters
{
    public array $aliases = [
        'toolbar'   => DebugToolbar::class,
        'cors'      => Cors::class,
        'forcehttps' => ForceHTTPS::class,
        'pagecache' => PageCache::class,
        'performance' => PerformanceMetrics::class,
        'jwt'       => JwtAuthFilter::class,
        'admin'     => AdminFilter::class,
        'ratelimit' => RateLimitFilter::class,
    ];

    public array $required = [
        'before' => [
            'forcehttps',
            'pagecache',
        ],
        'after' => [
            'pagecache',
            'performance',
            'toolbar',
        ],
    ];

    public array $globals = [
        'before' => [
            'cors',
        ],
        'after' => [],
    ];

    public array $methods = [];

    public array $filters = [
        'ratelimit' => [
            'before' => [
                'api/v1/auth/login',
                'api/v1/auth/register',
                'api/v1/auth/refresh',
                'api/v1/auth/password',
            ],
        ],
        'jwt' => [
            'before' => [
                'api/v1/dashboard',
                'api/v1/dashboard/*',
                'api/v1/transactions',
                'api/v1/transactions/*',
                'api/v1/categories',
                'api/v1/categories/*',
                'api/v1/profiles',
                'api/v1/profiles/*',
                'api/v1/budgets',
                'api/v1/budgets/*',
                'api/v1/goals',
                'api/v1/goals/*',
                'api/v1/tags',
                'api/v1/tags/*',
                'api/v1/settings',
                'api/v1/settings/*',
                'api/v1/receipts',
                'api/v1/receipts/*',
                'api/v1/reports',
                'api/v1/reports/*',
                'api/v1/recurring',
                'api/v1/recurring/*',
                'api/v1/net-worth',
                'api/v1/net-worth/*',
                'api/v1/admin',
                'api/v1/admin/*',
                'api/v1/auth/logout',
                'api/v1/auth/me',
                'api/v1/auth/profile',
                'api/v1/auth/password',
            ],
        ],
    ];
}
