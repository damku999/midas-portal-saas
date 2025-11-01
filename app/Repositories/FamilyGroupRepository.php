<?php

namespace App\Repositories;

use App\Contracts\Repositories\FamilyGroupRepositoryInterface;
use App\Models\Customer;
use App\Models\FamilyGroup;
use App\Models\FamilyMember;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Family Group Repository
 *
 * Handles FamilyGroup data access operations.
 * Inherits common CRUD operations from AbstractBaseRepository.
 */
class FamilyGroupRepository extends AbstractBaseRepository implements FamilyGroupRepositoryInterface
{
    /**
     * The model class name
     */
    protected string $modelClass = FamilyGroup::class;

    /**
     * Searchable fields for the getPaginated method
     */
    protected array $searchableFields = [
        'name',
    ];

    /**
     * Get paginated list of family groups with filtering and search
     */
    public function getFamilyGroupsWithFilters(Request $request, int $perPage = 15): LengthAwarePaginator
    {
        $query = FamilyGroup::with(['familyHead', 'familyMembers.customer']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('familyHead', function ($subQ) use ($search) {
                        $subQ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get family group with all relationships loaded
     */
    public function getFamilyGroupWithMembers(int $familyGroupId): ?FamilyGroup
    {
        return FamilyGroup::with(['familyHead', 'familyMembers.customer'])
            ->find($familyGroupId);
    }

    /**
     * Get family groups by family head
     */
    public function getFamilyGroupsByHead(int $familyHeadId): Collection
    {
        return FamilyGroup::with(['familyMembers.customer'])
            ->where('family_head_id', $familyHeadId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get family groups by customer (either as head or member)
     */
    public function getFamilyGroupsByCustomer(int $customerId): Collection
    {
        return FamilyGroup::with(['familyHead', 'familyMembers.customer'])
            ->where('family_head_id', $customerId)
            ->orWhereHas('familyMembers', function ($query) use ($customerId) {
                $query->where('customer_id', $customerId);
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get available customers for family group creation
     * (customers not in any family group)
     */
    public function getAvailableCustomers(): Collection
    {
        return Customer::where('status', true)
            ->whereNull('family_group_id')
            ->whereDoesntHave('familyMember') // Ensure no family_members record exists
            ->orderBy('name')
            ->get();
    }

    /**
     * Get available customers for family group editing
     * (customers not in other family groups)
     */
    public function getAvailableCustomersForEdit(int $familyGroupId): Collection
    {
        return Customer::where('status', true)
            ->where(function ($query) use ($familyGroupId) {
                $query->whereNull('family_group_id')
                    ->orWhere('family_group_id', $familyGroupId);
            })
            ->whereDoesntHave('familyMember', function ($query) use ($familyGroupId) {
                $query->where('family_group_id', '!=', $familyGroupId);
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * Check if customer is already in a family group
     */
    public function isCustomerInFamilyGroup(int $customerId): bool
    {
        return Customer::where('id', $customerId)
            ->whereNotNull('family_group_id')
            ->exists() ||
            FamilyMember::where('customer_id', $customerId)->exists();
    }

    /**
     * Get family members for a specific family group
     */
    public function getFamilyMembers(int $familyGroupId): Collection
    {
        return FamilyMember::with(['customer'])
            ->where('family_group_id', $familyGroupId)
            ->orderBy('is_head', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Update family head for a family group
     */
    public function updateFamilyHead(int $familyGroupId, int $newFamilyHeadId): bool
    {
        // Update the family group record
        $familyGroup = FamilyGroup::find($familyGroupId);
        if (! $familyGroup) {
            return false;
        }

        // Update old head in customers table
        if ($familyGroup->family_head_id) {
            Customer::where('id', $familyGroup->family_head_id)
                ->update(['family_group_id' => null]);
        }

        // Update new head in customers table
        Customer::where('id', $newFamilyHeadId)
            ->update(['family_group_id' => $familyGroupId]);

        // Update family group
        $familyGroup->update(['family_head_id' => $newFamilyHeadId]);

        // Update family members table
        FamilyMember::where('family_group_id', $familyGroupId)
            ->update(['is_head' => false]);

        FamilyMember::where('family_group_id', $familyGroupId)
            ->where('customer_id', $newFamilyHeadId)
            ->update(['is_head' => true, 'relationship' => 'head']);

        return true;
    }

    /**
     * Remove customer from family group
     */
    public function removeCustomerFromFamilyGroup(int $customerId): bool
    {
        // Update customer record
        Customer::where('id', $customerId)
            ->update(['family_group_id' => null]);

        // Remove from family members
        return FamilyMember::where('customer_id', $customerId)->delete() > 0;
    }

    /**
     * Get family group statistics
     * Refactored: Simplified average family size calculation
     */
    public function getFamilyGroupStatistics(): array
    {
        $totalFamilyGroups = FamilyGroup::count();
        $totalFamilyMembers = FamilyMember::count();
        $averageFamilySize = $totalFamilyGroups > 0 ? $totalFamilyMembers / $totalFamilyGroups : 0;

        return [
            'total_family_groups' => $totalFamilyGroups,
            'active_family_groups' => FamilyGroup::where('status', true)->count(),
            'inactive_family_groups' => FamilyGroup::where('status', false)->count(),
            'total_family_members' => $totalFamilyMembers,
            'average_family_size' => round($averageFamilySize, 2),
            'this_month_groups' => FamilyGroup::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];
    }

    /**
     * Search family groups by name or family head
     */
    public function searchFamilyGroups(string $searchTerm, int $limit = 20): Collection
    {
        if (strlen($searchTerm) < 2) {
            return collect();
        }

        return FamilyGroup::with(['familyHead', 'familyMembers.customer'])
            ->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', "%{$searchTerm}%")
                    ->orWhereHas('familyHead', function ($subQuery) use ($searchTerm) {
                        $subQuery->where('name', 'like', "%{$searchTerm}%")
                            ->orWhere('email', 'like', "%{$searchTerm}%");
                    });
            })
            ->limit($limit)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get all family groups with relationships for export
     */
    public function getAllFamilyGroupsWithRelationships(): Collection
    {
        return FamilyGroup::with(['familyHead', 'familyMembers.customer'])
            ->orderBy('name')
            ->get();
    }
}
