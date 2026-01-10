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
        $setting = \App\Models\GeneralSetting::first();
        
        \Illuminate\Support\Facades\View::share('settings', (object)[
            'site_name' => $setting->site_name ?? config('app.name'),
            'contact_email' => $setting->contact_email ?? '',
            'address' => $setting->address ?? '',
            'currency_name' => $setting->currency_name ?? 'USD',
            'currency_icon' => $setting->currency_icon ?? '$',
            'currency_rate' => $setting->currency_rate ?? 1.0000,
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
