<?php

namespace App\Providers;

use App\Services\Nutrisi\LayananVisionNutrisi;
use App\Services\Nutrisi\LayananVisionNutrisiHttp;
use App\Services\Nutrisi\StubLayananVisionNutrisi;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(LayananVisionNutrisi::class, function () {
            if (config('nutrisi.penyedia') !== 'http') {
                return new StubLayananVisionNutrisi;
            }

            return new LayananVisionNutrisiHttp(
                config('nutrisi.http.url'),
                config('nutrisi.http.timeout'),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        RateLimiter::for('masuk', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('email').'|'.$request->ip());
        });

        RateLimiter::for('lupa-sandi', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('email').'|'.$request->ip());
        });

        RateLimiter::for('analisis', function (Request $request) {
            return Limit::perHour(20)->by($request->user()?->id ?: $request->ip());
        });
    }
}
