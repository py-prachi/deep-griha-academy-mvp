<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; padding: 20px; }

    .header { text-align: center; border-bottom: 2px solid #2c3e50; padding-bottom: 10px; margin-bottom: 16px; }
    .header h1 { font-size: 16px; font-weight: bold; color: #2c3e50; }
    .header h2 { font-size: 13px; color: #444; margin-top: 4px; }
    .header p { font-size: 8px; color: #888; margin-top: 3px; }

    .section { margin-bottom: 14px; }
    .section-title {
        font-size: 9px; font-weight: bold; text-transform: uppercase;
        letter-spacing: 0.05em; color: #fff; background: #2c3e50;
        padding: 3px 8px; margin-bottom: 8px;
    }

    .grid { display: table; width: 100%; }
    .grid-row { display: table-row; }
    .grid-cell { display: table-cell; width: 50%; padding: 4px 6px; vertical-align: top; }
    .grid-cell.full { width: 100%; }

    .field-label { font-size: 8px; color: #777; margin-bottom: 2px; }
    .field-value { font-size: 10px; color: #222; line-height: 1.4; }

    .divider { border: none; border-top: 1px solid #ddd; margin: 10px 0; }

    .footer { margin-top: 20px; border-top: 1px solid #ddd; padding-top: 6px;
               font-size: 8px; color: #999; display: table; width: 100%; }
    .footer-left { display: table-cell; }
    .footer-right { display: table-cell; text-align: right; }
</style>
</head>
<body>

<div class="header">
    <h1>Deep Griha Academy</h1>
    <h2>{{ $event->title }}</h2>
    <p>Generated on {{ now()->format('d M Y, h:i A') }}
        &bull; Logged by: {{ optional($event->creator)->full_name ?? '—' }}
    </p>
</div>

{{-- Basic details --}}
<div class="section">
    <div class="section-title">Activity Details</div>
    <div class="grid">
        <div class="grid-row">
            <div class="grid-cell">
                <div class="field-label">Date</div>
                <div class="field-value">
                    {{ \Carbon\Carbon::parse($event->start)->format('d M Y') }}
                    @if($event->end && $event->end !== $event->start)
                        &nbsp;–&nbsp; {{ \Carbon\Carbon::parse($event->end)->format('d M Y') }}
                    @endif
                </div>
            </div>
            <div class="grid-cell">
                <div class="field-label">Activity Type</div>
                <div class="field-value">{{ $event->activity_type ?: '—' }}</div>
            </div>
        </div>
        <div class="grid-row">
            <div class="grid-cell">
                <div class="field-label">Grade</div>
                <div class="field-value">{{ $event->grade ?: '—' }}</div>
            </div>
            <div class="grid-cell">
                <div class="field-label">Location</div>
                <div class="field-value">{{ $event->location ?: '—' }}</div>
            </div>
        </div>
        <div class="grid-row">
            <div class="grid-cell">
                <div class="field-label">Duration</div>
                <div class="field-value">{{ $event->duration ?: '—' }}</div>
            </div>
            <div class="grid-cell">
                <div class="field-label">Participants</div>
                <div class="field-value">{{ $event->participants ?: '—' }}</div>
            </div>
        </div>
        <div class="grid-row">
            <div class="grid-cell">
                <div class="field-label">Participant Count</div>
                <div class="field-value">{{ $event->participant_count ?: '—' }}</div>
            </div>
            <div class="grid-cell"></div>
        </div>
    </div>
</div>

{{-- Narrative fields --}}
@if($event->description || $event->purpose || $event->skills_values || $event->outcome)
<div class="section">
    <div class="section-title">Notes</div>
    @if($event->description)
    <div style="margin-bottom:8px;">
        <div class="field-label">Description</div>
        <div class="field-value">{{ $event->description }}</div>
    </div>
    @endif
    @if($event->purpose)
    <div style="margin-bottom:8px;">
        <div class="field-label">Purpose / Action Taken</div>
        <div class="field-value">{{ $event->purpose }}</div>
    </div>
    @endif
    @if($event->skills_values)
    <div style="margin-bottom:8px;">
        <div class="field-label">Skills / Values</div>
        <div class="field-value">{{ $event->skills_values }}</div>
    </div>
    @endif
    @if($event->outcome)
    <div style="margin-bottom:8px;">
        <div class="field-label">Remarks / Outcome</div>
        <div class="field-value">{{ $event->outcome }}</div>
    </div>
    @endif
</div>
@endif

<div class="footer">
    <div class="footer-left">Deep Griha Academy &bull; Confidential</div>
    <div class="footer-right">{{ now()->format('d M Y') }}</div>
</div>
</body>
</html>
