<?php

namespace App\Providers;

use App\Services\MqttService;
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
        // Jika perlu menambahkan route binding atau middleware khusus
    }
}