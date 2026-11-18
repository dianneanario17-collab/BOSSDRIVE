<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rental Agreement - {{ $reservation->control_number }}</title>
    @include('partials.favicon')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #e8e8e8; font-family: Arial, sans-serif; }
        .contract-paper { max-width: 980px; margin: 24px auto; padding: 38px 48px; background: #fff; color: #171717; font-family: Arial, sans-serif; font-size: 14px; line-height: 1.55; box-shadow: 0 4px 18px rgba(0,0,0,.12); }
        .contract-text { white-space: pre-wrap; overflow-wrap: anywhere; }
        @media (max-width: 575.98px) { .contract-paper { margin: 12px; padding: 24px 18px; } }
        @media print {
            @page { size: A4; margin: 14mm; }
            body { background: #fff; }
            .no-print { display: none !important; }
            .contract-paper { max-width: none; margin: 0; padding: 0; box-shadow: none; font-size: 11pt; }
        }
    </style>
</head>
<body>
    <div class="container no-print py-3 d-flex justify-content-between align-items-center gap-2">
        <a href="{{ route('admin.reservations') }}" class="btn btn-sm btn-outline-secondary rounded-pill">Back to Reservations</a>
        <button type="button" class="btn btn-sm btn-dark rounded-pill" onclick="window.print()">
            <i class="fas fa-print me-1"></i> Print Agreement
        </button>
    </div>
    <article class="contract-paper">
        <div class="contract-text">{{ $printableContract }}</div>
    </article>
    @if(request()->query('print') === '1')
        <script>window.addEventListener('load', function () { window.print(); });</script>
    @endif
</body>
</html>
