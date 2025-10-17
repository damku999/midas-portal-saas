<?php

namespace App\Contracts\Services;

use App\Models\FamilyGroup;
use App\Models\FamilyMember;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Family Group Service Interface
 *
 * Defines business logic operations for FamilyGroup management.
 * Includes complex family relationship management, password setup, and notifications.
 */
interface FamilyGroupServiceInterface
{
    /**
     * Get paginated list of family groups with filters
     */
    public function getFamilyGroups(Request $request): LengthAwarePaginator;

    /**
     * Get family group with all relationships loaded
     */
    public function getFamilyGroupWithMembers(int $familyGroupId): ?FamilyGroup;

    /**
     * Create a new family group with family head and members
     */
    public function createFamilyGroup(array $data): FamilyGroup;

    /**
     * Update an existing family group
     */
    public function updateFamilyGroup(FamilyGroup $familyGroup, array $data): bool;

    /**
     * Delete a family group and handle member cleanup
     */
    public function deleteFamilyGroup(FamilyGroup $familyGroup): bool;

    /**
     * Update family group status
     */
    public function updateFamilyGroupStatus(int $familyGroupId, bool $status): bool;

    /**
     * Add a new member to family group
     */
    public function addFamilyMember(int $familyGroupId, array $memberData): FamilyMember;

    /**
     * Remove a member from family group
     */
    public function removeFamilyMember(int $familyGroupId, int $memberId): bool;

    /**
     * Remove a specific family member by FamilyMember object
     */
    public function removeFamilyMemberByObject(FamilyMember $familyMember): bool;

    /**
     * Update family member relationship
     */
    public function updateFamilyMember(int $familyMemberId, array $data): bool;

    /**
     * Change family head
     */
    public function changeFamilyHead(int $familyGroupId, int $newFamilyHeadId): bool;

    /**
     * Setup passwords for family members
     */
    public function setupMemberPasswords(array $memberIds, bool $forceChange = true): array;

    /**
     * Send password notifications to family members
     */
    public function sendPasswordNotifications(array $passwordNotifications, FamilyGroup $familyGroup): bool;

    /**
     * Get available customers for family group
     */
    public function getAvailableCustomers(?int $familyGroupId = null): \Illuminate\Database\Eloquent\Collection;

    /**
     * Cleanup orphaned family member records
     *
     * @return int Number of records cleaned up
     */
    public function cleanupOrphanedRecords(): int;

    /**
     * Get family group statistics
     */
    public function getFamilyGroupStatistics(): array;

    /**
     * Get all family groups for export
     */
    public function getAllFamilyGroupsForExport(): \Illuminate\Database\Eloquent\Collection;
}
