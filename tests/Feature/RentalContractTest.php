<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RentalContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_save_and_print_their_booking_agreement(): void
    {
        $user = User::factory()->create([
            'name' => 'Jamie Renter',
            'address' => '12 Main Street',
            'city' => 'GMA',
            'province' => 'Cavite',
        ]);
        $vehicle = Vehicle::create([
            'name' => 'Vios',
            'brand_name' => 'Toyota',
            'plate' => 'CON-100',
            'price' => 1800,
            'status' => 'rented',
        ]);
        $reservation = $this->createReservation($user, $vehicle);
        SystemSetting::current()->update([
            'business_name' => 'BossDrive Rentals',
            'pickup_location' => 'GMA Garage',
            'agreement_text' => 'Booking-only terms.',
            'rental_contract_text' => "Printable terms for {business_name}.\nDeposit: ₱{deposit_amount}",
            'rental_contract_template' => "CUSTOM RENTAL AGREEMENT\nOwner: {owner_address}\nBooking: {control_number}\nRenter: {renter_name}\nVehicle: {vehicle_make} {vehicle_model}\nTerms:\n{agreement_terms}",
            'security_deposit' => 1500,
        ]);

        $this->actingAs($user)
            ->get(route('user.active-rental'))
            ->assertOk()
            ->assertSee(route('user.active-rental.contract', $reservation), false);

        $this->get(route('user.active-rental.contract', $reservation))
            ->assertOk()
            ->assertSee('CUSTOM RENTAL AGREEMENT')
            ->assertSee($reservation->control_number)
            ->assertSee('Jamie Renter')
            ->assertSee('Toyota')
            ->assertSee('GMA Garage')
            ->assertSee('₱1,500.00')
            ->assertSee('Printable terms for BossDrive Rentals.')
            ->assertSee('12 Main Street');

        $response = $this->put(route('user.active-rental.contract.update', $reservation), [
            'agreement_date' => '2026-11-18',
            'renter_address' => '12 Main Street, GMA, Cavite',
            'vehicle_year' => '2024',
            'vehicle_color' => 'Silver',
            'vehicle_vin' => 'VIN-123',
            'vehicle_mileage' => '23500',
            'mileage_unlimited' => '0',
            'mileage_limit' => '200',
            'allowed_area' => 'GMA to Tagaytay',
            'print_after_save' => '1',
        ]);

        $response->assertRedirect(route('user.active-rental.contract', $reservation).'?print=1')
            ->assertSessionHas('success');

        $this->assertSame([
            'agreement_date' => '2026-11-18',
            'renter_address' => '12 Main Street, GMA, Cavite',
            'vehicle_year' => '2024',
            'vehicle_color' => 'Silver',
            'vehicle_vin' => 'VIN-123',
            'vehicle_mileage' => '23500',
            'mileage_unlimited' => false,
            'mileage_limit' => '200',
            'allowed_area' => 'GMA to Tagaytay',
        ], $reservation->fresh()->rental_contract_data);

        $this->get($response->headers->get('Location'))
            ->assertOk()
            ->assertSee('window.print()', false)
            ->assertSee('GMA to Tagaytay')
            ->assertSee('VIN-123');
    }

    public function test_customer_cannot_view_or_change_another_renters_contract(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $reservation = $this->createReservation($owner);

        $this->actingAs($otherUser)
            ->get(route('user.active-rental.contract', $reservation))
            ->assertForbidden();

        $this->put(route('user.active-rental.contract.update', $reservation), [])
            ->assertForbidden();

        $this->assertNull($reservation->fresh()->rental_contract_data);
    }

    public function test_admin_can_open_a_renters_saved_agreement_from_reservations(): void
    {
        $user = User::factory()->create(['name' => 'Jamie Renter']);
        $reservation = $this->createReservation($user);
        $reservation->update([
            'rental_contract_data' => [
                'agreement_date' => '2026-11-18',
                'renter_address' => '12 Main Street, GMA, Cavite',
                'vehicle_year' => '2024',
                'vehicle_color' => 'Silver',
                'vehicle_vin' => 'VIN-123',
                'vehicle_mileage' => '23500',
                'mileage_unlimited' => false,
                'mileage_limit' => '200',
                'allowed_area' => 'GMA to Tagaytay',
            ],
        ]);

        $this->withSession(['is_admin' => true])
            ->get(route('admin.reservations'))
            ->assertOk()
            ->assertSee('View Agreement')
            ->assertSee('reservation-action-agreement');

        $this->withSession(['is_admin' => true])
            ->getJson(route('admin.reservations.live'))
            ->assertOk()
            ->assertJsonPath('0.rentalContractUrl', route('admin.reservations.contract', $reservation));

        $this->withSession(['is_admin' => true])
            ->get(route('admin.reservations.contract', $reservation))
            ->assertOk()
            ->assertSee('Jamie Renter')
            ->assertSee('12 Main Street, GMA, Cavite')
            ->assertSee('VIN-123')
            ->assertSee('GMA to Tagaytay')
            ->assertSee('Print Agreement');
    }

    public function test_limited_mileage_requires_a_limit_before_saving(): void
    {
        $user = User::factory()->create();
        $reservation = $this->createReservation($user);

        $this->actingAs($user)
            ->from(route('user.active-rental.contract', $reservation))
            ->put(route('user.active-rental.contract.update', $reservation), [
                'agreement_date' => '2026-11-18',
                'renter_address' => '12 Main Street',
                'mileage_unlimited' => '0',
                'allowed_area' => 'GMA',
            ])
            ->assertSessionHasErrors('mileage_limit');

        $this->assertNull($reservation->fresh()->rental_contract_data);
    }

    private function createReservation(User $user, ?Vehicle $vehicle = null): Reservation
    {
        return Reservation::create([
            'user_id' => $user->id,
            'vehicle_id' => $vehicle?->id,
            'customer_name' => $user->name,
            'customer_phone' => '09123456789',
            'vehicle' => $vehicle?->name ?? 'Rental Vehicle',
            'service_option' => 'pickup',
            'driver_option' => 'self_drive',
            'pickup_date' => now()->toDateString(),
            'pickup_time' => '09:00',
            'return_date' => now()->addDay()->toDateString(),
            'return_time' => '09:00',
            'document_paths' => [],
            'privacy_consent' => true,
            'payment_mode' => 'deposit',
            'total_amount' => 3600,
            'paid_amount' => 1000,
            'control_number' => 'CONTRACT-'.$user->id.'-'.uniqid(),
            'status' => 'verified',
        ]);
    }
}
