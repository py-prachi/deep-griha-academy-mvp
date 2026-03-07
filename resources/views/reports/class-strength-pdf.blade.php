<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        h2, h4 { margin: 0; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #222; color: #fff; padding: 6px 8px; text-align: left; font-size: 10px; }
        td { padding: 5px 8px; border-bottom: 1px solid #ddd; font-size: 10px; }
        .text-end { text-align: right; }
        tfoot td { background: #222; color: #fff; font-weight: bold; }
        .footer { margin-top: 16px; font-size: 9px; color: #888; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Deep Griha Academy</h2>
        <h4>Class Strength Report — {{ now()->format('d M Y') }}</h4>
    </div>
    @php
        $grandTotal    = array_sum(array_column((array)$strength, 'total'));
        $grandGeneral  = array_sum(array_column((array)$strength, 'general'));
        $grandRte      = array_sum(array_column((array)$strength, 'rte'));
        $grandCoc      = array_sum(array_column((array)$strength, 'coc'));
        $grandDiscount = array_sum(array_column((array)$strength, 'discount'));
    @endphp
    <table>
        <thead>
            <tr><th>Class</th><th>Division</th><th class="text-end">Total</th><th class="text-end">General</th><th class="text-end">RTE</th><th class="text-end">COC</th><th class="text-end">Discount</th></tr>
        </thead>
        <tbody>
            @foreach($strength as $row)
            <tr>
                <td>{{ $row->class_name }}</td><td>{{ $row->section_name }}</td>
                <td class="text-end">{{ $row->total }}</td><td class="text-end">{{ $row->general }}</td>
                <td class="text-end">{{ $row->rte }}</td><td class="text-end">{{ $row->coc }}</td>
                <td class="text-end">{{ $row->discount }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr><td colspan="2" class="text-end">Grand Total</td><td class="text-end">{{ $grandTotal }}</td><td class="text-end">{{ $grandGeneral }}</td><td class="text-end">{{ $grandRte }}</td><td class="text-end">{{ $grandCoc }}</td><td class="text-end">{{ $grandDiscount }}</td></tr>
        </tfoot>
    </table>
    <div class="footer">Generated on {{ now()->format('d M Y, h:i A') }}</div>
</body>
</html>
