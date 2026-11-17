<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSettingsController extends Controller
{
    public function index(): View
    {
        return view('admin.settings', ['settings' => SystemSetting::current()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'support_name' => ['required', 'string', 'max:255'],
            'support_phone' => ['required', 'string', 'max:30'],
            'messenger_url' => ['nullable', 'url', 'max:255'],
            'pickup_location' => ['required', 'string', 'max:255'],
            'agreement_text' => ['required', 'string', 'max:15000'],
            'rental_location_guide' => ['required', 'string', 'max:5000'],
            'security_deposit' => ['required', 'integer', 'min:0', 'max:100000'],
            'booking_window_days' => ['required', 'integer', 'min:1', 'max:90'],
            'max_rental_days' => ['required', 'integer', 'min:1', 'max:30'],
            'min_city_days' => ['required', 'integer', 'min:1', 'max:30', 'lte:max_rental_days', 'lte:booking_window_days'],
            'min_province_days' => ['required', 'integer', 'min:1', 'max:30', 'lte:max_rental_days', 'lte:booking_window_days'],
            'min_long_distance_days' => ['required', 'integer', 'min:1', 'max:30', 'lte:max_rental_days', 'lte:booking_window_days'],
            'driver_daily_fee' => ['required', 'numeric', 'min:0', 'max:100000'],
            'province_gas_surcharge' => ['required', 'numeric', 'min:0', 'max:100000'],
            'province_diesel_surcharge' => ['required', 'numeric', 'min:0', 'max:100000'],
            'long_distance_surcharge' => ['required', 'numeric', 'min:0', 'max:100000'],
            'admin_page_notices' => ['nullable', 'array'],
            'admin_page_notices.*' => ['nullable', 'string', 'max:500'],
        ]);

        $settings = SystemSetting::current();
        $settings->fill($data)->save();
        AuditLog::record('system_settings.updated', $settings, [
            'sections' => ['business', 'agreement', 'booking_policies', 'fees', 'admin_page_notices'],
        ]);

        return back()->with('success', 'System settings updated.');
    }
}
