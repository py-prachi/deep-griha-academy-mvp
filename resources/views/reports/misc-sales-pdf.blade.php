<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        h2, h3 { margin: 0 0 4px 0; }
        .header { text-align: center; margin-bottom: 16px; border-bottom: 2px solid #333; padding-bottom: 8px; }
        .sub { color: #555; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th { background: #f0f0f0; border: 1px solid #ccc; padding: 6px 8px; text-align: left; font-size: 11px; }
        td { border: 1px solid #ddd; padding: 5px 8px; font-size: 11px; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .total-row { background: #fff8dc; font-weight: bold; }
        .badge { background: #ffc107; color: #333; padding: 1px 5px; border-radius: 3px; font-size: 10px; }
        .section-title { font-size: 13px; font-weight: bold; margin: 12px 0 6px 0; border-bottom: 1px solid #ccc; padding-bottom: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Deep Griha Academy</h2>
        <h3>Misc Sales Report</h3>
        <div class="sub">
            Period: {{ \Carbon\Carbon::parse($from)->format('d M Y') }} to {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
            &nbsp;|&nbsp; Generated: {{ now()->format('d M Y, h:i A') }}
        </div>
    </div>

    {{-- Summary --}}
    @if(count($summary) > 0)
    <div class="section-title">Summary by Item</div>
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th class="text-center">Transactions</th>
                <th class="text-end">Total (₹)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($summary as $row)
            <tr>
                <td>{{ $row['label'] }}</td>
                <td class="text-center">{{ $row['count'] }}</td>
                <td class="text-end">{{ number_format($row['total'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2">Grand Total</td>
                <td class="text-end">₹{{ number_format($grandTotal, 2) }}</td>
            </tr>
        </tfoot>
    </table>
    @endif

    {{-- Detail --}}
    <div class="section-title">Transaction Detail</div>
    <table>
        <thead>
            <tr>
                <th>Challan</th>
                <th>Date</th>
                <th>Student</th>
                <th>Items</th>
                <th>Mode</th>
                <th class="text-end">Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
            <tr>
                <td>{{ str_pad($payment->challan_no, 4, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $payment->payment_date->format('d M Y') }}</td>
                <td>{{ $payment->student ? $payment->student->first_name . ' ' . $payment->student->last_name : '—' }}</td>
                <td>
                    @foreach($payment->lineItems as $item)
                        <span class="badge">{{ \App\Models\FeeLineItem::descriptionLabels()[$item->description] ?? $item->description }}</span>
                    @endforeach
                </td>
                <td>{{ ucfirst($payment->payment_mode) }}</td>
                <td class="text-end">{{ number_format($payment->amount_paid, 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
