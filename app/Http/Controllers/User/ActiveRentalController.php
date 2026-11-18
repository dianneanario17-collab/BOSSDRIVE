<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PickupConditionReport;
use App\Models\RentalExtension;
use App\Models\Reservation;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ActiveRentalController extends Controller
{
    public function show(Request $request): View
    {
        $reservation = null;
        $vehicleDailyPrice = 0;
        $activeVehicle = null;
        $activeReservations = $request->user()->reservations()
            ->whereIn('status', ['verified', 'processing', 'released'])
            ->with('vehicleUnit:id,price,image_path')
            ->latest()
            ->get();
        $reservationId = $request->integer('reservation_id')
            ?: $request->session()->get('active_rental_reservation_id');

        if ($reservationId) {
            $reservation = $activeReservations->firstWhere('id', $reservationId);
        }

        if (! $reservation) {
            $reservation = $activeReservations->first();
            if ($reservation) {
                $request->session()->put('active_rental_reservation_id', $reservation->id);
            }
        }

        if ($reservation) {
            $activeVehicle = $reservation->vehicleUnit;
            $vehicleDailyPrice = (float) ($activeVehicle?->price ?? 0);
        }

        $settings = SystemSetting::current();

        return view('user.active-rental', compact(
            'reservation',
            'vehicleDailyPrice',
            'activeVehicle',
            'activeReservations',
        ) + [
            'driverDailyFee' => (float) $settings->driver_daily_fee,
            'pickupConditionChecks' => $settings->pickup_condition_checks,
        ]);
    }

    public function contract(Request $request, Reservation $reservation): View
    {
        abort_unless($reservation->user_id === $request->user()->id, 403);
        abort_unless(in_array($reservation->status, ['verified', 'processing', 'released'], true), 422);

        $user = $request->user();
        $vehicle = $reservation->vehicleUnit;
        $settings = SystemSetting::current();
        $address = collect([
            $user->address,
            $user->barangay,
            $user->city,
            $user->province,
        ])->filter()->implode(', ');
        $contractData = array_merge([
            'agreement_date' => today()->toDateString(),
            'renter_address' => $address,
            'vehicle_year' => '',
            'vehicle_color' => '',
            'vehicle_vin' => '',
            'vehicle_mileage' => '',
            'mileage_unlimited' => null,
            'mileage_limit' => '',
            'allowed_area' => '',
        ], $reservation->rental_contract_data ?? []);
        $agreementText = str_replace(
            ['{business_name}', '{deposit_amount}'],
            [$settings->business_name, number_format((float) $settings->security_deposit, 2)],
            $settings->rental_contract_text ?? $settings->agreement_text
        );
        $vehicleMake = $vehicle?->brand_name ?: $reservation->vehicle;
        $vehicleModel = $vehicle?->brand_name ? $vehicle->name : $reservation->vehicle;
        $mileageLimit = $contractData['mileage_unlimited'] === true
            ? 'No mileage limit'
            : ($contractData['mileage_limit'] !== ''
                ? number_format((float) $contractData['mileage_limit']).' kilometers'
                : 'Not specified');
        $placeholders = [
            '{business_name}' => $settings->business_name,
            '{owner_address}' => $settings->pickup_location,
            '{agreement_date}' => Carbon::parse($contractData['agreement_date'])->format('F j, Y'),
            '{renter_name}' => $reservation->customer_name ?: $user->name,
            '{renter_address}' => $contractData['renter_address'],
            '{renter_contact}' => $reservation->customer_phone ?: ($user->contact_number ?: ''),
            '{renter_email}' => $user->email,
            '{control_number}' => $reservation->control_number,
            '{vehicle_make}' => $vehicleMake,
            '{vehicle_model}' => $vehicleModel,
            '{vehicle_year}' => $contractData['vehicle_year'],
            '{vehicle_color}' => $contractData['vehicle_color'],
            '{vehicle_plate}' => $vehicle?->plate ?: 'Not assigned',
            '{vehicle_mileage}' => $contractData['vehicle_mileage'],
            '{vehicle_vin}' => $contractData['vehicle_vin'],
            '{pickup_date}' => $reservation->pickup_date->format('F j, Y'),
            '{pickup_time}' => $reservation->pickup_time
                ? Carbon::parse($reservation->pickup_time)->format('g:i A')
                : '',
            '{return_date}' => $reservation->return_date->format('F j, Y'),
            '{return_time}' => $reservation->return_time
                ? Carbon::parse($reservation->return_time)->format('g:i A')
                : '',
            '{allowed_area}' => $contractData['allowed_area'],
            '{mileage_limit}' => $mileageLimit,
            '{rental_fee}' => number_format((float) $reservation->total_amount, 2),
            '{amount_paid}' => number_format((float) $reservation->paid_amount, 2),
            '{remaining_balance}' => number_format(
                max((float) $reservation->total_amount - (float) $reservation->paid_amount, 0),
                2
            ),
            '{security_deposit}' => number_format((float) $settings->security_deposit, 2),
            '{agreement_terms}' => $agreementText,
            '{condition_report}' => 'Complete the Vehicle Pickup Condition Check separately.',
            '{renter_signature}' => '____________________________  Date: ______________',
            '{owner_signature}' => '____________________________  Date: ______________',
        ];
        $printableContract = strtr(
            $settings->rental_contract_template ?? SystemSetting::defaults()['rental_contract_template'],
            $placeholders
        );

        return view('user.rental-contract', [
            'reservation' => $reservation,
            'user' => $user,
            'vehicle' => $vehicle,
            'settings' => $settings,
            'contractData' => $contractData,
            'printableContract' => $printableContract,
        ]);
    }

    public function updateContract(Request $request, Reservation $reservation): RedirectResponse
    {
        abort_unless($reservation->user_id === $request->user()->id, 403);
        abort_unless(in_array($reservation->status, ['verified', 'processing', 'released'], true), 422);

        $data = $request->validate([
            'agreement_date' => ['required', 'date'],
            'renter_address' => ['required', 'string', 'max:1000'],
            'vehicle_year' => ['nullable', 'string', 'max:20'],
            'vehicle_color' => ['nullable', 'string', 'max:100'],
            'vehicle_vin' => ['nullable', 'string', 'max:100'],
            'vehicle_mileage' => ['nullable', 'string', 'max:50'],
            'mileage_unlimited' => ['required', 'boolean'],
            'mileage_limit' => ['nullable', 'required_if:mileage_unlimited,0', 'numeric', 'min:0', 'max:1000000'],
            'allowed_area' => ['required', 'string', 'max:500'],
            'print_after_save' => ['nullable', 'boolean'],
        ]);
        $shouldPrint = $request->boolean('print_after_save');
        unset($data['print_after_save']);
        $data['mileage_unlimited'] = (bool) $data['mileage_unlimited'];
        $data['mileage_limit'] = $data['mileage_limit'] ?? '';
        $reservation->update(['rental_contract_data' => $data]);

        $redirectUrl = route('user.active-rental.contract', $reservation);

        return redirect()->to($redirectUrl.($shouldPrint ? '?print=1' : ''))
            ->with('success', 'Rental agreement details saved.');
    }

    public function verify(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'control_number' => ['required', 'string', 'max:255'],
        ]);

        $reservation = $request->user()->reservations()
            ->where('control_number', $data['control_number'])
            ->first();

        if (! $reservation) {
            return back()->withErrors([
                'control_number' => 'The control number is invalid or does not belong to your account.',
            ])->withInput();
        }

        if (! in_array($reservation->status, ['verified', 'processing', 'released'], true)) {
            return back()->withErrors([
                'control_number' => 'Your rental is not ready for Active Rental access yet. Staff must verify the reservation first.',
            ])->withInput();
        }

        $request->session()->put('active_rental_reservation_id', $reservation->id);

        return redirect()->route('user.active-rental');
    }

    public function submitCondition(Request $request, Reservation $reservation): RedirectResponse
    {
        abort_unless($reservation->user_id === $request->user()->id, 403);
        abort_unless(in_array($reservation->status, ['verified', 'processing', 'released'], true), 422);

        $data = $request->validate([
            'checks' => ['required', 'array', 'min:1'],
            'checks.*' => ['string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'damage_part' => ['nullable', 'string', 'max:255'],
            'damage_description' => ['nullable', 'string', 'max:2000'],
        ]);
        abort_unless(
            SystemSetting::hasCompleteChecklist(
                SystemSetting::current()->pickup_condition_checks ?? [],
                $data['checks']
            ),
            422,
            'Complete every vehicle pickup condition check before submitting.'
        );

        $path = $request->hasFile('photo')
            ? $request->file('photo')->store('reservations/conditions', 'public')
            : null;

        PickupConditionReport::create([
            'reservation_id' => $reservation->id,
            'user_id' => $request->user()->id,
            'checks' => $data['checks'],
            'notes' => collect([
                $data['notes'] ?? null,
                filled($data['damage_part'] ?? null) ? 'Part: '.$data['damage_part'] : null,
                filled($data['damage_description'] ?? null) ? 'Description: '.$data['damage_description'] : null,
            ])->filter()->implode("\n"),
            'photo_path' => $path,
        ]);

        return back()->with('success', 'Vehicle condition report submitted.');
    }

    public function requestExtension(Request $request, Reservation $reservation): RedirectResponse
    {
        abort_unless($reservation->user_id === $request->user()->id, 403);
        abort_unless($reservation->status === 'released', 422);

        $data = $request->validate([
            'requested_return_date' => ['required', 'date', 'after:'.$reservation->return_date->toDateString()],
        ]);
        $newDate = Carbon::parse($data['requested_return_date']);
        $extensionDays = max(1, $reservation->return_date->diffInDays($newDate));
        $vehicle = $reservation->vehicleUnit;
        abort_if(
            $vehicle && $vehicle->hasAvailabilityConflict($reservation->pickup_date->toDateString(), $newDate->toDateString(), $reservation->id),
            422,
            'The requested extension conflicts with another reservation or maintenance schedule for this unit.'
        );
        $extensionAmount = ($vehicle?->price ?? 0) * $extensionDays
            + ($reservation->driver_option === 'with_driver' ? (float) SystemSetting::current()->driver_daily_fee * $extensionDays : 0);

        RentalExtension::create([
            'reservation_id' => $reservation->id,
            'days' => $extensionDays,
            'amount' => $extensionAmount,
            'requested_return_date' => $newDate,
            'status' => 'approved',
        ]);
        $reservation->update(['return_date' => $newDate]);
        if ($vehicle) {
            $schedule = collect($vehicle->schedule ?? [])->map(function (array $entry) use ($reservation, $newDate) {
                if (($entry['reservationId'] ?? null) === $reservation->id) {
                    $entry['end'] = $newDate->toDateString();
                }

                return $entry;
            })->values()->all();
            $vehicle->update(['schedule' => $schedule]);
        }

        return back()->with('success', 'Rental extension saved. New return date: '.$newDate->format('F j, Y').'.');
    }

    public function initiateReturn(Request $request, Reservation $reservation): RedirectResponse
    {
        abort_unless($reservation->user_id === $request->user()->id, 403);
        abort_unless($reservation->status === 'released', 422);

        $reservation->update(['status' => 'completed']);
        $vehicle = $reservation->vehicleUnit;
        if ($vehicle && $vehicle->status !== 'maintenance') {
            $hasActiveReservation = $vehicle->reservations()
                ->where('id', '<>', $reservation->id)
                ->whereNotIn('status', ['cancelled', 'void', 'completed'])
                ->exists();
            $vehicle->update([
                'status' => $hasActiveReservation ? 'rented' : 'available',
                'schedule' => collect($vehicle->schedule ?? [])
                    ->reject(fn (array $entry): bool => ($entry['reservationId'] ?? null) === $reservation->id)
                    ->values()
                    ->all(),
            ]);
        }

        return redirect()->route('user.dashboard')->with('success', 'Return request submitted. Your booking is now completed.');
    }
}
