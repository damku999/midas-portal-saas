<?php

namespace App\Http\Controllers;

use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\ReferenceUser;
use App\Models\RelationshipManager;
use App\Models\User;
use App\Services\LeadConversionService;
use App\Services\LeadService;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    protected LeadService $leadService;

    protected LeadConversionService $conversionService;

    public function __construct(LeadService $leadService, LeadConversionService $conversionService)
    {
        $this->leadService = $leadService;
        $this->conversionService = $conversionService;
    }

    /**
     * Display a listing of leads
     */
    public function index(Request $request)
    {
        $filters = [
            'status_id' => $request->input('status_id'),
            'source_id' => $request->input('source_id'),
            'assigned_to' => $request->input('assigned_to'),
            'priority' => $request->input('priority'),
            'search' => $request->input('search'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'sort_by' => $request->input('sort_by', 'created_at'),
            'sort_order' => $request->input('sort_order', 'desc'),
        ];

        $perPage = $request->input('per_page', 15);
        $leads = $this->leadService->getAllLeads($filters, $perPage);

        return view('leads.index', [
            'leads' => $leads,
            'filters' => $filters,
            'sources' => LeadSource::active()->ordered()->get(),
            'statuses' => LeadStatus::active()->ordered()->get(),
            'users' => User::where('status', true)->get(['id', 'first_name', 'last_name']),
        ]);
    }

    /**
     * Show the form for creating a new lead
     */
    public function create()
    {
        return view('leads.create', [
            'sources' => LeadSource::active()->ordered()->get(),
            'statuses' => LeadStatus::active()->ordered()->get(),
            'users' => User::where('status', true)->get(['id', 'first_name', 'last_name']),
            'relationshipManagers' => RelationshipManager::where('status', true)->get(['id', 'name']),
            'referenceUsers' => ReferenceUser::where('status', true)->get(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created lead
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'mobile_number' => 'required|string|max:20',
            'alternate_mobile' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'occupation' => 'nullable|string|max:255',
            'source_id' => 'required|exists:lead_sources,id',
            'product_interest' => 'nullable|string|max:255',
            'status_id' => 'required|exists:lead_statuses,id',
            'priority' => 'nullable|in:low,medium,high',
            'assigned_to' => 'nullable|exists:users,id',
            'relationship_manager_id' => 'nullable|exists:relationship_managers,id',
            'reference_user_id' => 'nullable|exists:reference_users,id',
            'next_follow_up_date' => 'nullable|date',
            'remarks' => 'nullable|string',
        ]);

        try {
            $lead = $this->leadService->createLead($validated);

            return redirect()->route('leads.show', $lead->id)
                ->with('success', 'Lead created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to create lead: '.$e->getMessage());
        }
    }

    /**
     * Display the specified lead
     */
    public function show(int $id)
    {
        try {
            $lead = $this->leadService->getLeadById($id);

            if (! $lead) {
                return redirect()->route('leads.index')
                    ->with('error', 'Lead not found.');
            }

            return view('leads.show', [
                'lead' => $lead->load([
                    'source',
                    'status',
                    'assignedUser',
                    'relationshipManager',
                    'referenceUser',
                    'convertedCustomer',
                    'creator',
                    'updater',
                    'activities.creator',
                    'documents.uploader',
                ]),
                'statuses' => LeadStatus::active()->ordered()->get(),
            ]);
        } catch (\Exception $e) {
            return redirect()->route('leads.index')
                ->with('error', 'Failed to load lead: '.$e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified lead
     */
    public function edit(int $id)
    {
        try {
            $lead = $this->leadService->getLeadById($id);

            if (! $lead) {
                return redirect()->route('leads.index')
                    ->with('error', 'Lead not found.');
            }

            return view('leads.edit', [
                'lead' => $lead,
                'sources' => LeadSource::active()->ordered()->get(),
                'statuses' => LeadStatus::active()->ordered()->get(),
                'users' => User::where('status', true)->get(['id', 'first_name', 'last_name']),
                'relationshipManagers' => RelationshipManager::where('status', true)->get(['id', 'name']),
                'referenceUsers' => ReferenceUser::where('status', true)->get(['id', 'name']),
            ]);
        } catch (\Exception $e) {
            return redirect()->route('leads.index')
                ->with('error', 'Failed to load lead: '.$e->getMessage());
        }
    }

    /**
     * Update the specified lead
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'mobile_number' => 'required|string|max:20',
            'alternate_mobile' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'occupation' => 'nullable|string|max:255',
            'source_id' => 'required|exists:lead_sources,id',
            'product_interest' => 'nullable|string|max:255',
            'status_id' => 'required|exists:lead_statuses,id',
            'priority' => 'nullable|in:low,medium,high',
            'assigned_to' => 'nullable|exists:users,id',
            'relationship_manager_id' => 'nullable|exists:relationship_managers,id',
            'reference_user_id' => 'nullable|exists:reference_users,id',
            'next_follow_up_date' => 'nullable|date',
            'remarks' => 'nullable|string',
        ]);

        try {
            $lead = $this->leadService->updateLead($id, $validated);

            return redirect()->route('leads.show', $lead->id)
                ->with('success', 'Lead updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to update lead: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified lead
     */
    public function destroy(int $id)
    {
        try {
            $this->leadService->deleteLead($id);

            return redirect()->route('leads.index')
                ->with('success', 'Lead deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete lead: '.$e->getMessage());
        }
    }

    /**
     * Update lead status
     */
    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status_id' => 'required|exists:lead_statuses,id',
            'notes' => 'nullable|string',
        ]);

        try {
            $lead = $this->leadService->updateLeadStatus(
                $id,
                $validated['status_id'],
                $validated['notes'] ?? null
            );

            return back()->with('success', 'Lead status updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update status: '.$e->getMessage());
        }
    }

    /**
     * Assign lead to user
     */
    public function assign(Request $request, int $id)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        try {
            $lead = $this->leadService->assignLeadTo($id, $validated['assigned_to']);

            return back()->with('success', 'Lead assigned successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to assign lead: '.$e->getMessage());
        }
    }

    /**
     * Convert lead to customer (automatic conversion)
     */
    public function convertAuto(Request $request, int $id)
    {
        $validated = $request->validate([
            'type' => 'nullable|in:Corporate,Retail',
            'pan_card_number' => 'nullable|string|max:50',
            'aadhar_card_number' => 'nullable|string|max:50',
            'gst_number' => 'nullable|string|max:50',
            'family_group_id' => 'nullable|exists:family_groups,id',
            'conversion_notes' => 'nullable|string',
        ]);

        try {
            $result = $this->conversionService->convertLeadToCustomer($id, $validated);

            return redirect()->route('leads.show', $id)
                ->with('success', $result['message'].". Customer ID: {$result['customer']->id}");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to convert lead: '.$e->getMessage());
        }
    }

    /**
     * Convert lead to customer (link to existing customer)
     */
    public function convert(Request $request, int $id)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'notes' => 'nullable|string',
        ]);

        try {
            $lead = $this->leadService->convertLeadToCustomer(
                $id,
                $validated['customer_id'],
                $validated['notes'] ?? null
            );

            return redirect()->route('leads.show', $lead->id)
                ->with('success', 'Lead linked to existing customer successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to link lead: '.$e->getMessage());
        }
    }

    /**
     * Bulk convert leads to customers
     */
    public function bulkConvert(Request $request)
    {
        $validated = $request->validate([
            'lead_ids' => 'required|array',
            'lead_ids.*' => 'exists:leads,id',
        ]);

        try {
            $results = $this->conversionService->bulkConvertLeads($validated['lead_ids']);

            $message = "Conversion complete: {$results['total']} total, ".
                count($results['successful']).' successful, '.
                count($results['failed']).' failed.';

            return back()->with('success', $message)->with('conversion_results', $results);
        } catch (\Exception $e) {
            return back()->with('error', 'Bulk conversion failed: '.$e->getMessage());
        }
    }

    /**
     * Get conversion statistics
     */
    public function conversionStats(Request $request)
    {
        try {
            $filters = [
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
                'source_id' => $request->input('source_id'),
                'assigned_to' => $request->input('assigned_to'),
            ];

            $statistics = $this->conversionService->getConversionStatistics($filters);

            return response()->json($statistics);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Mark lead as lost
     */
    public function markAsLost(Request $request, int $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        try {
            $lead = $this->leadService->markLeadAsLost($id, $validated['reason']);

            return redirect()->route('leads.show', $lead->id)
                ->with('success', 'Lead marked as lost.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to mark lead as lost: '.$e->getMessage());
        }
    }

    /**
     * Bulk assign leads to user
     */
    public function bulkAssign(Request $request)
    {
        $validated = $request->validate([
            'lead_ids' => 'required|array',
            'lead_ids.*' => 'exists:leads,id',
            'assigned_to' => 'required|exists:users,id',
        ]);

        try {
            $count = 0;
            foreach ($validated['lead_ids'] as $leadId) {
                $this->leadService->assignLeadTo($leadId, $validated['assigned_to']);
                $count++;
            }

            return back()->with('success', "Successfully assigned {$count} leads.");
        } catch (\Exception $e) {
            return back()->with('error', 'Bulk assign failed: '.$e->getMessage());
        }
    }

    /**
     * Export leads to Excel
     */
    public function export(Request $request)
    {
        try {
            $filters = [
                'status_id' => $request->input('status_id'),
                'source_id' => $request->input('source_id'),
                'assigned_to' => $request->input('assigned_to'),
                'priority' => $request->input('priority'),
                'search' => $request->input('search'),
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
            ];

            $leads = $this->leadService->getAllLeads($filters, null);

            $filename = 'leads_export_'.date('Y-m-d_His').'.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ];

            $callback = function () use ($leads) {
                $file = fopen('php://output', 'w');

                // Add CSV headers
                fputcsv($file, [
                    'Lead Number',
                    'Name',
                    'Email',
                    'Mobile Number',
                    'Alternate Mobile',
                    'Date of Birth',
                    'Occupation',
                    'Address',
                    'City',
                    'State',
                    'Pincode',
                    'Source',
                    'Status',
                    'Priority',
                    'Product Interest',
                    'Assigned To',
                    'Relationship Manager',
                    'Reference User',
                    'Next Follow-up Date',
                    'Remarks',
                    'Created At',
                    'Converted At',
                ]);

                // Add data rows
                foreach ($leads as $lead) {
                    fputcsv($file, [
                        $lead->lead_number ?? '',
                        $lead->name ?? '',
                        $lead->email ?? '',
                        $lead->mobile_number ?? '',
                        $lead->alternate_mobile ?? '',
                        $lead->date_of_birth ?? '',
                        $lead->occupation ?? '',
                        $lead->address ?? '',
                        $lead->city ?? '',
                        $lead->state ?? '',
                        $lead->pincode ?? '',
                        $lead->source->name ?? '',
                        $lead->status->name ?? '',
                        $lead->priority ?? '',
                        $lead->product_interest ?? '',
                        ($lead->assignedUser ? $lead->assignedUser->first_name.' '.$lead->assignedUser->last_name : ''),
                        $lead->relationshipManager->name ?? '',
                        $lead->referenceUser->name ?? '',
                        $lead->next_follow_up_date ?? '',
                        $lead->remarks ?? '',
                        $lead->created_at ? $lead->created_at->format('Y-m-d H:i:s') : '',
                        $lead->converted_at ? $lead->converted_at->format('Y-m-d H:i:s') : '',
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            return back()->with('error', 'Export failed: '.$e->getMessage());
        }
    }

    /**
     * Get lead statistics
     */
    public function statistics()
    {
        try {
            $statistics = $this->leadService->getStatistics();

            return response()->json($statistics);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
