<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $isSingle      = $plans->count() === 1;
        $firstGroup    = $plans->first();
        $firstEntry    = $firstGroup ? $firstGroup->first() : null;
        $titleParts = [
            config('app.name'),
            'Learning Standard',
            $teacher->first_name . ' ' . $teacher->last_name,
        ];
        if ($isSingle && $firstEntry) {
            $titleParts[] = ($firstEntry->schoolClass->class_name ?? '') . ' ' . ($firstEntry->section->section_name ?? '');
            $titleParts[] = $firstEntry->subject->name ?? '';
        }
    @endphp
    <title>{{ implode(' — ', array_filter($titleParts)) }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Noto Sans', 'Mangal', 'Arial Unicode MS', Arial, sans-serif;
            font-size: 11pt;
            color: #000;
            padding: 20px;
        }

        .page-header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .page-header h2 { font-size: 15pt; font-weight: bold; }
        .page-header p  { font-size: 10pt; color: #444; margin-top: 4px; }

        .subject-block {
            margin-bottom: 28px;
            page-break-inside: avoid;
        }
        .subject-header {
            background: #1a1a2e;
            color: #fff;
            padding: 6px 10px;
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 0;
        }
        .subject-header span {
            font-weight: normal;
            font-size: 9.5pt;
            opacity: 0.8;
            margin-left: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
        }
        th {
            background: #e8e8e8;
            border: 1px solid #aaa;
            padding: 5px 7px;
            text-align: left;
            font-size: 9.5pt;
        }
        td {
            border: 1px solid #ccc;
            padding: 5px 7px;
            vertical-align: top;
        }
        tr:nth-child(even) td { background: #fafafa; }

        .badge-planned   { color: #856404; font-weight: bold; }
        .badge-completed { color: #155724; font-weight: bold; }

        .no-print {
            text-align: center;
            padding: 16px;
            margin-bottom: 20px;
            background: #f0f0f0;
            border-radius: 6px;
        }
        .no-print button {
            padding: 8px 24px;
            font-size: 13px;
            background: #0d6efd;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 8px;
        }
        .no-print a {
            color: #555;
            text-decoration: none;
            font-size: 12px;
        }

        @media print {
            .no-print { display: none; }
            body { padding: 10px; }
            .subject-block { page-break-inside: avoid; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()">🖨 Print / Save as PDF</button>
    <a href="{{ route('lesson-plans.index') }}">← Back</a>
</div>

<div class="page-header">
    <h2>{{ config('app.name') }}</h2>
    <h3 style="font-size:13pt; font-weight:600; margin-top:4px;">Learning Standard</h3>
    <p style="margin-top:6px;">
        <strong>{{ $teacher->first_name }} {{ $teacher->last_name }}</strong>
        @if($isSingle && $firstEntry)
            &nbsp;|&nbsp; {{ $firstEntry->schoolClass->class_name ?? '' }} {{ $firstEntry->section->section_name ?? '' }}
            &nbsp;|&nbsp; {{ $firstEntry->subject->name ?? '' }}
        @endif
    </p>
    <p style="margin-top:3px; color:#555;">
        Academic Year: {{ $session->session_name ?? '—' }}
        &nbsp;|&nbsp; Generated on {{ now()->format('d M Y') }}
    </p>
</div>

@forelse($plans as $key => $entries)
@php
    $first   = $entries->first();
    $subject = $first->subject;
    $class   = $first->schoolClass;
    $section = $first->section;
    $total   = $entries->sum('days_allocated');
@endphp

<div class="subject-block">
    <div class="subject-header">
        {{ $subject->name ?? '—' }}
        <span>{{ $class->class_name ?? '' }} {{ $section->section_name ?? '' }}</span>
    </div>
    <table>
        <thead>
            <tr>
                <th style="width:70px;">Month</th>
                <th style="width:40px;">Days</th>
                <th style="width:55px;">Ch. No.</th>
                <th style="width:28%;">Chapter Name</th>
                <th>Learning Standards / Outcome</th>
                <th style="width:70px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($entries as $plan)
            <tr>
                <td><strong>{{ \App\Models\LessonPlan::MONTHS[$plan->month] }}</strong></td>
                <td style="text-align:center;">{{ $plan->days_allocated }}</td>
                <td>{{ $plan->chapter_number ?? '—' }}</td>
                <td>{{ $plan->chapter_name }}</td>
                <td>{!! \App\Support\RichText::render($plan->learning_standards) !!}</td>
                <td class="badge-{{ $plan->status }}">
                    {{ \App\Models\LessonPlan::STATUS_LABELS[$plan->status] }}
                </td>
            </tr>
            @endforeach
            <tr>
                <td colspan="1" style="text-align:right; font-size:9pt; color:#555;">Total days:</td>
                <td style="text-align:center; font-weight:bold;">{{ $total }}</td>
                <td colspan="4"></td>
            </tr>
        </tbody>
    </table>
</div>
@empty
<p style="color:#888; text-align:center; margin-top:40px;">No Learning Standard entries found.</p>
@endforelse

</body>
</html>
