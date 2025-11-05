<?php

namespace Tests\Feature;

use App\Models\Central\TenantUser;
use App\Models\Central\Plan;
use App\Models\Central\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TenantCreationTest extends TestCase
{
    use RefreshDatabase;

    protected $connectionsToTransact = ['central'];

    protected TenantUser $centralUser;
    protected Plan $plan;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up central database connection
        config(['database.default' => 'central']);

        // Create central user for authentication
        $this->centralUser = TenantUser::create([
            'name' => 'Test Admin',
            'email' => 'admin@midastech.in',
            'password' => bcrypt('password'),
            'is_active' => true,
            'role' => 'super_admin',
        ]);

        // Create a test plan
        $this->plan = Plan::create([
            'name' => 'Test Plan',
            'slug' => 'test-plan',
            'description' => 'Test plan description',
            'price' => 99.99,
            'billing_interval' => 'monthly',
            'max_users' => 10,
            'max_customers' => 100,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Set up fake storage
        Storage::fake('public');
    }

    public function test_tenant_creation_page_loads_successfully()
    {
        $response = $this->actingAs($this->centralUser, 'central')
            ->get(route('central.tenants.create'));

        $response->assertStatus(200)
            ->assertViewIs('central.tenants.create')
            ->assertViewHas('plans');
    }

    public function test_tenant_creation_requires_authentication()
    {
        $response = $this->get(route('central.tenants.create'));

        $response->assertRedirect(route('central.login'));
    }

    public function test_tenant_creation_with_progress_validates_required_fields()
    {
        $response = $this->actingAs($this->centralUser, 'central')
            ->postJson(route('central.tenants.store-with-progress'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'company_name',
                'subdomain',
                'domain',
                'email',
                'plan_id',
                'admin_first_name',
                'admin_last_name',
                'admin_email',
            ]);
    }

    public function test_tenant_creation_validates_subdomain_format()
    {
        $response = $this->actingAs($this->centralUser, 'central')
            ->postJson(route('central.tenants.store-with-progress'), [
                'company_name' => 'Test Company',
                'subdomain' => 'Invalid_Subdomain!',
                'domain' => 'midastech.testing.in',
                'email' => 'test@example.com',
                'plan_id' => $this->plan->id,
                'admin_first_name' => 'John',
                'admin_last_name' => 'Doe',
                'admin_email' => 'admin@example.com',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['subdomain']);
    }

    public function test_tenant_creation_validates_email_format()
    {
        $response = $this->actingAs($this->centralUser, 'central')
            ->postJson(route('central.tenants.store-with-progress'), [
                'company_name' => 'Test Company',
                'subdomain' => 'testcompany',
                'domain' => 'midastech.testing.in',
                'email' => 'invalid-email',
                'plan_id' => $this->plan->id,
                'admin_first_name' => 'John',
                'admin_last_name' => 'Doe',
                'admin_email' => 'admin@example.com',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_tenant_creation_validates_plan_exists()
    {
        $response = $this->actingAs($this->centralUser, 'central')
            ->postJson(route('central.tenants.store-with-progress'), [
                'company_name' => 'Test Company',
                'subdomain' => 'testcompany',
                'domain' => 'midastech.testing.in',
                'email' => 'test@example.com',
                'plan_id' => 99999,
                'admin_first_name' => 'John',
                'admin_last_name' => 'Doe',
                'admin_email' => 'admin@example.com',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['plan_id']);
    }

    public function test_tenant_creation_prevents_duplicate_subdomain()
    {
        // Create existing tenant
        $existingTenant = Tenant::create(['id' => \Illuminate\Support\Str::uuid()]);
        $existingTenant->domains()->create(['domain' => 'existing.midastech.testing.in']);

        $response = $this->actingAs($this->centralUser, 'central')
            ->postJson(route('central.tenants.store-with-progress'), [
                'company_name' => 'Test Company',
                'subdomain' => 'existing',
                'domain' => 'midastech.testing.in',
                'email' => 'test@example.com',
                'plan_id' => $this->plan->id,
                'admin_first_name' => 'John',
                'admin_last_name' => 'Doe',
                'admin_email' => 'admin@example.com',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['domain']);
    }

    public function test_tenant_creation_validates_theme_color_length()
    {
        $response = $this->actingAs($this->centralUser, 'central')
            ->postJson(route('central.tenants.store-with-progress'), [
                'company_name' => 'Test Company',
                'subdomain' => 'testcompany',
                'domain' => 'midastech.testing.in',
                'email' => 'test@example.com',
                'plan_id' => $this->plan->id,
                'admin_first_name' => 'John',
                'admin_last_name' => 'Doe',
                'admin_email' => 'admin@example.com',
                'theme_primary_color' => '#FFFFFFFF', // Too long
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['theme_primary_color']);
    }

    public function test_tenant_creation_accepts_valid_theme_color()
    {
        $response = $this->actingAs($this->centralUser, 'central')
            ->postJson(route('central.tenants.store-with-progress'), [
                'company_name' => 'Test Company',
                'subdomain' => 'testcompany',
                'domain' => 'localhost',
                'email' => 'test@example.com',
                'plan_id' => $this->plan->id,
                'admin_first_name' => 'John',
                'admin_last_name' => 'Doe',
                'admin_email' => 'admin@example.com',
                'theme_primary_color' => '#FF5733',
                'session_id' => 'test-session',
            ]);

        // Should not have validation errors for theme_primary_color
        $response->assertJsonMissingValidationErrors(['theme_primary_color']);
    }

    public function test_tenant_creation_validates_logo_file_type()
    {
        $invalidFile = UploadedFile::fake()->create('logo.pdf', 100);

        $response = $this->actingAs($this->centralUser, 'central')
            ->post(route('central.tenants.store-with-progress'), [
                'company_name' => 'Test Company',
                'subdomain' => 'testcompany',
                'domain' => 'midastech.testing.in',
                'email' => 'test@example.com',
                'plan_id' => $this->plan->id,
                'admin_first_name' => 'John',
                'admin_last_name' => 'Doe',
                'admin_email' => 'admin@example.com',
                'company_logo' => $invalidFile,
            ]);

        $response->assertSessionHasErrors(['company_logo']);
    }

    public function test_tenant_creation_validates_logo_file_size()
    {
        $oversizedFile = UploadedFile::fake()->image('logo.png')->size(3000); // 3MB

        $response = $this->actingAs($this->centralUser, 'central')
            ->post(route('central.tenants.store-with-progress'), [
                'company_name' => 'Test Company',
                'subdomain' => 'testcompany',
                'domain' => 'midastech.testing.in',
                'email' => 'test@example.com',
                'plan_id' => $this->plan->id,
                'admin_first_name' => 'John',
                'admin_last_name' => 'Doe',
                'admin_email' => 'admin@example.com',
                'company_logo' => $oversizedFile,
            ]);

        $response->assertSessionHasErrors(['company_logo']);
    }

    public function test_progress_endpoint_requires_progress_key()
    {
        $response = $this->actingAs($this->centralUser, 'central')
            ->postJson(route('central.tenants.progress'), []);

        $response->assertStatus(400)
            ->assertJson(['error' => 'Progress key required']);
    }

    public function test_progress_endpoint_returns_progress_data()
    {
        $progressKey = 'tenant_creation_progress_test123';

        // Set up fake progress data
        Cache::store('file')->put($progressKey, [
            'status' => 'running',
            'current_step' => 5,
            'total_steps' => 10,
            'percentage' => 50,
            'steps' => [],
        ], now()->addMinutes(30));

        $response = $this->actingAs($this->centralUser, 'central')
            ->postJson(route('central.tenants.progress'), [
                'progress_key' => $progressKey,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'running',
                'current_step' => 5,
                'percentage' => 50,
            ]);
    }

    public function test_tenant_creation_validates_trial_days_range()
    {
        $response = $this->actingAs($this->centralUser, 'central')
            ->postJson(route('central.tenants.store-with-progress'), [
                'company_name' => 'Test Company',
                'subdomain' => 'testcompany',
                'domain' => 'midastech.testing.in',
                'email' => 'test@example.com',
                'plan_id' => $this->plan->id,
                'admin_first_name' => 'John',
                'admin_last_name' => 'Doe',
                'admin_email' => 'admin@example.com',
                'trial_days' => 100, // Exceeds max of 90
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['trial_days']);
    }

    public function test_tenant_creation_validates_timezone_format()
    {
        $response = $this->actingAs($this->centralUser, 'central')
            ->postJson(route('central.tenants.store-with-progress'), [
                'company_name' => 'Test Company',
                'subdomain' => 'testcompany',
                'domain' => 'midastech.testing.in',
                'email' => 'test@example.com',
                'plan_id' => $this->plan->id,
                'admin_first_name' => 'John',
                'admin_last_name' => 'Doe',
                'admin_email' => 'admin@example.com',
                'timezone' => 'Invalid/Timezone',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['timezone']);
    }

    public function test_tenant_creation_validates_currency_length()
    {
        $response = $this->actingAs($this->centralUser, 'central')
            ->postJson(route('central.tenants.store-with-progress'), [
                'company_name' => 'Test Company',
                'subdomain' => 'testcompany',
                'domain' => 'midastech.testing.in',
                'email' => 'test@example.com',
                'plan_id' => $this->plan->id,
                'admin_first_name' => 'John',
                'admin_last_name' => 'Doe',
                'admin_email' => 'admin@example.com',
                'currency' => 'INVALID', // Must be 3 chars
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['currency']);
    }

    protected function tearDown(): void
    {
        // Clean up cache
        Cache::store('file')->flush();

        parent::tearDown();
    }
}
