<?php

namespace Tests\Feature;

use App\Services\SubscriptionService;
use Tests\TestCase;

class SubscriptionServiceAliasTest extends TestCase
{
    public function test_pos_access_alias_maps_to_pos_system_feature(): void
    {
        session([
            'user' => [
                'id' => 1,
                'plan' => 'Basic',
            ],
        ]);

        $this->assertFalse(SubscriptionService::hasFeature('pos_access'));

        session([
            'user' => [
                'id' => 1,
                'plan' => 'Premium',
            ],
        ]);

        $this->assertTrue(SubscriptionService::hasFeature('pos_access'));
    }

    public function test_plan_name_normalization_supports_mixed_case_inputs(): void
    {
        session([
            'user' => [
                'id' => 1,
                'plan' => 'PrEmIuM',
            ],
        ]);

        $this->assertTrue(SubscriptionService::hasFeature('advanced_analytics'));
        $this->assertSame('Premium', SubscriptionService::getPlanDisplayName('PrEmIuM'));
    }
}
