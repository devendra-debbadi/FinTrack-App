<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\JwtManager;

class JwtAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (empty($authHeader) || ! str_starts_with($authHeader, 'Bearer ')) {
            return service('response')
                ->setJSON(['status' => 'error', 'message' => 'Access token required'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $token = substr($authHeader, 7);
        $jwt = new JwtManager();
        $decoded = $jwt->validateAccessToken($token);

        if ($decoded === null) {
            return service('response')
                ->setJSON(['status' => 'error', 'message' => 'Invalid or expired access token'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        // Attach user data to the request for downstream controllers
        $request->userData = $decoded->data;

        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No post-processing needed
    }
}
