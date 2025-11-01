<?php

namespace App\Http\Controllers;

use App\Models\LeadActivity;
use App\Services\LeadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeadActivityController extends Controller
{
    protected LeadService $leadService;

    public function __construct(LeadService $leadService)
    {
        $this->leadService = $leadService;
    }

    /**
     * Store a new activity for a lead
     */
    public function store(Request $request, int $leadId)
    {
        $validated = $request->validate([
            'activity_type' => 'required|in:call,email,meeting,note,status_change,assignment,document,quotation',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'outcome' => 'nullable|string|max:255',
            'next_action' => 'nullable|string',
            'scheduled_at' => 'nullable|date',
        ]);

        try {
            $activity = $this->leadService->addActivity(
                $leadId,
                $validated['activity_type'],
                $validated['subject'],
                $validated['description'] ?? null,
                $validated['outcome'] ?? null,
                $validated['next_action'] ?? null,
                $validated['scheduled_at'] ?? null
            );

            return back()->with('success', 'Activity added successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to add activity: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing activity
     */
    public function update(Request $request, int $leadId, int $activityId)
    {
        $validated = $request->validate([
            'activity_type' => 'required|in:call,email,meeting,note,status_change,assignment,document,quotation',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'outcome' => 'nullable|string|max:255',
            'next_action' => 'nullable|string',
            'scheduled_at' => 'nullable|date',
            'completed_at' => 'nullable|date',
        ]);

        try {
            $activity = LeadActivity::where('lead_id', $leadId)->findOrFail($activityId);
            $activity->update($validated);

            return back()->with('success', 'Activity updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update activity: ' . $e->getMessage());
        }
    }

    /**
     * Mark activity as completed
     */
    public function complete(int $leadId, int $activityId)
    {
        try {
            $activity = LeadActivity::where('lead_id', $leadId)->findOrFail($activityId);
            $activity->markAsCompleted();

            return back()->with('success', 'Activity marked as completed.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to complete activity: ' . $e->getMessage());
        }
    }

    /**
     * Delete an activity
     */
    public function destroy(int $leadId, int $activityId)
    {
        try {
            $activity = LeadActivity::where('lead_id', $leadId)->findOrFail($activityId);
            $activity->delete();

            return back()->with('success', 'Activity deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete activity: ' . $e->getMessage());
        }
    }

    /**
     * Get activities for a lead
     */
    public function index(int $leadId)
    {
        try {
            $activities = LeadActivity::where('lead_id', $leadId)
                ->with('creator')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($activities);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get upcoming activities for current user
     */
    public function upcoming()
    {
        try {
            $activities = LeadActivity::with(['lead', 'creator'])
                ->where('created_by', Auth::id())
                ->upcoming()
                ->orderBy('scheduled_at', 'asc')
                ->get();

            return response()->json($activities);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get overdue activities for current user
     */
    public function overdue()
    {
        try {
            $activities = LeadActivity::with(['lead', 'creator'])
                ->where('created_by', Auth::id())
                ->overdue()
                ->orderBy('scheduled_at', 'asc')
                ->get();

            return response()->json($activities);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get today's activities for current user
     */
    public function today()
    {
        try {
            $activities = LeadActivity::with(['lead', 'creator'])
                ->where('created_by', Auth::id())
                ->today()
                ->orderBy('scheduled_at', 'asc')
                ->get();

            return response()->json($activities);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
