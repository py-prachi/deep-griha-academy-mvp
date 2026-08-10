<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        @if($lesson)Lesson Plan — {{ optional($lesson->subject)->name }}@else Module — {{ optional($module->subject)->name }}@endif
    </title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #222; padding: 20px; }
        h2 { font-size: 16px; text-align: center; margin-bottom: 4px; }
        .subtitle { text-align: center; font-size: 12px; color: #555; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { border: 1px solid #aaa; padding: 6px 8px; vertical-align: top; }
        th { background: #f0f0f0; font-weight: bold; width: 30%; white-space: nowrap; }
        td { white-space: pre-wrap; }
        .section-title { background: #d8e4f0; font-weight: bold; text-align: center; font-size: 13px; }
        .action-bar { display: flex; gap: 8px; align-items: center; margin-bottom: 20px; padding: 10px 12px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; }
        .action-bar a, .action-bar button { padding: 5px 14px; border-radius: 4px; font-size: 12px; cursor: pointer; text-decoration: none; }
        .btn-back { background: #fff; border: 1px solid #aaa; color: #333; }
        .btn-print { background: #198754; border: 1px solid #198754; color: #fff; }
        .pdf-hint { font-size: 11px; color: #666; margin-left: auto; }
        @@media print {
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
    @if($session){{ $session->session }} Academic Session
    @endif
</div>

@if($lesson)
@php
    $subjectNameLower = strtolower(optional($lesson->subject)->name ?? '');
    $isAgri   = $subjectNameLower === 'agriculture';
    $isSports = $subjectNameLower === 'physical education';
@endphp

@if($isSports)
{{-- SPORTS LESSON PLAN PRINT --}}
<h2 style="margin-top:8px;">Sports Lesson Plan</h2>
<div class="subtitle">
    Grade {{ optional($lesson->schoolClass)->class_name }} {{ optional($lesson->section)->section_name }}
    &mdash; Teacher: {{ optional($lesson->teacher)->first_name }} {{ optional($lesson->teacher)->last_name }}
</div>

<table>
    <tr><th>Date of Planning</th><td>{{ $lesson->date_written ? $lesson->date_written->format('d M Y') : '—' }}</td></tr>
    <tr><th>Scheduled Teaching Date</th><td>{{ $lesson->scheduled_date ? $lesson->scheduled_date->format('d M Y') : '—' }}</td></tr>
    <tr><th>Date of Execution</th><td>{{ $lesson->date_execution ?: '—' }}</td></tr>
    <tr><th>Grade</th><td>{{ optional($lesson->schoolClass)->class_name }}</td></tr>
    <tr><th>Time</th><td>{{ $lesson->period_timing ?? '—' }}</td></tr>
    <tr><th>Name of the Game</th><td>{!! \App\Support\RichText::render($lesson->chapter_topic) !!}</td></tr>
    <tr><th>Outdoor / Indoor</th><td>{{ $lesson->lesson_type ?? '—' }}</td></tr>
    <tr><th>Objective</th><td>{!! \App\Support\RichText::render($lesson->objective) !!}</td></tr>
    <tr><th>Introduction</th><td>{!! \App\Support\RichText::render($lesson->hook) !!}</td></tr>
    <tr><th>Practical</th><td>{{ $lesson->practical_notes ?? '—' }}</td></tr>
</table>

@elseif($isAgri)
{{-- AGRICULTURE LESSON PLAN PRINT --}}
<h2 style="margin-top:8px;">Agriculture Lesson Plan</h2>
<div class="subtitle">
    Grade {{ optional($lesson->schoolClass)->class_name }} {{ optional($lesson->section)->section_name }}
    &mdash; Teacher: {{ optional($lesson->teacher)->first_name }} {{ optional($lesson->teacher)->last_name }}
</div>

<table>
    <tr><th>Date of Planning</th><td>{{ $lesson->date_written ? $lesson->date_written->format('d M Y') : '—' }}</td></tr>
    <tr><th>Scheduled Teaching Date</th><td>{{ $lesson->scheduled_date ? $lesson->scheduled_date->format('d M Y') : '—' }}</td></tr>
    <tr><th>Date of Execution</th><td>{{ $lesson->date_execution ?: '—' }}</td></tr>
    <tr><th>Grade</th><td>{{ optional($lesson->schoolClass)->class_name }}</td></tr>
    <tr><th>Topic</th><td>{!! \App\Support\RichText::render($lesson->chapter_topic) !!}</td></tr>
    <tr><th>Practical / Theory</th><td>{{ $lesson->lesson_type ?? '—' }}</td></tr>
    <tr><th>Objective</th><td>{!! \App\Support\RichText::render($lesson->objective) !!}</td></tr>
    <tr><th>Teach</th><td>{!! \App\Support\RichText::render($lesson->teach) !!}</td></tr>
    <tr><th>Practical Activity</th><td>{{ $lesson->practical_notes ?? '—' }}</td></tr>
    <tr><th>Closure</th><td>{!! \App\Support\RichText::render($lesson->closure) !!}</td></tr>
    <tr><th>Project / HW</th><td>{!! \App\Support\RichText::render($lesson->homework) !!}</td></tr>
</table>

@else
{{-- REGULAR LESSON PLAN PRINT --}}
<h2 style="margin-top:8px;">Lesson Plan</h2>
<div class="subtitle">
    {{ optional($lesson->schoolClass)->class_name }} {{ optional($lesson->section)->section_name }}
    &mdash; {{ optional($lesson->subject)->name }}
    &mdash; Teacher: {{ optional($lesson->teacher)->first_name }} {{ optional($lesson->teacher)->last_name }}
</div>

<table>
    <tr><th>Date Written</th><td>{{ $lesson->date_written ? $lesson->date_written->format('d M Y') : '—' }}</td></tr>
    <tr><th>Scheduled Teaching Date</th><td>{{ $lesson->scheduled_date ? $lesson->scheduled_date->format('d M Y') : '—' }}</td></tr>
    <tr><th>Date of Execution</th><td>{{ $lesson->date_execution ?: '—' }}</td></tr>
    <tr><th>Chapter / Topic</th><td>{!! \App\Support\RichText::render($lesson->chapter_topic) !!}</td></tr>
    <tr><th>Period Timing</th><td>{{ $lesson->period_timing ?? '—' }}</td></tr>
    @if($lesson->module)
    <tr><th>Linked Module</th><td>{!! \App\Support\RichText::render($lesson->module->topic) !!}</td></tr>
    @endif
</table>

<table>
    <tr><td colspan="2" class="section-title">Curriculum Details</td></tr>
    <tr><th>Learning Standard</th><td>{!! \App\Support\RichText::render($lesson->learning_standard) !!}</td></tr>
    <tr><th>Objective</th><td>{!! \App\Support\RichText::render($lesson->objective) !!}</td></tr>
    <tr><th>Material Needed</th><td>{!! \App\Support\RichText::render($lesson->material_needed) !!}</td></tr>
    <tr><th>Training Component</th><td>{!! \App\Support\RichText::render($lesson->training_component) !!}</td></tr>
    <tr><th>Student to Whom Responses Expected</th><td>{!! \App\Support\RichText::render($lesson->student_responses) !!}</td></tr>
</table>

<table>
    <tr><td colspan="2" class="section-title">Lesson Flow</td></tr>
    <tr><th>Hook</th><td>{!! \App\Support\RichText::render($lesson->hook) !!}</td></tr>
    <tr><th>Teach</th><td>{!! \App\Support\RichText::render($lesson->teach) !!}</td></tr>
    <tr><th>Guided Practice (CW)</th><td>{!! \App\Support\RichText::render($lesson->guided_practice) !!}</td></tr>
    <tr><th>Independent Practice (CW)</th><td>{!! \App\Support\RichText::render($lesson->independent_practice) !!}</td></tr>
    <tr><th>Closure</th><td>{!! \App\Support\RichText::render($lesson->closure) !!}</td></tr>
</table>

<table>
    <tr><td colspan="2" class="section-title">Additional</td></tr>
    <tr><th>Homework</th><td>{!! \App\Support\RichText::render($lesson->homework) !!}</td></tr>
    <tr><th>Any Other Note</th><td>{!! \App\Support\RichText::render($lesson->other_notes) !!}</td></tr>
    <tr><th>Remark</th><td>{!! \App\Support\RichText::render($lesson->remark) !!}</td></tr>
</table>

@endif {{-- end isAgri --}}

@elseif($module)
{{-- MODULE PRINT --}}
<h2 style="margin-top:8px;">Module Plan</h2>
<div class="subtitle">
    {{ optional($module->schoolClass)->class_name }} {{ optional($module->section)->section_name }}
    &mdash; {{ optional($module->subject)->name }}
    &mdash; Teacher: {{ optional($module->teacher)->first_name }} {{ optional($module->teacher)->last_name }}
</div>

<table>
    <tr><th>Date Written</th><td>{{ $module->date_written ? $module->date_written->format('d M Y') : '—' }}</td></tr>
    <tr><th>Topic/Topics, Chapter/Chapters</th><td>{!! \App\Support\RichText::render($module->topic) !!}</td></tr>
</table>

<table>
    <tr><td colspan="2" class="section-title">Module Details</td></tr>
    <tr><th>Learning Outcome</th><td>{!! \App\Support\RichText::render($module->learning_outcome) !!}</td></tr>
    <tr><th>Assessment</th><td>{!! \App\Support\RichText::render($module->assessment) !!}</td></tr>
    <tr><th>Rubric</th><td>{!! \App\Support\RichText::render($module->rubric) !!}</td></tr>
    <tr><th>Objectives</th><td>{!! \App\Support\RichText::render($module->objectives) !!}</td></tr>
    <tr><th>Duration and Flow of Days</th><td style="white-space:pre-wrap;">{!! \App\Support\RichText::render($module->duration_and_flow) !!}</td></tr>
    <tr><th>Materials</th><td>{!! \App\Support\RichText::render($module->materials) !!}</td></tr>
</table>
@endif

<div style="margin-top:20px; text-align:right;" class="action-bar" style="justify-content:flex-end;">
    <button onclick="window.print()" class="btn-print">&#128438; Print / Save as PDF</button>
</div>

</body>
</html>
