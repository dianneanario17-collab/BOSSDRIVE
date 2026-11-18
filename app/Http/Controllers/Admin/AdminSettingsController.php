<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\PaymentSetting;
use App\Models\PriceGuide;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminSettingsController extends Controller
{
    public function index(): View
    {
        return view('admin.settings', [
            'settings' => SystemSetting::current(),
            'priceGuides' => PriceGuide::current(),
            'paymentSettings' => PaymentSetting::current(),
        ]);
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
            'rental_contract_text' => ['sometimes', 'required', 'string', 'max:15000'],
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
            'vehicle_categories' => ['sometimes', 'required', 'string', 'max:5000'],
            'vehicle_transmissions' => ['sometimes', 'required', 'string', 'max:5000'],
            'vehicle_fuel_types' => ['sometimes', 'required', 'string', 'max:5000'],
            'vehicle_capacities' => ['sometimes', 'required', 'string', 'max:5000'],
            'pickup_condition_checks' => ['sometimes', 'required', 'string', 'max:5000'],
            'return_condition_checks' => ['sometimes', 'required', 'string', 'max:5000'],
        ]);

        foreach ([
            'vehicle_categories' => ['max_length' => 255, 'reserved_custom' => true],
            'vehicle_transmissions' => ['max_length' => 50, 'reserved_custom' => true],
            'vehicle_fuel_types' => ['max_length' => 50, 'reserved_custom' => true],
            'vehicle_capacities' => ['max_length' => 50, 'reserved_custom' => true],
            'pickup_condition_checks' => ['max_length' => 100, 'reserved_custom' => false],
            'return_condition_checks' => ['max_length' => 100, 'reserved_custom' => false],
        ] as $field => $rules) {
            if (! array_key_exists($field, $data)) {
                continue;
            }

            $options = collect(preg_split('/\R/u', $data[$field]) ?: [])
                ->map(fn (string $option): string => trim($option))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if ($options === [] || ($rules['reserved_custom'] && in_array('__custom__', $options, true))) {
                throw ValidationException::withMessages([
                    $field => $rules['reserved_custom']
                        ? 'Enter at least one valid option, one per line. The reserved value "__custom__" cannot be used.'
                        : 'Enter at least one checklist item, one per line.',
                ]);
            }

            if (count($options) > 40 || collect($options)->contains(fn (string $option): bool => mb_strlen($option) > $rules['max_length'])) {
                throw ValidationException::withMessages([
                    $field => 'Each option is too long or there are too many options.',
                ]);
            }

            $data[$field] = $options;
        }

        $settings = SystemSetting::current();
        $settings->fill($data)->save();
        AuditLog::record('system_settings.updated', $settings, [
            'sections' => ['business', 'agreement', 'booking_policies', 'fees', 'admin_page_notices', 'vehicle_options', 'condition_reports'],
        ]);

        return back()->with('success', 'System settings updated.');
    }
}
