<?php

/**
 * PhpStorm Metadata for better IDE support
 */

// This file helps IDE understand Laravel's magic methods and facades
namespace PHPSTORM_META {
    override(\Route::class, map([
        'middleware' => \Illuminate\Routing\RouteRegistrar::class,
        'prefix' => \Illuminate\Routing\RouteRegistrar::class,
        'group' => \Illuminate\Routing\Router::class,
    ]));
}
