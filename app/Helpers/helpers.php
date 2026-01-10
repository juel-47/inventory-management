<?php

if (!function_exists('setActive')) {
    /**
     * Set active class for sidebar menu.
     *
     * @param array $routes
     * @param string $class
     * @return string
     */
    function setActive(array $routes, string $class = 'active'): string
    {
        foreach ($routes as $route) {
            if (request()->routeIs($route)) {
                return $class;
            }
        }

        return '';
    }
}

if (!function_exists('getSettings')) {
    function getSettings()
    {
        return \App\Models\GeneralSetting::first() ?? (object)[
            'site_name' => config('app.name'),
            'currency_name' => 'USD',
            'currency_icon' => '$',
            'currency_rate' => 1.0000,
        ];
    }
}

if (!function_exists('formatWithCurrency')) {
    /**
     * Format amount with base currency (USD) and local currency.
     *
     * @param float|int $amount
     * @return string
     */
    function formatWithCurrency($amount)
    {
        $settings = getSettings();
        $basePrice = '$' . number_format($amount, 2);
        
        if ($settings->currency_name != 'USD' && $settings->currency_rate != 1) {
            $localAmount = $amount * $settings->currency_rate;
            $localPrice = $settings->currency_icon . number_format($localAmount, 2);
            return $basePrice . ' (' . $localPrice . ')';
        }

        return $basePrice;
    }
}
