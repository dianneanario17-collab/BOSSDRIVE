<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Rental Agreement - {{ $reservation->control_number }}</title>
    @include('partials.favicon')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body{background:#e8e8e8;font-family:Arial,sans-serif}
        .content-container{padding:24px}
        .contract-form,.contract-paper{max-width:980px;margin:0 auto 22px}
        .contract-paper{background:#fff;padding:38px 48px;box-shadow:0 4px 18px rgba(0,0,0,.12);color:#171717;font-family:Arial,sans-serif;font-size:14px;line-height:1.55}
        .contract-paper h1{font-size:23px;letter-spacing:.04em}
        .contract-paper h2{font-size:15px;margin-top:22px;text-transform:uppercase}
        .contract-paper p{margin-bottom:10px}
        .contract-line{display:inline-block;min-width:100px;border-bottom:1px solid #222;padding:0 4px;min-height:20px}
        .contract-line.wide{min-width:240px}
        .contract-line.full{display:block;width:100%;min-height:22px}
        .contract-grid{display:grid;grid-template-columns:1fr 1fr;gap:7px 24px}
        .grid-span{grid-column:1/-1}
        .signature-grid{display:grid;grid-template-columns:1fr 1fr;gap:60px;margin-top:42px}
        .signature-line{border-top:1px solid #222;padding-top:5px;text-align:center}
        .terms-text{white-space:normal}
        @media(max-width:575.98px){.content-container{padding:12px}.contract-paper{padding:24px 18px}.contract-grid,.signature-grid{grid-template-columns:1fr;gap:12px}.contract-paper h1{font-size:19px}}
        @media print{
            @page{size:A4;margin:14mm}
            body *{visibility:hidden!important}
            .contract-paper,.contract-paper *{visibility:visible!important}
            .contract-paper{position:absolute;left:0;top:0;width:100%;max-width:none;margin:0;padding:0;box-shadow:none;font-size:11pt}
            .contract-paper h1{font-size:18pt}
            .contract-paper h2{font-size:12pt}
        }
    </style>
</head>
<body>
@include('user.partials.navigation', ['title' => 'RENTAL', 'accent' => 'AGREEMENT'])
<div class="content-container">
    @php
        $mileageChoice = old('mileage_unlimited', $contractData['mileage_unlimited']);
    @endphp
    <div class="contract-form card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <a href="{{ route('user.active-rental', ['reservation_id' => $reservation->id]) }}" class="btn btn-sm btn-outline-secondary rounded-pill mb-3"><i class="fas fa-arrow-left me-1"></i> Back to Active Rental</a>
            <h1 class="h4 fw-bold mb-1">Fill Out Rental Agreement</h1>
            <p class="text-muted mb-3">Complete the remaining details and save before printing. Your booking, vehicle, business terms, and saved agreement details will appear below.</p>
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger"><strong>Please check the agreement details:</strong><ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            <form id="rentalContractForm" method="POST" action="{{ route('user.active-rental.contract.update', $reservation) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="agreement_date" class="form-label fw-semibold">Agreement Date</label>
                        <input id="agreement_date" type="date" name="agreement_date" value="{{ old('agreement_date', $contractData['agreement_date']) }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label for="renter_address" class="form-label fw-semibold">Renter Address</label>
                        <input id="renter_address" type="text" name="renter_address" value="{{ old('renter_address', $contractData['renter_address']) }}" class="form-control" maxlength="1000" required>
                    </div>
                    <div class="col-md-4">
                        <label for="vehicle_year" class="form-label fw-semibold">Vehicle Year (if known)</label>
                        <input id="vehicle_year" type="text" name="vehicle_year" value="{{ old('vehicle_year', $contractData['vehicle_year']) }}" class="form-control" maxlength="20">
                    </div>
                    <div class="col-md-4">
                        <label for="vehicle_color" class="form-label fw-semibold">Vehicle Color</label>
                        <input id="vehicle_color" type="text" name="vehicle_color" value="{{ old('vehicle_color', $contractData['vehicle_color']) }}" class="form-control" maxlength="100">
                    </div>
                    <div class="col-md-4">
                        <label for="vehicle_mileage" class="form-label fw-semibold">Starting Mileage (if known)</label>
                        <input id="vehicle_mileage" type="text" name="vehicle_mileage" value="{{ old('vehicle_mileage', $contractData['vehicle_mileage']) }}" class="form-control" maxlength="50">
                    </div>
                    <div class="col-md-6">
                        <label for="vehicle_vin" class="form-label fw-semibold">VIN / Chassis No. (if known)</label>
                        <input id="vehicle_vin" type="text" name="vehicle_vin" value="{{ old('vehicle_vin', $contractData['vehicle_vin']) }}" class="form-control" maxlength="100">
                    </div>
                    <div class="col-md-6">
                        <label for="allowed_area" class="form-label fw-semibold">Agreed Rental Area / Destination</label>
                        <input id="allowed_area" type="text" name="allowed_area" value="{{ old('allowed_area', $contractData['allowed_area']) }}" class="form-control" maxlength="500" required>
                    </div>
                    <div class="col-12">
                        <span class="form-label fw-semibold d-block">Mileage Limit</span>
                        <div class="form-check">
                            <input class="form-check-input mileage-choice" type="radio" name="mileage_unlimited" id="mileage_unlimited" value="1" required @checked(in_array($mileageChoice, [true, 1, '1'], true))>
                            <label class="form-check-label" for="mileage_unlimited">No mileage limit</label>
                        </div>
                        <div class="form-check d-flex align-items-center gap-2">
                            <input class="form-check-input mileage-choice" type="radio" name="mileage_unlimited" id="mileage_limited" value="0" required @checked(in_array($mileageChoice, [false, 0, '0'], true))>
                            <label class="form-check-label" for="mileage_limited">Limit:</label>
                            <input id="mileage_limit" type="number" min="0" step="1" name="mileage_limit" value="{{ old('mileage_limit', $contractData['mileage_limit']) }}" class="form-control form-control-sm" style="max-width:180px" aria-label="Mileage limit in kilometers">
                            <span class="small text-muted">kilometers</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2 mt-4">
                    <button type="submit" class="btn btn-danger rounded-pill px-4"><i class="fas fa-save me-2"></i>Save Agreement Details</button>
                    <button type="submit" name="print_after_save" value="1" class="btn btn-dark rounded-pill px-4"><i class="fas fa-print me-2"></i>Save &amp; Print Agreement</button>
                </div>
            </form>
        </div>
    </div>

    <article class="contract-paper" id="contractPaper">
        <header class="text-center border-bottom border-dark pb-3 mb-3">
            <h1 class="fw-bold mb-1">{{ $settings->business_name }}</h1>
            <p class="mb-1">{{ $settings->pickup_location }}</p>
            <h2 class="fw-bold mt-3 mb-0">Vehicle Rental Agreement</h2>
        </header>
        <p>This Vehicle Rental Agreement is made on <strong>{{ \Illuminate\Support\Carbon::parse($contractData['agreement_date'])->format('F j, Y') }}</strong> between <strong>{{ $settings->business_name }}</strong> (“Owner”), located at <strong>{{ $settings->pickup_location }}</strong>, and <strong>{{ $reservation->customer_name ?: $user->name }}</strong> (“Renter”), residing at <span class="contract-line wide">{{ $contractData['renter_address'] ?: ' ' }}</span>.</p>

        <h2>Renter and Booking Information</h2>
        <div class="contract-grid">
            <div><strong>Renter:</strong> {{ $reservation->customer_name ?: $user->name }}</div>
            <div><strong>Contact:</strong> {{ $reservation->customer_phone ?: ($user->contact_number ?: ' ') }}</div>
            <div><strong>Email:</strong> {{ $user->email }}</div>
            <div><strong>Control No.:</strong> {{ $reservation->control_number }}</div>
            <div class="grid-span"><strong>Address:</strong> {{ $contractData['renter_address'] ?: ' ' }}</div>
        </div>

        <h2>Vehicle and Rental Period</h2>
        <div class="contract-grid">
            <div><strong>Make:</strong> {{ $vehicle?->brand_name ?: $reservation->vehicle }}</div>
            <div><strong>Model:</strong> {{ $vehicle?->brand_name ? $vehicle->name : ' ' }}</div>
            <div><strong>Year:</strong> {{ $contractData['vehicle_year'] ?: ' ' }}</div>
            <div><strong>Color:</strong> {{ $contractData['vehicle_color'] ?: ' ' }}</div>
            <div><strong>Plate No.:</strong> {{ $vehicle?->plate ?: ' ' }}</div>
            <div><strong>Starting Mileage:</strong> {{ $contractData['vehicle_mileage'] ?: ' ' }}</div>
            <div class="grid-span"><strong>VIN / Chassis No.:</strong> {{ $contractData['vehicle_vin'] ?: ' ' }}</div>
            <div><strong>Pickup:</strong> {{ $reservation->pickup_date->format('F j, Y') }}{{ $reservation->pickup_time ? ' at '.\Illuminate\Support\Carbon::parse($reservation->pickup_time)->format('g:i A') : '' }}</div>
            <div><strong>Return:</strong> {{ $reservation->return_date->format('F j, Y') }}{{ $reservation->return_time ? ' at '.\Illuminate\Support\Carbon::parse($reservation->return_time)->format('g:i A') : '' }}</div>
            <div class="grid-span"><strong>Agreed Rental Area / Destination:</strong> {{ $contractData['allowed_area'] ?: ' ' }}</div>
            <div class="grid-span"><strong>Mileage Limit:</strong> {{ $contractData['mileage_unlimited'] === true ? 'No mileage limit' : ($contractData['mileage_limit'] !== '' ? number_format((float) $contractData['mileage_limit']).' kilometers' : ' ') }}</div>
        </div>

        <h2>Rental Fees and Deposit</h2>
        <div class="contract-grid">
            <div><strong>Rental Fee:</strong> ₱{{ number_format((float) $reservation->total_amount, 2) }}</div>
            <div><strong>Amount Paid:</strong> ₱{{ number_format((float) $reservation->paid_amount, 2) }}</div>
            <div><strong>Remaining Balance:</strong> ₱{{ number_format(max((float) $reservation->total_amount - (float) $reservation->paid_amount, 0), 2) }}</div>
            <div><strong>Security Deposit:</strong> ₱{{ number_format((float) $settings->security_deposit, 2) }}</div>
        </div>

        <h2>Agreement Terms and Rules</h2>
        <div class="terms-text">{!! nl2br(e($agreementText)) !!}</div>
        <p class="mt-3"><strong>Vehicle condition report:</strong> Complete the Vehicle Pickup Condition Check separately.</p>

        <div class="signature-grid">
            <div class="signature-line"><strong>{{ $reservation->customer_name ?: $user->name }}</strong><br>Renter Signature &amp; Date</div>
            <div class="signature-line"><strong>{{ $settings->business_name }}</strong><br>Owner / Authorized Representative &amp; Date</div>
        </div>
    </article>
</div>
</main>
<script>
    (function () {
        const limited = document.getElementById('mileage_limited');
        const unlimited = document.getElementById('mileage_unlimited');
        const mileageLimit = document.getElementById('mileage_limit');
        const updateMileageInput = function () {
            const isLimited = limited.checked;
            mileageLimit.disabled = !isLimited;
            mileageLimit.required = isLimited;
        };
        document.querySelectorAll('.mileage-choice').forEach(function (choice) {
            choice.addEventListener('change', updateMileageInput);
        });
        updateMileageInput();
        @if(request()->query('print') === '1')
            window.addEventListener('load', function () { window.print(); });
        @endif
    }());
</script>
</body>
</html>
