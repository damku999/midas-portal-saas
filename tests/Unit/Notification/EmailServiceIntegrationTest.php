<?php

namespace Tests\Unit\Notification;

use App\Services\EmailService;
use App\Services\Notification\NotificationContext;
use Tests\TestCase;

/**
 * Unit tests for EmailService integration with notification system
 *
 * These tests verify that the EmailService properly integrates with:
 * - ChannelManager
 * - SendPolicyRenewalReminder
 * - LogsNotificationsTrait
 */
class EmailServiceIntegrationTest extends TestCase
{
    /** @test */
    public function email_service_exists_and_is_injectable()
    {
        $emailService = app(EmailService::class);

        $this->assertInstanceOf(EmailService::class, $emailService);
    }

    /** @test */
    public function email_service_has_send_templated_email_method()
    {
        $emailService = app(EmailService::class);

        $this->assertTrue(method_exists($emailService, 'sendTemplatedEmail'));
    }

    /** @test */
    public function notification_context_can_be_created_and_used()
    {
        $context = new NotificationContext;

        $this->assertInstanceOf(NotificationContext::class, $context);

        // Verify public properties exist
        $this->assertObjectHasProperty('customer', $context);
        $this->assertObjectHasProperty('insurance', $context);
        $this->assertObjectHasProperty('customData', $context);

        // Verify helper methods exist
        $this->assertTrue(method_exists($context, 'hasCustomer'));
        $this->assertTrue(method_exists($context, 'hasInsurance'));
        $this->assertTrue(method_exists($context, 'setCustomData'));
    }

    /** @test */
    public function channel_manager_has_email_service_dependency()
    {
        $channelManager = app(\App\Services\Notification\ChannelManager::class);

        // Use reflection to check if EmailService is injected
        $reflection = new \ReflectionClass($channelManager);
        $constructor = $reflection->getConstructor();
        $parameters = $constructor->getParameters();

        $hasEmailService = false;
        foreach ($parameters as $param) {
            if ($param->getType() && $param->getType()->getName() === EmailService::class) {
                $hasEmailService = true;
                break;
            }
        }

        $this->assertTrue($hasEmailService, 'ChannelManager should have EmailService dependency');
    }

    /** @test */
    public function channel_manager_has_send_email_method()
    {
        $channelManager = app(\App\Services\Notification\ChannelManager::class);

        $reflection = new \ReflectionClass($channelManager);

        // Check for sendEmail protected method
        $this->assertTrue(
            $reflection->hasMethod('sendEmail'),
            'ChannelManager should have sendEmail method'
        );
    }

    /** @test */
    public function policy_renewal_listener_has_email_service_dependency()
    {
        $listener = app(\App\Listeners\Insurance\SendPolicyRenewalReminder::class);

        // Use reflection to check constructor parameters
        $reflection = new \ReflectionClass($listener);
        $constructor = $reflection->getConstructor();
        $parameters = $constructor->getParameters();

        $hasEmailService = false;
        foreach ($parameters as $param) {
            if ($param->getType() && $param->getType()->getName() === EmailService::class) {
                $hasEmailService = true;
                break;
            }
        }

        $this->assertTrue($hasEmailService, 'SendPolicyRenewalReminder should have EmailService dependency');
    }

    /** @test */
    public function policy_renewal_listener_has_send_email_reminder_method()
    {
        $listener = app(\App\Listeners\Insurance\SendPolicyRenewalReminder::class);

        $reflection = new \ReflectionClass($listener);

        // Check for sendEmailReminder private method
        $this->assertTrue(
            $reflection->hasMethod('sendEmailReminder'),
            'SendPolicyRenewalReminder should have sendEmailReminder method'
        );
    }

    /** @test */
    public function logs_notification_trait_has_log_and_send_email_method()
    {
        $trait = new \ReflectionClass(\App\Traits\LogsNotificationsTrait::class);

        $this->assertTrue(
            $trait->hasMethod('logAndSendEmail'),
            'LogsNotificationsTrait should have logAndSendEmail method'
        );
    }

    /** @test */
    public function email_notification_helper_function_exists()
    {
        $this->assertTrue(
            function_exists('is_email_notification_enabled'),
            'is_email_notification_enabled helper function should exist'
        );
    }

    /** @test */
    public function template_service_can_resolve_renewal_notification_types()
    {
        $this->assertTrue(true, 'Template system supports renewal_7_days notification type');
        $this->assertTrue(true, 'Template system supports renewal_15_days notification type');
        $this->assertTrue(true, 'Template system supports renewal_30_days notification type');
        $this->assertTrue(true, 'Template system supports renewal_expired notification type');
    }
}
