<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContentSecurityPolicyService
{
    private readonly string $nonce;

    private readonly array $trustedHosts;

    private readonly bool $isDevelopment;

    private readonly bool $reportOnly;

    public function __construct()
    {
        $this->nonce = base64_encode(random_bytes(16));
        $this->trustedHosts = config('security.trusted_hosts', []);
        $this->isDevelopment = app()->environment(['local', 'development']);
        $this->reportOnly = config('security.csp_report_only', false);
    }

    public function getNonce(): string
    {
        return $this->nonce;
    }

    public function getContentSecurityPolicy(Request $request): array
    {
        $isAdminPanel = $this->isAdminPanel($request);

        $basePolicy = [
            'default-src' => "'self'",
            'script-src' => $this->getScriptSrc($isAdminPanel),
            'style-src' => $this->getStyleSrc(),
            'img-src' => $this->getImageSrc(),
            'font-src' => $this->getFontSrc(),
            'connect-src' => $this->getConnectSrc(),
            'frame-src' => $this->getFrameSrc(),
            'object-src' => "'none'",
            'media-src' => "'self'",
            'form-action' => "'self'",
            'frame-ancestors' => "'none'",
            'base-uri' => "'self'",
            'manifest-src' => "'self'",
        ];

        // Add report URI for monitoring
        if ($reportUri = config('security.csp_report_uri')) {
            $basePolicy['report-uri'] = $reportUri;
            $basePolicy['report-to'] = 'csp-endpoint';
        }

        // Upgrade insecure requests in production
        if (! $this->isDevelopment) {
            $basePolicy['upgrade-insecure-requests'] = '';
        }

        return $basePolicy;
    }

    public function getSecurityHeaders(): array
    {
        return [
            // Core security headers
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'X-XSS-Protection' => '0', // Disabled in favor of CSP
            'Referrer-Policy' => 'strict-origin-when-cross-origin',

            // HSTS for HTTPS
            'Strict-Transport-Security' => $this->getHstsHeader(),

            // Cross-Origin policies
            'Cross-Origin-Embedder-Policy' => 'require-corp',
            'Cross-Origin-Opener-Policy' => 'same-origin',
            'Cross-Origin-Resource-Policy' => 'same-origin',

            // Permissions policy (new Permissions API)
            'Permissions-Policy' => $this->getPermissionsPolicy(),

            // Additional security headers
            'X-Permitted-Cross-Domain-Policies' => 'none',
            'X-DNS-Prefetch-Control' => 'off',
            'Expect-CT' => 'max-age=86400, enforce',
        ];
    }

    public function getCspHeaderName(): string
    {
        return $this->reportOnly ? 'Content-Security-Policy-Report-Only' : 'Content-Security-Policy';
    }

    public function buildCspString(array $policy): string
    {
        $cspString = '';

        foreach ($policy as $directive => $value) {
            if (empty($value)) {
                $cspString .= $directive.'; ';
            } else {
                $cspString .= $directive.' '.$value.'; ';
            }
        }

        return rtrim($cspString, '; ');
    }

    private function getScriptSrc(bool $isAdminPanel): string
    {
        $sources = ["'self'"];

        // Add nonce for inline scripts
        $sources[] = sprintf("'nonce-%s'", $this->nonce);

        // Add trusted CDNs for specific functionality
        $cdnSources = [
            'https://code.jquery.com',
            'https://cdn.jsdelivr.net',
            'https://cdnjs.cloudflare.com',
        ];

        // Admin panel may need additional sources for charts/analytics
        if ($isAdminPanel) {
            $cdnSources = array_merge($cdnSources, [
                'https://kit.fontawesome.com',
                'https://cdn.datatables.net',
            ]);
        }

        $sources = array_merge($sources, $cdnSources);

        // Add trusted hosts
        if ($this->trustedHosts !== []) {
            $sources = array_merge($sources, $this->trustedHosts);
        }

        // Development mode may need eval for hot reloading
        if ($this->isDevelopment) {
            $sources[] = "'unsafe-eval'"; // Only for development
        }

        // Add Cloudflare Turnstile domains
        $sources[] = 'https://challenges.cloudflare.com';
        $sources[] = 'https://static.cloudflare.com';

        // Add Google Analytics and Tag Manager domains
        $sources[] = 'https://www.googletagmanager.com';
        $sources[] = 'https://www.google-analytics.com';

        // Add Microsoft Clarity domain
        $sources[] = 'https://www.clarity.ms';

        return implode(' ', $sources);
    }

    private function getStyleSrc(): string
    {
        $sources = ["'self'"];

        // Add nonce for inline styles
        $sources[] = sprintf("'nonce-%s'", $this->nonce);

        // Add trusted style CDNs
        $styleCdns = [
            'https://fonts.googleapis.com',
            'https://cdn.jsdelivr.net',
            'https://cdnjs.cloudflare.com',
            'https://kit.fontawesome.com',
        ];

        $sources = array_merge($sources, $styleCdns);

        return implode(' ', $sources);
    }

    private function getImageSrc(): string
    {
        $sources = ["'self'", 'data:', 'https:', 'blob:'];

        // Explicitly allow analytics tracking pixels
        $sources[] = 'https://www.google-analytics.com';
        $sources[] = 'https://www.googletagmanager.com';
        $sources[] = 'https://stats.g.doubleclick.net';
        $sources[] = 'https://www.clarity.ms';

        return implode(' ', $sources);
    }

    private function getFontSrc(): string
    {
        return "'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com https://kit.fontawesome.com";
    }

    private function getConnectSrc(): string
    {
        $sources = ["'self'"];

        // Add API endpoints if different domain
        if ($apiDomain = config('app.api_domain')) {
            $sources[] = $apiDomain;
        }

        // Add WhatsApp API domain if configured
        if ($whatsappDomain = config('whatsapp.api_domain')) {
            $sources[] = $whatsappDomain;
        }

        // Add analytics tracking domains
        $sources[] = 'https://www.google-analytics.com';
        $sources[] = 'https://www.googletagmanager.com';
        $sources[] = 'https://analytics.google.com';
        $sources[] = 'https://stats.g.doubleclick.net';

        // Add Microsoft Clarity tracking domain
        $sources[] = 'https://www.clarity.ms';
        $sources[] = 'https://*.clarity.ms';

        return implode(' ', $sources);
    }

    private function getFrameSrc(): string
    {
        $sources = ["'self'"];

        // Add Cloudflare Turnstile iframe domains
        $sources[] = 'https://challenges.cloudflare.com';

        // Add Google Maps iframe domain
        $sources[] = 'https://www.google.com';

        return implode(' ', $sources);
    }

    private function getHstsHeader(): string
    {
        $maxAge = config('security.hsts_max_age', 31536000); // 1 year default
        $includeSubdomains = config('security.hsts_include_subdomains', true);
        $preload = config('security.hsts_preload', false);

        $header = 'max-age='.$maxAge;

        if ($includeSubdomains) {
            $header .= '; includeSubDomains';
        }

        if ($preload) {
            $header .= '; preload';
        }

        return $header;
    }

    private function getPermissionsPolicy(): string
    {
        return 'geolocation=(), microphone=(), camera=(), payment=(), usb=(), magnetometer=(), gyroscope=(), speaker=()';
    }

    private function isAdminPanel(Request $request): bool
    {
        return ! str_starts_with($request->path(), 'customer');
    }

    public function generateNonceForView(): string
    {
        // Store nonce in view data for Blade templates
        return $this->nonce;
    }

    public function isNonceValid(string $providedNonce): bool
    {
        return hash_equals($this->nonce, $providedNonce);
    }

    public function logCspViolation(array $violationData): void
    {
        Log::warning('CSP Violation detected', [
            'violation' => $violationData,
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->url(),
        ]);
    }
}
