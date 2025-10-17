<?php

namespace App\Http\Middleware;

use App\Services\ContentSecurityPolicyService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    public function __construct(
        private ContentSecurityPolicyService $cspService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        // Share CSP nonce with all views
        View::share('cspNonce', $this->cspService->getNonce());

        $response = $next($request);

        // Skip security headers for certain routes (e.g., API documentation)
        if ($this->shouldSkipSecurityHeaders($request)) {
            return $response;
        }

        // Add comprehensive security headers
        $headers = $this->cspService->getSecurityHeaders();

        foreach ($headers as $header => $value) {
            $response->headers->set($header, $value);
        }

        // Add Content Security Policy
        if (config('security.csp_enabled', true)) {
            $csp = $this->cspService->getContentSecurityPolicy($request);
            $cspString = $this->cspService->buildCspString($csp);
            $cspHeaderName = $this->cspService->getCspHeaderName();

            $response->headers->set($cspHeaderName, $cspString);
        }

        // Add CSP Report-To header if report URI is configured
        if ($reportUri = config('security.csp_report_uri')) {
            $reportToHeader = json_encode([
                'group' => 'csp-endpoint',
                'max_age' => 86400,
                'endpoints' => [
                    ['url' => $reportUri],
                ],
            ]);
            $response->headers->set('Report-To', $reportToHeader);
        }

        return $response;
    }

    private function shouldSkipSecurityHeaders(Request $request): bool
    {
        $skipRoutes = [
            'horizon.*',
            'telescope.*',
            'webmonks-log-viewer.*',
            'api/docs.*',
        ];

        foreach ($skipRoutes as $pattern) {
            if ($request->routeIs($pattern)) {
                return true;
            }
        }

        return false;
    }
}
