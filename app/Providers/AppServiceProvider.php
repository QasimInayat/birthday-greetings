<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
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
        Schema::defaultStringLength(191);

        // Single source of truth lives in config/templates.php
        View::share('templateTypes', config('templates.types'));
        View::share('automatedTypes', config('templates.automated'));

        // Welcome / farewell fire from employee record changes.
        \App\Models\Employee::observe(\App\Observers\EmployeeObserver::class);
    }
}
