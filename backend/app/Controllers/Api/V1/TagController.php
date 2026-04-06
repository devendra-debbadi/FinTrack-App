<?php

namespace App\Controllers\Api\V1;

use App\Models\TagModel;

class TagController extends BaseApiController
{
    private TagModel $tagModel;

    public function __construct()
    {
        $this->tagModel = model(TagModel::class);
    }

    /**
     * GET /api/v1/tags
     */
    public function index()
    {
        $tags = $this->tagModel->getByUser($this->getUserId());

        return $this->success($tags);
    }

    /**
     * POST /api/v1/tags
     */
    public function create()
    {
        $rules = [
            'name' => 'required|min_length[2]|max_length[50]',
        ];

        if (! $this->validate($rules)) {
            return $this->error('Validation failed', 422, $this->validator->getErrors());
        }

        $id = $this->tagModel->insert([
            'user_id'    => $this->getUserId(),
            'name'       => $this->getInput('name'),
            'color'      => $this->getInput('color') ?? '#6366f1',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->success($this->tagModel->find($id), 'Tag created', 201);
    }

    /**
     * PUT /api/v1/tags/:id
     */
    public function update($id = null)
    {
        $tag = $this->tagModel
            ->where('user_id', $this->getUserId())
            ->find($id);

        if (! $tag) {
            return $this->error('Tag not found', 404);
        }

        $data = array_filter([
            'name'  => $this->getInput('name'),
            'color' => $this->getInput('color'),
        ], fn($v) => $v !== null);

        $this->tagModel->update($id, $data);

        return $this->success($this->tagModel->find($id), 'Tag updated');
    }

    /**
     * DELETE /api/v1/tags/:id
     */
    public function delete($id = null)
    {
        $tag = $this->tagModel
            ->where('user_id', $this->getUserId())
            ->find($id);

        if (! $tag) {
            return $this->error('Tag not found', 404);
        }

        $this->tagModel->delete($id);

        return $this->success(null, 'Tag deleted');
    }
}
