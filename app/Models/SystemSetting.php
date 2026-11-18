<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'business_name',
        'support_name',
        'support_phone',
        'messenger_url',
        'pickup_location',
        'agreement_text',
        'rental_contract_text',
        'rental_location_guide',
        'security_deposit',
        'booking_window_days',
        'max_rental_days',
        'min_city_days',
        'min_province_days',
        'min_long_distance_days',
        'driver_daily_fee',
        'province_gas_surcharge',
        'province_diesel_surcharge',
        'long_distance_surcharge',
        'admin_page_notices',
        'vehicle_categories',
        'vehicle_transmissions',
        'vehicle_fuel_types',
        'vehicle_capacities',
        'pickup_condition_checks',
        'return_condition_checks',
    ];

    protected function casts(): array
    {
        return [
            'security_deposit' => 'integer',
            'booking_window_days' => 'integer',
            'max_rental_days' => 'integer',
            'min_city_days' => 'integer',
            'min_province_days' => 'integer',
            'min_long_distance_days' => 'integer',
            'driver_daily_fee' => 'decimal:2',
            'province_gas_surcharge' => 'decimal:2',
            'province_diesel_surcharge' => 'decimal:2',
            'long_distance_surcharge' => 'decimal:2',
            'admin_page_notices' => 'array',
            'vehicle_categories' => 'array',
            'vehicle_transmissions' => 'array',
            'vehicle_fuel_types' => 'array',
            'vehicle_capacities' => 'array',
            'pickup_condition_checks' => 'array',
            'return_condition_checks' => 'array',
        ];
    }

    public static function defaults(): array
    {
        $agreementText = implode("\n", [
            'Please read carefully before proceeding:',
            'Document Requirements: Renter must provide two (2) valid Government IDs and a Proof of Billing (Electric/Water bill) under their name or immediate family.',
            'Accidents & Damages: Any damage to the vehicle during the rental period is the sole responsibility of the renter. The renter shall pay for all repair costs and a Loss of Use fee equivalent to the daily rate while the vehicle is in the shop.',
            'Liability: The Renter shall be held liable for any loss or damage to the Vehicle parts, body, tools, or accessories, as well as any personal belongings of the Owner left inside the Vehicle, sustained during the rental period. The Renter shall likewise be liable for any traffic violations incurred while the vehicle is in their possession; {business_name} reserves the right to collect the equivalent penalty from the Renter. Use of the Vehicle for illegal purposes, towing, or driving instruction/training is strictly prohibited. Additional mileage and/or time charges apply when the vehicle is used beyond the agreed place or rental time without prior arrangement.',
            'Payment Policy: We strictly implement a Pay Before Drive policy. A ₱{deposit_amount} security deposit is required for reservation. Online payments are accepted via GCash only.',
            'Late Returns: Overtime returns will be charged an additional hourly rate. If the delay exceeds 5 hours, a full day rate will be applied.',
            'Carnapping Policy: Failure to return the vehicle or contact management within 24 hours of the deadline will be reported to the PNP-HPG as a Carnapping/Theft case.',
            'Maintenance: The renter must ensure the vehicle has enough oil and coolant. Negligence leading to engine failure will be charged to the renter.',
        ]);

        return [
            'business_name' => 'Big Boss Car Rental',
            'support_name' => 'Big Boss Dispatcher',
            'support_phone' => '+63 912 345 6789',
            'messenger_url' => 'https://www.facebook.com/profile.php?id=100064277850431',
            'pickup_location' => 'Big Boss Garage, GMA, Cavite',
            'agreement_text' => $agreementText,
            'rental_contract_text' => $agreementText,
            'rental_location_guide' => implode("\n", [
                'City Driving: GMA, Carmona, Biñan, San Pedro, Dasmariñas, Silang, General Trias, Imus, Bacoor, Muntinlupa, Parañaque, Pasay, Makati, Taguig',
                'Province: Tagaytay, Batangas, Laguna, Rizal, Cavite farther areas, Quezon',
                'Long Distance: Baguio, La Union, Vigan, Ilocos, Nueva Ecija, Pangasinan, Bicol, Legazpi',
            ]),
            'security_deposit' => 1000,
            'booking_window_days' => 7,
            'max_rental_days' => 7,
            'min_city_days' => 1,
            'min_province_days' => 1,
            'min_long_distance_days' => 2,
            'driver_daily_fee' => 1000,
            'province_gas_surcharge' => 1000,
            'province_diesel_surcharge' => 500,
            'long_distance_surcharge' => 1500,
            'admin_page_notices' => [],
            'vehicle_categories' => [
                'Sedan',
                'Pick-up / Expanded (7-Seater) (2 Days) — Gas',
                'Expanded — Diesel',
            ],
            'vehicle_transmissions' => ['Manual', 'Automatic'],
            'vehicle_fuel_types' => ['Gasoline', 'Diesel'],
            'vehicle_capacities' => ['4 Seater', '5 Seater', '6 Seater', '7 Seater'],
            'pickup_condition_checks' => [
                'Fuel level checked and noted',
                'Exterior/body inspected',
                'Interior checked and clean',
                'Accessories/tools verified',
                'I confirm the vehicle condition',
            ],
            'return_condition_checks' => [
                'Fuel level checked',
                'Exterior/body inspected',
                'Interior checked and clean',
                'Accessories/tools verified',
                'I confirm the vehicle condition',
            ],
        ];
    }

    public function rateSurcharge(string $rateType, ?string $category = null): float
    {
        return match ($rateType) {
            'province' => str_contains(strtolower((string) $category), 'diesel')
                ? (float) $this->province_diesel_surcharge
                : (float) $this->province_gas_surcharge,
            'long_distance' => (float) $this->long_distance_surcharge,
            default => 0.0,
        };
    }

    public static function hasCompleteChecklist(array $configuredChecks, array $submittedChecks): bool
    {
        return count($submittedChecks) === count($configuredChecks)
            && array_diff($configuredChecks, $submittedChecks) === []
            && array_diff($submittedChecks, $configuredChecks) === [];
    }

    public static function current(): self
    {
        $settings = static::query()->firstOrCreate(['id' => 1], static::defaults());
        $missingDefaults = collect(static::defaults())
            ->filter(fn ($value, string $key): bool => $settings->{$key} === null)
            ->all();

        if ($missingDefaults !== []) {
            $settings->fill($missingDefaults)->save();
        }

        return $settings;
    }
}
