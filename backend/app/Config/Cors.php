<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Cors extends BaseConfig
{
    public array $default = [
        'allowedOrigins'         => [],
        'allowedOriginsPatterns' => [],
        'supportsCredentials'    => true,
        'allowedHeaders'         => [
            'Content-Type',
            'Authorization',
            'X-Requested-With',
            'Accept',
            'Origin',
        ],
        'exposedHeaders' => [
            'Content-Disposition',
        ],
        'allowedMethods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
        'maxAge'         => 7200,
    ];

    public function __construct()
    {
        parent::__construct();

        $origin = env('cors.allowedOrigins', 'http://localhost:5173');
        $this->default['allowedOrigins'] = [$origin];
    }
}
