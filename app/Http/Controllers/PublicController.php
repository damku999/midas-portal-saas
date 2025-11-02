<?php

namespace App\Http\Controllers;

use App\Models\Central\Plan;

class PublicController extends Controller
{
    /**
     * Show the homepage
     */
    public function home()
    {
        return view('public.home');
    }

    /**
     * Show features page
     */
    public function features()
    {
        return view('public.features');
    }

    /**
     * Show pricing page
     */
    public function pricing()
    {
        // Get plans from central database
        try {
            $plans = \DB::connection('central')
                ->table('plans')
                ->where('is_active', true)
                ->orderBy('price')
                ->get();
        } catch (\Exception $e) {
            // Fallback if central database not available
            $plans = collect([]);
        }

        return view('public.pricing', compact('plans'));
    }

    /**
     * Show about page
     */
    public function about()
    {
        return view('public.about');
    }

    /**
     * Show contact page
     */
    public function contact()
    {
        return view('public.contact');
    }

    /**
     * Handle contact form submission
     */
    public function submitContact()
    {
        // TODO: Implement contact form submission
        // Could send email to central admin or store in database

        return back()->with('success', 'Thank you for your message. We will get back to you soon!');
    }
}
