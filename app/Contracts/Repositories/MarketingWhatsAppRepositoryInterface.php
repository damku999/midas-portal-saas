<?php

namespace App\Contracts\Repositories;

use App\Models\MarketingWhatsApp;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Marketing WhatsApp Repository Interface
 *
 * Defines data access operations for MarketingWhatsApp entity.
 * Handles WhatsApp marketing message storage, retrieval, and filtering.
 */
interface MarketingWhatsAppRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get paginated marketing WhatsApp messages with filters
     */
    public function getMarketingMessagesWithFilters(Request $request, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get marketing messages by status
     */
    public function getMarketingMessagesByStatus(bool $status): Collection;

    /**
     * Get marketing messages by type
     */
    public function getMarketingMessagesByType(string $type): Collection;

    /**
     * Get marketing messages sent today
     */
    public function getTodayMarketingMessages(): Collection;

    /**
     * Get marketing messages by date range
     */
    public function getMarketingMessagesByDateRange(string $startDate, string $endDate): Collection;

    /**
     * Get marketing message statistics
     */
    public function getMarketingMessageStatistics(): array;

    /**
     * Search marketing messages by content
     */
    public function searchMarketingMessages(string $searchTerm, int $limit = 20): Collection;

    /**
     * Get marketing messages by phone number
     */
    public function getMarketingMessagesByPhoneNumber(string $phoneNumber): Collection;

    /**
     * Get failed marketing messages for retry
     */
    public function getFailedMarketingMessages(): Collection;

    /**
     * Get all marketing messages for export
     */
    public function getAllMarketingMessagesForExport(): Collection;

    /**
     * Mark message as sent
     */
    public function markMessageAsSent(MarketingWhatsApp $marketingMessage, string $messageId): bool;

    /**
     * Mark message as failed
     */
    public function markMessageAsFailed(MarketingWhatsApp $marketingMessage, string $errorMessage): bool;
}
