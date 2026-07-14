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
        // Fix for precision issues in JSON serialization of floats
        ini_set('serialize_precision', -1);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //Fix problema de redondeo
        ini_set('serialize_precision', -1);
    }
}
