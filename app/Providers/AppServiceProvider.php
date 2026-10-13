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
        // Keep verification limited to the existing local store administrator.
        \Illuminate\Support\Facades\Gate::define('verify-payments', fn(\App\Models\User $user)=>$user->email==='admin@tehani.local');
    }
}
