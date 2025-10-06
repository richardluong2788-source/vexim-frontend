<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, etc.
     */
    public function boot(): void
    {
        // Bỏ cache route cũ (tạm thời)
        $this->clearCachedRoutes();

        // Đăng ký route
        $this->routes(function () {
            // Route API
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // Route Web
            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Xóa route cache để chắc chắn Laravel load web.php
     */
    protected function clearCachedRoutes(): void
    {
        if (file_exists(base_path('bootstrap/cache/routes-v7.php'))) {
            unlink(base_path('bootstrap/cache/routes-v7.php'));
        }
    }
}
