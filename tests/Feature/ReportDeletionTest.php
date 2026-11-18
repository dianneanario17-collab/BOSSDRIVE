<?php

namespace Tests\Feature;

use App\Models\PickupConditionReport;
use App\Models\RentalExtension;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_report_delete_buttons_are_wired_to_persistent_delete_routes(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $cancelled = $this->createReservation($user, 'cancelled');
        $voided = $this->createReservation($user, 'void');
        $pickupReservation = $this->createReservation($user, 'released');
        $photoPath = 'reservations/conditions/report-photo.jpg';
        Storage::disk('public')->put($photoPath, 'photo');
        $pickupReport = PickupConditionReport::create([
            'reservation_id' => $pickupReservation->id,
            'user_id' => $user->id,
            'checks' => ['Vehicle checked'],
            'photo_path' => $photoPath,
        ]);

        $this->withSession(['is_admin' => true])
            ->get(route('admin.reports'))
            ->assertOk()
            ->assertSee('class="management-sticky-overview"', false)
            ->assertSee("deleteRow(this, 'Cancelled Booking', '".route('admin.reservations.destroy', $cancelled), false)
            ->assertSee("deleteRow(this, 'Voided Booking', '".route('admin.reservations.destroy', $voided), false)
            ->assertSee("deleteRow(this, 'Pickup Condition Report', '".route('admin.reports.pickup.destroy', $pickupReport), false);
    }

    public function test_admin_and_staff_report_categories_render_in_sticky_overviews(): void
    {
        foreach ([
            ['admin.reports', 'is_admin'],
            ['staff.reports', 'is_staff'],
        ] as [$routeName, $sessionRole]) {
            $response = $this->withSession([
                'is_admin' => $sessionRole === 'is_admin',
                'is_staff' => $sessionRole === 'is_staff',
            ])->get(route($routeName))->assertOk();

            $this->assertStringContainsString('class="management-sticky-overview"', $response->getContent());
        }
    }

    public function test_admin_reports_include_bookings_payments_fleet_customers_drivers_and_return_checks(): void
    {
        $user = User::factory()->create(['name' => 'Reports Customer']);
        $vehicle = Vehicle::create([
            'name' => 'Reports Sedan',
            'brand_name' => 'Reports Brand',
            'plate' => 'REPORTS-1',
            'price' => 1800,
            'category' => 'Sedan',
            'status' => 'available',
        ]);
        $reservation = Reservation::create([
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'booking_source' => 'user',
            'customer_name' => $user->name,
            'customer_phone' => '09123456789',
            'vehicle' => $vehicle->name,
            'service_option' => 'pickup',
            'rate_type' => 'city',
            'driver_option' => 'with_driver',
            'assigned_driver_name' => 'Reports Driver',
            'assigned_driver_contact' => '09987654321',
            'pickup_date' => now()->toDateString(),
            'return_date' => now()->addDay()->toDateString(),
            'document_paths' => [],
            'privacy_consent' => true,
            'payment_mode' => 'deposit',
            'payment_status' => 'pending',
            'total_amount' => 3600,
            'paid_amount' => 1000,
            'control_number' => 'REPORTS-ALL-1',
            'status' => 'completed',
            'return_condition_checks' => ['Fuel recorded', 'Body inspected'],
            'return_condition_notes' => 'No new damage.',
            'return_condition_checked_at' => now(),
            'return_condition_checked_by' => 'Reports Staff',
            'return_condition_checked_by_role' => 'Staff',
        ]);
        RentalExtension::create([
            'reservation_id' => $reservation->id,
            'days' => 1,
            'amount' => 500,
            'paid_amount' => 200,
            'requested_return_date' => now()->addDays(2)->toDateString(),
            'status' => 'approved',
            'payment_reference_id' => 'EXT-REPORT-1',
        ]);
        PickupConditionReport::create([
            'reservation_id' => $reservation->id,
            'user_id' => $user->id,
            'checks' => ['Pickup check complete'],
        ]);

        $this->withSession(['is_admin' => true])
            ->get(route('admin.reports', [
                'from' => now()->subDay()->toDateString(),
                'to' => now()->addDay()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('name="from"', false)
            ->assertSee('All Bookings')
            ->assertSee('Payment Report')
            ->assertSee('Vehicle Fleet Report')
            ->assertSee('Driver Assignments')
            ->assertSee('Customer Account Report')
            ->assertSee('Vehicle Pickup Condition Reports')
            ->assertSee('Vehicle Return Condition Reports')
            ->assertSee('REPORTS-ALL-1')
            ->assertSee('Reports Customer')
            ->assertSee('Reports Driver')
            ->assertSee('Reports Sedan')
            ->assertSee('Reports Staff')
            ->assertSee('EXT-REPORT-1')
            ->assertSee('Fuel recorded')
            ->assertSee('1/1 Checked')
            ->assertSee('₱4,100.00');
    }

    public function test_admin_can_delete_cancelled_and_voided_reservations_and_pickup_report_records(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $cancelled = $this->createReservation($user, 'cancelled');
        $voided = $this->createReservation($user, 'void');
        $pickupReservation = $this->createReservation($user, 'released');
        $photoPath = 'reservations/conditions/delete-report-photo.jpg';
        Storage::disk('public')->put($photoPath, 'photo');
        $pickupReport = PickupConditionReport::create([
            'reservation_id' => $pickupReservation->id,
            'user_id' => $user->id,
            'checks' => ['Vehicle checked'],
            'photo_path' => $photoPath,
        ]);

        $this->withSession(['is_admin' => true])
            ->deleteJson(route('admin.reservations.destroy', $cancelled))
            ->assertOk();
        $this->assertDatabaseMissing('reservations', ['id' => $cancelled->id]);

        $this->withSession(['is_admin' => true])
            ->deleteJson(route('admin.reservations.destroy', $voided))
            ->assertOk();
        $this->assertDatabaseMissing('reservations', ['id' => $voided->id]);

        $this->withSession(['is_admin' => true])
            ->deleteJson(route('admin.reports.pickup.destroy', $pickupReport))
            ->assertOk();
        $this->assertDatabaseMissing('pickup_condition_reports', ['id' => $pickupReport->id]);
        Storage::disk('public')->assertMissing($photoPath);
    }

    public function test_admin_can_persistently_remove_a_vehicle_feedback_reply(): void
    {
        $vehicle = Vehicle::create([
            'name' => 'Feedback Reply Vehicle',
            'plate' => 'FEEDBACK-1',
            'price' => 1800,
            'status' => 'available',
            'feedbacks' => [[
                'name' => 'Renter',
                'date' => 'Jan 1, 2026',
                'stars' => 5,
                'comment' => 'Great vehicle.',
                'adminReply' => [
                    'text' => 'Thank you!',
                    'by' => 'Admin',
                    'date' => 'Jan 2, 2026',
                ],
            ]],
        ]);

        $this->withSession(['is_admin' => true])
            ->deleteJson(route('admin.vehicles.feedback.reply.destroy', [$vehicle, 0]))
            ->assertOk()
            ->assertJsonPath('feedbacks.0.adminReply', null)
            ->assertJsonPath('feedbacks.0.comment', 'Great vehicle.');

        $this->assertNull($vehicle->fresh()->feedbacks[0]['adminReply']);
    }

    public function test_admin_can_delete_a_completed_reservation_and_vehicle_with_json_responses(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $vehicle = Vehicle::create([
            'name' => 'Completed Rental Vehicle',
            'plate' => 'COMPLETED-1',
            'price' => 1800,
            'status' => 'available',
        ]);
        $completed = $this->createReservation($user, 'completed');
        $completed->update(['vehicle_id' => $vehicle->id]);

        $this->withSession(['is_admin' => true])
            ->deleteJson(route('admin.reservations.destroy', $completed))
            ->assertOk()
            ->assertJsonPath('message', 'Reservation '.$completed->control_number.' was deleted.');
        $this->assertDatabaseMissing('reservations', ['id' => $completed->id]);

        $this->withSession(['is_admin' => true])
            ->deleteJson(route('admin.vehicles.destroy', $vehicle))
            ->assertOk()
            ->assertJsonPath('message', 'Vehicle removed.');
        $this->assertDatabaseMissing('vehicles', ['id' => $vehicle->id]);
    }

    public function test_admin_json_delete_requests_receive_an_authentication_error_when_session_expires(): void
    {
        $reservation = $this->createReservation(User::factory()->create(), 'pending');

        $this->deleteJson(route('admin.reservations.destroy', $reservation))
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Your administrator session has expired. Please sign in again.');
    }

    public function test_urls_use_https_when_the_request_is_forwarded_through_a_secure_proxy(): void
    {
        $this->withHeaders(['X-Forwarded-Proto' => 'https'])
            ->get('http://bossdrive.test/admin/login')
            ->assertOk()
            ->assertSee('action="https://bossdrive.test/admin/login"', false)
            ->assertSee('https://bossdrive.test/image/banner-car.jpg', false);
    }

    private function createReservation(User $user, string $status): Reservation
    {
        return Reservation::create([
            'user_id' => $user->id,
            'vehicle' => 'Report Delete Vehicle',
            'service_option' => 'pickup',
            'driver_option' => 'self_drive',
            'pickup_date' => now()->addDays(10)->toDateString(),
            'return_date' => now()->addDays(11)->toDateString(),
            'document_paths' => [],
            'privacy_consent' => true,
            'payment_mode' => 'deposit',
            'control_number' => 'REPORT-DELETE-'.strtoupper($status).'-'.uniqid(),
            'status' => $status,
        ]);
    }
}
