<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Card — {{ $student->first_name ?? '' }} {{ $student->last_name ?? '' }}</title>
    @include('marks._print-styles')
</head>
<body>

<div class="print-toolbar no-print">
    <button onclick="window.print()" style="background:#198754;color:#fff;border:none;padding:6px 16px;border-radius:4px;cursor:pointer;font-size:13px;">
        &#128438; Print / Save PDF
    </button>
    <a href="{{ route('preprimary.entry', ['class_id' => $promotion->class_id, 'section_id' => $promotion->section_id]) }}"
        style="text-decoration:none;color:#555;font-size:12px;margin-left:12px;">
        &larr; Back to Entry
    </a>
</div>

<div class="page">
    @include('preprimary._print-card', [
        'student'    => $student,
        'promotion'  => $promotion,
        'schoolClass'=> $schoolClass,
        'section'    => $section,
        'skills'     => $skills,
        'grades'     => $grades,
        'narratives' => $narratives,
        'ctName'     => $ctName,
    ])
</div>

</body>
</html>
