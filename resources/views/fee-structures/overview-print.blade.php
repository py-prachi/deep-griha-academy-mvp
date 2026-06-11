<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Fee Structure — {{ $session ? $session->session_name : '' }}</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: Arial, sans-serif; font-size: 11px; color: #000; padding: 16px 20px; }

    .header { text-align: center; margin-bottom: 14px; }
    .header h2 { font-size: 16px; font-weight: bold; margin-bottom: 2px; }
    .header p  { font-size: 11px; color: #444; }
    .header .meta { font-size: 10px; color: #666; margin-top: 2px; }

    .divider { border-top: 2px solid #333; margin-bottom: 10px; }

    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #bbb; padding: 5px 7px; vertical-align: top; }
    thead tr:first-child th { background: #2e4057; color: #fff; text-align: center; font-size: 11px; }
    thead tr:last-child th  { background: #dce6f1; color: #000; font-size: 10px; text-align: center; }

    td.class-name { font-weight: bold; white-space: nowrap; }

    .total   { font-weight: bold; font-size: 12px; text-align: right; display: block; }
    .breakdown { font-size: 9px; color: #555; line-height: 1.4; margin-top: 2px; }

    .badge-rte      { background: #d4edda; color: #155724; padding: 2px 6px; border-radius: 3px; font-size: 10px; }
    .badge-coc      { background: #d1ecf1; color: #0c5460; padding: 2px 6px; border-radius: 3px; font-size: 10px; }
    .badge-nil      { color: #aaa; font-size: 10px; }

    .section-pre    { background: #fef9ef; }
    .section-prim   { background: #f0f8ff; }
    .section-upper  { background: #f5fff0; }

    .footer { margin-top: 14px; font-size: 9px; color: #666; text-align: center; border-top: 1px solid #ccc; padding-top: 8px; }

    .no-print { display: block; text-align: right; margin-bottom: 12px; }
    .no-print button {
        background: #2e4057; color: #fff; border: none; padding: 6px 16px;
        border-radius: 4px; cursor: pointer; font-size: 12px;
    }

    @media print {
        .no-print { display: none !important; }
        body { padding: 10px 14px; }
    }
</style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()">&#128438; Print / Save as PDF</button>
</div>

<div class="header">
    <h2>Deep Griha Academy</h2>
    <p>A/p. Deulgaon Gada, Tal. Daund, Dist. Pune</p>
    <p class="meta"><strong>Fee Structure &mdash; Academic Year: {{ $session ? $session->session_name : '—' }}</strong></p>
    <p class="meta">Printed on {{ date('d M Y') }}</p>
</div>

<div class="divider"></div>

@if(empty($structured))
    <p style="text-align:center;color:#999">No fee structures set up for this session.</p>
@else
<table>
    <thead>
        <tr>
            <th rowspan="2" style="width:80px; vertical-align:middle">Class</th>
            <th colspan="2">General</th>
            <th>RTE</th>
            <th>Discount</th>
            <th>COC</th>
        </tr>
        <tr>
            <th style="width:150px">Boys Annual Total</th>
            <th style="width:150px">Girls Annual Total</th>
            <th style="width:90px">Annual Total</th>
            <th style="width:130px">Annual Total</th>
            <th style="width:70px">Status</th>
        </tr>
    </thead>
    <tbody>
        @php
            $prePrimary = ['Nursery', 'Lower KG', 'Upper KG'];
            $primary    = ['Class 1', 'Class 2', 'Class 3', 'Class 4'];
        @endphp
        @foreach($classes as $class)
        @php
            $gen   = isset($structured[$class->id]['general'])  ? $structured[$class->id]['general']  : null;
            $rte   = isset($structured[$class->id]['rte'])      ? $structured[$class->id]['rte']      : null;
            $disc  = isset($structured[$class->id]['discount']) ? $structured[$class->id]['discount'] : null;
            $coc   = isset($structured[$class->id]['coc'])      ? $structured[$class->id]['coc']      : null;

            $girlsTuit  = $gen ? ($gen->girls_tuition_fee !== null ? $gen->girls_tuition_fee : $gen->tuition_fee) : null;
            $girlsTotal = $gen ? ($gen->admission_fee + $girlsTuit + $gen->transport_fee + $gen->other_fee) : null;

            if (in_array($class->class_name, $prePrimary)) {
                $rowClass = 'section-pre';
            } elseif (in_array($class->class_name, $primary)) {
                $rowClass = 'section-prim';
            } else {
                $rowClass = 'section-upper';
            }
        @endphp
        <tr class="{{ $rowClass }}">
            <td class="class-name">{{ $class->class_name }}</td>

            {{-- General boys --}}
            <td>
                @if($gen)
                    <span class="total">₹{{ number_format($gen->total_fee, 0) }}</span>
                    <span class="breakdown">
                        Adm: ₹{{ number_format($gen->admission_fee, 0) }}<br>
                        Tuition: ₹{{ number_format($gen->tuition_fee, 0) }}<br>
                        @if($gen->transport_fee > 0) Transport: ₹{{ number_format($gen->transport_fee, 0) }}<br> @endif
                        @if($gen->other_fee > 0) Other: ₹{{ number_format($gen->other_fee, 0) }}<br> @endif
                    </span>
                @else
                    <span class="badge-nil">—</span>
                @endif
            </td>

            {{-- General girls --}}
            <td>
                @if($gen)
                    <span class="total">₹{{ number_format($girlsTotal, 0) }}</span>
                    <span class="breakdown">
                        Adm: ₹{{ number_format($gen->admission_fee, 0) }}<br>
                        Tuition: ₹{{ number_format($girlsTuit, 0) }}<br>
                        @if($gen->transport_fee > 0) Transport: ₹{{ number_format($gen->transport_fee, 0) }}<br> @endif
                        @if($gen->other_fee > 0) Other: ₹{{ number_format($gen->other_fee, 0) }}<br> @endif
                    </span>
                @else
                    <span class="badge-nil">—</span>
                @endif
            </td>

            {{-- RTE --}}
            <td style="text-align:center; vertical-align:middle">
                @if($rte)
                    <span class="badge-rte">₹{{ number_format($rte->total_fee, 0) }}</span>
                    @if($rte->total_fee == 0)
                        <div class="breakdown" style="margin-top:3px">Govt. reimbursed</div>
                    @endif
                @else
                    <span class="badge-nil">—</span>
                @endif
            </td>

            {{-- Discount --}}
            <td>
                @if($disc)
                    <span class="total">₹{{ number_format($disc->total_fee, 0) }}</span>
                    <span class="breakdown">
                        @if($disc->admission_fee > 0) Adm: ₹{{ number_format($disc->admission_fee, 0) }}<br> @endif
                        Tuition: ₹{{ number_format($disc->tuition_fee, 0) }}<br>
                        @if($disc->transport_fee > 0) Transport: ₹{{ number_format($disc->transport_fee, 0) }}<br> @endif
                    </span>
                @else
                    <span class="badge-nil">—</span>
                @endif
            </td>

            {{-- COC --}}
            <td style="text-align:center; vertical-align:middle">
                @if($coc)
                    <span class="badge-coc">Internal</span>
                @else
                    <span class="badge-nil">—</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="footer">
    All amounts are annual. Girls tuition uses the girls-specific rate where set, otherwise same as boys.
    RTE students are government-sponsored — fees collected from government, not from the student.
    COC (Centre of Change) = Internal Transfer.
    &nbsp;|&nbsp; Authorised by: _________________________ &nbsp;|&nbsp; Date: _____________
</div>
@endif

</body>
</html>
