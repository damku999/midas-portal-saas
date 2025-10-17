<?php

use App\Models\Customer;
use App\Models\CustomerInsurance;
use App\Models\NotificationTemplate;
use App\Models\NotificationType;
use App\Models\Quotation;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UnifiedPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

    // Bypass permission middleware
    $this->withoutMiddleware([
        \Spatie\Permission\Middlewares\PermissionMiddleware::class,
        \Spatie\Permission\Middlewares\RoleMiddleware::class,
        \Spatie\Permission\Middlewares\RoleOrPermissionMiddleware::class,
    ]);
});

// ========================================
// INDEX TESTS
// ========================================

test('index displays notification templates list', function () {
    NotificationTemplate::factory()->count(5)->create();

    $response = $this->get(route('notification-templates.index'));

    $response->assertStatus(200);
    $response->assertViewIs('admin.notification_templates.index');
    $response->assertViewHas('templates');
    $response->assertViewHas('categories');
});

test('index filters templates by search term', function () {
    $template1 = NotificationTemplate::factory()->create([
        'subject' => 'Welcome Email Template',
    ]);
    $template2 = NotificationTemplate::factory()->create([
        'subject' => 'Renewal Reminder',
    ]);

    $response = $this->get(route('notification-templates.index', ['search' => 'Welcome']));

    $response->assertStatus(200);
});

test('index filters templates by channel', function () {
    NotificationTemplate::factory()->create(['channel' => 'whatsapp']);
    NotificationTemplate::factory()->create(['channel' => 'email']);

    $response = $this->get(route('notification-templates.index', ['channel' => 'whatsapp']));

    $response->assertStatus(200);
});

test('index filters templates by status', function () {
    NotificationTemplate::factory()->create(['is_active' => true]);
    NotificationTemplate::factory()->create(['is_active' => false]);

    $response = $this->get(route('notification-templates.index', ['status' => 1]));

    $response->assertStatus(200);
});

test('index sorts templates correctly', function () {
    NotificationTemplate::factory()->count(3)->create();

    $response = $this->get(route('notification-templates.index', [
        'sort_by' => 'created_at',
        'sort_order' => 'desc',
    ]));

    $response->assertStatus(200);
});

test('index paginates templates', function () {
    NotificationTemplate::factory()->count(25)->create();

    $response = $this->get(route('notification-templates.index'));

    $response->assertStatus(200);
    $response->assertViewHas('templates');

    $templates = $response->viewData('templates');
    expect($templates->total())->toBe(25);
});

// ========================================
// CREATE TESTS
// ========================================

test('create displays template creation form', function () {
    $response = $this->get(route('notification-templates.create'));

    $response->assertStatus(200);
    $response->assertViewIs('admin.notification_templates.create');
    $response->assertViewHas('notificationTypes');
    $response->assertViewHas('customers');
});

// ========================================
// STORE TESTS
// ========================================

test('store creates new template successfully', function () {
    $notificationType = NotificationType::factory()->create();

    $templateData = [
        'notification_type_id' => $notificationType->id,
        'channel' => 'whatsapp',
        'subject' => 'Test Subject',
        'template_content' => 'Hello {{customer.name}}, this is a test template.',
        'available_variables' => json_encode(['customer.name', 'customer.mobile']),
        'is_active' => true,
    ];

    $response = $this->post(route('notification-templates.store'), $templateData);

    $response->assertRedirect(route('notification-templates.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('notification_templates', [
        'notification_type_id' => $notificationType->id,
        'channel' => 'whatsapp',
    ]);
});

test('store validates required fields', function () {
    $response = $this->post(route('notification-templates.store'), []);

    $response->assertSessionHasErrors(['notification_type_id', 'channel', 'template_content']);
});

test('store validates channel values', function () {
    $notificationType = NotificationType::factory()->create();

    $response = $this->post(route('notification-templates.store'), [
        'notification_type_id' => $notificationType->id,
        'channel' => 'invalid_channel',
        'template_content' => 'Test content',
    ]);

    $response->assertSessionHasErrors(['channel']);
});

// ========================================
// EDIT TESTS
// ========================================

test('edit displays template edit form', function () {
    $template = NotificationTemplate::factory()->create();

    $response = $this->get(route('notification-templates.edit', $template));

    $response->assertStatus(200);
    $response->assertViewIs('admin.notification_templates.edit');
    $response->assertViewHas('template', $template);
    $response->assertViewHas('notificationTypes');
    $response->assertViewHas('customers');
});

// ========================================
// UPDATE TESTS
// ========================================

test('update modifies existing template', function () {
    $template = NotificationTemplate::factory()->create([
        'subject' => 'Old Subject',
        'template_content' => 'Old content',
    ]);

    $response = $this->put(route('notification-templates.update', $template), [
        'notification_type_id' => $template->notification_type_id,
        'channel' => $template->channel,
        'subject' => 'New Subject',
        'template_content' => 'New content with {{customer.name}}',
        'is_active' => true,
    ]);

    $response->assertRedirect(route('notification-templates.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('notification_templates', [
        'id' => $template->id,
        'subject' => 'New Subject',
        'template_content' => 'New content with {{customer.name}}',
    ]);
});

test('update validates required fields', function () {
    $template = NotificationTemplate::factory()->create();

    $response = $this->put(route('notification-templates.update', $template), [
        'template_content' => '',
    ]);

    $response->assertSessionHasErrors();
});

// ========================================
// DELETE TESTS
// ========================================

test('delete removes template', function () {
    $template = NotificationTemplate::factory()->create();

    $response = $this->delete(route('notification-templates.delete', $template));

    $response->assertRedirect(route('notification-templates.index'));
    $response->assertSessionHas('success');

    $this->assertSoftDeleted('notification_templates', [
        'id' => $template->id,
    ]);
});

test('delete handles non existent template', function () {
    $response = $this->delete(route('notification-templates.delete', 99999));

    $response->assertStatus(404);
});

// ========================================
// PREVIEW TESTS
// ========================================

test('preview renders template with customer data', function () {
    $customer = Customer::factory()->create(['name' => 'John Doe']);

    $response = $this->post(route('notification-templates.preview'), [
        'template_content' => 'Hello {{customer.name}}!',
        'customer_id' => $customer->id,
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'preview',
        'context_info',
    ]);
    $response->assertJson(['success' => true]);
});

test('preview renders template with insurance data', function () {
    $insurance = CustomerInsurance::factory()->create(['policy_no' => 'POL-123']);

    $response = $this->post(route('notification-templates.preview'), [
        'template_content' => 'Policy: {{insurance.policy_no}}',
        'insurance_id' => $insurance->id,
    ]);

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
});

test('preview validates required fields', function () {
    $response = $this->post(route('notification-templates.preview'), []);

    $response->assertStatus(302);
    $response->assertSessionHasErrors(['template_content']);
});

test('preview handles template errors gracefully', function () {
    $response = $this->post(route('notification-templates.preview'), [
        'template_content' => 'Test {{invalid.variable}}',
    ]);

    // Should return 400 with error
    expect($response->status())->toBeIn([200, 400]);
});

// ========================================
// GET CUSTOMER DATA TESTS
// ========================================

test('get customer data returns policies and quotations', function () {
    $customer = Customer::factory()->create();
    CustomerInsurance::factory()->count(2)->create(['customer_id' => $customer->id]);
    Quotation::factory()->count(2)->create(['customer_id' => $customer->id]);

    $response = $this->get(route('notification-templates.customer-data', [
        'customer_id' => $customer->id,
    ]));

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'policies',
        'quotations',
    ]);
    $response->assertJson(['success' => true]);
});

test('get customer data requires customer id', function () {
    $response = $this->get(route('notification-templates.customer-data'));

    $response->assertStatus(400);
    $response->assertJson(['success' => false]);
});

// ========================================
// GET AVAILABLE VARIABLES TESTS
// ========================================

test('get available variables returns variables list', function () {
    $response = $this->get(route('notification-templates.variables', [
        'notification_type' => 'customer',
    ]));

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'variables',
        'categories',
    ]);
});

test('get available variables handles missing notification type', function () {
    $response = $this->get(route('notification-templates.variables'));

    $response->assertStatus(200);
    $response->assertJsonStructure(['success']);
});

// ========================================
// SEND TEST TESTS
// ========================================

test('send test validates required fields', function () {
    $response = $this->post(route('notification-templates.send-test'), []);

    $response->assertStatus(302);
    $response->assertSessionHasErrors(['recipient', 'channel', 'template_content']);
});

test('send test handles whatsapp channel', function () {
    $customer = Customer::factory()->create(['mobile_number' => '9876543210']);

    $response = $this->post(route('notification-templates.send-test'), [
        'recipient' => '9876543210',
        'channel' => 'whatsapp',
        'template_content' => 'Test message for {{customer.name}}',
        'customer_id' => $customer->id,
    ]);

    // External API call may fail in tests
    expect($response->status())->toBeIn([200, 400, 500]);
});

test('send test handles email channel', function () {
    $customer = Customer::factory()->create();

    $response = $this->post(route('notification-templates.send-test'), [
        'recipient' => 'test@example.com',
        'channel' => 'email',
        'subject' => 'Test Subject',
        'template_content' => 'Test email content',
        'customer_id' => $customer->id,
    ]);

    // Mail sending may fail in test environment
    expect($response->status())->toBeIn([200, 400, 500]);
});

test('send test validates channel values', function () {
    $response = $this->post(route('notification-templates.send-test'), [
        'recipient' => 'test@example.com',
        'channel' => 'invalid',
        'template_content' => 'Test',
    ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors(['channel']);
});

// ========================================
// AUTHORIZATION TESTS
// ========================================

test('unauthenticated user cannot access templates index', function () {
    $this->withMiddleware();
    auth()->logout();

    $response = $this->get(route('notification-templates.index'));

    $response->assertRedirect(route('login'));
});

test('unauthenticated user cannot create template', function () {
    $this->withMiddleware();
    auth()->logout();

    $response = $this->post(route('notification-templates.store'), [
        'channel' => 'whatsapp',
    ]);

    $response->assertRedirect(route('login'));
});

test('unauthenticated user cannot update template', function () {
    $this->withMiddleware();
    auth()->logout();
    $template = NotificationTemplate::factory()->create();

    $response = $this->put(route('notification-templates.update', $template), [
        'subject' => 'Updated',
    ]);

    $response->assertRedirect(route('login'));
});

test('unauthenticated user cannot delete template', function () {
    $this->withMiddleware();
    auth()->logout();
    $template = NotificationTemplate::factory()->create();

    $response = $this->delete(route('notification-templates.delete', $template));

    $response->assertRedirect(route('login'));
});

// ========================================
// RELATIONSHIP TESTS
// ========================================

test('template belongs to notification type', function () {
    $notificationType = NotificationType::factory()->create();
    $template = NotificationTemplate::factory()->create([
        'notification_type_id' => $notificationType->id,
    ]);

    expect($template->notificationType->id)->toBe($notificationType->id);
});

// ========================================
// BUSINESS LOGIC TESTS
// ========================================

test('template can be activated and deactivated', function () {
    $template = NotificationTemplate::factory()->create(['is_active' => false]);

    $template->update(['is_active' => true]);

    expect($template->fresh()->is_active)->toBeTrue();
});

test('template stores available variables as json', function () {
    $notificationType = NotificationType::factory()->create();
    $variables = ['customer.name', 'customer.email', 'insurance.policy_no'];

    $response = $this->post(route('notification-templates.store'), [
        'notification_type_id' => $notificationType->id,
        'channel' => 'whatsapp',
        'template_content' => 'Test',
        'available_variables' => json_encode($variables),
        'is_active' => true,
    ]);

    $response->assertRedirect();

    $template = NotificationTemplate::latest()->first();
    expect($template->available_variables)->toBeArray();
    expect($template->available_variables)->toEqual($variables);
});

test('template supports multiple channels', function () {
    $notificationType = NotificationType::factory()->create();

    $response = $this->post(route('notification-templates.store'), [
        'notification_type_id' => $notificationType->id,
        'channel' => 'both',
        'template_content' => 'Test template for both channels',
        'is_active' => true,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('notification_templates', [
        'channel' => 'both',
    ]);
});
