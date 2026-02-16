<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, $maxAttempts = 5, $decayMinutes = 1): Response
    {
        // Convert parameters to integers
        $maxAttempts = (int) $maxAttempts;
        $decayMinutes = (int) $decayMinutes;
        
        // Only apply rate limiting to login endpoint
        if ($request->is('login') && $request->isMethod('post')) {
            $key = 'login_attempts:' . $request->ip();
            
            $attempts = Cache::get($key, 0);
            
            if ($attempts >= $maxAttempts) {
                return response()->json([
                    'error' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam ' . $decayMinutes . ' menit.'
                ], 429);
            }
            
            Cache::put($key, $attempts + 1, now()->addMinutes($decayMinutes));
        }
        
        $response = $next($request);
        
        // Reset attempts on successful login
        if ($request->is('login') && $request->isMethod('post') && Auth::check()) {
            Cache::forget('login_attempts:' . $request->ip());
        }
        
        return $response;
    }
}
