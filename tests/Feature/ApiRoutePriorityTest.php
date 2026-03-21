<?php

namespace Tests\Feature;

use Illuminate\Http\Request as HttpRequest;
use Tests\TestCase;

class ApiRoutePriorityTest extends TestCase
{
    public function test_products_categories_route_is_not_shadowed_by_dynamic_product_route(): void
    {
        $route = app('router')->getRoutes()->match(HttpRequest::create('/api/products/categories', 'GET'));

        $this->assertStringContainsString('ProductController@categories', $route->getActionName());
    }

    public function test_sales_summary_route_is_not_shadowed_by_dynamic_sale_route(): void
    {
        $route = app('router')->getRoutes()->match(HttpRequest::create('/api/sales/summary', 'GET'));

        $this->assertStringContainsString('SalesController@summary', $route->getActionName());
    }

    public function test_suppliers_rankings_route_is_not_shadowed_by_dynamic_supplier_route(): void
    {
        $route = app('router')->getRoutes()->match(HttpRequest::create('/api/suppliers/rankings', 'GET'));

        $this->assertStringContainsString('SupplierController@rankings', $route->getActionName());
    }
}
