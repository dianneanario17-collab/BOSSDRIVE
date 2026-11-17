<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BossDrive - Admin Settings</title>
    @include('partials.favicon')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background:#f8f9fa; font-family:'Segoe UI',sans-serif; }
        .main-content { margin-left:250px; }
        .content-container { padding:30px; }
        .settings-card { background:#fff; border:0; border-radius:16px; padding:24px; box-shadow:0 4px 20px rgba(0,0,0,.07); }
        .section-title { color:#b71c1c; font-weight:800; font-size:.9rem; text-transform:uppercase; border-bottom:2px solid #eee; padding-bottom:10px; margin-bottom:18px; }
        .form-label { font-size:.78rem; font-weight:700; color:#555; }
        @media(max-width:992px) { .main-content { margin-left:0; } .content-container { padding:18px 12px; } }
    </style>
</head>
<body>
    @include('admin.partials.navigation', ['adminPageTitle' => 'System', 'adminPageAccent' => 'Settings'])
    <main class="main-content">
        <div class="content-container">
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf
                @method('PUT')
                <section class="settings-card mb-4">
                    <h2 class="section-title"><i class="fas fa-building me-2"></i>Business Information</h2>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label" for="business_name">Business name</label><input id="business_name" name="business_name" class="form-control" value="{{ old('business_name', $settings->business_name) }}" required></div>
                        <div class="col-md-6"><label class="form-label" for="support_name">Support contact name</label><input id="support_name" name="support_name" class="form-control" value="{{ old('support_name', $settings->support_name) }}" required></div>
                        <div class="col-md-6"><label class="form-label" for="support_phone">Support phone</label><input id="support_phone" name="support_phone" class="form-control" value="{{ old('support_phone', $settings->support_phone) }}" required></div>
                        <div class="col-md-6"><label class="form-label" for="messenger_url">Messenger / Facebook link</label><input id="messenger_url" type="url" name="messenger_url" class="form-control" value="{{ old('messenger_url', $settings->messenger_url) }}"></div>
                        <div class="col-12"><label class="form-label" for="pickup_location">Garage pickup address</label><input id="pickup_location" name="pickup_location" class="form-control" value="{{ old('pickup_location', $settings->pickup_location) }}" required></div>
                    </div>
                </section>

                <section class="settings-card mb-4">
                    <h2 class="section-title"><i class="fas fa-file-contract me-2"></i>Rental Agreement &amp; Rules</h2>
                    <label class="form-label" for="agreement_text">Agreement shown before booking</label>
                    <textarea id="agreement_text" name="agreement_text" class="form-control" rows="12" maxlength="15000" required>{{ old('agreement_text', $settings->agreement_text) }}</textarea>
                    <small class="text-muted">Plain text is supported. Use a new line for each rule. Tokens <code>{business_name}</code> and <code>{deposit_amount}</code> are replaced with the current settings.</small>
                    <label class="form-label mt-3" for="rental_location_guide">Rental location guide shown to customers and staff</label>
                    <textarea id="rental_location_guide" name="rental_location_guide" class="form-control" rows="4" maxlength="5000" required>{{ old('rental_location_guide', $settings->rental_location_guide) }}</textarea>
                    <small class="text-muted">Plain text; put each location/rate description on its own line.</small>
                </section>

                <section class="settings-card mb-4">
                    <h2 class="section-title"><i class="fas fa-calendar-check me-2"></i>Booking Policies</h2>
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label" for="security_deposit">Security deposit (₱)</label><input id="security_deposit" type="number" min="0" max="100000" step="1" name="security_deposit" class="form-control" value="{{ old('security_deposit', $settings->security_deposit) }}" required></div>
                        <div class="col-md-4"><label class="form-label" for="booking_window_days">Booking window (days)</label><input id="booking_window_days" type="number" min="1" max="90" name="booking_window_days" class="form-control" value="{{ old('booking_window_days', $settings->booking_window_days) }}" required></div>
                        <div class="col-md-4"><label class="form-label" for="max_rental_days">Maximum rental duration (days)</label><input id="max_rental_days" type="number" min="1" max="30" name="max_rental_days" class="form-control" value="{{ old('max_rental_days', $settings->max_rental_days) }}" required></div>
                        <div class="col-md-4"><label class="form-label" for="min_city_days">Minimum City Driving rental (days)</label><input id="min_city_days" type="number" min="1" max="30" name="min_city_days" class="form-control" value="{{ old('min_city_days', $settings->min_city_days) }}" required></div>
                        <div class="col-md-4"><label class="form-label" for="min_province_days">Minimum Province rental (days)</label><input id="min_province_days" type="number" min="1" max="30" name="min_province_days" class="form-control" value="{{ old('min_province_days', $settings->min_province_days) }}" required></div>
                        <div class="col-md-4"><label class="form-label" for="min_long_distance_days">Minimum Long Distance rental (days)</label><input id="min_long_distance_days" type="number" min="1" max="30" name="min_long_distance_days" class="form-control" value="{{ old('min_long_distance_days', $settings->min_long_distance_days) }}" required></div>
                    </div>
                </section>

                <section class="settings-card mb-4">
                    <h2 class="section-title"><i class="fas fa-coins me-2"></i>Rental Fees &amp; Surcharges</h2>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label" for="driver_daily_fee">With-driver fee per day (₱)</label><input id="driver_daily_fee" type="number" min="0" max="100000" step="0.01" name="driver_daily_fee" class="form-control" value="{{ old('driver_daily_fee', $settings->driver_daily_fee) }}" required></div>
                        <div class="col-md-6"><label class="form-label" for="province_gas_surcharge">Province surcharge — Gas (₱/day)</label><input id="province_gas_surcharge" type="number" min="0" max="100000" step="0.01" name="province_gas_surcharge" class="form-control" value="{{ old('province_gas_surcharge', $settings->province_gas_surcharge) }}" required></div>
                        <div class="col-md-6"><label class="form-label" for="province_diesel_surcharge">Province surcharge — Diesel (₱/day)</label><input id="province_diesel_surcharge" type="number" min="0" max="100000" step="0.01" name="province_diesel_surcharge" class="form-control" value="{{ old('province_diesel_surcharge', $settings->province_diesel_surcharge) }}" required></div>
                        <div class="col-md-6"><label class="form-label" for="long_distance_surcharge">Long Distance surcharge (₱/day)</label><input id="long_distance_surcharge" type="number" min="0" max="100000" step="0.01" name="long_distance_surcharge" class="form-control" value="{{ old('long_distance_surcharge', $settings->long_distance_surcharge) }}" required></div>
                    </div>
                    <small class="text-muted d-block mt-2">These are added per rental day to the vehicle's City Driving price. Edit the base vehicle prices in Price Guide below.</small>
                </section>

                <section class="settings-card mb-4">
                    <h2 class="section-title"><i class="fas fa-message me-2"></i>Admin Page Information</h2>
                    <p class="small text-muted">Optional notices shown at the top of each Admin page. Leave a field blank to hide its notice. These do not change button labels or form-field names.</p>
                    <div class="row g-3">
                        @foreach([
                            'dashboard' => 'Dashboard notice',
                            'users' => 'User Management notice',
                            'reservations' => 'Reservations notice',
                            'billing' => 'Payments & Billing notice',
                            'active-rentals' => 'Active Rentals notice',
                            'vehicles' => 'Vehicle Management notice',
                            'drivers' => 'Driver Management notice',
                            'reports' => 'Reports notice',
                        ] as $noticeKey => $noticeLabel)
                            <div class="col-md-6">
                                <label class="form-label" for="admin_notice_{{ $noticeKey }}">{{ $noticeLabel }}</label>
                                <textarea id="admin_notice_{{ $noticeKey }}" name="admin_page_notices[{{ $noticeKey }}]" class="form-control" rows="2" maxlength="500">{{ old('admin_page_notices.'.$noticeKey, $settings->admin_page_notices[$noticeKey] ?? '') }}</textarea>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="settings-card mb-4">
                    <h2 class="section-title"><i class="fas fa-sliders me-2"></i>Manage Admin Pages</h2>
                    <p class="text-muted mb-3">Vehicle records and their details are edited on Vehicle Management. The Price Guide controls the base vehicle rates. Add or manage drivers on Driver Management.</p>
                    <a class="btn btn-outline-dark rounded-pill me-2 mb-2" href="{{ route('admin.vehicles') }}"><i class="fas fa-car me-1"></i>Add Vehicle / Edit Vehicle Details</a>
                    <a class="btn btn-outline-dark rounded-pill me-2 mb-2" href="{{ route('admin.drivers') }}"><i class="fas fa-id-badge me-1"></i>Driver Management</a>
                    <a class="btn btn-outline-danger rounded-pill me-2 mb-2" href="{{ route('admin.billing') }}"><i class="fas fa-credit-card me-1"></i>Payment Settings</a>
                    <a class="btn btn-outline-danger rounded-pill mb-2" href="{{ route('admin.vehicles') }}"><i class="fas fa-tags me-1"></i>Price Guide</a>
                </section>

                <div class="text-end"><button class="btn btn-danger rounded-pill px-5 fw-bold" type="submit"><i class="fas fa-save me-2"></i>Save Settings</button></div>
            </form>

            <section class="settings-card mt-4" id="add-driver">
                <h2 class="section-title"><i class="fas fa-user-plus me-2"></i>Add Driver</h2>
                <p class="small text-muted">New drivers are saved to Driver Management and can be selected for bookings.</p>
                <form method="POST" action="{{ route('admin.drivers.store') }}" class="row g-3 align-items-end">
                    @csrf
                    <input type="hidden" name="return_to" value="settings">
                    <div class="col-md-5"><label class="form-label" for="new_driver_name">Driver full name</label><input id="new_driver_name" name="name" class="form-control" maxlength="255" value="{{ old('name') }}" required></div>
                    <div class="col-md-5"><label class="form-label" for="new_driver_contact">Contact number</label><input id="new_driver_contact" name="contact" class="form-control" maxlength="30" value="{{ old('contact') }}" required></div>
                    <div class="col-md-2 d-grid"><button class="btn btn-outline-danger fw-bold" type="submit"><i class="fas fa-plus me-1"></i>Add Driver</button></div>
                </form>
            </section>
        </div>
    </main>
</body>
</html>
