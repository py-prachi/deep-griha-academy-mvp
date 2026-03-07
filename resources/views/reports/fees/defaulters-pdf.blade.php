<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        h2, h4 { margin: 0; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 12px; }
        .summary-boxes { display: flex; gap: 12px; margin-bottom: 12px; }
        .box { border: 1px solid #999; border-radius: 4px; padding: 8px 12px; text-align: center; flex: 1; }
        .box .label { font-size: 9px; color: #666; }
        .box .value { font-size: 15px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th { background: #222; color: #fff; padding: 6px 8px; text-align: left; font-size: 10px; }
        td { padding: 5px 8px; border-bottom: 1px solid #ddd; font-size: 10px; }
        .text-end { text-align: right; }
        .text-danger { color: #c00; }
        tfoot td { background: #fdecea; font-weight: bold; }
        .footer { margin-top: 16px; font-size: 9px; color: #888; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Deep Griha Academy</h2>
        <h4>Fee Defaulters Report — {{ now()->format('d M Y') }}</h4>
    </div>
    <div class="summary-boxes">
        <div class="box"><div class="label">Total Defaulters</div><div class="value">{{ $defaulters->count() }}</div></div>
        <div class="box"><div class="label">Total Outstanding</div><div class="value">₹{{ number_format($defaulters->sum('balance'), 2) }}</div></div>
    </div>
    <table>
        <thead>
            <tr><th>#</th><th>Student Name</th><th>Class</th><th>Category</th><th>Admission No</th><th class="text-end">Due ₹</th><th class="text-end">Paid ₹</th><th class="text-end">Balance ₹</th></tr>
        </thead>
        <tbody>
            @foreach($defaulters as $i => $d)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $d->first_name }} {{ $d->last_name }}</td>
                <td>{{ $d->class_name ?? '—' }} {{ $d->section_name ?? '' }}</td>
                <td>{{ strtoupper($d->fee_category ?? 'general') }}</td>
                <td>{{ $d->dga_admission_no ?? $d->general_id ?? '—' }}</td>
                <td class="text-end">₹{{ number_format($d->total_due, 2) }}</td>
                <td class="text-end">₹{{ number_format($d->total_paid, 2) }}</td>
                <td class="text-end text-danger">₹{{ number_format($d->balance, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr><td colspan="7" class="text-end">Total Outstanding</td><td class="text-end text-danger">₹{{ number_format($defaulters->sum('balance'), 2) }}</td></tr>
        </tfoot>
    </table>
    <div class="footer">Generated on {{ now()->format('d M Y, h:i A') }}</div>
</body>
</html>
