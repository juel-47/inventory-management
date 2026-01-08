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
        \Illuminate\Support\Facades\View::share('settings', (object)[
            'site_name' => config('app.name'),
            'logo' => null,
            'favicon' => null,
        ]);

        // \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
        //     return $user->hasRole('Admin') || $user->hasPermissionTo('Admin') ? true : null;
        // });
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->hasRole('Admin') ? true : null;
        });
        
    }
}
