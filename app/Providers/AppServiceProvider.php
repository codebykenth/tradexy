<?php

namespace App\Providers;

use App\Listeners\ActivityLogSubscriber;
use App\Models\Balance;
use App\Models\Strategy;
use App\Models\Trade;
use App\Observers\ModelActivityObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

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
        // Activity Logging
        \Illuminate\Support\Facades\Event::subscribe(ActivityLogSubscriber::class);
        
        Trade::observe(ModelActivityObserver::class);
        Balance::observe(ModelActivityObserver::class);
        Strategy::observe(ModelActivityObserver::class);

        // Only users with id === 1 can access developer routes
        Gate::define('developer', fn($user) => $user->developer());

        RateLimiter::for('ai-analysis', function (Request $request) {
            return $request->user()?->developer() ? Limit::none() : Limit::perDay(1)->by($request->user()?->id ?: $request->ip())->response(function (Request $request, array $headers) {
                return response('You can generate 1 AI analysis per day.', 429, $headers);
            });
        });

        RateLimiter::for('read', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('write', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });
    }
}
