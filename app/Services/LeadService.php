<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Repositories\Contracts\LeadRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeadService
{
    protected LeadRepositoryInterface $leadRepository;

    public function __construct(LeadRepositoryInterface $leadRepository)
    {
        $this->leadRepository = $leadRepository;
    }

    /**
     * Get all leads with filters
     */
    public function getAllLeads(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->leadRepository->getAll($filters, $perPage);
    }

    /**
     * Get lead by ID
     */
    public function getLeadById(int $id): ?Lead
    {
        return $this->leadRepository->getWithRelations($id);
    }

    /**
     * Get lead by lead number
     */
    public function getLeadByNumber(string $leadNumber): ?Lead
    {
        return $this->leadRepository->findByLeadNumber($leadNumber);
    }

    /**
     * Create new lead
     */
    public function createLead(array $data): Lead
    {
        DB::beginTransaction();

        try {
            // Set created_by to current user if not provided
            if (! isset($data['created_by'])) {
                $data['created_by'] = Auth::id();
            }

            // Create the lead
            $lead = $this->leadRepository->create($data);

            // Log activity
            $this->logActivity($lead->id, LeadActivity::TYPE_NOTE, 'Lead Created', 'New lead created in the system');

            DB::commit();

            return $lead->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update lead
     */
    public function updateLead(int $id, array $data): Lead
    {
        DB::beginTransaction();

        try {
            // Set updated_by to current user
            $data['updated_by'] = Auth::id();

            // Get current lead state
            $lead = $this->leadRepository->findById($id);
            if (! $lead) {
                throw new \Exception("Lead not found with ID: {$id}");
            }

            $oldStatusId = $lead->status_id;

            // Update the lead
            $lead = $this->leadRepository->update($id, $data);

            // If status changed, log it
            if (isset($data['status_id']) && $data['status_id'] != $oldStatusId) {
                $this->logActivity(
                    $lead->id,
                    LeadActivity::TYPE_STATUS_CHANGE,
                    'Status Changed',
                    "Status changed from {$lead->status->name} to {$lead->status->name}"
                );
            }

            DB::commit();

            return $lead->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete lead (soft delete)
     */
    public function deleteLead(int $id): bool
    {
        return $this->leadRepository->delete($id);
    }

    /**
     * Restore soft deleted lead
     */
    public function restoreLead(int $id): bool
    {
        return $this->leadRepository->restore($id);
    }

    /**
     * Get leads by status
     */
    public function getLeadsByStatus(int $statusId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->leadRepository->getByStatus($statusId, $perPage);
    }

    /**
     * Get leads by source
     */
    public function getLeadsBySource(int $sourceId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->leadRepository->getBySource($sourceId, $perPage);
    }

    /**
     * Get leads assigned to user
     */
    public function getLeadsAssignedTo(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->leadRepository->getAssignedTo($userId, $perPage);
    }

    /**
     * Get active leads
     */
    public function getActiveLeads(int $perPage = 15): LengthAwarePaginator
    {
        return $this->leadRepository->getActive($perPage);
    }

    /**
     * Get converted leads
     */
    public function getConvertedLeads(int $perPage = 15): LengthAwarePaginator
    {
        return $this->leadRepository->getConverted($perPage);
    }

    /**
     * Get lost leads
     */
    public function getLostLeads(int $perPage = 15): LengthAwarePaginator
    {
        return $this->leadRepository->getLost($perPage);
    }

    /**
     * Get leads with follow-up due
     */
    public function getFollowUpDueLeads(int $perPage = 15): LengthAwarePaginator
    {
        return $this->leadRepository->getFollowUpDue($perPage);
    }

    /**
     * Get leads with overdue follow-up
     */
    public function getFollowUpOverdueLeads(int $perPage = 15): LengthAwarePaginator
    {
        return $this->leadRepository->getFollowUpOverdue($perPage);
    }

    /**
     * Search leads
     */
    public function searchLeads(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return $this->leadRepository->search($query, $perPage);
    }

    /**
     * Update lead status
     */
    public function updateLeadStatus(int $id, int $statusId, ?string $notes = null): Lead
    {
        DB::beginTransaction();

        try {
            $lead = $this->leadRepository->updateStatus($id, $statusId, $notes);

            // Log status change activity
            $this->logActivity(
                $lead->id,
                LeadActivity::TYPE_STATUS_CHANGE,
                'Status Changed',
                "Status changed to {$lead->status->name}",
                $notes
            );

            DB::commit();

            return $lead->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Assign lead to user
     */
    public function assignLeadTo(int $id, int $userId): Lead
    {
        DB::beginTransaction();

        try {
            $lead = $this->leadRepository->assignTo($id, $userId);

            // Log assignment activity
            $this->logActivity(
                $lead->id,
                LeadActivity::TYPE_ASSIGNMENT,
                'Lead Assigned',
                "Lead assigned to {$lead->assignedUser->name}"
            );

            DB::commit();

            return $lead->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Convert lead to customer
     */
    public function convertLeadToCustomer(int $id, int $customerId, ?string $notes = null): Lead
    {
        DB::beginTransaction();

        try {
            $lead = $this->leadRepository->convertToCustomer($id, $customerId, $notes);

            // Log conversion activity
            $this->logActivity(
                $lead->id,
                LeadActivity::TYPE_STATUS_CHANGE,
                'Lead Converted',
                'Lead converted to customer',
                $notes
            );

            DB::commit();

            return $lead->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Mark lead as lost
     */
    public function markLeadAsLost(int $id, string $reason): Lead
    {
        DB::beginTransaction();

        try {
            $lead = $this->leadRepository->markAsLost($id, $reason);

            // Log lost activity
            $this->logActivity(
                $lead->id,
                LeadActivity::TYPE_STATUS_CHANGE,
                'Lead Marked as Lost',
                "Lead marked as lost: {$reason}"
            );

            DB::commit();

            return $lead->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Add activity to lead
     */
    public function addActivity(
        int $leadId,
        string $activityType,
        string $subject,
        ?string $description = null,
        ?string $outcome = null,
        ?string $nextAction = null,
        ?string $scheduledAt = null
    ): LeadActivity {
        return $this->logActivity(
            $leadId,
            $activityType,
            $subject,
            $description,
            $outcome,
            $nextAction,
            $scheduledAt
        );
    }

    /**
     * Get lead statistics
     */
    public function getStatistics(): array
    {
        return $this->leadRepository->getStatistics();
    }

    /**
     * Log activity for lead
     */
    protected function logActivity(
        int $leadId,
        string $activityType,
        string $subject,
        ?string $description = null,
        ?string $outcome = null,
        ?string $nextAction = null,
        ?string $scheduledAt = null
    ): LeadActivity {
        return LeadActivity::create([
            'lead_id' => $leadId,
            'activity_type' => $activityType,
            'subject' => $subject,
            'description' => $description,
            'outcome' => $outcome,
            'next_action' => $nextAction,
            'scheduled_at' => $scheduledAt,
            'created_by' => Auth::id(),
        ]);
    }
}
