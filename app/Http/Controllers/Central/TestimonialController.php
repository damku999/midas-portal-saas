<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TestimonialController extends Controller
{
    /**
     * Display a listing of testimonials
     */
    public function index()
    {
        $testimonials = Testimonial::orderBy('display_order', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('central.testimonials.index', compact('testimonials'));
    }

    /**
     * Show the form for creating a new testimonial
     */
    public function create()
    {
        return view('central.testimonials.create');
    }

    /**
     * Store a newly created testimonial
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'testimonial' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'photo' => 'nullable|image|max:2048',
            'status' => 'required|in:active,inactive',
            'display_order' => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('testimonials', 'public');
        }

        Testimonial::create($validated);

        return redirect()
            ->route('central.testimonials.index')
            ->with('success', 'Testimonial created successfully');
    }

    /**
     * Show the form for editing the specified testimonial
     */
    public function edit(Testimonial $testimonial)
    {
        return view('central.testimonials.edit', compact('testimonial'));
    }

    /**
     * Update the specified testimonial
     */
    public function update(Request $request, Testimonial $testimonial)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'testimonial' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'photo' => 'nullable|image|max:2048',
            'status' => 'required|in:active,inactive',
            'display_order' => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($testimonial->photo) {
                Storage::disk('public')->delete($testimonial->photo);
            }
            $validated['photo'] = $request->file('photo')->store('testimonials', 'public');
        }

        $testimonial->update($validated);

        return redirect()
            ->route('central.testimonials.index')
            ->with('success', 'Testimonial updated successfully');
    }

    /**
     * Remove the specified testimonial
     */
    public function destroy(Testimonial $testimonial)
    {
        // Delete photo if exists
        if ($testimonial->photo) {
            Storage::disk('public')->delete($testimonial->photo);
        }

        $testimonial->delete();

        return redirect()
            ->route('central.testimonials.index')
            ->with('success', 'Testimonial deleted successfully');
    }

    /**
     * Toggle testimonial status
     */
    public function toggleStatus(Testimonial $testimonial)
    {
        $testimonial->update([
            'status' => $testimonial->status === 'active' ? 'inactive' : 'active',
        ]);

        return redirect()
            ->route('central.testimonials.index')
            ->with('success', 'Testimonial status updated successfully');
    }
}
