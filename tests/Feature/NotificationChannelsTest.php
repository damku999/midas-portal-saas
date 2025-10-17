<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerDevice;
use App\Models\CustomerInsurance;
use App\Models\NotificationLog;
use App\Services\Notification\ChannelManager;
use App\Services\Notification\NotificationContext;
use App\Services\PushNotificationService;
use App\Services\SmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationChannelsTest extends TestCase
{
    use RefreshDatabase;

    protected Customer $customer;

    protected NotificationContext $context;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test customer
        $this->customer = Customer::factory()->create([
            'mobile' => '9876543210',
            'email' => 'test@example.com',
            'notification_preferences' => [
                'channels' => ['whatsapp', 'email', 'sms', 'push'],
            ],
        ]);

        // Create test insurance
        $insurance = CustomerInsurance::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        // Create test context
        $this->context = NotificationContext::fromInsuranceId($insurance->id);
        $this->context->settings = $this->loadTestSettings();
    }

    /** @test */
    public function it_can_send_sms_notification()
    {
        $smsService = app(SmsService::class);

        // Note: This will use mock/test credentials
        $success = $smsService->sendPlainSms(
            to: $this->customer->mobile,
            message: 'Test SMS notification',
            customerId: $this->customer->id
        );

        // Check notification log was created
        $this->assertDatabaseHas('notification_logs', [
            'customer_id' => $this->customer->id,
            'channel' => 'sms',
        ]);

        $log = NotificationLog::latest()->first();
        $this->assertEquals('sms', $log->channel);
        $this->assertNotNull($log->message_content);
    }

    /** @test */
    public function it_truncates_long_sms_messages()
    {
        $smsService = app(SmsService::class);

        $longMessage = str_repeat('This is a very long message. ', 20); // >160 chars

        $success = $smsService->sendPlainSms(
            to: $this->customer->mobile,
            message: $longMessage,
            customerId: $this->customer->id
        );

        $log = NotificationLog::where('channel', 'sms')
            ->where('customer_id', $this->customer->id)
            ->latest()
            ->first();

        $this->assertLessThanOrEqual(160, strlen($log->message_content));
        $this->assertStringEndsWith('...', $log->message_content);
    }

    /** @test */
    public function it_can_register_device_for_push_notifications()
    {
        $pushService = app(PushNotificationService::class);

        $device = $pushService->registerDevice(
            customerId: $this->customer->id,
            deviceToken: 'test_fcm_token_123',
            deviceType: 'android',
            deviceInfo: [
                'device_name' => 'Test Device',
                'device_model' => 'Test Model',
                'os_version' => 'Android 13',
                'app_version' => '1.0.0',
            ]
        );

        $this->assertDatabaseHas('customer_devices', [
            'customer_id' => $this->customer->id,
            'device_token' => 'test_fcm_token_123',
            'device_type' => 'android',
            'is_active' => true,
        ]);

        $this->assertTrue($device->is_active);
        $this->assertEquals('Test Device', $device->device_name);
    }

    /** @test */
    public function it_updates_existing_device_token()
    {
        $pushService = app(PushNotificationService::class);

        // Register device first time
        $device1 = $pushService->registerDevice(
            customerId: $this->customer->id,
            deviceToken: 'test_token',
            deviceType: 'android'
        );

        // Register same token again (different customer or updated info)
        $device2 = $pushService->registerDevice(
            customerId: $this->customer->id,
            deviceToken: 'test_token',
            deviceType: 'ios', // Changed type
            deviceInfo: ['device_name' => 'Updated Device']
        );

        // Should update, not create new
        $this->assertEquals($device1->id, $device2->id);
        $this->assertEquals('ios', $device2->device_type);
        $this->assertEquals('Updated Device', $device2->device_name);

        // Only one device with this token
        $count = CustomerDevice::where('device_token', 'test_token')->count();
        $this->assertEquals(1, $count);
    }

    /** @test */
    public function it_can_get_customer_active_devices()
    {
        $pushService = app(PushNotificationService::class);

        // Register multiple devices
        $pushService->registerDevice($this->customer->id, 'token1', 'android');
        $pushService->registerDevice($this->customer->id, 'token2', 'ios');

        // Deactivate one
        CustomerDevice::where('device_token', 'token2')->update(['is_active' => false]);

        $devices = $pushService->getCustomerDevices($this->customer->id);

        $this->assertCount(1, $devices);
        $this->assertEquals('token1', $devices->first()->device_token);
    }

    /** @test */
    public function it_respects_customer_channel_preferences()
    {
        // Customer only wants WhatsApp and Email
        $this->customer->update([
            'notification_preferences' => [
                'channels' => ['whatsapp', 'email'],
            ],
        ]);

        $channelManager = app(ChannelManager::class);

        // Get available channels
        $reflection = new \ReflectionClass($channelManager);
        $method = $reflection->getMethod('filterChannelsByPreferences');
        $method->setAccessible(true);

        $filtered = $method->invoke(
            $channelManager,
            $this->customer,
            ['push', 'whatsapp', 'sms', 'email'],
            'test_notification'
        );

        // Should only include whatsapp and email
        $this->assertContains('whatsapp', $filtered);
        $this->assertContains('email', $filtered);
        $this->assertNotContains('push', $filtered);
        $this->assertNotContains('sms', $filtered);
    }

    /** @test */
    public function it_respects_quiet_hours()
    {
        // Set quiet hours
        $this->customer->update([
            'notification_preferences' => [
                'channels' => ['push', 'whatsapp', 'sms', 'email'],
                'quiet_hours' => [
                    'start' => '00:00',
                    'end' => '23:59', // All day for testing
                ],
            ],
        ]);

        $channelManager = app(ChannelManager::class);

        $reflection = new \ReflectionClass($channelManager);
        $method = $reflection->getMethod('filterChannelsByPreferences');
        $method->setAccessible(true);

        $filtered = $method->invoke(
            $channelManager,
            $this->customer,
            ['push', 'whatsapp', 'sms', 'email'],
            'test_notification'
        );

        // During quiet hours, only push and email allowed
        $this->assertContains('push', $filtered);
        $this->assertContains('email', $filtered);
        $this->assertNotContains('whatsapp', $filtered);
        $this->assertNotContains('sms', $filtered);
    }

    /** @test */
    public function it_respects_opt_out_types()
    {
        // Customer opted out of birthday wishes
        $this->customer->update([
            'notification_preferences' => [
                'channels' => ['whatsapp', 'sms'],
                'opt_out_types' => ['birthday_wish'],
            ],
        ]);

        $channelManager = app(ChannelManager::class);

        $reflection = new \ReflectionClass($channelManager);
        $method = $reflection->getMethod('filterChannelsByPreferences');
        $method->setAccessible(true);

        $filtered = $method->invoke(
            $channelManager,
            $this->customer,
            ['whatsapp', 'sms'],
            'birthday_wish'
        );

        // Should return empty array (opted out)
        $this->assertEmpty($filtered);
    }

    /** @test */
    public function it_logs_notification_attempts()
    {
        $smsService = app(SmsService::class);

        // Send SMS (will fail in test environment without credentials)
        $smsService->sendPlainSms(
            to: $this->customer->mobile,
            message: 'Test message',
            customerId: $this->customer->id
        );

        // Check log was created
        $this->assertDatabaseHas('notification_logs', [
            'customer_id' => $this->customer->id,
            'channel' => 'sms',
        ]);

        $log = NotificationLog::latest()->first();
        $this->assertNotNull($log);
        $this->assertEquals('sms', $log->channel);
        $this->assertContains($log->status, ['pending', 'sent', 'failed']);
    }

    /** @test */
    public function it_can_mark_notification_as_sent()
    {
        $log = NotificationLog::create([
            'customer_id' => $this->customer->id,
            'channel' => 'sms',
            'recipient' => '919876543210',
            'message_content' => 'Test',
            'status' => 'pending',
        ]);

        $log->markAsSent(['message_id' => 'SM123']);

        $this->assertEquals('sent', $log->fresh()->status);
        $this->assertNotNull($log->fresh()->sent_at);
        $this->assertEquals('SM123', $log->fresh()->metadata['message_id']);
    }

    /** @test */
    public function it_can_mark_notification_as_failed()
    {
        $log = NotificationLog::create([
            'customer_id' => $this->customer->id,
            'channel' => 'sms',
            'recipient' => '919876543210',
            'message_content' => 'Test',
            'status' => 'pending',
        ]);

        $log->markAsFailed('Invalid phone number');

        $this->assertEquals('failed', $log->fresh()->status);
        $this->assertNotNull($log->fresh()->failed_at);
        $this->assertEquals('Invalid phone number', $log->fresh()->error_message);
        $this->assertEquals(1, $log->fresh()->retry_count);
    }

    /** @test */
    public function it_validates_mobile_number_format()
    {
        $smsService = app(SmsService::class);

        // Invalid number
        $result = $smsService->sendPlainSms(
            to: 'invalid',
            message: 'Test',
            customerId: $this->customer->id
        );

        $this->assertFalse($result);

        $log = NotificationLog::latest()->first();
        $this->assertEquals('failed', $log->status);
        $this->assertStringContainsString('Invalid', $log->error_message);
    }

    /**
     * Helper method to load test settings
     */
    protected function loadTestSettings(): array
    {
        return [
            'company' => [
                'name' => 'Test Insurance Company',
                'advisor_name' => 'Test Advisor',
                'phone' => '1234567890',
                'email' => 'test@company.com',
            ],
        ];
    }
}
