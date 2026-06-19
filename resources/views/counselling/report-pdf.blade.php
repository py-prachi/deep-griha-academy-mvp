<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 8.5px; color: #111; }

    .header { border-bottom: 2px solid #000; padding-bottom: 6px; margin-bottom: 10px; }
    .header h2 { font-size: 13px; margin: 0 0 2px 0; }
    .header h4 { font-size: 9px; color: #444; margin: 0; font-weight: normal; }

    .summary-boxes { display: table; width: 100%; margin-bottom: 10px; }
    .summary-box { display: table-cell; width: 33%; border: 1px solid #ccc; padding: 6px 8px; text-align: center; }
    .summary-box .num { font-size: 18px; font-weight: bold; }
    .summary-box .lbl { font-size: 7.5px; color: #555; }

    table { width: 100%; border-collapse: collapse; }
    th { background: #222; color: #fff; padding: 4px 5px; text-align: left; font-size: 8px; white-space: nowrap; }
    td { padding: 3px 5px; border-bottom: 1px solid #ddd; font-size: 8px; vertical-align: top; }
    tr:nth-child(even) td { background: #f7f7f7; }
    tfoot td { background: #eee !important; font-weight: bold; border-top: 1.5px solid #999; }

    .badge-danger  { background: #dc3545; color: #fff; padding: 1px 4px; border-radius: 3px; font-size: 7px; }
    .badge-warning { background: #ffc107; color: #000; padding: 1px 4px; border-radius: 3px; font-size: 7px; }
    .badge-grey    { background: #6c757d; color: #fff; padding: 1px 4px; border-radius: 3px; font-size: 7px; }
    .badge-green   { background: #198754; color: #fff; padding: 1px 4px; border-radius: 3px; font-size: 7px; }

    .footer { margin-top: 12px; font-size: 7px; color: #888; border-top: 1px solid #ccc; padding-top: 4px; }
</style>
</head>
<body>

<div class="header">
    <h2>Deep Griha Academy — Counselling Report</h2>
    <h4>
        @if($type === 'active')
            Active Cases List
        @elseif($type === 'classwise')
            Class-wise Summary
        @elseif($type === 'reasonwise')
            Reason-wise Summary
        @elseif($type === 'longduration')
            Long-duration Cases ({{ $threshold }}+ days open)
        @elseif($type === 'monthly')
            Monthly Activity — {{ $monthStart->format('F Y') }}
        @elseif($type === 'closed')
            Closed Cases History
        @endif
        &nbsp;&nbsp;|&nbsp;&nbsp;Generated: {{ now()->format('d M Y, h:i A') }}
    </h4>
</div>

{{-- ── ACTIVE CASES ─────────────────────────────────────── --}}
@if($type === 'active')
<p style="font-size:8px; color:#555; margin-bottom:6px;">{{ $activeCases->count() }} active case(s) &nbsp;|&nbsp; Student names not shown for confidentiality</p>
@if($activeCases->isEmpty())
    <p style="color:#888;">No active counselling cases at this time.</p>
@else
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Student ID</th>
            <th>Class</th>
            <th>Reason</th>
            <th>Start Date</th>
            <th>Days Active</th>
            <th>Session Notes</th>
        </tr>
    </thead>
    <tbody>
        @foreach($activeCases as $i => $c)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td><strong>{{ $c->display_id }}</strong></td>
            <td>{{ $c->class_name }}{{ $c->section_name !== '—' ? ' '.$c->section_name : '' }}</td>
            <td>{{ $c->reason ?: '—' }}</td>
            <td>{{ \Carbon\Carbon::parse($c->start_date)->format('d M Y') }}</td>
            <td>
                @if($c->days_active >= 90)
                    <span class="badge-danger">{{ $c->days_active }} days</span>
                @elseif($c->days_active >= 30)
                    <span class="badge-warning">{{ $c->days_active }} days</span>
                @else
                    <span class="badge-grey">{{ $c->days_active }} days</span>
                @endif
            </td>
            <td>{{ $c->remarkLogs->count() }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- ── CLASS-WISE SUMMARY ───────────────────────────────── --}}
@elseif($type === 'classwise')
<p style="font-size:8px; color:#555; margin-bottom:6px;">{{ $classwise->count() }} class(es) with counselling records</p>
@if($classwise->isEmpty())
    <p style="color:#888;">No counselling records found.</p>
@else
<table style="max-width:400px;">
    <thead>
        <tr>
            <th>#</th>
            <th>Class</th>
            <th style="text-align:center;">Active</th>
            <th style="text-align:center;">Closed</th>
            <th style="text-align:center;">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($classwise as $i => $row)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $row['class'] }}</td>
            <td style="text-align:center;">{{ $row['active'] }}</td>
            <td style="text-align:center;">{{ $row['closed'] }}</td>
            <td style="text-align:center; font-weight:bold;">{{ $row['total'] }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2">Total</td>
            <td style="text-align:center;">{{ $classwise->sum('active') }}</td>
            <td style="text-align:center;">{{ $classwise->sum('closed') }}</td>
            <td style="text-align:center;">{{ $classwise->sum('total') }}</td>
        </tr>
    </tfoot>
</table>
@endif

{{-- ── REASON-WISE SUMMARY ──────────────────────────────── --}}
@elseif($type === 'reasonwise')
<p style="font-size:8px; color:#555; margin-bottom:6px;">{{ $reasonwise->count() }} reason category(s)</p>
@if($reasonwise->isEmpty())
    <p style="color:#888;">No counselling records found.</p>
@else
<table style="max-width:450px;">
    <thead>
        <tr>
            <th>#</th>
            <th>Reason / Category</th>
            <th style="text-align:center;">Active</th>
            <th style="text-align:center;">Closed</th>
            <th style="text-align:center;">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($reasonwise as $i => $row)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $row->reason }}</td>
            <td style="text-align:center;">{{ $row->active }}</td>
            <td style="text-align:center;">{{ $row->closed }}</td>
            <td style="text-align:center; font-weight:bold;">{{ $row->total }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2">Total</td>
            <td style="text-align:center;">{{ $reasonwise->sum('active') }}</td>
            <td style="text-align:center;">{{ $reasonwise->sum('closed') }}</td>
            <td style="text-align:center;">{{ $reasonwise->sum('total') }}</td>
        </tr>
    </tfoot>
</table>
@endif

{{-- ── LONG-DURATION CASES ──────────────────────────────── --}}
@elseif($type === 'longduration')
<p style="font-size:8px; color:#555; margin-bottom:6px;">{{ $longDuration->count() }} active case(s) open for {{ $threshold }}+ days &nbsp;|&nbsp; Student names not shown for confidentiality</p>
@if($longDuration->isEmpty())
    <p style="color:#888;">No active cases found open for {{ $threshold }}+ days.</p>
@else
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Student ID</th>
            <th>Class</th>
            <th>Reason</th>
            <th>Start Date</th>
            <th>Days Open</th>
            <th>Session Notes</th>
        </tr>
    </thead>
    <tbody>
        @foreach($longDuration as $i => $c)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td><strong>{{ $c->display_id }}</strong></td>
            <td>{{ $c->class_name }}{{ $c->section_name !== '—' ? ' '.$c->section_name : '' }}</td>
            <td>{{ $c->reason ?: '—' }}</td>
            <td>{{ \Carbon\Carbon::parse($c->start_date)->format('d M Y') }}</td>
            <td><span class="badge-danger">{{ $c->days_active }} days</span></td>
            <td>{{ $c->remarkLogs->count() }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- ── MONTHLY ACTIVITY ─────────────────────────────────── --}}
@elseif($type === 'monthly')
<div class="summary-boxes">
    <div class="summary-box">
        <div class="num">{{ $monthlyOpened }}</div>
        <div class="lbl">New Cases Opened</div>
    </div>
    <div class="summary-box">
        <div class="num">{{ $monthlyClosed }}</div>
        <div class="lbl">Cases Closed</div>
    </div>
    <div class="summary-box">
        <div class="num">{{ $monthlyRemarks }}</div>
        <div class="lbl">Session Notes Added</div>
    </div>
</div>
<p style="font-size:8px; color:#555; margin-bottom:6px;">{{ $monthlyCases->count() }} case(s) active during {{ $monthStart->format('F Y') }} &nbsp;|&nbsp; Student names not shown for confidentiality</p>
@if($monthlyCases->isEmpty())
    <p style="color:#888;">No counselling activity in {{ $monthStart->format('F Y') }}.</p>
@else
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Student ID</th>
            <th>Class</th>
            <th>Reason</th>
            <th>Start Date</th>
            <th>Status</th>
            <th>Session Notes</th>
        </tr>
    </thead>
    <tbody>
        @foreach($monthlyCases as $i => $c)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td><strong>{{ $c->display_id }}</strong></td>
            <td>{{ $c->class_name }}{{ $c->section_name !== '—' ? ' '.$c->section_name : '' }}</td>
            <td>{{ $c->reason ?: '—' }}</td>
            <td>{{ \Carbon\Carbon::parse($c->start_date)->format('d M Y') }}</td>
            <td>
                @if($c->end_date)
                    <span class="badge-grey">Closed {{ \Carbon\Carbon::parse($c->end_date)->format('d M') }}</span>
                @else
                    <span class="badge-green">Active</span>
                @endif
            </td>
            <td>{{ $c->remarkLogs->count() }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- ── CLOSED CASES ─────────────────────────────────────── --}}
@elseif($type === 'closed')
<p style="font-size:8px; color:#555; margin-bottom:6px;">{{ $closedCases->count() }} closed case(s) &nbsp;|&nbsp; Student names not shown for confidentiality</p>
@if($closedCases->isEmpty())
    <p style="color:#888;">No closed counselling cases yet.</p>
@else
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Student ID</th>
            <th>Class</th>
            <th>Reason</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Duration</th>
            <th>Session Notes</th>
        </tr>
    </thead>
    <tbody>
        @foreach($closedCases as $i => $c)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td><strong>{{ $c->display_id }}</strong></td>
            <td>{{ $c->class_name }}{{ $c->section_name !== '—' ? ' '.$c->section_name : '' }}</td>
            <td>{{ $c->reason ?: '—' }}</td>
            <td>{{ \Carbon\Carbon::parse($c->start_date)->format('d M Y') }}</td>
            <td>{{ \Carbon\Carbon::parse($c->end_date)->format('d M Y') }}</td>
            <td>{{ $c->duration_days }} days</td>
            <td>{{ $c->remarkLogs->count() }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif
@endif

<div class="footer">
    Deep Griha Academy &nbsp;|&nbsp; Confidential — Student names not included in this report &nbsp;|&nbsp; Generated {{ now()->format('d M Y, h:i A') }}
</div>
</body>
</html>
