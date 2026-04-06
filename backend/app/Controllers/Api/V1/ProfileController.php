<?php

namespace App\Controllers\Api\V1;

use App\Models\ProfileModel;

class ProfileController extends BaseApiController
{
    private ProfileModel $profileModel;

    public function __construct()
    {
        $this->profileModel = model(ProfileModel::class);
    }

    /**
     * GET /api/v1/profiles
     */
    public function index()
    {
        $profiles = $this->profileModel->getByUser($this->getUserId());

        return $this->success($profiles);
    }

    /**
     * POST /api/v1/profiles
     */
    public function create()
    {
        $rules = [
            'name' => 'required|min_length[2]|max_length[100]',
        ];

        if (! $this->validate($rules)) {
            return $this->error('Validation failed', 422, $this->validator->getErrors());
        }

        $id = $this->profileModel->insert([
            'user_id'     => $this->getUserId(),
            'name'        => $this->getInput('name'),
            'description' => $this->getInput('description'),
            'is_default'  => 0,
        ]);

        return $this->success($this->profileModel->find($id), 'Profile created', 201);
    }

    /**
     * PUT /api/v1/profiles/:id
     */
    public function update($id = null)
    {
        $profile = $this->profileModel
            ->where('user_id', $this->getUserId())
            ->find($id);

        if (! $profile) {
            return $this->error('Profile not found', 404);
        }

        $data = array_filter([
            'name'        => $this->getInput('name'),
            'description' => $this->getInput('description'),
        ], fn($v) => $v !== null);

        $this->profileModel->update($id, $data);

        return $this->success($this->profileModel->find($id), 'Profile updated');
    }

    /**
     * PATCH /api/v1/profiles/:id/default
     */
    public function setDefault($id = null)
    {
        $profile = $this->profileModel
            ->where('user_id', $this->getUserId())
            ->find($id);

        if (! $profile) {
            return $this->error('Profile not found', 404);
        }

        // Unset all defaults
        $this->profileModel->where('user_id', $this->getUserId())
            ->set(['is_default' => 0])
            ->update();

        // Set new default
        $this->profileModel->update($id, ['is_default' => 1]);

        return $this->success($this->profileModel->find($id), 'Default profile updated');
    }

    /**
     * DELETE /api/v1/profiles/:id
     */
    public function delete($id = null)
    {
        $profile = $this->profileModel
            ->where('user_id', $this->getUserId())
            ->find($id);

        if (! $profile) {
            return $this->error('Profile not found', 404);
        }

        if ($profile['is_default']) {
            return $this->error('Cannot delete default profile', 409);
        }

        $this->profileModel->delete($id);

        return $this->success(null, 'Profile deleted');
    }
}
