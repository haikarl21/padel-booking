<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * LocalhostSecurityMiddleware
 * 
 * Middleware untuk menangani security di localhost development
 * Khususnya untuk HTTPS/HTTP switching dan CORS issues
 */
class LocalhostSecurityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Di localhost, allow semua untuk development
        // Di production, ini harus lebih strict
        
        $isProduction = config('app.env') === 'production';
        
        if (!$isProduction) {
            // Development environment
            // Allow localhost testing tanpa HTTPS verification
            
            // Header untuk allow CORS di localhost
            if ($request->getHost() === 'localhost' || $request->getHost() === '127.0.0.1') {
                // Allow dari localhost
            }
        }
        
        $response = $next($request);
        
        // Add security headers
        if (!$isProduction) {
            // Development headers - lebih lenient
            $response->header('X-Frame-Options', 'SAMEORIGIN');
            $response->header('X-Content-Type-Options', 'nosniff');
            $response->header('X-XSS-Protection', '1; mode=block');
        } else {
            // Production headers - strict
            $response->header('X-Frame-Options', 'DENY');
            $response->header('X-Content-Type-Options', 'nosniff');
            $response->header('X-XSS-Protection', '1; mode=block');
            $response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }
        
        return $response;
    }
}
