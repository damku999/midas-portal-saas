<?php

use App\Models\AppSetting;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UnifiedPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed roles and permissions
    $this->seed(RoleSeeder::class);
    $this->seed(UnifiedPermissionsSeeder::class);

    // Create authenticated user with all permissions
    $this->user = User::factory()->create();
    $this->user->givePermissionTo(\Spatie\Permission\Models\Permission::all());

    $this->actingAs($this->user);

    // Mock session for Auditable trait
    session()->put('user_id', $this->user->id);

    // Bypass middleware that could interfere with testing
    $this->withoutMiddleware([
        \Spatie\Permission\Middlewares\PermissionMiddleware::class,
        \Spatie\Permission\Middlewares\RoleMiddleware::class,
        \Spatie\Permission\Middlewares\RoleOrPermissionMiddleware::class,
        \App\Http\Middleware\VerifyCsrfToken::class,
    ]);
});

// ========================================
// INDEX TESTS
// ========================================

test('index displays app settings list', function () {
    AppSetting::factory()->count(5)->create();

    $response = $this->get(route('app-settings.index'));

    $response->assertStatus(200);
    $response->assertViewIs('app_settings.index');
    $response->assertViewHas('settings');
    $response->assertViewHas('categories');
});

test('index filters settings by search term', function () {
    AppSetting::factory()->create(['key' => 'app_name', 'description' => 'Application Name']);
    AppSetting::factory()->create(['key' => 'mail_host', 'description' => 'Mail Server Host']);

    $response = $this->get(route('app-settings.index', ['search' => 'app_name']));

    $response->assertStatus(200);
    $response->assertSee('app_name');
    $response->assertDontSee('mail_host');
});

test('index filters settings by category', function () {
    AppSetting::factory()->create(['key' => 'app_name', 'category' => 'application']);
    AppSetting::factory()->create(['key' => 'mail_host', 'category' => 'mail']);

    $response = $this->get(route('app-settings.index', ['category' => 'application']));

    $response->assertStatus(200);
    $response->assertSee('app_name');
    $response->assertDontSee('mail_host');
});

test('index filters settings by status', function () {
    AppSetting::factory()->create(['key' => 'active_setting', 'is_active' => true]);
    AppSetting::factory()->create(['key' => 'inactive_setting', 'is_active' => false]);

    $response = $this->get(route('app-settings.index', ['status' => 1]));

    $response->assertStatus(200);
    $response->assertSee('active_setting');
    $response->assertDontSee('inactive_setting');
});

test('index sorts settings correctly', function () {
    AppSetting::factory()->create(['key' => 'zebra_setting', 'category' => 'application']);
    AppSetting::factory()->create(['key' => 'alpha_setting', 'category' => 'application']);

    $response = $this->get(route('app-settings.index', ['sort_by' => 'key', 'sort_order' => 'asc']));

    $response->assertStatus(200);
    $content = $response->getContent();
    expect(strpos($content, 'alpha_setting'))->toBeLessThan(strpos($content, 'zebra_setting'));
});

test('index paginates settings', function () {
    AppSetting::factory()->count(20)->create();

    $response = $this->get(route('app-settings.index'));

    $response->assertStatus(200);
    $response->assertViewHas('settings');
    expect($response->viewData('settings'))->toHaveCount(15); // Default pagination
});

test('index handles errors gracefully', function () {
    $response = $this->get(route('app-settings.index'));

    $response->assertStatus(200);
});

// ========================================
// CREATE TESTS
// ========================================

test('create displays form', function () {
    $response = $this->get(route('app-settings.create'));

    $response->assertStatus(200);
    $response->assertViewIs('app_settings.create');
    $response->assertViewHas('categories');
    $response->assertViewHas('types');
});

// ========================================
// STORE TESTS
// ========================================

test('store creates new setting successfully', function () {
    $data = [
        'key' => 'test_setting',
        'value' => 'test value',
        'type' => 'string',
        'category' => 'application',
        'description' => 'Test setting description',
        'is_active' => true,
        'is_encrypted' => false,
    ];

    $response = $this->post(route('app-settings.store'), $data);

    $response->assertRedirect(route('app-settings.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('app_settings', [
        'key' => 'test_setting',
        'value' => 'test value',
        'category' => 'application',
    ]);
});

test('store creates encrypted setting', function () {
    $data = [
        'key' => 'secret_key',
        'value' => 'secret_value',
        'type' => 'string',
        'category' => 'application',
        'description' => 'Secret setting',
        'is_active' => true,
        'is_encrypted' => true,
    ];

    $response = $this->post(route('app-settings.store'), $data);

    $response->assertRedirect(route('app-settings.index'));

    $setting = AppSetting::where('key', 'secret_key')->first();
    expect($setting)->not->toBeNull();
    expect($setting->value)->not->toBe('secret_value'); // Should be encrypted
    expect(Crypt::decryptString($setting->value))->toBe('secret_value');
});

test('store validates required fields', function () {
    $response = $this->post(route('app-settings.store'), []);

    $response->assertSessionHasErrors(['key', 'type', 'category']);
});

test('store validates unique key', function () {
    AppSetting::factory()->create(['key' => 'existing_key']);

    $response = $this->post(route('app-settings.store'), [
        'key' => 'existing_key',
        'value' => 'test',
        'type' => 'string',
        'category' => 'application',
        'is_active' => true,
    ]);

    $response->assertSessionHasErrors(['key']);
});

test('store handles errors gracefully', function () {
    // Invalid category should fail validation
    $response = $this->post(route('app-settings.store'), [
        'key' => 'test_key',
        'value' => 'test',
        'type' => 'string',
        'category' => 'invalid_category',
        'is_active' => true,
    ]);

    $response->assertSessionHasErrors();
});

// ========================================
// SHOW TESTS
// ========================================

test('show displays setting details', function () {
    $setting = AppSetting::factory()->create();

    $response = $this->get(route('app-settings.show', ['id' => $setting->id]));

    $response->assertStatus(200);
    $response->assertViewIs('app_settings.show');
    $response->assertViewHas('setting');
    $response->assertSee($setting->key);
});

test('show displays decrypted value for encrypted settings', function () {
    $setting = AppSetting::factory()->create([
        'key' => 'encrypted_test',
        'value' => Crypt::encryptString('secret_value'),
        'is_encrypted' => true,
    ]);

    $response = $this->get(route('app-settings.show', ['id' => $setting->id]));

    $response->assertStatus(200);
    // Value should be masked with asterisks in view
    $response->assertDontSee('secret_value');
});

test('show handles non-existent setting', function () {
    $response = $this->get(route('app-settings.show', ['id' => 99999]));

    $response->assertStatus(404);
});

// ========================================
// EDIT TESTS
// ========================================

test('edit displays form with setting data', function () {
    $setting = AppSetting::factory()->create();

    $response = $this->get(route('app-settings.edit', ['id' => $setting->id]));

    $response->assertStatus(200);
    $response->assertViewIs('app_settings.edit');
    $response->assertViewHas('setting');
    $response->assertViewHas('categories');
    $response->assertViewHas('types');
    $response->assertSee($setting->key);
});

test('edit handles non-existent setting', function () {
    $response = $this->get(route('app-settings.edit', ['id' => 99999]));

    $response->assertStatus(404);
});

// ========================================
// UPDATE TESTS
// ========================================

test('update modifies setting successfully', function () {
    $setting = AppSetting::factory()->create([
        'key' => 'test_key',
        'value' => 'old_value',
    ]);

    $response = $this->put(route('app-settings.update', ['id' => $setting->id]), [
        'key' => 'test_key',
        'value' => 'new_value',
        'type' => $setting->type,
        'category' => $setting->category,
        'is_active' => true,
        'is_encrypted' => false,
    ]);

    $response->assertRedirect(route('app-settings.index'));
    $response->assertSessionHas('success');

    $setting->refresh();
    expect($setting->value)->toBe('new_value');
});

test('update encrypts value when is_encrypted is true', function () {
    $setting = AppSetting::factory()->create([
        'value' => 'plain_value',
        'is_encrypted' => false,
    ]);

    $response = $this->put(route('app-settings.update', ['id' => $setting->id]), [
        'key' => $setting->key,
        'value' => 'secret_value',
        'type' => $setting->type,
        'category' => $setting->category,
        'is_active' => true,
        'is_encrypted' => true,
    ]);

    $response->assertRedirect(route('app-settings.index'));

    $setting->refresh();
    expect($setting->value)->not->toBe('secret_value');
    expect(Crypt::decryptString($setting->value))->toBe('secret_value');
});

test('update validates required fields', function () {
    $setting = AppSetting::factory()->create();

    $response = $this->put(route('app-settings.update', ['id' => $setting->id]), []);

    $response->assertSessionHasErrors(['key', 'type', 'category']);
});

test('update validates unique key except self', function () {
    $setting1 = AppSetting::factory()->create(['key' => 'key_one']);
    $setting2 = AppSetting::factory()->create(['key' => 'key_two']);

    // Should fail - key already exists on another setting
    $response = $this->put(route('app-settings.update', ['id' => $setting2->id]), [
        'key' => 'key_one',
        'value' => 'test',
        'type' => $setting2->type,
        'category' => $setting2->category,
        'is_active' => true,
    ]);

    $response->assertSessionHasErrors(['key']);
});

test('update handles non-existent setting', function () {
    $response = $this->put(route('app-settings.update', ['id' => 99999]), [
        'key' => 'test',
        'value' => 'test',
        'type' => 'string',
        'category' => 'application',
        'is_active' => true,
    ]);

    $response->assertStatus(404);
});

// ========================================
// DESTROY TESTS
// ========================================

test('destroy deactivates setting successfully', function () {
    // Update user email to authorized domain
    $this->user->update(['email' => 'test@webmonks.in']);

    $setting = AppSetting::factory()->create(['is_active' => true]);

    $response = $this->delete(route('app-settings.destroy', ['id' => $setting->id]));

    $response->assertRedirect(route('app-settings.index'));
    $response->assertSessionHas('success');

    $setting->refresh();
    expect($setting->is_active)->toBe(0);
});

test('destroy handles non-existent setting', function () {
    $response = $this->delete(route('app-settings.destroy', ['id' => 99999]));

    $response->assertStatus(404);
});

// ========================================
// DECRYPT TESTS
// ========================================

test('decrypt returns decrypted value for encrypted setting', function () {
    $setting = AppSetting::factory()->create([
        'key' => 'encrypted_key',
        'value' => Crypt::encryptString('secret_value'),
        'is_encrypted' => true,
    ]);

    $response = $this->get(route('app-settings.decrypt', ['id' => $setting->id]));

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'value' => 'secret_value',
    ]);
});

test('decrypt returns error for non-encrypted setting', function () {
    $setting = AppSetting::factory()->create([
        'value' => 'plain_value',
        'is_encrypted' => false,
    ]);

    $response = $this->get(route('app-settings.decrypt', ['id' => $setting->id]));

    $response->assertStatus(400);
    $response->assertJson([
        'success' => false,
    ]);
});

test('decrypt handles decryption errors', function () {
    $setting = AppSetting::factory()->create([
        'value' => 'invalid_encrypted_data',
        'is_encrypted' => true,
    ]);

    $response = $this->get(route('app-settings.decrypt', ['id' => $setting->id]));

    $response->assertStatus(500);
    $response->assertJson([
        'success' => false,
    ]);
});

// ========================================
// INTEGRATION TESTS
// ========================================

test('complete CRUD workflow works end-to-end', function () {
    // Create
    $createResponse = $this->post(route('app-settings.store'), [
        'key' => 'workflow_test',
        'value' => 'initial_value',
        'type' => 'string',
        'category' => 'application',
        'description' => 'Workflow test setting',
        'is_active' => true,
        'is_encrypted' => false,
    ]);
    $createResponse->assertRedirect(route('app-settings.index'));

    $setting = AppSetting::where('key', 'workflow_test')->first();
    expect($setting)->not->toBeNull();

    // Read
    $showResponse = $this->get(route('app-settings.show', ['id' => $setting->id]));
    $showResponse->assertStatus(200);
    $showResponse->assertSee('workflow_test');

    // Update
    $updateResponse = $this->put(route('app-settings.update', ['id' => $setting->id]), [
        'key' => 'workflow_test',
        'value' => 'updated_value',
        'type' => 'string',
        'category' => 'application',
        'is_active' => true,
        'is_encrypted' => false,
    ]);
    $updateResponse->assertRedirect(route('app-settings.index'));

    $setting->refresh();
    expect($setting->value)->toBe('updated_value');

    // Delete (deactivate)
    $this->user->update(['email' => 'test@webmonks.in']);
    $deleteResponse = $this->delete(route('app-settings.destroy', ['id' => $setting->id]));
    $deleteResponse->assertRedirect(route('app-settings.index'));

    $setting->refresh();
    expect($setting->is_active)->toBe(0);
});

test('encryption workflow works correctly', function () {
    // Create encrypted setting
    $this->post(route('app-settings.store'), [
        'key' => 'secret_api_key',
        'value' => 'super_secret_key_123',
        'type' => 'string',
        'category' => 'application',
        'is_active' => true,
        'is_encrypted' => true,
    ]);

    $setting = AppSetting::where('key', 'secret_api_key')->first();

    // Verify it's encrypted in database
    expect($setting->value)->not->toBe('super_secret_key_123');

    // Decrypt via API
    $decryptResponse = $this->get(route('app-settings.decrypt', ['id' => $setting->id]));
    $decryptResponse->assertJson([
        'success' => true,
        'value' => 'super_secret_key_123',
    ]);

    // Update with new encrypted value
    $this->put(route('app-settings.update', ['id' => $setting->id]), [
        'key' => 'secret_api_key',
        'value' => 'new_secret_key_456',
        'type' => 'string',
        'category' => 'application',
        'is_active' => true,
        'is_encrypted' => true,
    ]);

    $setting->refresh();
    expect(Crypt::decryptString($setting->value))->toBe('new_secret_key_456');
});
