<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BossDrive - Payments</title>
    @include('partials.favicon')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --boss-red:#b71c1c; --boss-dark:#121212; --boss-grey:#e0e0e0; }
        body { background:var(--boss-grey); color:#212529; font-family:'Segoe UI',sans-serif; }
        .content-container { padding:30px; }
        .payment-card { background:#fff; border:1px solid #e9ecef; border-radius:18px; box-shadow:0 8px 24px rgba(18,18,18,.06); overflow:hidden; }
        .payment-panel { border:1px solid #e9ecef!important; border-radius:18px!important; background:#fff; box-shadow:0 8px 24px rgba(18,18,18,.06)!important; }
        .payment-section-heading { display:flex; align-items:center; gap:12px; }
        .payment-heading-icon { display:grid; width:42px; height:42px; flex:0 0 42px; place-items:center; border-radius:13px; background:#fff1f2; color:var(--boss-red); }
        .payment-section-heading h5,.payment-section-heading h6 { margin:0; font-weight:800; }
        .payment-section-description { margin:.2rem 0 0; color:#6c757d; font-size:.82rem; }
        .gcash-header-badge { background:linear-gradient(135deg,#0788ff,#0069d9); color:#fff; padding:12px 20px; border-radius:12px; font-weight:bold; display:flex; align-items:center; justify-content:center; gap:10px; margin:20px 0; }
        .payment-qr-frame { width:100%; min-height:150px; padding:10px; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:10px; border:1px solid #e9ecef; border-radius:14px; background:#f8f9fa; }
        .gcash-qr-thumbnail { display:inline-flex; max-width:100%; padding:0; border:0; border-radius:10px; background:transparent; cursor:zoom-in; }
        .qr-code-img { display:block; width:min(132px,42vw); height:auto; border:3px solid #fff; border-radius:10px; box-shadow:0 2px 10px rgba(18,18,18,.08); object-fit:contain; transition:transform .15s ease; }
        .gcash-qr-thumbnail:hover .qr-code-img { transform:scale(1.03); }
        .gcash-qr-preview-image { display:block; max-width:min(90vw,760px); max-height:min(78vh,760px); margin:auto; object-fit:contain; }
        .status-badge { display:inline-flex; align-items:center; justify-content:center; flex:0 0 auto; max-width:125px; padding:7px 10px; border-radius:16px; font-weight:700; font-size:.7rem; line-height:1.25; text-align:center; white-space:normal; }
        .status-pending { background:#f5c451; color:#3f2d00; } .status-paid { background:#d1e7dd; color:#0f5132; }
        .status-approved,.status-payment_pending { background:#fff3cd; color:#856404; }
        .status-unpaid { background:#f8d7da; color:#842029; }
        .payment-history-entry { border:1px solid #e9ecef!important; border-radius:12px!important; background:#fff; transition:border-color .15s ease, box-shadow .15s ease; }
        .payment-history-entry:hover { border-color:#d6a0a4!important; box-shadow:0 4px 14px rgba(18,18,18,.05); }
        .payment-proof-preview { display:inline-flex; flex-direction:column; align-items:flex-start; gap:5px; border:0; padding:0; background:none; color:#0d6efd; text-align:left; }
        .payment-proof-thumbnail { display:block; width:auto; max-width:100%; height:90px; object-fit:cover; border:1px solid #dee2e6; border-radius:8px; }
        .payment-completed-dialog { max-width:760px; }
        .payment-completed-dialog .modal-content { max-height:75vh; border:0; border-radius:20px; overflow:hidden; box-shadow:0 24px 70px rgba(18,18,18,.2); }
        .payment-completed-dialog .modal-header { flex:0 0 auto; padding:14px 18px; border-bottom:1px solid #edf0f2; }
        .payment-completed-dialog .modal-body { flex:1 1 auto; min-height:0; max-height:calc(75vh - 68px); overflow-y:auto; padding:14px 16px; background:#f7f8fa; scrollbar-color:#ced4da transparent; scrollbar-width:thin; }
        .completed-payments-title { display:flex; align-items:center; gap:11px; }
        .completed-payments-title-icon { display:grid; width:38px; height:38px; flex:0 0 38px; place-items:center; border-radius:12px; background:#e9f7ef; color:#198754; font-size:1rem; }
        .completed-payments-title small { display:block; margin-top:2px; color:#6c757d; font-size:.76rem; font-weight:500; }
        .completed-payment-card { padding:12px; border:1px solid #e6e9ed; border-radius:13px; background:#fff; box-shadow:0 3px 12px rgba(18,18,18,.035); }
        .completed-payment-heading { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; }
        .completed-payment-vehicle { display:flex; align-items:center; gap:11px; min-width:0; }
        .completed-payment-vehicle > div { min-width:0; }
        .completed-payment-vehicle-icon { display:grid; width:34px; height:34px; flex:0 0 34px; place-items:center; border-radius:10px; background:#fff1f2; color:var(--boss-red); }
        .completed-payment-vehicle-name { display:block; font-size:.9rem; font-weight:800; line-height:1.25; overflow-wrap:anywhere; }
        .completed-payment-control { display:inline-flex; align-items:center; gap:5px; margin-top:2px; color:#6c757d; font-size:.7rem; overflow-wrap:anywhere; }
        .completed-payment-card .status-badge { max-width:none; padding:5px 8px; background:#e9f7ef; color:#167347; letter-spacing:.03em; font-size:.62rem; }
        .completed-payment-detail { height:100%; padding:10px; border:1px solid #edf0f2; border-radius:10px; background:#fff; }
        .completed-payment-detail-title { display:flex; align-items:center; gap:7px; margin-bottom:8px; font-size:.8rem; font-weight:800; }
        .completed-payment-detail-title i { color:var(--boss-red); }
        .completed-payment-amounts { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:6px; }
        .completed-payment-amount { min-width:0; padding:7px 9px; border-radius:8px; background:#f7f8fa; }
        .completed-payment-amount.paid { background:#e9f7ef; }
        .completed-payment-amount-label { display:block; color:#6c757d; font-size:.66rem; font-weight:600; }
        .completed-payment-amount-value { display:block; margin-top:2px; font-size:.84rem; font-weight:800; overflow-wrap:anywhere; }
        .completed-payment-amount.paid .completed-payment-amount-value { color:#167347; }
        .completed-payment-approvals { display:grid; gap:4px; margin-top:8px; }
        .completed-payment-approval { display:flex; align-items:flex-start; gap:7px; padding:6px 8px; border-radius:8px; background:#f0f8f3; color:#26734d; font-size:.69rem; line-height:1.35; overflow-wrap:anywhere; }
        .completed-payment-approval i { margin-top:2px; color:#198754; }
        .completed-payment-empty { padding:24px 16px; border:1px dashed #ced4da; border-radius:13px; background:#fff; }
        .payment-layout-row { align-items:flex-start; }
        .payment-history-scroll { max-height:min(60vh, 560px); overflow-y:auto; padding-right:4px; scrollbar-color:#ced4da transparent; scrollbar-width:thin; }
        .payment-step[hidden] { display:none!important; }
        .payment-history-tabs { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); }
        .payment-history-tabs .nav-item { min-width:0; }
        .payment-history-tabs .nav-link { width:100%; min-height:46px; margin:0; padding:10px 8px; border-radius:10px; color:#495057; background:#f1f3f5; font-weight:700; white-space:normal; line-height:1.25; }
        .payment-history-tabs .nav-link.active { color:#fff; background:var(--boss-red); }
        @media(max-width:991.98px){ .payment-history-scroll { max-height:50vh; } }
        @media(max-width:575.98px){.payment-card,.payment-panel{border-radius:15px!important}.payment-card.p-4,.payment-panel.p-4{padding:1rem!important}.payment-history-scroll{max-height:55vh}.gcash-header-badge{padding:10px 12px;font-size:.85rem}.payment-qr-frame{min-height:140px;padding:6px}.qr-code-img{width:112px}.payment-completed-dialog .modal-content{max-height:80vh;border-radius:16px}.payment-completed-dialog .modal-header{padding:12px 14px}.payment-completed-dialog .modal-body{max-height:calc(80vh - 60px);padding:10px!important}.completed-payment-card{padding:10px}.completed-payment-vehicle-icon{width:30px;height:30px;flex-basis:30px}.completed-payment-vehicle-name{font-size:.84rem}.completed-payment-control{font-size:.66rem}.completed-payment-detail{padding:8px}.completed-payment-detail-title{font-size:.74rem}.completed-payment-amount-value{font-size:.78rem}.completed-payment-approval{font-size:.65rem;padding:5px 7px}.payment-history-tabs .nav-link{min-height:44px;padding:8px 5px;font-size:.78rem}.table-responsive{font-size:.85rem}.modal-dialog{margin:.5rem}.modal-body{padding:1rem!important}}
    </style>
</head>
<body>
    @include('user.partials.navigation', ['title' => 'PAYMENT', 'accent' => 'CENTER'])

        <div class="content-container">
            @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
            @if($errors->any()) <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div> @endif

            @php
                $bookingTotalFor = fn ($item) => (float) $item->total_amount > 0
                    ? (float) $item->total_amount
                    : (float) ($item->vehicleUnit?->price ?? 0) * $item->rentalDays()
                        + ($item->driver_option === 'with_driver' ? 1500 * $item->rentalDays() : 0);
            @endphp
            <div class="row g-4 payment-layout-row">
                <div class="col-lg-5">
                    <div class="payment-card payment-panel p-4">
                        <div class="payment-section-heading">
                            <span class="payment-heading-icon"><i class="fas fa-wallet" aria-hidden="true"></i></span>
                            <div>
                                <h5>Payment Method</h5>
                                <p class="payment-section-description">Pay for your booking or rental extension.</p>
                            </div>
                        </div>
                        <div class="gcash-header-badge"><i class="fas fa-mobile-alt" aria-hidden="true"></i><strong>GCash</strong> PAY VIA GCASH</div>

                        @if($reservation)
                            <form id="paymentSubmissionForm" action="{{ route('user.payments.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="reservation_id" id="paymentReservationId">
                                <input type="hidden" name="extension_id" id="paymentExtensionId">
                                <div id="paymentControlStep" class="payment-step">
                                    <label for="paymentControlNumber" class="fw-bold small mb-2">Enter Your Control Number</label>
                                    <input type="text" id="paymentControlNumber" name="control_number" class="form-control shadow-sm mb-2" placeholder="Enter your booking control number" autocomplete="off" required>
                                    <div id="paymentTargetMessage" class="small mb-2" aria-live="polite">Enter your Control Number to see which balance to pay.</div>
                                    <div id="paymentTargetSummary" class="alert alert-primary py-2 small mb-3 d-none">
                                        <span class="fw-bold" id="paymentTargetType"></span>
                                        <span class="d-block">Amount Due: <strong id="paymentTargetBalance"></strong></span>
                                    </div>
                                    <div class="text-center">
                                        <h6 class="fw-bold mb-3 text-primary">Scan QR Code to Pay</h6>
                                        <div class="payment-qr-frame mb-3">
                                            @if($paymentSettings->gcash_qr_path)
                                                <button type="button" class="gcash-qr-thumbnail" aria-label="View full-size GCash QR code" data-qr-src="{{ asset('storage/'.$paymentSettings->gcash_qr_path) }}" onclick="openGcashQrPreview(this)">
                                                    <img id="gcashQrCode" src="{{ asset('storage/'.$paymentSettings->gcash_qr_path) }}" alt="GCash QR Code" class="img-fluid qr-code-img">
                                                </button>
                                                <p id="gcashQrError" class="small text-danger text-center mb-0" hidden>GCash QR code is unavailable. Please contact us for payment assistance.</p>
                                            @else
                                                <i class="fas fa-qrcode fa-3x text-primary mb-2 d-block"></i>
                                                <p class="small text-muted text-center mb-0">GCash QR code will appear here after staff uploads it.</p>
                                            @endif
                                        </div>
                                        <p class="small text-muted mb-4">Account Name: <span class="fw-bold text-dark">{{ $paymentSettings->account_name ?: 'BIG BOSS RENTAL' }}</span><br>Account No: <span class="fw-bold text-dark">{{ $paymentSettings->account_number ?: '0912 345 6789' }}</span></p>
                                    </div>
                                    <hr>
                                    <button type="button" id="paymentDetailsNextButton" class="btn btn-primary w-100 rounded-pill fw-bold py-2 shadow" onclick="showPaymentDetailsStep()" disabled>NEXT <i class="fas fa-arrow-right ms-1"></i></button>
                                </div>
                                <div id="paymentDetailsStep" class="payment-step" hidden>
                                    <hr>
                                    <h6 class="fw-bold mb-3">Payment Details</h6>
                                    <label for="paymentAmount" class="fw-bold small mb-2">Amount You Are Paying (₱)</label>
                                    <input type="number" id="paymentAmount" name="payment_amount" class="form-control mb-3 rounded-pill shadow-sm" min="0.01" step="0.01" required placeholder="Enter amount paid" disabled>
                                    <label for="referenceId" class="fw-bold small mb-2">GCash Ref (or upload a screenshot)</label>
                                    <input type="text" id="referenceId" name="reference_id" class="form-control mb-3 rounded-pill shadow-sm" placeholder="Enter your GCash reference number" maxlength="32" disabled>
                                    <label for="paymentProof" class="fw-bold small mb-2">GCash Screenshot (optional if GCash Ref is provided)</label>
                                    <input type="file" id="paymentProof" name="payment_proof" class="form-control mb-3 rounded-pill shadow-sm" accept="image/jpeg,image/png" disabled>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-outline-secondary w-50 rounded-pill fw-bold py-2" onclick="showPaymentControlStep()"><i class="fas fa-arrow-left me-1"></i> BACK</button>
                                        <button type="submit" id="submitPaymentButton" class="btn btn-danger w-50 rounded-pill fw-bold py-2 shadow" disabled>SUBMIT PAYMENT</button>
                                    </div>
                                </div>
                            </form>
                        @else
                            <div class="alert alert-info">You do not have a reservation yet. Submit a reservation first before sending payment proof.</div>
                        @endif
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="card payment-panel border-0 rounded-4 shadow-sm p-4">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                            <div class="payment-section-heading">
                                <span class="payment-heading-icon"><i class="fas fa-history" aria-hidden="true"></i></span>
                                <div>
                                    <h6>Payment History</h6>
                                    <p class="payment-section-description">Track pending payments and extensions.</p>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-success rounded-pill text-nowrap" data-bs-toggle="modal" data-bs-target="#completedPaymentsModal">View Completed</button>
                        </div>
                        <ul class="nav nav-pills payment-history-tabs gap-2 mb-3" role="tablist">
                            <li class="nav-item" role="presentation"><button class="nav-link active" id="pending-history-tab" data-bs-toggle="pill" data-bs-target="#pending-history" type="button" role="tab" aria-controls="pending-history" aria-selected="true">Pending Payment Book</button></li>
                            <li class="nav-item" role="presentation"><button class="nav-link" id="extension-history-tab" data-bs-toggle="pill" data-bs-target="#extension-history" type="button" role="tab">Extended Book</button></li>
                        </ul>
                        <div class="tab-content payment-history-scroll">
                            <div class="tab-pane fade show active" id="pending-history" role="tabpanel" aria-labelledby="pending-history-tab">
                                @php
                                    $pendingBookings = $reservations->filter(fn ($item) => !in_array($item->status, ['cancelled', 'void'], true)
                                        && ($bookingTotalFor($item) > (float) $item->paid_amount
                                            || ($item->payment_status === 'pending'
                                                && ($item->payment_submitted_amount > 0 || $item->payment_proof_path || $item->payment_reference_id))));
                                @endphp
                                @forelse($pendingBookings as $item)
                                    @php
                                        $bookingTotal = $bookingTotalFor($item);
                                        $bookingPaid = (float) $item->paid_amount;
                                        $bookingBalance = max($bookingTotal - $bookingPaid, 0);
                                        $bookingProofPending = $item->payment_status === 'pending'
                                            && ($item->payment_submitted_amount > 0 || $item->payment_proof_path || $item->payment_reference_id);
                                    @endphp
                                    <div class="payment-history-entry p-3 mb-2">
                                        <div class="d-flex justify-content-between gap-2">
                                            <div>
                                                <strong>{{ $item->vehicle }}</strong>
                                                <small class="d-block text-muted">Control Number: {{ $item->control_number }} · {{ $item->pickup_date->format('M d, Y') }}</small>
                                            </div>
                                            <span class="status-badge {{ $bookingProofPending ? 'status-pending' : 'status-unpaid' }}">{{ $bookingProofPending ? 'PENDING VERIFICATION' : 'PENDING PAYMENT' }}</span>
                                        </div>
                                        <div class="small mt-2">Total: ₱{{ number_format($bookingTotal, 2) }} · Paid: ₱{{ number_format($bookingPaid, 2) }}</div>
                                        <strong class="small text-danger d-block">Pending Balance: ₱{{ number_format($bookingBalance, 2) }}</strong>
                                        @if($item->payment_submitted_amount)
                                            <small class="d-block text-muted">Submitted for verification: ₱{{ number_format($item->payment_submitted_amount, 2) }}</small>
                                        @endif
                                        @if($item->payment_reference_id)
                                            <small class="d-block text-muted">Reference: {{ $item->payment_reference_id }}</small>
                                        @endif
                                        @if($item->payment_proof_path)
                                            <button type="button" class="payment-proof-preview mt-2" data-screenshot-url="{{ asset('storage/'.$item->payment_proof_path) }}" onclick="openPaymentScreenshot(this)">
                                                <img class="payment-proof-thumbnail" src="{{ asset('storage/'.$item->payment_proof_path) }}" alt="Submitted booking payment screenshot">
                                                <span class="small text-decoration-underline">View submitted screenshot</span>
                                            </button>
                                        @endif
                                        @if(!$bookingProofPending)
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill mt-2" data-control-number="{{ $item->control_number }}" onclick="selectPaymentTarget(this)">Pay This Booking</button>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-muted small mb-0">No pending booking payments.</p>
                                @endforelse
                            </div>
                            <div class="tab-pane fade" id="extension-history" role="tabpanel" aria-labelledby="extension-history-tab">
                                @php
                                    $extensions = $reservations->flatMap->extensions
                                        ->filter(fn ($item) => in_array($item->status, ['approved', 'payment_pending'], true)
                                            && (float) $item->amount > (float) $item->paid_amount)
                                        ->sortByDesc('created_at');
                                @endphp
                                @forelse($extensions as $extension)
                                    @php
                                        $extensionProofPending = $extension->status === 'payment_pending'
                                            && ($extension->payment_proof_path || $extension->payment_reference_id);
                                        $extensionPaid = (float) $extension->paid_amount;
                                        $extensionBalance = max((float) $extension->amount - $extensionPaid, 0);
                                        $extensionStatus = $extensionBalance <= 0
                                            ? 'COMPLETED'
                                            : ($extensionProofPending
                                                ? 'PENDING VERIFICATION'
                                                : (in_array($extension->status, ['approved', 'payment_pending'], true)
                                                    ? 'PENDING PAYMENT'
                                                    : strtoupper(str_replace('_', ' ', $extension->status))));
                                    @endphp
                                    <div class="payment-history-entry p-3 mb-2">
                                        <div class="d-flex justify-content-between gap-2">
                                            <div>
                                                <strong>{{ $extension->reservation->vehicle }} · Extension</strong>
                                                <small class="d-block text-muted">Control Number: {{ $extension->reservation->control_number }}</small>
                                                <small class="d-block text-muted">{{ $extension->days }} day(s) · Requested {{ $extension->created_at->format('M d, Y') }}</small>
                                            </div>
                                            <span class="status-badge {{ $extensionBalance <= 0 ? 'status-paid' : ($extensionProofPending ? 'status-pending' : 'status-unpaid') }}">{{ $extensionStatus }}</span>
                                        </div>
                                        <div class="small mt-2">Extension Total: ₱{{ number_format($extension->amount, 2) }} · Paid: ₱{{ number_format($extensionPaid, 2) }}</div>
                                        <strong class="small text-danger d-block mt-1">Pending Balance: ₱{{ number_format($extensionBalance, 2) }}</strong>
                                        @if($extension->payment_submitted_amount)
                                            <small class="d-block text-muted">Submitted for verification: ₱{{ number_format($extension->payment_submitted_amount, 2) }}</small>
                                        @endif
                                        @if($extension->payment_reference_id)
                                            <small class="d-block text-muted">Reference: {{ $extension->payment_reference_id }}</small>
                                        @endif
                                        @if($extension->payment_proof_path)
                                            <button type="button" class="payment-proof-preview mt-2" data-screenshot-url="{{ asset('storage/'.$extension->payment_proof_path) }}" onclick="openPaymentScreenshot(this)">
                                                <img class="payment-proof-thumbnail" src="{{ asset('storage/'.$extension->payment_proof_path) }}" alt="Submitted extension payment screenshot">
                                                <span class="small text-decoration-underline">View submitted screenshot</span>
                                            </button>
                                        @endif
                                        @if($extensionBalance > 0 && in_array($extension->status, ['approved', 'payment_pending'], true) && !$extensionProofPending)
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill mt-2" data-control-number="{{ $extension->reservation->control_number }}" onclick="selectPaymentTarget(this)">Pay This Extension</button>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-muted small mb-0">No rental extension history.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="completedPaymentsModal" tabindex="-1" aria-labelledby="completedPaymentsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable payment-completed-dialog">
            <div class="modal-content border-0 rounded-4">
                <div class="modal-header">
                    <div class="completed-payments-title">
                        <span class="completed-payments-title-icon"><i class="fas fa-circle-check" aria-hidden="true"></i></span>
                        <h5 class="modal-title fw-bold mb-0" id="completedPaymentsModalLabel">Completed Payments<small>Your paid bookings and extensions</small></h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body completed-payments-body">
                    @php
                        $completedBookings = $reservations->filter(fn ($item) => !in_array($item->status, ['cancelled', 'void'], true)
                            && $item->payment_status === 'approved'
                            && $bookingTotalFor($item) > 0
                            && (float) $item->paid_amount >= $bookingTotalFor($item));
                        $completedExtensions = $reservations->flatMap->extensions
                            ->filter(fn ($item) => $item->status === 'paid' && (float) $item->amount > 0
                                && (float) $item->paid_amount >= (float) $item->amount)
                            ->sortByDesc('created_at');
                        $completedBookingsByReservation = $completedBookings->keyBy('id');
                        $completedExtensionsByReservation = $completedExtensions->groupBy('reservation_id');
                        $completedReservationIds = $completedBookingsByReservation->keys()
                            ->merge($completedExtensionsByReservation->keys())
                            ->unique();
                    @endphp
                    @forelse($completedReservationIds as $reservationId)
                        @php
                            $booking = $completedBookingsByReservation->get($reservationId);
                            $extensionsForBooking = $completedExtensionsByReservation->get($reservationId, collect());
                            $paymentReservation = $booking ?? $extensionsForBooking->first()->reservation;
                        @endphp
                        <div class="completed-payment-card mb-2" data-completed-control="{{ $paymentReservation->control_number }}">
                            <div class="completed-payment-heading mb-2">
                                <div class="completed-payment-vehicle">
                                    <span class="completed-payment-vehicle-icon"><i class="fas fa-car-side" aria-hidden="true"></i></span>
                                    <div class="min-w-0">
                                        <strong class="completed-payment-vehicle-name">{{ $paymentReservation->vehicle }}</strong>
                                        <span class="completed-payment-control"><i class="fas fa-hashtag" aria-hidden="true"></i>Control Number: {{ $paymentReservation->control_number }}</span>
                                    </div>
                                </div>
                                <span class="status-badge status-paid">COMPLETED</span>
                            </div>
                            <div class="row g-2 completed-payment-details">
                                @if($booking)
                                    <div class="{{ $extensionsForBooking->isNotEmpty() ? 'col-md-6' : 'col-12' }}">
                                        <div class="completed-payment-detail">
                                            <strong class="completed-payment-detail-title"><i class="fas fa-receipt" aria-hidden="true"></i>{{ $booking->vehicle }} · Original Booking</strong>
                                            <div class="completed-payment-amounts">
                                                <div class="completed-payment-amount">
                                                    <span class="completed-payment-amount-label">Booking total</span>
                                                    <strong class="completed-payment-amount-value">₱{{ number_format($bookingTotalFor($booking), 2) }}</strong>
                                                </div>
                                                <div class="completed-payment-amount paid">
                                                    <span class="completed-payment-amount-label">Amount paid</span>
                                                    <strong class="completed-payment-amount-value">₱{{ number_format($booking->paid_amount, 2) }}</strong>
                                                </div>
                                            </div>
                                            <div class="completed-payment-approvals">
                                                @if($approval = $reservationApprovals->get($booking->id))
                                                    <div class="completed-payment-approval"><i class="fas fa-circle-check" aria-hidden="true"></i><span>Approved/recorded by {{ $approval['role'] }}: {{ $approval['name'] }}@if($approval['amount'] !== null) · ₱{{ number_format((float) $approval['amount'], 2) }}@endif · {{ $approval['date'] }}</span></div>
                                                @else
                                                    <div class="small text-muted">Approval staff details are unavailable for this payment.</div>
                                                @endif
                                            </div>
                                        @if($booking->payment_proof_path)
                                            <button type="button" class="btn btn-link btn-sm p-0 mt-2" data-screenshot-url="{{ asset('storage/'.$booking->payment_proof_path) }}" onclick="openPaymentScreenshot(this)">
                                                <i class="fas fa-image me-1"></i>View payment screenshot
                                            </button>
                                        @endif
                                        </div>
                                    </div>
                                @endif
                                @foreach($extensionsForBooking as $extension)
                                    <div class="{{ $booking ? 'col-md-6' : 'col-12' }}">
                                        <div class="completed-payment-detail">
                                            <strong class="completed-payment-detail-title"><i class="fas fa-calendar-plus" aria-hidden="true"></i>{{ $extension->reservation->vehicle }} · Extension</strong>
                                            <div class="completed-payment-amounts">
                                                <div class="completed-payment-amount">
                                                    <span class="completed-payment-amount-label">Extension total</span>
                                                    <strong class="completed-payment-amount-value">₱{{ number_format($extension->amount, 2) }}</strong>
                                                </div>
                                                <div class="completed-payment-amount paid">
                                                    <span class="completed-payment-amount-label">Amount paid</span>
                                                    <strong class="completed-payment-amount-value">₱{{ number_format($extension->paid_amount, 2) }}</strong>
                                                </div>
                                            </div>
                                            <div class="completed-payment-approvals">
                                                @if($approval = $extensionApprovals->get($extension->id))
                                                    <div class="completed-payment-approval"><i class="fas fa-circle-check" aria-hidden="true"></i><span>Approved by {{ $approval['role'] }}: {{ $approval['name'] }}@if($approval['amount'] !== null) · ₱{{ number_format((float) $approval['amount'], 2) }}@endif · {{ $approval['date'] }}</span></div>
                                                @else
                                                    <div class="small text-muted">Approval staff details are unavailable for this payment.</div>
                                                @endif
                                            </div>
                                        @if($extension->payment_proof_path)
                                            <button type="button" class="btn btn-link btn-sm p-0 mt-2" data-screenshot-url="{{ asset('storage/'.$extension->payment_proof_path) }}" onclick="openPaymentScreenshot(this)">
                                                <i class="fas fa-image me-1"></i>View payment screenshot
                                            </button>
                                        @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="completed-payment-empty text-center text-muted">
                            <i class="fas fa-receipt fa-2x text-secondary mb-2" aria-hidden="true"></i>
                            <p class="fw-bold text-dark mb-1">No completed payments yet</p>
                            <small>Your completed bookings and extensions will appear here.</small>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="paymentScreenshotModal" tabindex="-1" aria-labelledby="paymentScreenshotModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 overflow-hidden">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="paymentScreenshotModalLabel">Submitted Payment Screenshot</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center bg-light p-2">
                    <img id="paymentScreenshotImage" src="" alt="Submitted payment screenshot" class="img-fluid rounded" style="max-height:75vh;">
                </div>
            </div>
            <div class="modal fade" id="gcashQrPreviewModal" tabindex="-1" aria-labelledby="gcashQrPreviewModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-0 rounded-4 overflow-hidden">
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold" id="gcashQrPreviewModalLabel">GCash QR Code</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center bg-light p-3">
                            <img id="gcashQrPreviewImage" src="" alt="Full-size GCash QR Code" class="img-fluid rounded gcash-qr-preview-image">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2000">
        <div id="paymentToast" class="toast align-items-center text-bg-dark border-0" role="status" aria-live="polite" aria-atomic="true">
            <div class="d-flex">
                <div id="paymentToastMessage" class="toast-body"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const paymentTargets = @json($paymentTargets);
        const gcashQrCode = document.getElementById('gcashQrCode');
        if (gcashQrCode) {
            const showGcashQrError = function () {
                gcashQrCode.hidden = true;
                document.getElementById('gcashQrError')?.removeAttribute('hidden');
            };
            gcashQrCode.addEventListener('error', showGcashQrError);
            if (gcashQrCode.complete && gcashQrCode.naturalWidth === 0) showGcashQrError();
        }

        function openGcashQrPreview(button) {
            document.getElementById('gcashQrPreviewImage').src = button.dataset.qrSrc;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('gcashQrPreviewModal')).show();
        }

        document.getElementById('gcashQrPreviewModal')?.addEventListener('hidden.bs.modal', function () {
            document.getElementById('gcashQrPreviewImage').src = '';
        });

        function showPaymentToast(message) {
            const toastElement = document.getElementById('paymentToast');
            document.getElementById('paymentToastMessage').textContent = message;
            bootstrap.Toast.getOrCreateInstance(toastElement).show();
        }

        function openPaymentScreenshot(button) {
            const image = document.getElementById('paymentScreenshotImage');
            image.src = button.dataset.screenshotUrl;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('paymentScreenshotModal')).show();
        }

        document.getElementById('paymentScreenshotModal')?.addEventListener('hidden.bs.modal', function() {
            document.getElementById('paymentScreenshotImage').src = '';
        });

        function resetPaymentDetails(message, className) {
            showPaymentControlStep();
            document.getElementById('paymentReservationId').value = '';
            document.getElementById('paymentExtensionId').value = '';
            document.getElementById('paymentDetailsNextButton').disabled = true;
            document.getElementById('paymentAmount').value = '';
            document.getElementById('paymentAmount').removeAttribute('max');
            document.getElementById('paymentAmount').disabled = true;
            document.getElementById('referenceId').value = '';
            document.getElementById('referenceId').disabled = true;
            document.getElementById('paymentProof').value = '';
            document.getElementById('paymentProof').disabled = true;
            document.getElementById('submitPaymentButton').disabled = true;
            document.getElementById('paymentTargetSummary').classList.add('d-none');
            document.getElementById('paymentTargetType').textContent = '';
            document.getElementById('paymentTargetBalance').textContent = '';
            const messageElement = document.getElementById('paymentTargetMessage');
            messageElement.className = 'small mb-3 ' + className;
            messageElement.textContent = message;
        }

        function checkPaymentTarget(showNotFound = false) {
            const controlNumber = document.getElementById('paymentControlNumber').value.trim().toUpperCase();
            if (!controlNumber) {
                resetPaymentDetails('Enter your Control Number to see which balance to pay.', 'text-muted');
                return;
            }
            const target = paymentTargets.find(item => item.controlNumber.toUpperCase() === controlNumber);
            if (!target || target.cancelled) {
                resetPaymentDetails(
                    showNotFound ? 'Control Number was not found in your bookings.' : 'Enter the full Control Number from your booking.',
                    showNotFound ? 'text-danger' : 'text-muted'
                );
                if (showNotFound) {
                    showPaymentToast('Control Number was not found in your bookings.');
                }
                return;
            }

            if (target.bookingWaiting) {
                showWaitingPaymentTarget(
                    target,
                    null,
                    target.bookingBalance,
                    'Pending Payment Book',
                    'Payment is waiting for Admin or Staff verification.'
                );
                return;
            }

            if (target.bookingBalance > 0) {
                activatePaymentTarget(target, null, target.bookingBalance, 'Pending Booking Payment');
                return;
            }

            if (target.extensionId) {
                if (target.extensionWaiting) {
                    showWaitingPaymentTarget(
                        target,
                        target.extensionId,
                        target.extensionBalance,
                        'Extended Book',
                        'Extension payment is waiting for Admin or Staff verification.'
                    );
                    return;
                }
                activatePaymentTarget(target, target.extensionId, target.extensionBalance, 'Extended Rental Payment');
                return;
            }

            resetPaymentDetails('Wala ka nang natitirang babayaran para sa booking na ito.', 'text-success');
            showPaymentToast('Wala ka nang babayaran para sa Control Number na ito.');
        }

        function showWaitingPaymentTarget(target, extensionId, balance, label, message) {
            if (balance > 0) {
                activatePaymentTarget(target, extensionId, balance, label);
            } else {
                resetPaymentDetails(message, 'text-warning');
                document.getElementById('paymentReservationId').value = target.reservationId;
                document.getElementById('paymentExtensionId').value = extensionId || '';
                document.getElementById('paymentTargetType').textContent = label;
                document.getElementById('paymentTargetBalance').textContent = '₱' + Number(balance).toLocaleString('en-PH', { minimumFractionDigits: 2 });
                document.getElementById('paymentTargetSummary').classList.remove('d-none');
            }
            const messageElement = document.getElementById('paymentTargetMessage');
            messageElement.className = 'small mb-3 text-warning';
            messageElement.textContent = message + (balance > 0
                ? ' You can update and resubmit the amount or proof; the new submission will replace the unverified one.'
                : ' No further amount is due unless Admin or Staff updates the booking balance.');
        }

        function activatePaymentTarget(target, extensionId, balance, label) {
            document.getElementById('paymentReservationId').value = target.reservationId;
            document.getElementById('paymentExtensionId').value = extensionId || '';
            document.getElementById('paymentDetailsNextButton').disabled = false;
            document.getElementById('paymentTargetType').textContent = label;
            document.getElementById('paymentTargetBalance').textContent = '₱' + Number(balance).toLocaleString('en-PH', { minimumFractionDigits: 2 });
            document.getElementById('paymentTargetSummary').classList.remove('d-none');
            const amountInput = document.getElementById('paymentAmount');
            amountInput.max = balance;
            amountInput.disabled = false;
            document.getElementById('referenceId').disabled = false;
            document.getElementById('paymentProof').disabled = false;
            document.getElementById('submitPaymentButton').disabled = false;
            const messageElement = document.getElementById('paymentTargetMessage');
            messageElement.className = 'small mb-3 text-success';
            messageElement.textContent = label + ' found for ' + target.controlNumber + '.';
        }

        function showPaymentDetailsStep() {
            if (document.getElementById('paymentDetailsNextButton').disabled) {
                showPaymentToast('Ilagay muna ang valid na Control Number bago magpatuloy.');
                return;
            }
            document.getElementById('paymentControlStep').hidden = true;
            document.getElementById('paymentDetailsStep').hidden = false;
            document.getElementById('paymentAmount').focus();
        }

        function showPaymentControlStep() {
            const controlStep = document.getElementById('paymentControlStep');
            if (controlStep) controlStep.hidden = false;
            const detailsStep = document.getElementById('paymentDetailsStep');
            if (detailsStep) detailsStep.hidden = true;
        }

        function selectPaymentTarget(button) {
            document.getElementById('paymentControlNumber').value = button.dataset.controlNumber;
            checkPaymentTarget();
            document.getElementById('paymentSubmissionForm').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        document.getElementById('paymentControlNumber')?.addEventListener('input', function() {
            checkPaymentTarget();
        });

        document.getElementById('paymentControlNumber')?.addEventListener('blur', function() {
            if (this.value.trim()) checkPaymentTarget(true);
        });

        document.getElementById('paymentControlNumber')?.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                checkPaymentTarget(true);
            }
        });

        document.getElementById('paymentSubmissionForm')?.addEventListener('submit', function(event) {
            if (document.getElementById('submitPaymentButton').disabled) {
                event.preventDefault();
                showPaymentToast('Ilagay muna ang valid na Control Number bago magbayad.');
            }
        });

    </script>
</body>
</html>
