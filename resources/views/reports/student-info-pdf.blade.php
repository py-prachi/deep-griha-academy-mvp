<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #111; }
    .header { border-bottom: 2px solid #000; padding-bottom: 6px; margin-bottom: 8px; }
    .header h2 { font-size: 13px; margin: 0; }
    .header h4 { font-size: 9px; color: #444; margin: 2px 0 0 0; font-weight: normal; }
    .meta { font-size: 8px; color: #555; margin-bottom: 8px; }
    table { width: 100%; border-collapse: collapse; table-layout: auto; }
    th { background: #222; color: #fff; padding: 4px 5px; text-align: left; font-size: 7.5px; white-space: nowrap; }
    td { padding: 3px 5px; border-bottom: 1px solid #ddd; font-size: 7.5px; vertical-align: top; }
    tr:nth-child(even) td { background: #f8f8f8; }
    .footer { margin-top: 10px; font-size: 7px; color: #888; text-align: right; border-top: 1px solid #ccc; padding-top: 4px; }
</style>
</head>
<body>

<div class="header">
    <h2>Deep Griha Academy</h2>
    <h4>Student Information Report
        @if($selectedSession) — {{ $selectedSession->session_name }} @endif
        &nbsp;|&nbsp; {{ $students->count() }} student(s)
        &nbsp;|&nbsp; Generated: {{ now()->format('d M Y, h:i A') }}
    </h4>
</div>

<table>
    <thead>
        <tr>
            <th>#</th>
            @foreach($selectedFields as $fieldKey)
                @if(isset($availableFields[$fieldKey]))
                    <th>{{ $availableFields[$fieldKey]['label'] }}</th>
                @endif
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($students as $i => $student)
        <tr>
            <td>{{ $i + 1 }}</td>
            @foreach($selectedFields as $fieldKey)
            @if(isset($availableFields[$fieldKey]))
            <td>
                @if($fieldKey === 'student_name')
                    {{ $student->first_name }} {{ $student->last_name }}
                @elseif($fieldKey === 'class_div')
                    {{ $student->class_name }} {{ $student->section_name }}
                @elseif($fieldKey === 'date_of_birth')
                    {{ $student->birthday ? \Carbon\Carbon::parse($student->birthday)->format('d M Y') : '—' }}
                @elseif($fieldKey === 'fee_category')
                    {{ ucfirst($student->fee_category ?? '—') }}
                @elseif($fieldKey === 'transport_required')
                    {{ $student->admission && $student->admission->transport_required ? 'Yes' : 'No' }}
                @else
                    {{ $student->admission ? ($student->admission->$fieldKey ?? '—') : '—' }}
                @endif
            </td>
            @endif
            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>

<div class="footer">Deep Griha Academy — Confidential — Generated {{ now()->format('d M Y, h:i A') }}</div>
</body>
</html>
