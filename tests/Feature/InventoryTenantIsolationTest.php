<?php

namespace Tests\Feature;

use App\Http\Controllers\InventoryController;
use App\Models\InventoryBatch;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class InventoryTenantIsolationTest extends TestCase
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

    public function test_update_batch_returns_404_when_batch_not_found_in_tenant_scope(): void
    {
        $user = new User([
            'id' => 10,
            'tenant_id' => 'tenant-alpha',
            'email' => 'alpha@example.com',
        ]);

        $inventory = Mockery::mock(InventoryService::class);
        $inventory->shouldReceive('findBatchByIdentifier')
            ->once()
            ->with('tenant-alpha', 'BATCH-001')
            ->andReturn(null);

        $controller = new InventoryController($inventory, Mockery::mock(ProductService::class));
        $request = $this->makeRequest('PUT', '/api/inventory/batch/BATCH-001', [
            'quantity' => 4,
        ], $user);

        $response = $controller->updateBatch($request, 'BATCH-001');

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame([
            'success' => false,
            'message' => 'Batch not found.',
        ], $response->getData(true));
    }

    public function test_record_waste_returns_404_when_batch_not_found_in_tenant_scope(): void
    {
        $user = new User([
            'id' => 11,
            'tenant_id' => 'tenant-beta',
            'email' => 'beta@example.com',
        ]);

        $inventory = Mockery::mock(InventoryService::class);
        $inventory->shouldReceive('findBatchByIdentifier')
            ->once()
            ->with('tenant-beta', 'BATCH-404')
            ->andReturn(null);

        $controller = new InventoryController($inventory, Mockery::mock(ProductService::class));
        $request = $this->makeRequest('POST', '/api/inventory/batch/BATCH-404/waste', [
            'quantity' => 1,
            'reason' => 'damaged',
        ], $user);

        $response = $controller->recordWaste($request, 'BATCH-404');

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame([
            'success' => false,
            'message' => 'Batch not found.',
        ], $response->getData(true));
    }

    public function test_update_batch_successfully_returns_updated_batch_in_tenant_scope(): void
    {
        $user = new User([
            'id' => 12,
            'tenant_id' => 'tenant-gamma',
            'email' => 'gamma@example.com',
        ]);

        $existingBatch = new InventoryBatch([
            'id' => 77,
            'batch_code' => 'BATCH-777',
            'quantity' => 12,
        ]);

        $updatedBatch = new InventoryBatch([
            'id' => 77,
            'batch_code' => 'BATCH-777',
            'quantity' => 8,
        ]);

        $inventory = Mockery::mock(InventoryService::class);
        $inventory->shouldReceive('findBatchByIdentifier')
            ->once()
            ->with('tenant-gamma', 'BATCH-777')
            ->andReturn($existingBatch);

        $inventory->shouldReceive('updateBatch')
            ->once()
            ->with($existingBatch, Mockery::type('array'))
            ->andReturn($updatedBatch);

        $controller = new InventoryController($inventory, Mockery::mock(ProductService::class));
        $request = $this->makeRequest('PUT', '/api/inventory/batch/BATCH-777', [
            'quantity' => 8,
        ], $user);

        $response = $controller->updateBatch($request, 'BATCH-777');

        $this->assertSame(200, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertSame('Batch updated successfully.', $data['message']);
        $this->assertSame('BATCH-777', $data['data']['batch']['batch_code']);
        $this->assertSame(8, $data['data']['batch']['quantity']);
    }
}
