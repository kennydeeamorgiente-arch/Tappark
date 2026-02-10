<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class SecurityHeadersFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Add security headers
        $response = service('response');
        
        // Prevent clickjacking
        $response->setHeader('X-Frame-Options', 'DENY');
        
        // Prevent MIME-type sniffing
        $response->setHeader('X-Content-Type-Options', 'nosniff');
        
        // Enable XSS protection (for older browsers)
        $response->setHeader('X-XSS-Protection', '1; mode=block');
        
        // Content Security Policy (allows your external CDN resources)
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval' " .
               "https://code.jquery.com " .
               "https://cdn.jsdelivr.net " .
               "https://cdnjs.cloudflare.com; " .
               "style-src 'self' 'unsafe-inline' " .
               "https://cdn.jsdelivr.net " .
               "https://cdnjs.cloudflare.com; " .
               "img-src 'self' data: blob:; " .
               "font-src 'self' " .
               "https://cdnjs.cloudflare.com; " .
               "connect-src 'self' " .
               "https://cdn.jsdelivr.net " .
               "https://cdnjs.cloudflare.com; " .
               "frame-ancestors 'none';";
        
        $response->setHeader('Content-Security-Policy', $csp);
        
        // HSTS (only when HTTPS is enabled)
        // Uncomment when you have SSL certificate
        // $response->setHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        
        // Referrer Policy
        $response->setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        // Permissions Policy
        $response->setHeader('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed after
    }
}
