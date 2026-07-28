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

    public function boot(): void
    {
        if (in_array(config('app.env'), ['local', 'testing'])) {
            \Illuminate\Support\Facades\Http::globalOptions([
                'verify' => false,
            ]);
        }
    }
}
