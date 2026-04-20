<?php

namespace Tests\Feature;

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\EnsureCentralAdmin;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\User;
use App\Services\GitHubService;
use App\Services\UpdateService;
use Mockery;
use Tests\TestCase;

class SystemUpdateControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_central_admin_can_queue_latest_system_update_from_api(): void
    {
        $this->withoutMiddleware([
            Authenticate::class,
            EnsureCentralAdmin::class,
            VerifyCsrfToken::class,
        ]);

        $github = Mockery::mock(GitHubService::class);
        $updates = Mockery::mock(UpdateService::class);

        $updates->shouldReceive('downloadAndQueueLatest')
            ->once()
            ->with([
                'regenerate_app_key' => true,
                'run_composer_install' => true,
                'run_npm_install' => false,
                'run_migrations' => true,
            ])
            ->andReturn([
                'success' => true,
                'update_id' => 42,
                'version' => '1.2.3',
                'options' => [
                    'regenerate_app_key' => true,
                    'run_composer_install' => true,
                    'run_npm_install' => false,
                    'run_migrations' => true,
                ],
            ]);

        $this->app->instance(GitHubService::class, $github);
        $this->app->instance(UpdateService::class, $updates);

        $response = $this->actingAs($this->makeCentralAdmin())
            ->postJson(route('admin.updates.download-latest'), [
                'regenerate_app_key' => true,
                'run_composer_install' => true,
                'run_npm_install' => false,
                'run_migrations' => true,
            ]);

        $response->assertOk()->assertJson([
            'success' => true,
            'update_id' => 42,
            'version' => '1.2.3',
            'options' => [
                'regenerate_app_key' => true,
                'run_composer_install' => true,
                'run_npm_install' => false,
                'run_migrations' => true,
            ],
        ]);
    }

    public function test_central_admin_can_start_update_from_web_form(): void
    {
        $this->withoutMiddleware([
            Authenticate::class,
            EnsureCentralAdmin::class,
            VerifyCsrfToken::class,
        ]);

        $updates = Mockery::mock(UpdateService::class);
        $updates->shouldReceive('downloadAndQueueLatest')
            ->once()
            ->with([
                'regenerate_app_key' => false,
                'run_composer_install' => true,
                'run_npm_install' => true,
                'run_migrations' => true,
            ])
            ->andReturn([
                'success' => true,
                'update_id' => 7,
            ]);

        $this->app->instance(UpdateService::class, $updates);

        $response = $this->actingAs($this->makeCentralAdmin())
            ->post(route('admin.update.perform'), [
                'run_composer_install' => 1,
                'run_npm_install' => 1,
                'run_migrations' => 1,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'System update queued successfully.');
    }

    protected function makeCentralAdmin(): User
    {
        $user = new User([
            'role' => 'admin',
            'email' => 'admin@example.com',
            'tenant_id' => null,
        ]);

        $user->id = 999;

        return $user;
    }
}
