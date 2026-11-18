<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\SystemSetting;
use Illuminate\Support\Carbon;

class RentalContractBuilder
{
    public function build(Reservation $reservation): array
    {
        $user = $reservation->user;
        $vehicle = $reservation->vehicleUnit;
        $settings = SystemSetting::current();
        $address = collect([
            $user?->address,
            $user?->barangay,
            $user?->city,
            $user?->province,
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
            '{renter_name}' => $reservation->customer_name ?: ($user?->name ?? ''),
            '{renter_address}' => $contractData['renter_address'],
            '{renter_contact}' => $reservation->customer_phone ?: ($user?->contact_number ?: ''),
            '{renter_email}' => $user?->email ?: ($reservation->customer_email ?: ''),
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

        return [
            'reservation' => $reservation,
            'user' => $user,
            'vehicle' => $vehicle,
            'settings' => $settings,
            'contractData' => $contractData,
            'printableContract' => $printableContract,
        ];
    }
}
