<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\RESTful\ResourceController;

class BaseApiController extends ResourceController
{
    protected $format = 'json';

    /**
     * Get authenticated user data from JWT.
     */
    protected function getUser(): object
    {
        return $this->request->userData;
    }

    /**
     * Get authenticated user ID.
     */
    protected function getUserId(): int
    {
        return (int) $this->getUser()->id;
    }

    /**
     * Get active profile ID from query param or default.
     */
    protected function getProfileId(): int
    {
        $profileModel = model(\App\Models\ProfileModel::class);
        $profileId = $this->request->getGet('profile_id');

        if ($profileId) {
            $profileId = (int) $profileId;

            // Verify profile belongs to authenticated user
            $profile = $profileModel->where('user_id', $this->getUserId())->find($profileId);

            if (! $profile) {
                return 0;
            }

            return $profileId;
        }

        // Get default profile
        $default = $profileModel->getDefault($this->getUserId());

        return $default ? (int) $default['id'] : 0;
    }

    /**
     * Get input from JSON body or POST form data.
     * Works for both Content-Type: application/json and application/x-www-form-urlencoded.
     */
    protected function getInput(?string $key = null)
    {
        $contentType = $this->request->getHeaderLine('Content-Type');

        if (str_contains($contentType, 'application/json')) {
            $json = $this->request->getJSON(true) ?? [];
            if ($key === null) {
                return $json;
            }
            return $json[$key] ?? null;
        }

        if ($key === null) {
            return $this->request->getPost();
        }
        return $this->request->getPost($key);
    }

    /**
     * Override validate() to support JSON request bodies.
     * CI4's default validate() reads from getVar() which ignores JSON bodies.
     */
    protected function validate($rules, array $messages = []): bool
    {
        $contentType = $this->request->getHeaderLine('Content-Type');

        if (str_contains($contentType, 'application/json')) {
            $data = $this->request->getJSON(true) ?? [];
            return $this->validateData($data, $rules, $messages);
        }

        return parent::validate($rules, $messages);
    }

    /**
     * Log an activity event for the authenticated user.
     */
    protected function logActivity(
        string $action,
        string $entityType = '',
        ?int $entityId = null,
        string $description = '',
        ?int $userId = null
    ): void {
        try {
            $logModel = model(\App\Models\ActivityLogModel::class);
            $logModel->insert([
                'user_id'     => $userId ?? $this->getUserId(),
                'action'      => $action,
                'entity_type' => $entityType ?: null,
                'entity_id'   => $entityId,
                'description' => $description ?: null,
                'ip_address'  => $this->request->getIPAddress(),
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            // Never let logging break the main request
            log_message('error', 'ActivityLog insert failed: ' . $e->getMessage());
        }
    }

    /**
     * Standard success response.
     */
    protected function success($data = null, string $message = 'Success', int $code = 200)
    {
        return $this->respond([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    /**
     * Standard error response.
     */
    protected function error(string $message = 'Error', int $code = 400, $errors = null)
    {
        $response = [
            'status'  => 'error',
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return $this->respond($response, $code);
    }
}
