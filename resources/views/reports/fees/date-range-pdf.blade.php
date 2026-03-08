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
        tfoot td { background: #e9f9ee; font-weight: bold; }
        .footer { margin-top: 16px; font-size: 9px; color: #888; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Deep Griha Academy</h2>
        <h4>Collection Report: {{ \Carbon\Carbon::parse($from)->format('d M Y') }} — {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</h4>
    </div>
    <div class="summary-boxes">
        <div class="box"><div class="label">Total Collected</div><div class="value">₹{{ number_format($total, 2) }}</div></div>
        <div class="box"><div class="label">Payments</div><div class="value">{{ $payments->count() }}</div></div>
        <div class="box"><div class="label">Cash</div><div class="value">{{ $payments->where('payment_mode','cash')->count() }}</div></div>
        <div class="box"><div class="label">QR/UPI</div><div class="value">{{ $payments->where('payment_mode','qr')->count() }}</div></div>
        <div class="box"><div class="label">Cheque</div><div class="value">{{ $payments->where('payment_mode','cheque')->count() }}</div></div>
    </div>
    <table>
        <thead>
            <tr><th>Challan</th><th>Date</th><th>Student Name</th><th>Class</th><th>Category</th><th>Mode</th><th class="text-end">Amount ₹</th></tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
            <tr>
                <td>{{ str_pad($payment->challan_no, 4, '0', STR_PAD_LEFT) }}</td>
                <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
                <td>{{ $payment->student->first_name ?? '—' }} {{ $payment->student->last_name ?? '' }}</td>
                <td>{{ optional(optional(optional($payment->student)->promotions)->first())->section->schoolClass->class_name ?? '—' }} {{ optional(optional(optional($payment->student)->promotions)->first())->section->section_name ?? '' }}</td>
                <td>{{ $payment->is_internal_transfer ? 'COC/Internal' : ucfirst($payment->student->fee_category ?? 'general') }}</td>
                <td>{{ strtoupper($payment->payment_mode) }}</td>
                <td class="text-end">₹{{ number_format($payment->amount_paid, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr><td colspan="6" class="text-end">Total Collected</td><td class="text-end">₹{{ number_format($total, 2) }}</td></tr>
        </tfoot>
    </table>
    <div class="footer">Generated on {{ now()->format('d M Y, h:i A') }}</div>
</body>
</html>
