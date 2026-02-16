<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class ForceHttpsMiddleware
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
        // Only force HTTPS in production environment when SSL is available
        if (App::environment('production') && !$request->secure() && $this->hasSsl()) {
            return redirect()->secure($request->getRequestUri());
        }

        return $next($request);
    }

    /**
     * Check if SSL is available
     */
    private function hasSsl(): bool
    {
        // Check if HTTPS is enabled in the environment
        $https = env('HTTPS', false);
        $appUrl = env('APP_URL', '');
        
        // Check if APP_URL starts with https://
        return $https === true || str_starts_with($appUrl, 'https://');
    }
}