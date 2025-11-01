<?php

namespace App\Repositories;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

/**
 * Customer Repository
 *
 * Extends base repository functionality for Customer-specific operations.
 * Common CRUD operations are inherited from AbstractBaseRepository.
 */
class CustomerRepository extends AbstractBaseRepository implements CustomerRepositoryInterface
{
    protected string $modelClass = Customer::class;

    protected array $searchableFields = ['name', 'email', 'mobile_number'];

    /**
     * Get all customers with optional filters.
     * Refactored: Added eager loading to prevent N+1 queries
     */
    public function getAll(array $filters = []): Collection
    {
        $query = Customer::with(['familyGroup', 'insurance']);

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['from_date']) && ! empty($filters['to_date'])) {
            $query->whereBetween('created_at', [$filters['from_date'], $filters['to_date']]);
        }

        return $query->get();
    }

    /**
     * Override base getPaginated to support advanced filtering
     * Refactored: Added eager loading to prevent N+1 queries
     */
    public function getPaginated(Request $request, int $perPage = 10): LengthAwarePaginator
    {
        $query = Customer::with(['familyGroup', 'insurance']);
        $filters = $request->all();

        // Search filter
        if (! empty($filters['search'])) {
            $searchTerm = '%'.trim($filters['search']).'%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', $searchTerm)
                    ->orWhere('email', 'LIKE', $searchTerm)
                    ->orWhere('mobile_number', 'LIKE', $searchTerm);
            });
        }

        // Type filter
        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Status filter
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Date range filter
        if (! empty($filters['from_date']) && ! empty($filters['to_date'])) {
            $query->whereBetween('created_at', [$filters['from_date'], $filters['to_date']]);
        }

        // Sorting
        $sortField = $filters['sort_field'] ?? 'name';
        $sortOrder = $filters['sort_order'] ?? 'asc';
        $query->orderBy($sortField, $sortOrder);

        return $query->paginate($perPage);
    }

    public function findByEmail(string $email): ?Customer
    {
        return Customer::where('email', $email)->first();
    }

    public function findByMobileNumber(string $mobileNumber): ?Customer
    {
        return Customer::where('mobile_number', $mobileNumber)->first();
    }

    /**
     * Override base update method to match interface signature
     */
    public function update(\Illuminate\Database\Eloquent\Model $entity, array $data): \Illuminate\Database\Eloquent\Model
    {
        return parent::update($entity, $data);
    }

    /**
     * Override base delete method to match interface signature
     */
    public function delete(\Illuminate\Database\Eloquent\Model $entity): bool
    {
        return parent::delete($entity);
    }

    public function getByFamilyGroup(int $familyGroupId): Collection
    {
        // Refactored: Added eager loading to prevent N+1 queries
        return Customer::with(['familyGroup', 'insurance'])
            ->where('family_group_id', $familyGroupId)
            ->get();
    }

    public function getByType(string $type): Collection
    {
        // Refactored: Added eager loading to prevent N+1 queries
        return Customer::with(['familyGroup', 'insurance'])
            ->where('type', $type)
            ->get();
    }

    public function search(string $query): Collection
    {
        $searchTerm = '%'.trim($query).'%';

        // Refactored: Added eager loading to prevent N+1 queries
        return Customer::with(['familyGroup', 'insurance'])
            ->where('name', 'LIKE', $searchTerm)
            ->orWhere('email', 'LIKE', $searchTerm)
            ->orWhere('mobile_number', 'LIKE', $searchTerm)
            ->get();
    }

    public function exists(int $id): bool
    {
        return Customer::where('id', $id)->exists();
    }

    public function count(): int
    {
        return Customer::count();
    }

    /**
     * Get active customers
     */
    public function getActiveCustomers(): Collection
    {
        return Customer::select('id', 'name', 'mobile_number', 'email', 'status')
            ->where('status', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get customers with valid mobile numbers
     */
    public function getCustomersWithValidMobileNumbers(): Collection
    {
        return Customer::where('status', true)
            ->whereNotNull('mobile_number')
            ->where('mobile_number', '!=', '')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get customers by array of IDs
     */
    public function getCustomersByIds(array $ids): Collection
    {
        return Customer::whereIn('id', $ids)
            ->orderBy('name')
            ->get();
    }
}
