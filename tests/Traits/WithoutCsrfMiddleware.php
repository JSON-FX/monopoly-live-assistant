<?php

namespace Tests\Traits;

trait WithoutCsrfMiddleware
{
    /**
     * Disable CSRF middleware for route protection tests.
     * 
     * This is commonly needed when testing route protection to bypass
     * CSRF token validation and focus on authentication checks.
     */
    private function withoutCsrfMiddleware(): self
    {
        return $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class
        ]);
    }
} 