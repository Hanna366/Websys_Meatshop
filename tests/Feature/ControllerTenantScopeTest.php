<?php

namespace Tests\Feature;

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\SupplierController;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use App\Services\CustomerService;
use App\Services\SalesService;
use App\Services\SupplierService;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class ControllerTenantScopeTest extends TestCase
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

    public function test_customer_show_uses_tenant_scope_and_returns_not_found(): void
    {
        $user = new User([
            'id' => 1,
            'tenant_id' => 'tenant-alpha',
            'email' => 'alpha@example.com',
        ]);

        $customers = Mockery::mock(CustomerService::class);
        $customers->shouldReceive('findForTenant')
            ->once()
            ->with('tenant-alpha', 'CUST-404')
            ->andReturn(null);

        $controller = new CustomerController($customers);
        $request = $this->makeRequest('GET', '/api/customers/CUST-404', [], $user);
        $response = $controller->show($request, 'CUST-404');

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame([
            'success' => false,
            'message' => 'Customer not found.',
        ], $response->getData(true));
    }

    public function test_supplier_rankings_uses_tenant_scope_and_returns_data(): void
    {
        $user = new User([
            'id' => 2,
            'tenant_id' => 'tenant-beta',
            'email' => 'beta@example.com',
        ]);

        $suppliers = Mockery::mock(SupplierService::class);
        $suppliers->shouldReceive('rankings')
            ->once()
            ->with('tenant-beta')
            ->andReturn([
                ['supplier_code' => 'SUP-001', 'score' => 95],
                ['supplier_code' => 'SUP-002', 'score' => 80],
            ]);

        $controller = new SupplierController($suppliers);
        $request = $this->makeRequest('GET', '/api/suppliers/rankings', [], $user);
        $response = $controller->rankings($request);

        $this->assertSame(200, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertCount(2, $data['data']['rankings']);
        $this->assertSame('SUP-001', $data['data']['rankings'][0]['supplier_code']);
    }

    public function test_sales_void_uses_tenant_scope_and_returns_not_found(): void
    {
        $user = new User([
            'id' => 3,
            'tenant_id' => 'tenant-gamma',
            'email' => 'gamma@example.com',
        ]);

        $sales = Mockery::mock(SalesService::class);
        $sales->shouldReceive('findForTenant')
            ->once()
            ->with('tenant-gamma', 'SAL-404')
            ->andReturn(null);

        $controller = new SalesController($sales);
        $request = $this->makeRequest('POST', '/api/sales/SAL-404/void', [], $user);
        $response = $controller->void($request, 'SAL-404');

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame([
            'success' => false,
            'message' => 'Sale not found.',
        ], $response->getData(true));
    }

    public function test_sales_process_returns_422_when_service_throws_exception(): void
    {
        $user = new User([
            'id' => 4,
            'tenant_id' => 'tenant-delta',
            'email' => 'delta@example.com',
        ]);

        $sales = Mockery::mock(SalesService::class);
        $sales->shouldReceive('process')
            ->once()
            ->with('tenant-delta', Mockery::type('int'), Mockery::type('array'))
            ->andThrow(new \RuntimeException('Insufficient stock for product 123.'));

        $controller = new SalesController($sales);
        $request = $this->makeRequest('POST', '/api/sales', [
            'items' => [
                [
                    'product_id' => '123',
                    'quantity' => 2,
                    'unit_price' => 10,
                ],
            ],
            'tax' => 0,
            'discount' => 0,
        ], $user);

        $response = $controller->process($request);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame([
            'success' => false,
            'message' => 'Insufficient stock for product 123.',
        ], $response->getData(true));
    }

    public function test_customer_store_successfully_passes_tenant_scope_and_returns_created(): void
    {
        $user = new User([
            'id' => 5,
            'tenant_id' => 'tenant-epsilon',
            'email' => 'epsilon@example.com',
        ]);

        $createdCustomer = new Customer([
            'customer_code' => 'CUST-001',
            'status' => 'active',
        ]);

        $customers = Mockery::mock(CustomerService::class);
        $customers->shouldReceive('createForTenant')
            ->once()
            ->with('tenant-epsilon', Mockery::type('array'))
            ->andReturn($createdCustomer);

        $controller = new CustomerController($customers);
        $request = $this->makeRequest('POST', '/api/customers', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'phone' => '123456789',
        ], $user);

        $response = $controller->store($request);

        $this->assertSame(201, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertSame('Customer created successfully.', $data['message']);
        $this->assertSame('CUST-001', $data['data']['customer']['customer_code']);
    }

    public function test_supplier_update_successfully_passes_tenant_scope(): void
    {
        $user = new User([
            'id' => 6,
            'tenant_id' => 'tenant-zeta',
            'email' => 'zeta@example.com',
        ]);

        $existingSupplier = new Supplier(['supplier_code' => 'SUP-010']);
        $updatedSupplier = new Supplier(['supplier_code' => 'SUP-010', 'status' => 'active']);

        $suppliers = Mockery::mock(SupplierService::class);
        $suppliers->shouldReceive('findForTenant')
            ->once()
            ->with('tenant-zeta', 'SUP-010')
            ->andReturn($existingSupplier);
        $suppliers->shouldReceive('update')
            ->once()
            ->with($existingSupplier, Mockery::type('array'))
            ->andReturn($updatedSupplier);

        $controller = new SupplierController($suppliers);
        $request = $this->makeRequest('PUT', '/api/suppliers/SUP-010', [
            'name' => 'Regional Supplier',
            'status' => 'active',
        ], $user);

        $response = $controller->update($request, 'SUP-010');

        $this->assertSame(200, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertSame('Supplier updated.', $data['message']);
        $this->assertSame('SUP-010', $data['data']['supplier']['supplier_code']);
    }

    public function test_sales_process_successfully_passes_tenant_scope_and_returns_created(): void
    {
        $user = new User([
            'id' => 7,
            'tenant_id' => 'tenant-eta',
            'email' => 'eta@example.com',
        ]);

        $createdSale = new Sale([
            'sale_code' => 'SAL-001',
            'status' => 'completed',
            'grand_total' => 100,
        ]);

        $sales = Mockery::mock(SalesService::class);
        $sales->shouldReceive('process')
            ->once()
            ->with('tenant-eta', Mockery::type('int'), Mockery::type('array'))
            ->andReturn($createdSale);

        $controller = new SalesController($sales);
        $request = $this->makeRequest('POST', '/api/sales', [
            'items' => [
                [
                    'product_id' => 'PRD-1',
                    'quantity' => 2,
                    'unit_price' => 50,
                ],
            ],
            'tax' => 0,
            'discount' => 0,
        ], $user);

        $response = $controller->process($request);

        $this->assertSame(201, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertSame('Sale processed successfully.', $data['message']);
        $this->assertSame('SAL-001', $data['data']['sale']['sale_code']);
    }
}
