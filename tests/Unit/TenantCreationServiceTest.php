<?php

namespace Tests\Unit;

use App\Models\Central\Plan;
use App\Models\Central\Tenant;
use App\Services\TenantCreationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TenantCreationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $connectionsToTransact = ['central'];

    protected function setUp(): void
    {
        parent::setUp();

        // Set up central database connection
        config(['database.default' => 'central']);
    }

    public function test_service_initializes_with_session_id()
    {
        $sessionId = 'test-session-123';
        $service = new TenantCreationService($sessionId);

        expect($service->getProgressKey())->toBe('tenant_creation_progress_' . $sessionId);
    }

    public function test_service_generates_random_session_id_if_not_provided()
    {
        $service = new TenantCreationService();

        expect($service->getProgressKey())
            ->toStartWith('tenant_creation_progress_')
            ->toHaveLength(42); // 'tenant_creation_progress_' + 16 random chars
    }

    public function test_progress_tracking_initializes_correctly()
    {
        $service = new TenantCreationService('test-progress');

        // Access private method via reflection
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('initializeProgress');
        $method->setAccessible(true);
        $method->invoke($service);

        $progress = $service->getProgress();

        expect($progress)
            ->toHaveKey('status', 'running')
            ->toHaveKey('current_step', 0)
            ->toHaveKey('total_steps', 10)
            ->toHaveKey('percentage', 0)
            ->toHaveKey('steps', [])
            ->toHaveKey('started_at')
            ->toHaveKey('completed_at', null)
            ->toHaveKey('error', null);
    }

    public function test_create_tenant_validates_subdomain_availability()
    {
        // Create a plan first
        $plan = Plan::create([
            'name' => 'Test Plan',
            'slug' => 'test-plan',
            'price' => 99.99,
            'billing_interval' => 'monthly',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Create existing tenant with domain
        $existingTenant = Tenant::create(['id' => \Illuminate\Support\Str::uuid()]);
        $existingTenant->domains()->create(['domain' => 'existing.midastech.testing.in']);

        $service = new TenantCreationService('test-validation');

        $validatedData = [
            'company_name' => 'Test Company',
            'subdomain' => 'existing',
            'domain' => 'midastech.testing.in',
            'email' => 'test@example.com',
            'phone' => '1234567890',
            'plan_id' => $plan->id,
            'trial_enabled' => true,
            'trial_days' => 14,
            'admin_first_name' => 'John',
            'admin_last_name' => 'Doe',
            'admin_email' => 'admin@example.com',
            'admin_password' => 'password123',
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Subdomain 'existing' is already taken");

        $service->create($validatedData);
    }

    public function test_get_progress_returns_default_when_not_started()
    {
        $service = new TenantCreationService('not-started');
        $progress = $service->getProgress();

        expect($progress)
            ->toHaveKey('status', 'not_started')
            ->toHaveKey('current_step', 0)
            ->toHaveKey('total_steps', 10)
            ->toHaveKey('percentage', 0);
    }

    public function test_progress_key_format()
    {
        $sessionId = 'abc123';
        $service = new TenantCreationService($sessionId);

        expect($service->getProgressKey())
            ->toBe('tenant_creation_progress_abc123')
            ->toContain('tenant_creation_progress_');
    }

    public function test_cache_stores_progress_with_correct_ttl()
    {
        Cache::shouldReceive('store')
            ->with('file')
            ->andReturnSelf();

        Cache::shouldReceive('put')
            ->once()
            ->withArgs(function ($key, $value, $ttl) {
                return str_starts_with($key, 'tenant_creation_progress_')
                    && is_array($value)
                    && $ttl->diffInMinutes(now()) === 30;
            });

        $service = new TenantCreationService('cache-test');

        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('initializeProgress');
        $method->setAccessible(true);
        $method->invoke($service);
    }

    protected function tearDown(): void
    {
        // Clean up cache
        Cache::store('file')->flush();

        parent::tearDown();
    }
}
