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
