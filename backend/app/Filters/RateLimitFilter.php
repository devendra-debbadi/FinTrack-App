<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RateLimitFilter implements FilterInterface
{
    private int $maxAttempts = 10;
    private int $windowMinutes = 15;

    public function before(RequestInterface $request, $arguments = null)
    {
        try {
            $db = \Config\Database::connect();
            $ip = $request->getIPAddress();

            $cutoff = date('Y-m-d H:i:s', time() - ($this->windowMinutes * 60));

            $count = $db->table('login_attempts')
                ->where('ip_address', $ip)
                ->where('attempted_at >=', $cutoff)
                ->countAllResults();

            if ($count >= $this->maxAttempts) {
                return service('response')
                    ->setJSON([
                        'status'  => 'error',
                        'message' => 'Too many attempts. Please try again later.',
                    ])
                    ->setStatusCode(ResponseInterface::HTTP_TOO_MANY_REQUESTS);
            }
        } catch (\Throwable $e) {
            log_message('warning', 'RateLimitFilter: DB unavailable — ' . $e->getMessage());
        }

        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No post-processing needed
    }
}
