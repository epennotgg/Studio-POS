<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Http\Middleware\RateLimitMiddleware;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\ForceHttpsMiddleware;
use App\Http\Middleware\SecurityHeadersMiddleware;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register middleware aliases
        $router = $this->app['router'];
        $router->aliasMiddleware('ratelimit', RateLimitMiddleware::class);
        $router->aliasMiddleware('admin', AdminMiddleware::class);
        $router->aliasMiddleware('force.https', ForceHttpsMiddleware::class);
        $router->aliasMiddleware('security.headers', SecurityHeadersMiddleware::class);
    }
}
