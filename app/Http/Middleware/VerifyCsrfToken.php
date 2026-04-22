<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        // Midtrans callback routes (verify signature instead)
        'payment/callback',
        'midtrans/callback',
        'payment/check-status',
        
        // API endpoints (if any)
        'api/*',
    ];
}
