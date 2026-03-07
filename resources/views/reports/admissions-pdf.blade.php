<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        h2, h4 { margin: 0; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 12px; }
        .summary-boxes { display: flex; gap: 10px; margin-bottom: 12px; }
        .box { border: 1px solid #999; border-radius: 4px; padding: 8px; text-align: center; flex: 1; }
        .box .label { font-size: 9px; color: #666; }
        .box .value { font-size: 18px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #222; color: #fff; padding: 5px 6px; text-align: left; font-size: 9px; }
        td { padding: 4px 6px; border-bottom: 1px solid #eee; font-size: 9px; }
        .footer { margin-top: 16px; font-size: 9px; color: #888; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Deep Griha Academy</h2>
        <h4>Admissions Report — {{ $academic_year }}</h4>
    </div>
    <div class="summary-boxes">
        <div class="box"><div class="label">Inquiry</div><div class="value">{{ $summary['inquiry'] }}</div></div>
        <div class="box"><div class="label">Pending</div><div class="value">{{ $summary['pending'] }}</div></div>
        <div class="box"><div class="label">Confirmed</div><div class="value">{{ $summary['confirmed'] }}</div></div>
        <div class="box"><div class="label">Cancelled</div><div class="value">{{ $summary['cancelled'] }}</div></div>
        <div class="box"><div class="label">Total</div><div class="value">{{ array_sum($summary) }}</div></div>
    </div>
    <table>
        <thead>
            <tr><th>#</th><th>Student Name</th><th>Class</th><th>Category</th><th>DGA No / General ID</th><th>Date</th><th>Status</th></tr>
        </thead>
        <tbody>
            @foreach($admissions as $i => $admission)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $admission->child_first_name }} {{ $admission->child_last_name }}</td>
                <td>{{ $admission->schoolClass->class_name ?? '—' }}</td>
                <td>{{ strtoupper($admission->fee_category ?? 'general') }}</td>
                <td>{{ $admission->dga_admission_no ?? $admission->general_id ?? '—' }}</td>
                <td>{{ \Carbon\Carbon::parse($admission->created_at)->format('d M Y') }}</td>
                <td>{{ ucfirst($admission->status) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">Generated on {{ now()->format('d M Y, h:i A') }}</div>
</body>
</html>
