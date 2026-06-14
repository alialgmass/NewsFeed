<?php

namespace Modules\Gateway\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Gateway\Models\ApiKey;

class GatewayDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        ApiKey::generate('Development Key', [
            'rate_limit_tier' => 'unlimited',
            'metadata' => [
                'environment' => 'development',
            ],
        ]);

        ApiKey::generate('Test Key', [
            'rate_limit_tier' => 'basic',
            'metadata' => [
                'environment' => 'testing',
            ],
        ]);
    }
}
