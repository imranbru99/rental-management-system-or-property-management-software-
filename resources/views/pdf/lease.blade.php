<!DOCTYPE html>
<html lang="{{ $lease->organization?->country === 'BD' ? 'bn' : 'en' }}">
<head>
    <meta charset="utf-8">
    <title>Lease {{ $lease->number }}</title>
    <style>
        body { font-family: DejaVu Sans, Noto Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        .muted { color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { text-align: left; padding: 6px 4px; border-bottom: 1px solid #ddd; }
    </style>
</head>
<body>
    <h1>{{ $lease->organization?->name ?? 'RentOS' }}</h1>
    <p class="muted">Residential lease agreement · {{ $lease->number }}</p>

    <table>
        <tr><th>Property</th><td>{{ $lease->property?->name }} — {{ $lease->property?->full_address }}</td></tr>
        <tr><th>Tenant</th><td>{{ $lease->primaryTenant?->name }} ({{ $lease->primaryTenant?->email }})</td></tr>
        <tr><th>Term</th><td>{{ $lease->starts_on?->toDateString() }} to {{ $lease->ends_on?->toDateString() }}</td></tr>
        <tr><th>Monthly rent</th><td>{{ \App\Support\Money::format($lease->rent, $lease->currency) }}</td></tr>
        <tr><th>Security deposit</th><td>{{ \App\Support\Money::format($lease->security_deposit, $lease->currency) }}</td></tr>
        <tr><th>Due day</th><td>{{ $lease->due_day }}</td></tr>
        <tr><th>Notice period</th><td>{{ $lease->notice_days }} days</td></tr>
    </table>

    <h2>Terms</h2>
    <p>{!! nl2br(e($lease->terms ?: 'Standard jurisdiction-aware residential terms apply. Tenant shall pay rent on time, maintain the premises, and give written notice before vacating.')) !!}</p>
</body>
</html>
