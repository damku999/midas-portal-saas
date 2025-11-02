<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadStatus;
use App\Services\LeadConversionService;
use App\Services\LeadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeadDashboardController extends Controller
{
    protected LeadService $leadService;

    protected LeadConversionService $conversionService;

    public function __construct(LeadService $leadService, LeadConversionService $conversionService)
    {
        $this->leadService = $leadService;
        $this->conversionService = $conversionService;
    }

    /**
     * Display lead dashboard
     */
    public function index(Request $request)
    {
        $userId = Auth::id();

        // Get overview statistics
        $statistics = $this->getOverviewStatistics($request);

        // Get user-specific data
        $myLeads = Lead::where('assigned_to', $userId)->active()->count();
        $myFollowUps = Lead::where('assigned_to', $userId)->followUpDue()->count();
        $myOverdue = Lead::where('assigned_to', $userId)->followUpOverdue()->count();

        // Get recent activities
        $recentActivities = LeadActivity::with(['lead', 'creator'])
            ->where('created_by', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get upcoming activities
        $upcomingActivities = LeadActivity::with(['lead', 'creator'])
            ->where('created_by', $userId)
            ->upcoming()
            ->orderBy('scheduled_at', 'asc')
            ->limit(5)
            ->get();

        return view('leads.dashboard', [
            'statistics' => $statistics,
            'myLeads' => $myLeads,
            'myFollowUps' => $myFollowUps,
            'myOverdue' => $myOverdue,
            'recentActivities' => $recentActivities,
            'upcomingActivities' => $upcomingActivities,
        ]);
    }

    /**
     * Get overview statistics
     */
    public function getOverviewStatistics(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        $query = Lead::query();

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $totalLeads = $query->count();
        $activeLeads = (clone $query)->active()->count();
        $convertedLeads = (clone $query)->converted()->count();
        $lostLeads = (clone $query)->lost()->count();

        $conversionRate = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 2) : 0;

        return [
            'total_leads' => $totalLeads,
            'active_leads' => $activeLeads,
            'converted_leads' => $convertedLeads,
            'lost_leads' => $lostLeads,
            'conversion_rate' => $conversionRate,
            'follow_ups_due' => Lead::followUpDue()->count(),
            'follow_ups_overdue' => Lead::followUpOverdue()->count(),
        ];
    }

    /**
     * Get leads by status (for charts)
     */
    public function leadsByStatus(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        $data = DB::table('leads')
            ->join('lead_statuses', 'leads.status_id', '=', 'lead_statuses.id')
            ->select(
                'lead_statuses.name as status',
                'lead_statuses.color',
                DB::raw('COUNT(*) as count')
            )
            ->whereNull('leads.deleted_at')
            ->whereDate('leads.created_at', '>=', $dateFrom)
            ->whereDate('leads.created_at', '<=', $dateTo)
            ->groupBy('lead_statuses.id', 'lead_statuses.name', 'lead_statuses.color')
            ->orderBy('lead_statuses.display_order')
            ->get();

        return response()->json($data);
    }

    /**
     * Get leads by source (for charts)
     */
    public function leadsBySource(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        $data = DB::table('leads')
            ->join('lead_sources', 'leads.source_id', '=', 'lead_sources.id')
            ->select(
                'lead_sources.name as source',
                DB::raw('COUNT(*) as count')
            )
            ->whereNull('leads.deleted_at')
            ->whereDate('leads.created_at', '>=', $dateFrom)
            ->whereDate('leads.created_at', '<=', $dateTo)
            ->groupBy('lead_sources.id', 'lead_sources.name')
            ->orderBy('count', 'desc')
            ->get();

        return response()->json($data);
    }

    /**
     * Get leads by priority
     */
    public function leadsByPriority(Request $request)
    {
        $data = DB::table('leads')
            ->select('priority', DB::raw('COUNT(*) as count'))
            ->whereNull('deleted_at')
            ->groupBy('priority')
            ->get();

        return response()->json($data);
    }

    /**
     * Get lead trend over time (monthly)
     */
    public function leadTrend(Request $request)
    {
        $months = (int) $request->input('months', 12);

        $data = DB::table('leads')
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status_id IN (SELECT id FROM lead_statuses WHERE is_converted = 1) THEN 1 ELSE 0 END) as converted'),
                DB::raw('SUM(CASE WHEN status_id IN (SELECT id FROM lead_statuses WHERE is_lost = 1) THEN 1 ELSE 0 END) as lost')
            )
            ->whereNull('deleted_at')
            ->where('created_at', '>=', now()->subMonths($months))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json($data);
    }

    /**
     * Get top performing users
     */
    public function topPerformers(Request $request)
    {
        $limit = (int) $request->input('limit', 10);

        $data = DB::table('leads')
            ->join('users', 'leads.assigned_to', '=', 'users.id')
            ->join('lead_statuses', 'leads.status_id', '=', 'lead_statuses.id')
            ->select(
                'users.name as user',
                DB::raw('COUNT(*) as total_leads'),
                DB::raw('SUM(CASE WHEN lead_statuses.is_converted = 1 THEN 1 ELSE 0 END) as converted'),
                DB::raw('ROUND((SUM(CASE WHEN lead_statuses.is_converted = 1 THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as conversion_rate')
            )
            ->whereNull('leads.deleted_at')
            ->groupBy('users.id', 'users.name')
            ->orderBy('converted', 'desc')
            ->limit($limit)
            ->get();

        return response()->json($data);
    }

    /**
     * Get conversion funnel data
     */
    public function conversionFunnel(Request $request)
    {
        $statuses = LeadStatus::active()->ordered()->get();

        $funnelData = $statuses->map(function ($status) {
            return [
                'status' => $status->name,
                'count' => Lead::where('status_id', $status->id)->count(),
                'color' => $status->color,
            ];
        });

        return response()->json($funnelData);
    }

    /**
     * Get activity statistics
     */
    public function activityStats(Request $request)
    {
        $userId = $request->input('user_id', Auth::id());
        $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        $data = DB::table('lead_activities')
            ->select(
                'activity_type',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(CASE WHEN completed_at IS NOT NULL THEN 1 ELSE 0 END) as completed'),
                DB::raw('SUM(CASE WHEN completed_at IS NULL AND scheduled_at < NOW() THEN 1 ELSE 0 END) as overdue')
            )
            ->where('created_by', $userId)
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->groupBy('activity_type')
            ->get();

        return response()->json($data);
    }

    /**
     * Get lost reasons analysis
     */
    public function lostReasonsAnalysis(Request $request)
    {
        $data = DB::table('leads')
            ->join('lead_statuses', 'leads.status_id', '=', 'lead_statuses.id')
            ->select(
                'leads.lost_reason as reason',
                DB::raw('COUNT(*) as count')
            )
            ->where('lead_statuses.is_lost', true)
            ->whereNotNull('leads.lost_reason')
            ->whereNull('leads.deleted_at')
            ->groupBy('leads.lost_reason')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        return response()->json($data);
    }

    /**
     * Get lead aging report
     */
    public function leadAgingReport(Request $request)
    {
        $data = DB::table('leads')
            ->select(
                DB::raw('CASE
                    WHEN DATEDIFF(NOW(), created_at) <= 7 THEN "0-7 days"
                    WHEN DATEDIFF(NOW(), created_at) <= 14 THEN "8-14 days"
                    WHEN DATEDIFF(NOW(), created_at) <= 30 THEN "15-30 days"
                    WHEN DATEDIFF(NOW(), created_at) <= 60 THEN "31-60 days"
                    ELSE "60+ days"
                END as age_group'),
                DB::raw('COUNT(*) as count')
            )
            ->whereNull('deleted_at')
            ->whereNull('converted_at')
            ->whereNull('lost_at')
            ->groupBy('age_group')
            ->get();

        return response()->json($data);
    }

    /**
     * Export dashboard data
     */
    public function export(Request $request)
    {
        $format = $request->input('format', 'csv');

        // Implement export functionality based on format
        // CSV, Excel, PDF, etc.

        return response()->json(['message' => 'Export functionality to be implemented']);
    }
}
