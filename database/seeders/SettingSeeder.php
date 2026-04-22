<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'bot_enabled',
                'value' => 1,
            ],
            [
                'key' => 'bot_interval',
                'value' => 1,
            ],
            [
                'key' => 'bot_target_profit',
                'value' => 2,
            ],
            [
                'key' => 'bot_min_buy_usd',
                'value' => 5,
            ],
            [
                'key' => 'bot_max_risk_percent',
                'value' => 10,
            ],
            [
                'key' => 'bot_stop_loss_percent',
                'value' => 2,
            ],
            [
                'key' => 'bot_take_profit_percent',
                'value' => 3,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }
    }
}