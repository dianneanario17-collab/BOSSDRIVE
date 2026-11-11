<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class VehicleAvailabilityController extends Controller
{
    public function __invoke(Request $request): JsonResponse|Response
    {
        $includeReservationDetails = (bool) $request->session()->get('is_admin')
            || (bool) $request->session()->get('is_staff');
        $vehicleRevision = Vehicle::query()
            ->selectRaw('COUNT(*) as total, MAX(updated_at) as latest_update')
            ->first();
        $reservationRevision = Reservation::query()
            ->selectRaw('COUNT(*) as total, MAX(updated_at) as latest_update')
            ->first();
        $etag = '"'.hash('sha256', implode('|', [
            today()->toDateString(),
            $vehicleRevision->total,
            $vehicleRevision->latest_update,
            $reservationRevision->total,
            $reservationRevision->latest_update,
            $includeReservationDetails ? 'staff' : 'public',
        ])).'"';
        $hasUpdateThisSecond = collect([
            $vehicleRevision->latest_update,
            $reservationRevision->latest_update,
        ])->contains(fn ($updatedAt): bool => $updatedAt
            && Carbon::parse($updatedAt)->greaterThanOrEqualTo(now()->startOfSecond()));

        if (! $hasUpdateThisSecond && $request->headers->get('If-None-Match') === $etag) {
            return response()->noContent(304)
                ->header('ETag', $etag)
                ->header('Cache-Control', 'no-store, private');
        }

        $reservations = Reservation::query()
            ->whereNotIn('status', ['cancelled', 'void', 'completed'])
            ->whereNotNull('pickup_date')
            ->whereNotNull('return_date')
            ->with('vehicleUnit:id,name,plate')
            ->latest('created_at')
            ->get();

        $reservationSchedules = $reservations
            ->filter(fn (Reservation $reservation): bool => $reservation->vehicle_id !== null)
            ->map(function (Reservation $reservation) use ($includeReservationDetails): array {
                $schedule = [
                    'id' => $reservation->id,
                    'reservationId' => $reservation->id,
                    'vehicleId' => $reservation->vehicle_id,
                    'vehicle' => $reservation->vehicleUnit?->name,
                    'plate' => $reservation->vehicleUnit?->plate,
                    'type' => $reservation->status === 'released'
                        ? 'Rented'
                        : ($reservation->service_option === 'delivery' ? 'Special' : 'Reserved'),
                    'status' => $reservation->status === 'released' ? 'Ongoing' : 'Reserved',
                    'start' => $reservation->pickup_date->toDateString(),
                    'end' => $reservation->return_date->toDateString(),
                    'notes' => 'Reservation schedule',
                ];

                if ($includeReservationDetails && $schedule['type'] === 'Special') {
                    $schedule['controlNumber'] = $reservation->control_number;
                    $schedule['deliveryDate'] = $reservation->pickup_date->format('F j, Y');
                }

                return $schedule;
            })
            ->values();

        $reservationsByVehicle = $reservationSchedules
            ->groupBy('vehicleId')
            ->map(fn ($entries) => $entries->first());
        $activeVehicleIds = Reservation::query()
            ->whereNotIn('status', ['cancelled', 'void', 'completed'])
            ->whereNotNull('vehicle_id')
            ->distinct()
            ->pluck('vehicle_id')
            ->map(fn ($id): int => (int) $id)
            ->all();
        $lastReturnedAtByVehicle = Reservation::query()
            ->where('status', 'completed')
            ->whereNotNull('vehicle_id')
            ->select('vehicle_id')
            ->selectRaw('MAX(COALESCE(return_condition_checked_at, return_date)) as last_returned_at')
            ->groupBy('vehicle_id')
            ->get()
            ->pluck('last_returned_at', 'vehicle_id');

        $vehicles = Vehicle::query()
            ->withCount('reservations')
            ->orderBy('name')
            ->orderBy('id')
            ->get([
                'id', 'name', 'brand_name', 'plate', 'price', 'category', 'transmission',
                'fuel', 'capacity', 'capacity_type', 'status', 'schedule', 'image_path',
                'rating', 'feedbacks', 'damage_log',
            ])
            ->map(function (Vehicle $vehicle) use ($reservationSchedules, $reservationsByVehicle, $activeVehicleIds, $lastReturnedAtByVehicle): array {
                $maintenanceSchedules = collect($vehicle->schedule ?? [])
                    ->filter(fn (array $entry): bool => ($entry['type'] ?? null) === 'Maintenance')
                    ->map(fn (array $entry): array => [
                        'id' => $entry['id'] ?? null,
                        'type' => 'Maintenance',
                        'start' => $entry['start'] ?? null,
                        'end' => $entry['end'] ?? null,
                    ])
                    ->values();
                $vehicleReservations = $reservationSchedules
                    ->where('vehicleId', $vehicle->id)
                    ->values();
                $activeReservation = $reservationsByVehicle->get($vehicle->id);
                $status = $vehicle->status === 'maintenance'
                    ? 'Maintenance'
                    : ($activeReservation
                        ? 'Rented'
                        : (in_array($vehicle->status, ['unavailable', 'maintenance'], true)
                            ? 'Maintenance'
                            : ($vehicle->status === 'rented' ? 'Available' : ucfirst($vehicle->status))));

                return [
                    'id' => $vehicle->id,
                    'name' => $vehicle->name,
                    'brand_name' => $vehicle->brand_name ?? '',
                    'plate' => $vehicle->plate,
                    'price' => (float) $vehicle->price,
                    'category' => $vehicle->category ?? '',
                    'transmission' => $vehicle->transmission ?? '',
                    'fuel' => $vehicle->fuel ?? '',
                    'capacity' => (string) ($vehicle->capacity ?? ''),
                    'capacity_type' => $vehicle->capacity_type ?: (($vehicle->capacity ?? null) ? $vehicle->capacity.' Seater' : ''),
                    'status' => $status,
                    'photo' => $vehicle->image_path
                        ? asset('storage/'.$vehicle->image_path)
                        : asset('image/car'.(($vehicle->id - 1) % 4 + 1).'.jpg'),
                    'image_path' => $vehicle->image_path,
                    'rating' => (float) $vehicle->rating,
                    'timesRented' => $vehicle->reservations_count,
                    'feedbacks' => $vehicle->feedbacks ?? [],
                    'damageLog' => $vehicle->damage_log ?? [],
                    'bookingWindow' => $vehicle->bookingWindow(
                        in_array($vehicle->id, $activeVehicleIds, true),
                        $lastReturnedAtByVehicle->get($vehicle->id)
                    ),
                    'currentReservation' => $activeReservation ? [
                        'pickup_date' => $activeReservation['start'],
                        'return_date' => $activeReservation['end'],
                        'status' => $activeReservation['status'] === 'Ongoing' ? 'released' : 'reserved',
                    ] : null,
                    'schedule' => $maintenanceSchedules
                        ->concat($vehicleReservations)
                        ->values(),
                ];
            })
            ->values();

        $maintenance = $vehicles->flatMap(function (array $vehicle) {
            return collect($vehicle['schedule'])
                ->filter(fn (array $entry): bool => ($entry['type'] ?? null) === 'Maintenance')
                ->map(fn (array $entry): array => [
                    'vehicleId' => $vehicle['id'],
                    'vehicle' => $vehicle['name'],
                    'status' => 'Maintenance',
                    'start' => $entry['start'] ?? null,
                    'end' => $entry['end'] ?? null,
                ]);
        })->filter(fn (array $entry): bool => filled($entry['start']) && filled($entry['end']))
            ->values();

        return response()->json([
            'vehicles' => $vehicles,
            'reservations' => $reservationSchedules,
            'maintenance' => $maintenance,
        ])->header('ETag', $etag)
            ->header('Cache-Control', 'no-store, private');
    }
}
