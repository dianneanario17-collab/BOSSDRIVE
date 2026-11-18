<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_profile_links_to_settings_and_settings_page_shows_editable_sections(): void
    {
        $this->withSession(['is_admin' => true])
            ->get(route('admin.profile'))
            ->assertOk()
            ->assertSee(route('admin.settings'), false);

        $this->withSession(['is_admin' => true])
            ->get(route('admin.settings'))
            ->assertOk()
            ->assertSee('Rental Agreement & Rules')
            ->assertSee('Booking Policies')
            ->assertSee('Payment Settings')
            ->assertSee('Price Guide')
            ->assertSee('Add Vehicle / Edit Vehicle Details')
            ->assertSee('Driver Management')
            ->assertSee('name="driver_daily_fee"', false)
            ->assertSee('name="admin_page_notices[dashboard]"', false)
            ->assertSee('name="pickup_condition_checks"', false)
            ->assertSee('name="return_condition_checks"', false)
            ->assertSee('id="price-guide"', false)
            ->assertSee('name="prices[0][city_driving]"', false)
            ->assertSee('id="payment-settings"', false)
            ->assertSee('name="account_name"', false)
            ->assertSee('name="account_number"', false)
            ->assertSee('id="security_deposit" type="number" min="0" max="100000" step="1" name="security_deposit" class="form-control" value="1000"', false)
            ->assertSee('name="name"', false)
            ->assertSee('name="agreement_text"', false)
            ->assertSee('name="rental_contract_text"', false);
    }

    public function test_admin_can_update_persistent_business_agreement_and_booking_settings(): void
    {
        $this->withSession(['is_admin' => true])
            ->put(route('admin.settings.update'), [
                'business_name' => 'BossDrive Rentals',
                'support_name' => 'Support Team',
                'support_phone' => '+63 900 000 0000',
                'messenger_url' => 'https://www.facebook.com/example',
                'pickup_location' => 'New Garage, Cavite',
                'agreement_text' => "Read the rules.\nDeposit: ₱{deposit_amount}",
                'rental_contract_text' => "Printed agreement terms.\nDeposit: ₱{deposit_amount}",
                'rental_location_guide' => "City: nearby\nProvince: outside",
                'security_deposit' => 1500,
                'booking_window_days' => 10,
                'max_rental_days' => 5,
                'min_city_days' => 1,
                'min_province_days' => 2,
                'min_long_distance_days' => 3,
                'driver_daily_fee' => 1250,
                'province_gas_surcharge' => 1100,
                'province_diesel_surcharge' => 600,
                'long_distance_surcharge' => 1600,
                'admin_page_notices' => [
                    'dashboard' => 'Admin dashboard notice',
                    'vehicles' => 'Check vehicle records before saving.',
                ],
                'vehicle_categories' => "Sedan\nMinibus\nSUV",
                'vehicle_transmissions' => "Manual\nAutomatic\nCVT",
                'vehicle_fuel_types' => "Gasoline\nDiesel\nHybrid",
                'vehicle_capacities' => "4 Seater\n5 Seater\n8 Seater",
                'pickup_condition_checks' => "Fuel checked\nPickup photos taken",
                'return_condition_checks' => "Fuel checked\nReturn photos taken",
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('system_settings', [
            'id' => 1,
            'business_name' => 'BossDrive Rentals',
            'agreement_text' => "Read the rules.\nDeposit: ₱{deposit_amount}",
            'rental_contract_text' => "Printed agreement terms.\nDeposit: ₱{deposit_amount}",
            'rental_location_guide' => "City: nearby\nProvince: outside",
            'security_deposit' => 1500,
            'booking_window_days' => 10,
            'max_rental_days' => 5,
            'min_province_days' => 2,
            'min_long_distance_days' => 3,
            'driver_daily_fee' => 1250,
            'province_gas_surcharge' => 1100,
            'province_diesel_surcharge' => 600,
            'long_distance_surcharge' => 1600,
        ]);
        $this->assertSame('Admin dashboard notice', SystemSetting::current()->admin_page_notices['dashboard']);
        $this->assertSame(['Sedan', 'Minibus', 'SUV'], SystemSetting::current()->vehicle_categories);
        $this->assertSame(['Manual', 'Automatic', 'CVT'], SystemSetting::current()->vehicle_transmissions);
        $this->assertSame(['Gasoline', 'Diesel', 'Hybrid'], SystemSetting::current()->vehicle_fuel_types);
        $this->assertSame(['4 Seater', '5 Seater', '8 Seater'], SystemSetting::current()->vehicle_capacities);
        $this->assertSame(['Fuel checked', 'Pickup photos taken'], SystemSetting::current()->pickup_condition_checks);
        $this->assertSame(['Fuel checked', 'Return photos taken'], SystemSetting::current()->return_condition_checks);
    }

    public function test_configured_condition_checklists_are_used_and_required_on_customer_and_staff_forms(): void
    {
        SystemSetting::current()->update([
            'pickup_condition_checks' => ['Pickup fuel recorded', 'Pickup body inspected'],
            'return_condition_checks' => ['Return fuel recorded', 'Return body inspected'],
        ]);
        $user = User::factory()->create();
        $reservation = Reservation::create([
            'user_id' => $user->id,
            'vehicle' => 'Settings Checklist Vehicle',
            'service_option' => 'pickup',
            'driver_option' => 'self_drive',
            'pickup_date' => now()->subDay()->toDateString(),
            'return_date' => now()->addDay()->toDateString(),
            'document_paths' => [],
            'privacy_consent' => true,
            'payment_mode' => 'deposit',
            'control_number' => 'SETTINGS-CHECKLIST-1',
            'status' => 'released',
        ]);

        $this->withSession(['is_admin' => true])
            ->get(route('admin.reservations'))
            ->assertOk()
            ->assertSee('Return fuel recorded')
            ->assertSee('Return body inspected');

        $this->withSession(['is_staff' => true])
            ->get(route('staff.reservations'))
            ->assertOk()
            ->assertSee('Return fuel recorded')
            ->assertSee('Return body inspected');

        $this->actingAs($user)
            ->get(route('user.active-rental'))
            ->assertOk()
            ->assertSee('Pickup fuel recorded')
            ->assertSee('Pickup body inspected');

        $this->actingAs($user)
            ->post(route('user.active-rental.condition', $reservation), [
                'checks' => ['Pickup fuel recorded'],
            ])
            ->assertUnprocessable();
        $this->assertDatabaseCount('pickup_condition_reports', 0);

        $this->post(route('user.active-rental.condition', $reservation), [
            'checks' => ['Pickup fuel recorded', 'Pickup body inspected'],
        ])->assertRedirect();
        $this->assertDatabaseHas('pickup_condition_reports', [
            'reservation_id' => $reservation->id,
            'checks' => json_encode(['Pickup fuel recorded', 'Pickup body inspected']),
        ]);
    }

    public function test_booking_window_setting_is_reflected_in_vehicle_availability(): void
    {
        SystemSetting::current()->update(['booking_window_days' => 10]);
        $vehicle = Vehicle::create([
            'name' => 'Settings Window Vehicle',
            'plate' => 'SET 1001',
            'price' => 1800,
            'status' => 'available',
        ]);

        $availability = $this->getJson(route('vehicles.availability'))->assertOk()->json();
        $listedVehicle = collect($availability['vehicles'])->firstWhere('id', $vehicle->id);

        $this->assertSame(now()->addDays(10)->toDateString(), $listedVehicle['bookingWindow']['end']);
    }

    public function test_updated_business_and_deposit_settings_are_shown_to_renters(): void
    {
        SystemSetting::current()->update([
            'business_name' => 'BossDrive Rentals',
            'support_name' => 'Rental Support',
            'support_phone' => '+63 900 000 0000',
            'pickup_location' => 'New Garage, Cavite',
            'security_deposit' => 1500,
            'driver_daily_fee' => 1250,
            'province_gas_surcharge' => 1100,
        ]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('user.reservations'))
            ->assertOk()
            ->assertSee('BossDrive Rentals')
            ->assertSee('₱1,500')
            ->assertSee('"security_deposit":1500', false)
            ->assertSee('"driver_daily_fee":1250', false)
            ->assertSee('With Driver (+₱1,250/day)')
            ->assertSee('New Garage, Cavite');

        $this->get(route('user.dashboard'))
            ->assertOk()
            ->assertSee('Rental Support')
            ->assertSee('+63 900 000 0000')
            ->assertSee('New Garage, Cavite');
    }

    public function test_minimum_rental_duration_cannot_exceed_maximum_rental_duration(): void
    {
        $this->withSession(['is_admin' => true])
            ->from(route('admin.settings'))
            ->put(route('admin.settings.update'), [
                'business_name' => 'BossDrive',
                'support_name' => 'Support',
                'support_phone' => '09123456789',
                'messenger_url' => 'https://www.facebook.com/example',
                'pickup_location' => 'Garage',
                'agreement_text' => 'Agreement',
                'rental_location_guide' => 'City: nearby',
                'security_deposit' => 1000,
                'booking_window_days' => 7,
                'max_rental_days' => 2,
                'min_city_days' => 1,
                'min_province_days' => 1,
                'min_long_distance_days' => 3,
                'driver_daily_fee' => 1000,
                'province_gas_surcharge' => 1000,
                'province_diesel_surcharge' => 500,
                'long_distance_surcharge' => 1500,
            ])
            ->assertSessionHasErrors('min_long_distance_days');

        $this->assertDatabaseMissing('system_settings', ['id' => 1]);
    }

    public function test_admin_can_add_a_driver_from_settings_and_driver_is_saved(): void
    {
        $this->withSession(['is_admin' => true])
            ->post(route('admin.drivers.store'), [
                'name' => 'Jordan Driver',
                'contact' => '09123456789',
                'return_to' => 'settings',
            ])
            ->assertRedirect(route('admin.settings'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('on_call_drivers', [
            'name' => 'Jordan Driver',
            'contact' => '09123456789',
            'available_this_week' => true,
        ]);
    }

    public function test_admin_page_notice_is_rendered_from_settings(): void
    {
        SystemSetting::current()->update([
            'admin_page_notices' => ['dashboard' => 'Remember to review the weekly report.'],
        ]);

        $this->withSession(['is_admin' => true])
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Remember to review the weekly report.');
    }

    public function test_location_surcharges_and_minimum_days_are_reflected_in_rate_guides(): void
    {
        SystemSetting::current()->update([
            'province_gas_surcharge' => 1200,
            'province_diesel_surcharge' => 650,
            'long_distance_surcharge' => 1700,
            'min_long_distance_days' => 3,
        ]);

        $this->withSession(['is_admin' => true])
            ->get(route('admin.vehicles'))
            ->assertOk()
            ->assertSee('₱3,700')
            ->assertSee('₱4,200');

        $this->get(route('reservations'))
            ->assertOk()
            ->assertSee('₱3,700')
            ->assertSee('Long Distance (3 Days)');
    }

    public function test_custom_vehicle_options_are_available_in_add_and_edit_forms(): void
    {
        SystemSetting::current()->update([
            'vehicle_categories' => ['Sedan', 'Minibus'],
            'vehicle_transmissions' => ['Manual', 'CVT'],
            'vehicle_fuel_types' => ['Gasoline', 'Hybrid'],
            'vehicle_capacities' => ['5 Seater', '8 Seater'],
        ]);

        $this->withSession(['is_admin' => true])
            ->get(route('admin.vehicles'))
            ->assertOk()
            ->assertSee('value="Minibus"', false)
            ->assertSee('value="CVT"', false)
            ->assertSee('value="Hybrid"', false)
            ->assertSee('value="8 Seater"', false);
    }
}
