<?php

namespace App\Providers;

use App\Services\LinkStorageService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Re-resolved per request so tests can override the storage path
        // via config before each request is handled.
        $this->app->bind(LinkStorageService::class, function () {
            return new LinkStorageService;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Simple per-IP rate limit for the shorten endpoint.
        RateLimiter::for('shorten', function (Request $request) {
            $perMinute = (int) config('cuturl.shorten_rate_limit', 20);

            return Limit::perMinute($perMinute)->by($request->ip());
        });
    }
}
