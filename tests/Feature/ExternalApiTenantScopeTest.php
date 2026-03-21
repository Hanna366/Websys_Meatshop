<?php

namespace Tests\Feature;

use App\Http\Controllers\ApiController;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Services\CustomerService;
use App\Services\ProductService;
use App\Services\SalesService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Tests\TestCase;

class ExternalApiTenantScopeTest extends TestCase
{
    private function makeRequest(string $method, string $uri, array $payload, User $user): Request
    {
        $request = Request::create($uri, $method, $payload);
        $request->setUserResolver(static fn () => $user);

        return $request;
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_v1_products_uses_tenant_scoped_listing(): void
    {
        $user = new User([
            'id' => 21,
            'tenant_id' => 'tenant-v1-a',
            'email' => 'v1a@example.com',
        ]);

        $products = Mockery::mock(ProductService::class);
        $paginator = new LengthAwarePaginator(
            [new Product(['product_code' => 'P-1'])],
            1,
            100,
            1
        );
        $products->shouldReceive('listForTenant')
            ->once()
            ->with('tenant-v1-a', ['limit' => 100])
            ->andReturn($paginator);

        $controller = new ApiController($products, Mockery::mock(CustomerService::class), Mockery::mock(SalesService::class));
        $request = $this->makeRequest('GET', '/api/v1/products', [], $user);
        $response = $controller->products($request);

        $this->assertSame(200, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertSame('P-1', $data['data']['products']['data'][0]['product_code']);
    }

    public function test_v1_create_customer_uses_tenant_scope(): void
    {
        $user = new User([
            'id' => 22,
            'tenant_id' => 'tenant-v1-b',
            'email' => 'v1b@example.com',
        ]);

        $customers = Mockery::mock(CustomerService::class);
        $customers->shouldReceive('createForTenant')
            ->once()
            ->with('tenant-v1-b', Mockery::type('array'))
            ->andReturn(new Customer(['customer_code' => 'CUST-V1']));

        $controller = new ApiController(Mockery::mock(ProductService::class), $customers, Mockery::mock(SalesService::class));
        $request = $this->makeRequest('POST', '/api/v1/customers', [
            'first_name' => 'Ana',
            'last_name' => 'Bell',
            'email' => 'ana@example.com',
            'phone' => '123',
        ], $user);

        $response = $controller->createCustomer($request);

        $this->assertSame(201, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertSame('CUST-V1', $data['data']['customer']['customer_code']);
    }

    public function test_v1_create_sale_uses_tenant_scope(): void
    {
        $user = new User([
            'id' => 23,
            'tenant_id' => 'tenant-v1-c',
            'email' => 'v1c@example.com',
        ]);

        $sales = Mockery::mock(SalesService::class);
        $sales->shouldReceive('process')
            ->once()
            ->with('tenant-v1-c', Mockery::type('int'), Mockery::type('array'))
            ->andReturn(new Sale(['sale_code' => 'SAL-V1', 'status' => 'completed']));

        $controller = new ApiController(Mockery::mock(ProductService::class), Mockery::mock(CustomerService::class), $sales);
        $request = $this->makeRequest('POST', '/api/v1/sales', [
            'items' => [
                [
                    'product_id' => 'P-100',
                    'quantity' => 1,
                    'unit_price' => 45,
                ],
            ],
            'tax' => 0,
            'discount' => 0,
        ], $user);

        $response = $controller->createSale($request);

        $this->assertSame(201, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertSame('SAL-V1', $data['data']['sale']['sale_code']);
    }
}
