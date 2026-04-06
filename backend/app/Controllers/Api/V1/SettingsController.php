<?php

namespace App\Controllers\Api\V1;

use App\Models\SettingsModel;

class SettingsController extends BaseApiController
{
    private SettingsModel $settingsModel;

    public function __construct()
    {
        $this->settingsModel = model(SettingsModel::class);
    }

    /**
     * GET /api/v1/settings
     */
    public function index()
    {
        $settings = $this->settingsModel->getByUser($this->getUserId());

        return $this->success($settings);
    }

    /**
     * PUT /api/v1/settings
     */
    public function update($id = null)
    {
        $rules = [
            'currency'    => 'if_exist|in_list[EUR,USD,GBP,INR,JPY,CAD,AUD,CHF]',
            'theme'       => 'if_exist|in_list[light,dark,auto]',
            'language'    => 'if_exist|max_length[5]',
            'date_format' => 'if_exist|max_length[20]',
        ];

        if (! $this->validate($rules)) {
            return $this->error('Validation failed', 422, $this->validator->getErrors());
        }

        $data = array_filter([
            'currency'    => $this->getInput('currency'),
            'theme'       => $this->getInput('theme'),
            'language'    => $this->getInput('language'),
            'date_format' => $this->getInput('date_format'),
        ], fn($v) => $v !== null);

        $this->settingsModel->updateByUser($this->getUserId(), $data);

        $settings = $this->settingsModel->getByUser($this->getUserId());

        return $this->success($settings, 'Settings updated');
    }

    /**
     * GET /api/v1/settings/currencies
     */
    public function currencies()
    {
        $currencies = [
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€'],
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$'],
            ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£'],
            ['code' => 'INR', 'name' => 'Indian Rupee', 'symbol' => '₹'],
            ['code' => 'JPY', 'name' => 'Japanese Yen', 'symbol' => '¥'],
            ['code' => 'CAD', 'name' => 'Canadian Dollar', 'symbol' => 'CA$'],
            ['code' => 'AUD', 'name' => 'Australian Dollar', 'symbol' => 'A$'],
            ['code' => 'CHF', 'name' => 'Swiss Franc', 'symbol' => 'CHF'],
        ];

        return $this->success($currencies);
    }
}
