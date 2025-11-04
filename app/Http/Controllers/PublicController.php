<?php

namespace App\Http\Controllers;

use App\Mail\ContactSubmissionNotification;
use App\Models\Central\ContactSubmission;
use App\Models\Central\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

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
    public function submitContact(Request $request)
    {
        \Log::info('Contact form submission received', [
            'data' => $request->all(),
            'ip' => $request->ip(),
        ]);

        try {
            // Validation rules
            $rules = [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'nullable|string|max:20',
                'company' => 'nullable|string|max:255',
                'message' => 'required|string|max:5000',
            ];

            // CAPTCHA validation temporarily disabled for testing
            // if (config('services.turnstile.key') && config('services.turnstile.secret')) {
            //     $rules['cf-turnstile-response'] = ['required', Rule::turnstile()];
            // }

            // Validate form data
            $validated = $request->validate($rules, [
                'name.required' => 'Please enter your name.',
                'email.required' => 'Please enter your email address.',
                'email.email' => 'Please enter a valid email address.',
                'message.required' => 'Please enter your message.',
                'cf-turnstile-response.required' => 'Please complete the security verification.',
            ]);

            \Log::info('Contact form validation passed', ['validated' => $validated]);

            // Store contact submission in central database
            $submission = ContactSubmission::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'company' => $validated['company'] ?? null,
                'message' => $validated['message'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => 'new',
            ]);

            \Log::info('Contact submission saved successfully', ['id' => $submission->id]);

            // Send email notification to admin
            try {
                $adminEmail = config('mail.from.address');
                Mail::to($adminEmail)->send(new ContactSubmissionNotification($submission));
                \Log::info('Contact notification email sent', ['to' => $adminEmail]);
            } catch (\Exception $e) {
                \Log::error('Failed to send contact notification email', ['error' => $e->getMessage()]);
                // Don't fail the whole submission if email fails
            }

            return redirect()->route('public.contact')->with('success', 'Thank you for your message. We will get back to you soon!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('public.contact')
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Contact form submission error: ' . $e->getMessage());
            return redirect()->route('public.contact')
                ->with('error', 'Sorry, there was an error submitting your message. Please try again.')
                ->withInput();
        }
    }
}
