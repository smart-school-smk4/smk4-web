<?php

namespace App\Providers;

use App\Services\MqttService;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS URLs in non-local environments to avoid mixed content
        if (App::environment('production', 'staging')) {
            URL::forceScheme('https');
        }
    }
}