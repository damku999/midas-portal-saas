<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Central\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CustomerEmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create and initialize tenant context for testing
        $tenant = Tenant::factory()->create();
        tenancy()->initialize($tenant);
    }

    /**
     * Test that resend verification redirects to verify-email-notice page (not infinite loop)
     */
    public function test_resend_verification_redirects_to_notice_page(): void
    {
        Mail::fake();

        // Create unverified customer
        $customer = Customer::factory()->create([
            'email_verified_at' => null,
            'email_verification_token' => 'test-token-123',
        ]);

        // Act as customer (login)
        $this->actingAs($customer, 'customer');

        // Submit resend verification request
        $response = $this->post(route('customer.verification.send'));

        // Assert: Should redirect to verify-email-notice (NOT back to change-password)
        $response->assertRedirect(route('customer.verify-email-notice'));
        $response->assertSessionHas('success', 'Verification link sent to your email.');
    }

    /**
     * Test that already verified email redirects to dashboard
     */
    public function test_already_verified_email_redirects_to_dashboard(): void
    {
        Mail::fake();

        // Create verified customer
        $customer = Customer::factory()->create([
            'email_verified_at' => now(),
            'email_verification_token' => null,
        ]);

        $this->actingAs($customer, 'customer');

        // Try to resend verification
        $response = $this->post(route('customer.verification.send'));

        // Should redirect to dashboard since already verified
        $response->assertRedirect(route('customer.dashboard'));
    }

    /**
     * Test email verification with valid token
     */
    public function test_email_verification_with_valid_token_succeeds(): void
    {
        $customer = Customer::factory()->create([
            'email_verified_at' => null,
            'email_verification_token' => 'valid-token-456',
        ]);

        // Verify email with token
        $response = $this->get(route('customer.verify-email', ['token' => 'valid-token-456']));

        // Should redirect to dashboard after successful verification
        $response->assertRedirect(route('customer.dashboard'));
        $response->assertSessionHas('success', 'Email verified successfully.');

        // Verify customer is now marked as verified
        $customer->refresh();
        $this->assertNotNull($customer->email_verified_at);
        $this->assertNull($customer->email_verification_token);
    }

    /**
     * Test email verification with invalid token fails
     */
    public function test_email_verification_with_invalid_token_fails(): void
    {
        // Try to verify with non-existent token
        $response = $this->get(route('customer.verify-email', ['token' => 'invalid-token']));

        // Should redirect to login with error
        $response->assertRedirect(route('customer.login'));
        $response->assertSessionHas('error', 'Invalid verification link.');
    }

    /**
     * Test that unverified customers see verification notice after login
     */
    public function test_unverified_customer_sees_verification_notice_after_login(): void
    {
        $customer = Customer::factory()->create([
            'email' => 'unverified@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => null,
            'email_verification_token' => 'token-789',
            'status' => true,
        ]);

        // Attempt login
        $response = $this->post(route('customer.login'), [
            'email' => 'unverified@example.com',
            'password' => 'password123',
            'cf-turnstile-response' => 'test-turnstile-response',
        ]);

        // Should redirect to email verification notice
        $response->assertRedirect(route('customer.verify-email-notice'));
        $response->assertSessionHas('info', 'Please verify your email address to continue.');
    }

    /**
     * Test that verification routes are excluded from subscription middleware
     */
    public function test_verification_routes_excluded_from_subscription_middleware(): void
    {
        $middleware = new \App\Http\Middleware\CheckSubscriptionStatus();
        $reflection = new \ReflectionClass($middleware);
        $property = $reflection->getProperty('except');
        $property->setAccessible(true);
        $exceptRoutes = $property->getValue($middleware);

        // Assert verification routes are in the exception list
        $this->assertContains('customer.verify-email', $exceptRoutes);
        $this->assertContains('customer.verify-email-notice', $exceptRoutes);
        $this->assertContains('customer.resend-verification', $exceptRoutes);
        $this->assertContains('customer.verification.send', $exceptRoutes);
    }
}
