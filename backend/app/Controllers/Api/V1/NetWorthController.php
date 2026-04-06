<?php

namespace App\Controllers\Api\V1;

use App\Models\NetWorthEntryModel;

class NetWorthController extends BaseApiController
{
    private NetWorthEntryModel $model;

    public function __construct()
    {
        $this->model = model(NetWorthEntryModel::class);
    }

    /**
     * GET /api/v1/net-worth
     */
    public function index()
    {
        $profileId = $this->getProfileId();
        $summary = $this->model->getSummary($this->getUserId(), $profileId);

        return $this->success($summary);
    }

    /**
     * POST /api/v1/net-worth
     */
    public function create()
    {
        $rules = [
            'name'       => 'required|max_length[255]',
            'type'       => 'required|in_list[asset,liability]',
            'amount'     => 'required|decimal|greater_than_equal_to[0]',
            'entry_date' => 'required|valid_date',
        ];

        if (! $this->validate($rules)) {
            return $this->error('Validation failed', 422, $this->validator->getErrors());
        }

        $profileId = $this->getProfileId();

        $data = [
            'user_id'    => $this->getUserId(),
            'profile_id' => $profileId,
            'name'       => $this->getInput('name'),
            'type'       => $this->getInput('type'),
            'amount'     => $this->getInput('amount'),
            'currency'   => $this->getInput('currency') ?? 'EUR',
            'icon'       => $this->getInput('icon') ?? 'pi pi-wallet',
            'color'      => $this->getInput('color') ?? '#6366f1',
            'notes'      => $this->getInput('notes') ?? '',
            'entry_date' => $this->getInput('entry_date'),
        ];

        $id = $this->model->insert($data);

        return $this->success($this->model->find($id), 'Entry created', 201);
    }

    /**
     * PUT /api/v1/net-worth/:id
     */
    public function update($id = null)
    {
        $entry = $this->model
            ->where('user_id', $this->getUserId())
            ->find($id);

        if (! $entry) {
            return $this->error('Entry not found', 404);
        }

        $data = array_filter([
            'name'       => $this->getInput('name'),
            'type'       => $this->getInput('type'),
            'amount'     => $this->getInput('amount'),
            'currency'   => $this->getInput('currency'),
            'icon'       => $this->getInput('icon'),
            'color'      => $this->getInput('color'),
            'notes'      => $this->getInput('notes'),
            'entry_date' => $this->getInput('entry_date'),
        ], fn($v) => $v !== null);

        $this->model->update($id, $data);

        return $this->success($this->model->find($id), 'Entry updated');
    }

    /**
     * DELETE /api/v1/net-worth/:id
     */
    public function delete($id = null)
    {
        $entry = $this->model
            ->where('user_id', $this->getUserId())
            ->find($id);

        if (! $entry) {
            return $this->error('Entry not found', 404);
        }

        $this->model->delete($id);

        return $this->success(null, 'Entry deleted');
    }
}
