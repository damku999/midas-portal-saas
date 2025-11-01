<?php

namespace App\Repositories;

use App\Models\Lead;
use App\Repositories\Contracts\LeadRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class LeadRepository implements LeadRepositoryInterface
{
    protected Lead $model;

    public function __construct(Lead $model)
    {
        $this->model = $model;
    }

    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['source', 'status', 'assignedUser']);

        if (!empty($filters['status_id'])) {
            $query->where('status_id', $filters['status_id']);
        }

        if (!empty($filters['source_id'])) {
            $query->where('source_id', $filters['source_id']);
        }

        if (!empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('email', 'like', "%{$filters['search']}%")
                    ->orWhere('mobile_number', 'like', "%{$filters['search']}%")
                    ->orWhere('lead_number', 'like', "%{$filters['search']}%");
            });
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    public function findById(int $id): ?Lead
    {
        return $this->model->find($id);
    }

    public function findByLeadNumber(string $leadNumber): ?Lead
    {
        return $this->model->where('lead_number', $leadNumber)->first();
    }

    public function create(array $data): Lead
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Lead
    {
        $lead = $this->findById($id);

        if (!$lead) {
            throw new \Exception("Lead not found with ID: {$id}");
        }

        $lead->update($data);
        return $lead->fresh();
    }

    public function delete(int $id): bool
    {
        $lead = $this->findById($id);

        if (!$lead) {
            throw new \Exception("Lead not found with ID: {$id}");
        }

        return $lead->delete();
    }

    public function restore(int $id): bool
    {
        $lead = $this->model->withTrashed()->find($id);

        if (!$lead) {
            throw new \Exception("Lead not found with ID: {$id}");
        }

        return $lead->restore();
    }

    public function forceDelete(int $id): bool
    {
        $lead = $this->model->withTrashed()->find($id);

        if (!$lead) {
            throw new \Exception("Lead not found with ID: {$id}");
        }

        return $lead->forceDelete();
    }

    public function getByStatus(int $statusId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(['source', 'status', 'assignedUser'])
            ->byStatus($statusId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getBySource(int $sourceId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(['source', 'status', 'assignedUser'])
            ->bySource($sourceId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getAssignedTo(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(['source', 'status', 'assignedUser'])
            ->assignedTo($userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getActive(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(['source', 'status', 'assignedUser'])
            ->active()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getConverted(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(['source', 'status', 'assignedUser', 'convertedCustomer'])
            ->converted()
            ->orderBy('converted_at', 'desc')
            ->paginate($perPage);
    }

    public function getLost(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(['source', 'status', 'assignedUser'])
            ->lost()
            ->orderBy('lost_at', 'desc')
            ->paginate($perPage);
    }

    public function getFollowUpDue(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(['source', 'status', 'assignedUser'])
            ->followUpDue()
            ->orderBy('next_follow_up_date', 'asc')
            ->paginate($perPage);
    }

    public function getFollowUpOverdue(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(['source', 'status', 'assignedUser'])
            ->followUpOverdue()
            ->orderBy('next_follow_up_date', 'asc')
            ->paginate($perPage);
    }

    public function search(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(['source', 'status', 'assignedUser'])
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhere('mobile_number', 'like', "%{$query}%")
                    ->orWhere('lead_number', 'like', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function updateStatus(int $id, int $statusId, ?string $notes = null): Lead
    {
        $lead = $this->findById($id);

        if (!$lead) {
            throw new \Exception("Lead not found with ID: {$id}");
        }

        $lead->update([
            'status_id' => $statusId,
        ]);

        if ($notes) {
            $lead->update(['remarks' => $notes]);
        }

        return $lead->fresh();
    }

    public function assignTo(int $id, int $userId): Lead
    {
        $lead = $this->findById($id);

        if (!$lead) {
            throw new \Exception("Lead not found with ID: {$id}");
        }

        $lead->update([
            'assigned_to' => $userId,
        ]);

        return $lead->fresh();
    }

    public function convertToCustomer(int $id, int $customerId, ?string $notes = null): Lead
    {
        $lead = $this->findById($id);

        if (!$lead) {
            throw new \Exception("Lead not found with ID: {$id}");
        }

        $lead->update([
            'converted_customer_id' => $customerId,
            'converted_at' => now(),
            'conversion_notes' => $notes,
        ]);

        return $lead->fresh();
    }

    public function markAsLost(int $id, string $reason): Lead
    {
        $lead = $this->findById($id);

        if (!$lead) {
            throw new \Exception("Lead not found with ID: {$id}");
        }

        $lead->update([
            'lost_reason' => $reason,
            'lost_at' => now(),
        ]);

        return $lead->fresh();
    }

    public function getStatistics(): array
    {
        return [
            'total' => $this->model->count(),
            'active' => $this->model->active()->count(),
            'converted' => $this->model->converted()->count(),
            'lost' => $this->model->lost()->count(),
            'follow_up_due' => $this->model->followUpDue()->count(),
            'follow_up_overdue' => $this->model->followUpOverdue()->count(),
            'by_priority' => [
                'high' => $this->model->byPriority('high')->count(),
                'medium' => $this->model->byPriority('medium')->count(),
                'low' => $this->model->byPriority('low')->count(),
            ],
            'by_status' => DB::table('leads')
                ->join('lead_statuses', 'leads.status_id', '=', 'lead_statuses.id')
                ->select('lead_statuses.name', DB::raw('count(*) as count'))
                ->whereNull('leads.deleted_at')
                ->groupBy('lead_statuses.name')
                ->get()
                ->pluck('count', 'name')
                ->toArray(),
            'by_source' => DB::table('leads')
                ->join('lead_sources', 'leads.source_id', '=', 'lead_sources.id')
                ->select('lead_sources.name', DB::raw('count(*) as count'))
                ->whereNull('leads.deleted_at')
                ->groupBy('lead_sources.name')
                ->get()
                ->pluck('count', 'name')
                ->toArray(),
        ];
    }

    public function getWithRelations(int $id, array $relations = []): ?Lead
    {
        $defaultRelations = ['source', 'status', 'assignedUser', 'activities', 'documents'];
        $relations = empty($relations) ? $defaultRelations : $relations;

        return $this->model->with($relations)->find($id);
    }
}
