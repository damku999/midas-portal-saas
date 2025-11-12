<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterSubscriberController extends Controller
{
    /**
     * Display a listing of newsletter subscribers
     */
    public function index(Request $request)
    {
        $query = NewsletterSubscriber::query();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by email or name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $subscribers = $query->paginate(20);

        // Get statistics
        $stats = [
            'total' => NewsletterSubscriber::count(),
            'active' => NewsletterSubscriber::where('status', 'active')->count(),
            'unsubscribed' => NewsletterSubscriber::where('status', 'unsubscribed')->count(),
            'today' => NewsletterSubscriber::whereDate('created_at', today())->count(),
            'this_week' => NewsletterSubscriber::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => NewsletterSubscriber::whereMonth('created_at', now()->month)->count(),
        ];

        return view('central.newsletter-subscribers.index', compact('subscribers', 'stats'));
    }

    /**
     * Display the specified subscriber
     */
    public function show(NewsletterSubscriber $subscriber)
    {
        return view('central.newsletter-subscribers.show', compact('subscriber'));
    }

    /**
     * Update subscriber status
     */
    public function updateStatus(Request $request, NewsletterSubscriber $subscriber)
    {
        $request->validate([
            'status' => 'required|in:active,unsubscribed',
        ]);

        if ($request->status === 'active') {
            $subscriber->resubscribe();
            $message = 'Subscriber reactivated successfully';
        } else {
            $subscriber->unsubscribe();
            $message = 'Subscriber unsubscribed successfully';
        }

        return redirect()
            ->route('central.newsletter-subscribers.show', $subscriber)
            ->with('success', $message);
    }

    /**
     * Remove the specified subscriber
     */
    public function destroy(NewsletterSubscriber $subscriber)
    {
        $subscriber->delete();

        return redirect()
            ->route('central.newsletter-subscribers.index')
            ->with('success', 'Subscriber deleted successfully');
    }

    /**
     * Export subscribers to CSV
     */
    public function export(Request $request)
    {
        $query = NewsletterSubscriber::query();

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $subscribers = $query->get();

        $filename = 'newsletter-subscribers-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($subscribers) {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, ['Email', 'Name', 'Status', 'Subscribed At', 'Unsubscribed At', 'IP Address']);

            // Add data
            foreach ($subscribers as $subscriber) {
                fputcsv($file, [
                    $subscriber->email,
                    $subscriber->name,
                    $subscriber->status,
                    $subscriber->subscribed_at ? $subscriber->subscribed_at->format('Y-m-d H:i:s') : '',
                    $subscriber->unsubscribed_at ? $subscriber->unsubscribed_at->format('Y-m-d H:i:s') : '',
                    $subscriber->ip_address,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
