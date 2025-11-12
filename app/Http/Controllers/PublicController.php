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
        // Get featured plans for pricing section
        $plans = Plan::where('is_active', true)
            ->orderBy('price')
            ->get();

        // Load active testimonials for testimonials section
        $testimonials = \App\Models\Central\Testimonial::active()
            ->ordered()
            ->limit(3)
            ->get();

        return view('public.home', compact('plans', 'testimonials'));
    }

    /**
     * Show features page
     */
    public function features()
    {
        return view('public.features');
    }

    /**
     * Feature Detail Pages
     */
    public function customerManagement()
    {
        return view('public.features.customer-management');
    }

    public function familyManagement()
    {
        return view('public.features.family-management');
    }

    public function customerPortal()
    {
        return view('public.features.customer-portal');
    }

    public function leadManagement()
    {
        return view('public.features.lead-management');
    }

    public function policyManagement()
    {
        return view('public.features.policy-management');
    }

    public function claimsManagement()
    {
        return view('public.features.claims-management');
    }

    public function whatsappIntegration()
    {
        return view('public.features.whatsapp-integration');
    }

    public function quotationSystem()
    {
        return view('public.features.quotation-system');
    }

    public function analyticsReports()
    {
        return view('public.features.analytics-reports');
    }

    public function commissionTracking()
    {
        return view('public.features.commission-tracking');
    }

    public function documentManagement()
    {
        return view('public.features.document-management');
    }

    public function staffManagement()
    {
        return view('public.features.staff-management');
    }

    public function masterDataManagement()
    {
        return view('public.features.master-data-management');
    }

    public function notificationsAlerts()
    {
        return view('public.features.notifications-alerts');
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
     * Show blog listing page
     */
    public function blog(Request $request)
    {
        $query = \App\Models\Central\BlogPost::published()->latest('published_at');

        // Filter by category if provided
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $posts = $query->paginate(12);
        $featuredPost = \App\Models\Central\BlogPost::published()
            ->orderBy('views_count', 'desc')
            ->first();

        return view('public.blog.index', compact('posts', 'featuredPost'));
    }

    /**
     * Show blog detail page
     */
    public function blogShow(\App\Models\Central\BlogPost $post)
    {
        // Increment view count
        $post->incrementViews();

        // Get related posts
        $relatedPosts = \App\Models\Central\BlogPost::published()
            ->where('id', '!=', $post->id)
            ->where('category', $post->category)
            ->limit(3)
            ->get();

        return view('public.blog.show', compact('post', 'relatedPosts'));
    }

    /**
     * Show help center page
     */
    public function helpCenter()
    {
        return view('public.help-center');
    }

    /**
     * Show documentation page
     */
    public function documentation()
    {
        return view('public.documentation');
    }

    /**
     * Show API documentation page
     */
    public function api()
    {
        return view('public.api');
    }

    /**
     * Show privacy policy page
     */
    public function privacy()
    {
        return view('public.privacy');
    }

    /**
     * Show terms of service page
     */
    public function terms()
    {
        return view('public.terms');
    }

    /**
     * Show security page
     */
    public function security()
    {
        return view('public.security');
    }

    /**
     * Generate dynamic sitemap
     */
    public function sitemap()
    {
        // Build URLs array manually for better control over XML output
        $urls = [];

        // Add static pages
        $urls[] = [
            'loc' => url('/'),
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '1.0'
        ];

        $urls[] = [
            'loc' => url('/features'),
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'weekly',
            'priority' => '0.9'
        ];

        $urls[] = [
            'loc' => url('/pricing'),
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'weekly',
            'priority' => '0.9'
        ];

        $urls[] = [
            'loc' => url('/about'),
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'monthly',
            'priority' => '0.7'
        ];

        $urls[] = [
            'loc' => url('/contact'),
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'monthly',
            'priority' => '0.8'
        ];

        // Add feature detail pages
        $featurePages = [
            'customer-management', 'family-management', 'customer-portal', 'lead-management',
            'policy-management', 'claims-management', 'whatsapp-integration', 'quotation-system',
            'analytics-reports', 'commission-tracking', 'document-management', 'staff-management',
            'master-data-management', 'notifications-alerts'
        ];

        foreach ($featurePages as $feature) {
            $urls[] = [
                'loc' => url('/features/' . $feature),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.7'
            ];
        }

        // Add resource pages
        $urls[] = [
            'loc' => url('/blog'),
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '0.9'
        ];

        $urls[] = [
            'loc' => url('/help-center'),
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'weekly',
            'priority' => '0.7'
        ];

        $urls[] = [
            'loc' => url('/documentation'),
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'weekly',
            'priority' => '0.7'
        ];

        $urls[] = [
            'loc' => url('/api'),
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'weekly',
            'priority' => '0.6'
        ];

        $urls[] = [
            'loc' => url('/privacy'),
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'monthly',
            'priority' => '0.5'
        ];

        $urls[] = [
            'loc' => url('/terms'),
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'monthly',
            'priority' => '0.5'
        ];

        $urls[] = [
            'loc' => url('/security'),
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'monthly',
            'priority' => '0.6'
        ];

        // Add all blog posts dynamically
        $blogPosts = \App\Models\Central\BlogPost::published()
            ->orderBy('published_at', 'desc')
            ->get();

        foreach ($blogPosts as $post) {
            $urls[] = [
                'loc' => url('/blog/' . $post->slug),
                'lastmod' => $post->updated_at->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8'
            ];
        }

        // Build XML manually
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . htmlspecialchars($url['loc']) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . $url['lastmod'] . '</lastmod>' . "\n";
            $xml .= '    <changefreq>' . $url['changefreq'] . '</changefreq>' . "\n";
            $xml .= '    <priority>' . $url['priority'] . '</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>';

        // Return XML response
        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8'
        ]);
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

            // CAPTCHA validation
            if (config('services.turnstile.key') && config('services.turnstile.secret')) {
                $rules['cf-turnstile-response'] = ['required', Rule::turnstile()];
            }

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

            return redirect('/contact')->with('success', 'Thank you for your message. We will get back to you soon!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect('/contact')
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Contact form submission error: ' . $e->getMessage());
            return redirect('/contact')
                ->with('error', 'Sorry, there was an error submitting your message. Please try again.')
                ->withInput();
        }
    }

    /**
     * Handle newsletter subscription
     */
    public function subscribeNewsletter(Request $request)
    {
        try {
            $rules = [
                'email' => 'required|email|max:255',
                'name' => 'nullable|string|max:255',
            ];

            // CAPTCHA validation
            if (config('services.turnstile.key') && config('services.turnstile.secret')) {
                $rules['cf-turnstile-response'] = ['required', Rule::turnstile()];
            }

            $validated = $request->validate($rules, [
                'email.required' => 'Please enter your email address.',
                'email.email' => 'Please enter a valid email address.',
                'cf-turnstile-response.required' => 'Please complete the security verification.',
            ]);

            // Check if already subscribed
            $existing = \App\Models\Central\NewsletterSubscriber::where('email', $validated['email'])->first();

            if ($existing) {
                if ($existing->status === 'active') {
                    return back()->with('info', 'You are already subscribed to our newsletter!');
                } else {
                    // Reactivate subscription
                    $existing->update([
                        'status' => 'active',
                        'unsubscribed_at' => null,
                        'subscribed_at' => now(),
                    ]);
                    return back()->with('success', 'Welcome back! Your subscription has been reactivated.');
                }
            }

            // Create new subscription
            \App\Models\Central\NewsletterSubscriber::create([
                'email' => $validated['email'],
                'name' => $validated['name'] ?? null,
                'status' => 'active',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'subscribed_at' => now(),
            ]);

            // Send welcome email (optional)
            try {
                \Mail::to($validated['email'])->send(new \App\Mail\NewsletterWelcome($validated['email']));
            } catch (\Exception $e) {
                \Log::error('Failed to send newsletter welcome email', ['error' => $e->getMessage()]);
            }

            return back()->with('success', 'Thank you for subscribing! Check your inbox for a welcome message.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Newsletter subscription error: ' . $e->getMessage());
            return back()->with('error', 'Sorry, there was an error processing your subscription. Please try again.');
        }
    }
}
