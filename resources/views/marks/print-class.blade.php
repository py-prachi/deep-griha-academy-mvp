<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Cards — {{ $schoolClass->class_name ?? '' }} {{ $section->section_name ?? '' }}</title>
    @include('marks._print-styles')
    <style>
    .student-page { page-break-after: always; padding: 10mm 12mm; }
    .student-page:last-child { page-break-after: avoid; }
    @media print {
        .student-page { padding: 8mm 10mm; }
    }
    </style>
</head>
<body>

<div class="print-toolbar no-print">
    <button onclick="window.print()" style="background:#198754;color:#fff;border:none;padding:6px 16px;border-radius:4px;cursor:pointer;font-size:13px;">
        &#128438; Print All / Save PDF
    </button>
    <a href="{{ route('marks.review', ['class_id' => $class_id, 'section_id' => $section_id]) }}"
        style="text-decoration:none;color:#555;font-size:12px;margin-left:12px;">
        &larr; Back to Review
    </a>
    <span style="font-size:12px;color:#888;margin-left:16px;">
        {{ count($studentCards) }} students &mdash; {{ $schoolClass->class_name ?? '' }} {{ $section->section_name ?? '' }}
    </span>
</div>

@forelse($studentCards as $card)
<div class="student-page">
    @include('marks._print-card', [
        'student'          => $card['student'],
        'promotion'        => $card['p'],
        'schoolClass'      => $schoolClass,
        'section'          => $section,
        'subjects'         => $subjects,
        'marks'            => $card['marks'],
        'config'           => $config,
        'observations'     => $card['observations'],
        'presentCount'     => $card['presentCount'],
        'totalWorkingDays' => $totalWorkingDays,
        'ctName'           => $ctName,
        'publishedTerms'   => $publishedTerms,
    ])
</div>
@empty
<p style="padding:20px;">No students found.</p>
@endforelse

</body>
</html>
