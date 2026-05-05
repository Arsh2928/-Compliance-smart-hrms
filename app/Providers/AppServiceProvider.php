<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
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
        // Use Bootstrap 5 pagination instead of Tailwind (default).
        // Tailwind's w-5/h-5 classes are not loaded, causing SVG arrows to render at full viewport size.
        Paginator::useBootstrapFive();
    }
}
