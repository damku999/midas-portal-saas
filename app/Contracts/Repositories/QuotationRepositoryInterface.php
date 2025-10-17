<?php

namespace App\Contracts\Repositories;

use App\Models\Quotation;
use Illuminate\Database\Eloquent\Collection;

/**
 * Quotation Repository Interface
 *
 * Extends base repository functionality for Quotation-specific operations.
 * Common CRUD operations are inherited from BaseRepositoryInterface.
 */
interface QuotationRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get all quotations with optional filters.
     */
    public function getAll(array $filters = []): Collection;

    /**
     * Get quotations by customer ID.
     */
    public function getByCustomer(int $customerId): Collection;

    /**
     * Get quotations by status.
     */
    public function getByStatus(string $status): Collection;

    /**
     * Get recent quotations.
     */
    public function getRecent(int $limit = 10): Collection;

    /**
     * Search quotations by vehicle number or customer details.
     */
    public function search(string $query): Collection;

    /**
     * Get quotations sent via WhatsApp.
     */
    public function getSentQuotations(): Collection;

    /**
     * Get quotations pending to be sent.
     */
    public function getPendingQuotations(): Collection;

    /**
     * Get quotation count by status.
     */
    public function getCountByStatus(): array;

    /**
     * Check if quotation exists.
     */
    public function exists(int $id): bool;
}
