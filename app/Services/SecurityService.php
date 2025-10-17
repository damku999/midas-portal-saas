<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SecurityService
{
    public function getContentSecurityPolicy(): array
    {
        return [
            'default-src' => "'self'",
            'script-src' => "'self' 'unsafe-inline' 'unsafe-eval' https://code.jquery.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://kit.fontawesome.com",
            'style-src' => "'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
            'font-src' => "'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com",
            'img-src' => "'self' data: https:",
            'connect-src' => "'self'",
            'frame-src' => "'none'",
            'object-src' => "'none'",
            'media-src' => "'self'",
            'form-action' => "'self'",
            'frame-ancestors' => "'none'",
            'base-uri' => "'self'",
            'upgrade-insecure-requests' => '',
        ];
    }

    public function getSecurityHeaders(): array
    {
        return [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
            'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
        ];
    }

    public function sanitizeInput(string $input): string
    {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    public function validateFileUpload(string $filePath, array $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf']): bool
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        return in_array($extension, $allowedTypes);
    }

    public function generateSecureToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    public function logSecurityEvent(string $event, array $context = []): void
    {
        Log::channel('security')->warning($event, $context);
    }
}
