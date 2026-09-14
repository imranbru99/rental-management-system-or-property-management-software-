<!DOCTYPE html>
<html lang="{{ $invoice->organization?->country === 'BD' ? 'bn' : 'en' }}">
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->number }}</title>
    <style>
        body { font-family: DejaVu Sans, Noto Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        .muted { color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { text-align: left; padding: 6px 4px; border-bottom: 1px solid #ddd; }
        .right { text-align: right; }
        .total { font-weight: bold; }
    </style>
</head>
<body>
    <h1>{{ $invoice->organization?->name ?? 'RentOS' }}</h1>
    <p class="muted">Invoice {{ $invoice->number }} · {{ $invoice->type->getLabel() }}</p>
    <p>Bill to: {{ $invoice->payer?->name }}<br>Property: {{ $invoice->property?->name }}</p>
    <p>Issue date: {{ $invoice->issue_date?->toDateString() }} · Due: {{ $invoice->due_date?->toDateString() }}</p>

    <table>
        <thead>
            <tr><th>Description</th><th class="right">Amount</th></tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="right">{{ \App\Support\Money::format($item->amount, $invoice->currency) }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td>Total</td>
                <td class="right">{{ \App\Support\Money::format($invoice->total, $invoice->currency) }}</td>
            </tr>
            <tr>
                <td>Paid</td>
                <td class="right">{{ \App\Support\Money::format($invoice->amount_paid, $invoice->currency) }}</td>
            </tr>
            <tr class="total">
                <td>Balance due</td>
                <td class="right">{{ \App\Support\Money::format($invoice->balanceDue(), $invoice->currency) }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
