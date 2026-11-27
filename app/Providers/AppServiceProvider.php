<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

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
        Paginator::useTailwind();

        // Eager-load employee & office once per authenticated layout render when not already loaded
        View::composer('layouts.app', function () {
            if (Auth::check()) {
                Auth::user()->loadMissing('employee.office');
            }
        });

        // Support HTTPS reverse proxies and tunnels (Cloudflare Tunnel, ngrok, Cloud VPS)
        if (
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
            (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
        ) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
