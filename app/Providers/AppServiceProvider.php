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

        View::share('templateTypes', [
                    'birthday' => 'Birthday',
                    'anniversary' => 'Anniversary',
                    'welcome' => 'Welcome',
                    'farewell' => 'Farewell',
                    'general' => 'General'
                    ]
        );
    }
}
