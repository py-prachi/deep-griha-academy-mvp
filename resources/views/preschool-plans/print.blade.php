<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pre-School Daily Plan — {{ $plan->plan_date->format('d M Y') }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #222; padding: 20px; }
        h2 { font-size: 16px; text-align: center; margin-bottom: 4px; }
        .subtitle { text-align: center; font-size: 12px; color: #555; margin-bottom: 16px; }
        .plan-header { margin-bottom: 16px; padding: 10px 14px; border: 1px solid #ccc; background: #f9f9f9; }
        .plan-header table { width: auto; border: none; }
        .plan-header td { border: none; padding: 2px 12px 2px 0; }
        .plan-header td:first-child { font-weight: bold; white-space: nowrap; }
        .activity-block { margin-bottom: 14px; border: 1px solid #aaa; }
        .activity-title { background: #d8e4f0; font-weight: bold; padding: 5px 8px; font-size: 13px; }
        table.activity-fields { width: 100%; border-collapse: collapse; }
        table.activity-fields th, table.activity-fields td { border: 1px solid #aaa; padding: 5px 8px; vertical-align: top; }
        table.activity-fields th { background: #f0f0f0; font-weight: bold; width: 25%; white-space: nowrap; }
        table.activity-fields td { white-space: pre-wrap; }
        .action-bar { display: flex; gap: 8px; align-items: center; margin-bottom: 20px; padding: 10px 12px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; }
        .action-bar a, .action-bar button { padding: 5px 14px; border-radius: 4px; font-size: 12px; cursor: pointer; text-decoration: none; }
        .btn-back { background: #fff; border: 1px solid #aaa; color: #333; }
        .btn-print { background: #198754; border: 1px solid #198754; color: #fff; }
        .pdf-hint { font-size: 11px; color: #666; margin-left: auto; }
        @media print {
            body { padding: 10px; }
            .action-bar { display: none; }
        }
    </style>
</head>
<body>

<div class="action-bar">
    <a href="javascript:history.back()" class="btn-back">&#8592; Back</a>
    <button onclick="window.print()" class="btn-print">&#128438; Print</button>
    <span class="pdf-hint">To save as PDF: click Print &rarr; choose "Save as PDF" as the destination.</span>
</div>

<h2>Deep Griha Academy</h2>
<div class="subtitle">
    @if($session){{ $session->session }} Academic Session — @endif
    Pre-School Daily Activity Plan
</div>

<div class="plan-header">
    <table>
        <tr>
            <td>Class:</td>
            <td>{{ optional($plan->schoolClass)->class_name }} {{ optional($plan->section)->section_name }}</td>
        </tr>
        <tr>
            <td>Date:</td>
            <td>{{ $plan->plan_date->format('d M Y') }} ({{ $plan->plan_date->format('l') }})</td>
        </tr>
        <tr>
            <td>Teacher:</td>
            <td>{{ optional($plan->teacher)->first_name }} {{ optional($plan->teacher)->last_name }}</td>
        </tr>
    </table>
</div>

@foreach($plan->slots as $i => $slot)
<div class="activity-block">
    <div class="activity-title">Activity {{ $i + 1 }}: {{ $slot->activity_name }}</div>
    <table class="activity-fields">
        <tr>
            <th>Material</th>
            <td>{{ $slot->material ?? '—' }}</td>
        </tr>
        <tr>
            <th>Objective</th>
            <td>{{ $slot->objective ?? '—' }}</td>
        </tr>
        <tr>
            <th>Actual Teach</th>
            <td>{{ $slot->actual_teach ?? '—' }}</td>
        </tr>
        <tr>
            <th>Assessment</th>
            <td>{{ $slot->assessment ?? '—' }}</td>
        </tr>
    </table>
</div>
@endforeach

<div class="action-bar" style="margin-top:20px; justify-content:flex-end;">
    <button onclick="window.print()" class="btn-print">&#128438; Print / Save as PDF</button>
</div>

</body>
</html>
