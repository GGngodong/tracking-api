<?php

namespace App\Providers;

use App\Models\PermitLetters;
use App\Observers\PermitLetterObserver;
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
        PermitLetters::observe(PermitLetterObserver::class);
    }
}
