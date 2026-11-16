<?php

namespace Tests\Feature;

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
            ->assertSee('name="agreement_text"', false);
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
                'security_deposit' => 1500,
                'booking_window_days' => 10,
                'max_rental_days' => 5,
                'min_city_days' => 1,
                'min_province_days' => 2,
                'min_long_distance_days' => 3,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('system_settings', [
            'id' => 1,
            'business_name' => 'BossDrive Rentals',
            'agreement_text' => "Read the rules.\nDeposit: ₱{deposit_amount}",
            'security_deposit' => 1500,
            'booking_window_days' => 10,
            'max_rental_days' => 5,
            'min_province_days' => 2,
            'min_long_distance_days' => 3,
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
        ]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('user.reservations'))
            ->assertOk()
            ->assertSee('BossDrive Rentals')
            ->assertSee('₱1,500')
            ->assertSee('"security_deposit":1500', false)
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
                'security_deposit' => 1000,
                'booking_window_days' => 7,
                'max_rental_days' => 2,
                'min_city_days' => 1,
                'min_province_days' => 1,
                'min_long_distance_days' => 3,
            ])
            ->assertSessionHasErrors('min_long_distance_days');

        $this->assertDatabaseMissing('system_settings', ['id' => 1]);
    }
}
