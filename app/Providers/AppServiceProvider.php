<?php

namespace App\Providers;

use App\Models\SystemSetting;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
        View::composer([
            'admin.dashboard',
            'admin.users',
            'admin.reservations',
            'admin.billing',
            'admin.active-rentals',
            'admin.vehicles',
            'admin.drivers',
            'admin.reports',
        ], function ($view): void {
            if (request()->routeIs('admin.*')) {
                $view->with('adminPageNotices', SystemSetting::current()->admin_page_notices ?? []);
            }
        });
    }
}
