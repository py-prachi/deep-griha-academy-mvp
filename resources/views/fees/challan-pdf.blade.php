<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; margin: 0; }
    .copy { width: 100%; border: 1px solid #000; margin-bottom: 10px; padding: 8px; page-break-after: always; }
    .copy:last-child { page-break-after: avoid; }
    .copy-label { text-align: right; font-size: 9px; font-style: italic; color: #555; }
    .header { text-align: center; font-weight: bold; font-size: 13px; }
    .sub-header { text-align: center; font-size: 10px; }
    .challan-no { font-size: 16px; font-weight: bold; color: #c00; }
    table { width: 100%; border-collapse: collapse; margin-top: 6px; }
    th, td { border: 1px solid #000; padding: 3px 5px; }
    th { background: #eee; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .sig-line { border-bottom: 1px solid #000; height: 30px; width: 80%; }
    .row { display: table; width: 100%; }
    .col { display: table-cell; }
    .divider { border-top: 1px dashed #999; margin: 8px 0; }
</style>
</head>
<body>
@foreach(['School Copy', 'Student Copy', 'Bank Copy'] as $copy)
<div class="copy">
    <div class="copy-label">{{ $copy }}</div>
    <div class="header">DEEP GRIHA SOCIETY (Deep Griha Academy)</div>
    <div class="sub-header">A/p. Deulgaon gada Tal. Daund, Dist. Pune</div>
    <div class="sub-header">Bank of Maharashtra, Kedgaon Branch &nbsp;|&nbsp; A/c No: 60228514625</div>

    <div class="row" style="margin-top:6px">
        <div class="col">
            <span class="challan-no">Challan No: {{ str_pad($payment->challan_no, 4, '0', STR_PAD_LEFT) }}</span>
        </div>
        <div class="col text-right">
            Date: {{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}
        </div>
    </div>

    <div class="row" style="margin-top:4px">
        <div class="col">
            <strong>Name of student:</strong> {{ $student->first_name }} {{ $student->last_name }}
        </div>
        <div class="col text-right">
            <strong>Class:</strong> {{ $promotion->section->schoolClass->class_name ?? '—' }}
            &nbsp; <strong>Div:</strong> {{ $promotion->section->section_name ?? '—' }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width:30px">Sr.</th>
                <th>Description</th>
                <th class="text-right" style="width:80px">Amount ₹</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payment->lineItems as $i => $item)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ \App\Models\FeeLineItem::descriptionLabels()[$item->description] ?? $item->description }}</td>
                <td class="text-right">{{ number_format($item->amount, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td class="text-center">1</td>
                <td>Fee Payment
                    @if($payment->is_internal_transfer) (DGA Internal Transfer) @endif
                </td>
                <td class="text-right">{{ number_format($payment->amount_paid, 2) }}</td>
            </tr>
            @endforelse
            {{-- Blank rows to fill space --}}
            @for($i = $payment->lineItems->count(); $i < 5; $i++)
            <tr><td>&nbsp;</td><td></td><td></td></tr>
            @endfor
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="text-right"><strong>Total ₹</strong></td>
                <td class="text-right"><strong>{{ number_format($payment->amount_paid, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top:5px">
        <strong>Mode:</strong>
        @if($payment->payment_mode == 'cheque')
            Cheque | No: {{ $payment->cheque_no }} | Date: {{ $payment->cheque_date }} | Bank: {{ $payment->bank_name }}
        @elseif($payment->payment_mode == 'qr')
            QR/UPI | Ref: {{ $payment->transaction_ref ?? '—' }}
        @else
            Cash
        @endif
    </div>

    <div class="row" style="margin-top:15px">
        <div class="col">
            <div class="sig-line"></div>
            <small>Signature of School Clerk</small>
        </div>
        <div class="col text-right">
            <div class="sig-line" style="margin-left:auto"></div>
            <small>Depositor's Signature</small>
        </div>
    </div>
</div>

@endforeach
</body>
</html>
