<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $userData = $request->userData ?? null;

        if (! $userData || ($userData->role ?? '') !== 'admin') {
            return service('response')
                ->setJSON(['status' => 'error', 'message' => 'Admin access required'])
                ->setStatusCode(ResponseInterface::HTTP_FORBIDDEN);
        }

        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No post-processing needed
    }
}
