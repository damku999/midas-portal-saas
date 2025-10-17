<?php

namespace App\Contracts\Services;

use Illuminate\Database\Eloquent\Collection;

/**
 * Marketing WhatsApp Service Interface
 *
 * Defines business logic operations for WhatsApp marketing.
 * Handles customer selection, message sending, and campaign management.
 */
interface MarketingWhatsAppServiceInterface
{
    /**
     * Get active customers for WhatsApp marketing
     */
    public function getActiveCustomers(): Collection;

    /**
     * Get customers with valid mobile numbers for marketing
     *
     * @param  string  $recipients  ('all' or 'selected')
     */
    public function getValidCustomersForMarketing(string $recipients, array $selectedCustomerIds = []): Collection;

    /**
     * Send WhatsApp marketing campaign to customers
     *
     * @return array Campaign results
     */
    public function sendMarketingCampaign(array $campaignData): array;

    /**
     * Preview customer list for marketing campaign
     */
    public function previewCustomerList(string $recipients, array $selectedCustomerIds = []): array;

    /**
     * Send text message to customer
     */
    public function sendTextMessage(string $message, string $mobileNumber, int $customerId): bool;

    /**
     * Send image message to customer
     */
    public function sendImageMessage(string $message, string $mobileNumber, string $imagePath, int $customerId): bool;

    /**
     * Get marketing campaign statistics
     */
    public function getMarketingStatistics(): array;

    /**
     * Log marketing message attempt
     */
    public function logMarketingAttempt(int $customerId, string $messageType, bool $success, ?string $error = null): void;
}
