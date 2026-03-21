<?php

namespace Tests\Feature;

use Tests\TestCase;

class SubscriptionMiddlewareTest extends TestCase
{
    public function test_products_is_not_available_on_central_domain(): void
    {
        $response = $this->get('/products');

        $response->assertStatus(404);
    }

    public function test_sales_is_not_available_on_central_domain_even_with_authenticated_session(): void
    {
        $response = $this
            ->withSession([
                'authenticated' => true,
                'user' => [
                    'id' => 1,
                    'plan' => 'Basic',
                ],
            ])
            ->get('/sales');

        $response->assertStatus(404);
    }

    public function test_sales_is_not_available_on_central_domain_for_premium_session(): void
    {
        $response = $this
            ->withSession([
                'authenticated' => true,
                'user' => [
                    'id' => 1,
                    'plan' => 'Premium',
                ],
            ])
            ->get('/sales');

        $response->assertStatus(404);
    }
}
