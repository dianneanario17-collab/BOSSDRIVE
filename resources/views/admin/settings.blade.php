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
                    <h2 class="section-title"><i class="fas fa-sliders me-2"></i>Existing Settings</h2>
                    <p class="text-muted mb-3">Payment details and vehicle rates are managed in their existing Admin pages.</p>
                    <a class="btn btn-outline-danger rounded-pill me-2 mb-2" href="{{ route('admin.billing') }}"><i class="fas fa-credit-card me-1"></i>Payment Settings</a>
                    <a class="btn btn-outline-danger rounded-pill mb-2" href="{{ route('admin.vehicles') }}"><i class="fas fa-tags me-1"></i>Price Guide</a>
                </section>

                <div class="text-end"><button class="btn btn-danger rounded-pill px-5 fw-bold" type="submit"><i class="fas fa-save me-2"></i>Save Settings</button></div>
            </form>
        </div>
    </main>
</body>
</html>
