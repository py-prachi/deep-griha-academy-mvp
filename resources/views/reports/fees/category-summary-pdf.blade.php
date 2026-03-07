<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        h2, h4 { margin: 0; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th { background: #222; color: #fff; padding: 6px 8px; text-align: left; font-size: 10px; }
        td { padding: 6px 8px; border-bottom: 1px solid #ddd; font-size: 10px; }
        .text-end { text-align: right; }
        .text-danger { color: #c00; }
        .text-success { color: #080; }
        tfoot td { background: #222; color: #fff; font-weight: bold; }
        .footer { margin-top: 16px; font-size: 9px; color: #888; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Deep Griha Academy</h2>
        <h4>Category-wise Fee Summary — {{ now()->format('d M Y') }}</h4>
    </div>
    <table>
        <thead>
            <tr><th>Category</th><th class="text-end">Students</th><th class="text-end">Total Due ₹</th><th class="text-end">Collected ₹</th><th class="text-end">Outstanding ₹</th><th class="text-end">Collection %</th></tr>
        </thead>
        <tbody>
            @foreach($summary as $row)
            @php $pct = $row->total_due > 0 ? round(($row->total_collected / $row->total_due) * 100, 1) : 0; @endphp
            <tr>
                <td>{{ strtoupper($row->fee_category ?? 'General') }}</td>
                <td class="text-end">{{ $row->student_count }}</td>
                <td class="text-end">₹{{ number_format($row->total_due, 2) }}</td>
                <td class="text-end text-success">₹{{ number_format($row->total_collected, 2) }}</td>
                <td class="text-end {{ $row->total_balance > 0 ? 'text-danger' : 'text-success' }}">₹{{ number_format($row->total_balance, 2) }}</td>
                <td class="text-end">{{ $pct }}%</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>Total</td>
                <td class="text-end">{{ collect($summary)->sum('student_count') }}</td>
                <td class="text-end">₹{{ number_format(collect($summary)->sum('total_due'), 2) }}</td>
                <td class="text-end">₹{{ number_format(collect($summary)->sum('total_collected'), 2) }}</td>
                <td class="text-end">₹{{ number_format(collect($summary)->sum('total_balance'), 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    <div class="footer">Generated on {{ now()->format('d M Y, h:i A') }}</div>
</body>
</html>
