<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\ContactSubmission;
use Illuminate\Http\Request;

class ContactSubmissionController extends Controller
{
    /**
     * Display a listing of contact submissions
     */
    public function index(Request $request)
    {
        $query = ContactSubmission::query()
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $submissions = $query->paginate(20);

        // Get counts for status badges
        $counts = [
            'all' => ContactSubmission::count(),
            'new' => ContactSubmission::where('status', 'new')->count(),
            'read' => ContactSubmission::where('status', 'read')->count(),
            'replied' => ContactSubmission::where('status', 'replied')->count(),
            'archived' => ContactSubmission::where('status', 'archived')->count(),
        ];

        return view('central.contact-submissions.index', compact('submissions', 'counts'));
    }

    /**
     * Display the specified contact submission
     */
    public function show(ContactSubmission $contactSubmission)
    {
        // Mark as read if status is new
        if ($contactSubmission->status === 'new') {
            $contactSubmission->markAsRead();
        }

        return view('central.contact-submissions.show', compact('contactSubmission'));
    }

    /**
     * Update the status of a contact submission
     */
    public function updateStatus(Request $request, ContactSubmission $contactSubmission)
    {
        $request->validate([
            'status' => 'required|in:new,read,replied,archived',
        ]);

        $contactSubmission->update([
            'status' => $request->status,
        ]);

        return back()->with('success', 'Status updated successfully.');
    }

    /**
     * Remove the specified contact submission
     */
    public function destroy(ContactSubmission $contactSubmission)
    {
        $contactSubmission->delete();

        return redirect()->route('central.contact-submissions.index')
            ->with('success', 'Contact submission deleted successfully.');
    }
}
