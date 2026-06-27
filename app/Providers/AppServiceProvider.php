<?php

namespace App\Providers;

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
        // Enforce strict mode for database security (prevents mass assignment vulnerabilities and silent discards)
        \Illuminate\Database\Eloquent\Model::shouldBeStrict();
    }
}
