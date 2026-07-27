<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #222; }

    .header { text-align: center; margin-bottom: 12px; border-bottom: 2px solid #333; padding-bottom: 8px; }
    .header h1 { font-size: 15px; font-weight: bold; }
    .header p { font-size: 9px; color: #555; margin-top: 2px; }

    .filters { margin-bottom: 8px; font-size: 8px; color: #444; }
    .filters span { margin-right: 14px; }
    .filters strong { color: #222; }

    table { width: 100%; border-collapse: collapse; }
    thead tr { background: #2c3e50; color: #fff; }
    thead th { padding: 5px 4px; text-align: left; font-size: 8px; font-weight: bold; }
    tbody tr:nth-child(even) { background: #f5f5f5; }
    tbody tr { border-bottom: 1px solid #ddd; vertical-align: top; }
    tbody td { padding: 4px 4px; font-size: 8px; }

    .label { font-size: 7px; color: #777; display: block; }
    .badge { display: inline-block; background: #e8f4fd; color: #1a5276; border-radius: 3px; padding: 1px 4px; font-size: 7px; }

    .footer { margin-top: 10px; font-size: 7px; color: #999; text-align: right; }
    .meta { font-size: 8px; color: #555; margin-bottom: 6px; }
</style>
</head>
<body>

<div class="header">
    <h1>Deep Griha Academy — {{ $user->role !== 'admin' ? 'My Activities' : 'Event / Activity Report' }}</h1>
    <p>Generated on {{ now()->format('d M Y, h:i A') }}</p>
</div>

@if(!empty($filters))
<div class="filters">
    <strong>Filters:</strong>
    @foreach($filters as $label => $val)
    <span><strong>{{ $label }}:</strong> {{ $val }}</span>
    @endforeach
</div>
@endif

<div class="meta">Total: <strong>{{ $events->count() }}</strong> {{ Str::plural('activity', $events->count()) }}</div>

@if($events->isEmpty())
<p style="color:#888;font-style:italic;">No activities found.</p>
@else
<table>
    <thead>
        <tr>
            <th style="width:7%">Date</th>
            <th style="width:13%">Activity / Event Name</th>
            <th style="width:9%">Type</th>
            <th style="width:7%">Grade</th>
            <th style="width:8%">Location</th>
            <th style="width:6%">Duration</th>
            <th style="width:12%">Participants</th>
            <th style="width:4%">Count</th>
            <th style="width:14%">Description</th>
            <th style="width:12%">Purpose / Action</th>
            <th style="width:9%">Remarks</th>
            <th style="width:8%">Logged By</th>
        </tr>
    </thead>
    <tbody>
        @foreach($events as $event)
        <tr>
            <td>
                {{ \Carbon\Carbon::parse($event->start)->format('d M Y') }}
                @if($event->end && $event->end !== $event->start)
                <br><span class="label">to {{ \Carbon\Carbon::parse($event->end)->format('d M Y') }}</span>
                @endif
            </td>
            <td><strong>{{ $event->title }}</strong></td>
            <td>{{ $event->activity_type ?: '—' }}</td>
            <td>{{ $event->grade ?: '—' }}</td>
            <td>{{ $event->location ?: '—' }}</td>
            <td>{{ $event->duration ?: '—' }}</td>
            <td>{{ $event->participants ?: '—' }}</td>
            <td style="text-align:center;">{{ $event->participant_count ?: '—' }}</td>
            <td>{{ $event->description ?: '—' }}</td>
            <td>{{ $event->purpose ?: '—' }}</td>
            <td>{{ $event->outcome ?: '—' }}</td>
            <td>{{ optional($event->creator)->full_name ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

<div class="footer">Deep Griha Academy &bull; Confidential</div>
</body>
</html>
