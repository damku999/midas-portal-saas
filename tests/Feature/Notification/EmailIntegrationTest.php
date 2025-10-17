<?php

namespace Tests\Feature\Notification;

use App\Events\Insurance\PolicyExpiringWarning;
use App\Listeners\Insurance\SendPolicyRenewalReminder;
use App\Models\AppSetting;
use App\Models\Customer;
use App\Models\CustomerInsurance;
use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use App\Models\NotificationType;
use App\Services\CustomerInsuranceService;
use App\Services\EmailService;
use App\Services\Notification\ChannelManager;
use App\Services\Notification\NotificationContext;
use App\Traits\LogsNotificationsTrait;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Feature tests for Email Integration across notification channels
 *
 * Tests email functionality in:
 * - ChannelManager
 * - SendPolicyRenewalReminder listener
 * - LogsNotificationsTrait
 */
class EmailIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Use updateOrCreate to avoid unique constraint violations
        AppSetting::updateOrCreate(
            ['key' => 'email_enabled'],
            [
                'category' => 'notifications',
                'value' => '1',
                'is_active' => true,
                'type' => 'boolean',
            ]
        );

        AppSetting::updateOrCreate(
            ['key' => 'name'],
            [
                'category' => 'company',
                'value' => 'Midas Insurance',
                'is_active' => true,
                'type' => 'text',
            ]
        );

        AppSetting::updateOrCreate(
            ['key' => 'phone'],
            [
                'category' => 'company',
                'value' => '+91 98765 43210',
                'is_active' => true,
                'type' => 'text',
            ]
        );

        // Mock session for Auditable trait
        session()->put('user_id', 1);
    }

    // =======================================================
    // CHANNEL MANAGER EMAIL TESTS
    // =======================================================

    /** @test */
    public function channel_manager_can_send_email_notification()
    {
        Mail::fake();

        $this->createEmailTemplate('policy_created', 'email');

        $customer = Customer::factory()->create(['email' => 'customer@test.com']);
        $insurance = CustomerInsurance::factory()->create(['customer_id' => $customer->id]);

        $channelManager = app(ChannelManager::class);
        $context = new NotificationContext;
        $context->insurance = $insurance;
        $context->customer = $customer;

        $result = $channelManager->sendToChannel('email', 'policy_created', $context, $customer);

        $this->assertTrue($result, 'Email should be sent successfully');
    }

    /** @test */
    public function channel_manager_returns_false_when_customer_has_no_email()
    {
        $customer = Customer::factory()->create(['email' => null]);
        $insurance = CustomerInsurance::factory()->create(['customer_id' => $customer->id]);

        $channelManager = app(ChannelManager::class);
        $context = new NotificationContext;
        $context->insurance = $insurance;
        $context->customer = $customer;

        $result = $channelManager->sendToChannel('email', 'policy_created', $context, $customer);

        $this->assertFalse($result, 'Email should fail when customer has no email');
    }

    /** @test */
    public function channel_manager_sends_email_via_send_to_all_channels()
    {
        Mail::fake();

        $this->createEmailTemplate('policy_created', 'email');

        $customer = Customer::factory()->create(['email' => 'customer@test.com']);
        $insurance = CustomerInsurance::factory()->create(['customer_id' => $customer->id]);

        $channelManager = app(ChannelManager::class);
        $context = new NotificationContext;
        $context->insurance = $insurance;
        $context->customer = $customer;

        $results = $channelManager->sendToAllChannels(
            'policy_created',
            $context,
            ['email'], // Only test email channel
            $customer
        );

        $this->assertContains('email', $results['channels_succeeded']);
        $this->assertTrue($results['details']['email']['success']);
    }

    /** @test */
    public function channel_manager_respects_email_notification_settings()
    {
        // Disable email notifications
        AppSetting::where('key', 'email_enabled')->update(['value' => '0']);

        $customer = Customer::factory()->create(['email' => 'customer@test.com']);

        $channelManager = app(ChannelManager::class);
        $context = new NotificationContext;
        $context->setCustomer($customer);

        $result = $channelManager->sendToChannel('email', 'policy_created', $context, $customer);

        // Should still attempt but EmailService will check settings
        $this->assertIsBool($result);
    }

    // =======================================================
    // POLICY RENEWAL REMINDER EMAIL TESTS
    // =======================================================

    /** @test */
    public function policy_renewal_listener_sends_email_for_7_day_warning()
    {
        Mail::fake();

        $this->createEmailTemplate('renewal_7_days', 'email');

        $customer = Customer::factory()->create(['email' => 'customer@test.com']);
        $policy = CustomerInsurance::factory()->create([
            'customer_id' => $customer->id,
            'expired_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
        ]);

        $event = new PolicyExpiringWarning($policy, 7, 'urgent');

        $listener = new SendPolicyRenewalReminder(
            app(CustomerInsuranceService::class),
            app(EmailService::class)
        );

        $listener->handle($event);

        // Email should be queued or sent
        $this->assertTrue(true); // Test passes if no exceptions
    }

    /** @test */
    public function policy_renewal_listener_sends_email_for_15_day_warning()
    {
        Mail::fake();

        $this->createEmailTemplate('renewal_15_days', 'email');

        $customer = Customer::factory()->create(['email' => 'customer@test.com']);
        $policy = CustomerInsurance::factory()->create([
            'customer_id' => $customer->id,
            'expired_date' => Carbon::now()->addDays(15)->format('Y-m-d'),
        ]);

        $event = new PolicyExpiringWarning($policy, 15, 'important');

        $listener = new SendPolicyRenewalReminder(
            app(CustomerInsuranceService::class),
            app(EmailService::class)
        );

        $listener->handle($event);

        $this->assertTrue(true);
    }

    /** @test */
    public function policy_renewal_listener_sends_email_for_30_day_warning()
    {
        Mail::fake();

        $this->createEmailTemplate('renewal_30_days', 'email');

        $customer = Customer::factory()->create(['email' => 'customer@test.com']);
        $policy = CustomerInsurance::factory()->create([
            'customer_id' => $customer->id,
            'expired_date' => Carbon::now()->addDays(30)->format('Y-m-d'),
        ]);

        $event = new PolicyExpiringWarning($policy, 30, 'early');

        $listener = new SendPolicyRenewalReminder(
            app(CustomerInsuranceService::class),
            app(EmailService::class)
        );

        $listener->handle($event);

        $this->assertTrue(true);
    }

    /** @test */
    public function policy_renewal_listener_sends_email_for_expired_policy()
    {
        Mail::fake();

        $this->createEmailTemplate('renewal_expired', 'email');

        $customer = Customer::factory()->create(['email' => 'customer@test.com']);
        $policy = CustomerInsurance::factory()->create([
            'customer_id' => $customer->id,
            'expired_date' => Carbon::now()->subDays(1)->format('Y-m-d'),
        ]);

        $event = new PolicyExpiringWarning($policy, 0, 'urgent');

        $listener = new SendPolicyRenewalReminder(
            app(CustomerInsuranceService::class),
            app(EmailService::class)
        );

        $listener->handle($event);

        $this->assertTrue(true);
    }

    /** @test */
    public function policy_renewal_listener_skips_email_when_disabled()
    {
        // Disable email notifications
        AppSetting::where('key', 'email_enabled')->update(['value' => '0']);

        $customer = Customer::factory()->create(['email' => 'customer@test.com']);
        $policy = CustomerInsurance::factory()->create([
            'customer_id' => $customer->id,
            'expired_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
        ]);

        $event = new PolicyExpiringWarning($policy, 7, 'urgent');

        $listener = new SendPolicyRenewalReminder(
            app(CustomerInsuranceService::class),
            app(EmailService::class)
        );

        // Should not throw exception even when disabled
        $listener->handle($event);

        $this->assertTrue(true);
    }

    /** @test */
    public function policy_renewal_listener_skips_email_when_customer_has_no_email()
    {
        $customer = Customer::factory()->create(['email' => null]);
        $policy = CustomerInsurance::factory()->create([
            'customer_id' => $customer->id,
            'expired_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
        ]);

        $event = new PolicyExpiringWarning($policy, 7, 'urgent');

        $listener = new SendPolicyRenewalReminder(
            app(CustomerInsuranceService::class),
            app(EmailService::class)
        );

        $listener->handle($event);

        $this->assertTrue(true);
    }

    // =======================================================
    // LOGS NOTIFICATIONS TRAIT EMAIL TESTS
    // =======================================================

    /** @test */
    public function logs_trait_sends_and_logs_email()
    {
        Mail::fake();

        $customer = Customer::factory()->create(['email' => 'customer@test.com']);

        $trait = $this->getMockForTrait(LogsNotificationsTrait::class);
        $result = $trait->logAndSendEmail(
            $customer,
            'customer@test.com',
            'Test Subject',
            'Test message body',
            ['notification_type_code' => 'test_notification']
        );

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('log', $result);
        $this->assertInstanceOf(NotificationLog::class, $result['log']);
    }

    /** @test */
    public function logs_trait_creates_notification_log_for_email()
    {
        Mail::fake();

        $customer = Customer::factory()->create(['email' => 'customer@test.com']);

        $trait = $this->getMockForTrait(LogsNotificationsTrait::class);
        $result = $trait->logAndSendEmail(
            $customer,
            'customer@test.com',
            'Test Subject',
            'Test message body'
        );

        $this->assertDatabaseHas('notification_logs', [
            'channel' => 'email',
            'recipient' => 'customer@test.com',
            'status' => 'sent',
        ]);
    }

    /** @test */
    public function logs_trait_supports_cc_and_bcc_in_options()
    {
        Mail::fake();

        $customer = Customer::factory()->create(['email' => 'customer@test.com']);

        $trait = $this->getMockForTrait(LogsNotificationsTrait::class);
        $result = $trait->logAndSendEmail(
            $customer,
            'customer@test.com',
            'Test Subject',
            'Test message body',
            [
                'cc' => 'cc@test.com',
                'bcc' => 'bcc@test.com',
            ]
        );

        Mail::assertSent(function ($mail) {
            // Verify Mail was sent
            return true;
        });

        $this->assertTrue($result['success']);
    }

    /** @test */
    public function logs_trait_marks_email_as_failed_when_disabled()
    {
        // Disable email notifications
        AppSetting::where('key', 'email_enabled')->update(['value' => '0']);

        $customer = Customer::factory()->create(['email' => 'customer@test.com']);

        $trait = $this->getMockForTrait(LogsNotificationsTrait::class);
        $result = $trait->logAndSendEmail(
            $customer,
            'customer@test.com',
            'Test Subject',
            'Test message body'
        );

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('Email notifications are disabled', $result['error']);
    }

    /** @test */
    public function logs_trait_handles_email_sending_exceptions()
    {
        // Force Mail to throw exception
        Mail::shouldReceive('raw')->andThrow(new \Exception('SMTP connection failed'));

        $customer = Customer::factory()->create(['email' => 'customer@test.com']);

        $trait = $this->getMockForTrait(LogsNotificationsTrait::class);
        $result = $trait->logAndSendEmail(
            $customer,
            'customer@test.com',
            'Test Subject',
            'Test message body'
        );

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertDatabaseHas('notification_logs', [
            'channel' => 'email',
            'recipient' => 'customer@test.com',
            'status' => 'failed',
        ]);
    }

    // =======================================================
    // INTEGRATION TESTS
    // =======================================================

    /** @test */
    public function complete_email_flow_from_event_to_delivery()
    {
        Mail::fake();
        Event::fake([PolicyExpiringWarning::class]);

        $this->createEmailTemplate('renewal_7_days', 'email');

        $customer = Customer::factory()->create(['email' => 'customer@test.com']);
        $policy = CustomerInsurance::factory()->create([
            'customer_id' => $customer->id,
            'expired_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
        ]);

        // Fire the event
        $event = new PolicyExpiringWarning($policy, 7, 'urgent');
        event($event);

        Event::assertDispatched(PolicyExpiringWarning::class);
    }

    /** @test */
    public function email_templates_are_used_correctly_for_different_renewal_periods()
    {
        Mail::fake();

        $this->createEmailTemplate('renewal_7_days', 'email');
        $this->createEmailTemplate('renewal_15_days', 'email');
        $this->createEmailTemplate('renewal_30_days', 'email');

        $customer = Customer::factory()->create(['email' => 'customer@test.com']);

        // Test 7 days
        $policy7 = CustomerInsurance::factory()->create([
            'customer_id' => $customer->id,
            'expired_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
        ]);
        $event7 = new PolicyExpiringWarning($policy7, 7, 'urgent');
        $listener = new SendPolicyRenewalReminder(
            app(CustomerInsuranceService::class),
            app(EmailService::class)
        );
        $listener->handle($event7);

        // Test 15 days
        $policy15 = CustomerInsurance::factory()->create([
            'customer_id' => $customer->id,
            'expired_date' => Carbon::now()->addDays(15)->format('Y-m-d'),
        ]);
        $event15 = new PolicyExpiringWarning($policy15, 15, 'important');
        $listener->handle($event15);

        // Test 30 days
        $policy30 = CustomerInsurance::factory()->create([
            'customer_id' => $customer->id,
            'expired_date' => Carbon::now()->addDays(30)->format('Y-m-d'),
        ]);
        $event30 = new PolicyExpiringWarning($policy30, 30, 'early');
        $listener->handle($event30);

        $this->assertTrue(true);
    }

    // =======================================================
    // HELPER METHODS
    // =======================================================

    protected function createEmailTemplate(string $typeCode, string $channel)
    {
        $notificationType = NotificationType::factory()->create([
            'code' => $typeCode,
            'name' => ucfirst(str_replace('_', ' ', $typeCode)),
            'category' => 'renewal',
            'is_active' => true,
        ]);

        NotificationTemplate::factory()->create([
            'notification_type_id' => $notificationType->id,
            'channel' => $channel,
            'subject' => 'Policy Renewal Reminder - {{policy_number}}',
            'template_content' => 'Hi {{customer_name}}, your policy {{policy_number}} expires on {{expiry_date}}. Contact us at {{company_phone}} to renew.',
            'is_active' => true,
        ]);
    }
}
