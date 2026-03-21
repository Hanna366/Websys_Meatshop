<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class RouteBootstrapTest extends TestCase
{
    public function test_route_list_command_runs_successfully(): void
    {
        $exitCode = Artisan::call('route:list');

        $this->assertSame(0, $exitCode);
    }
}
