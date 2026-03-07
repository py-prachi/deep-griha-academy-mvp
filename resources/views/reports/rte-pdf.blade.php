<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        h2, h4 { margin: 0; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 12px; }
        .meta { margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #222; color: #fff; padding: 5px 6px; text-align: left; font-size: 9px; }
        td { padding: 4px 6px; border-bottom: 1px solid #eee; font-size: 9px; }
        .footer { margin-top: 16px; font-size: 9px; color: #888; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Deep Griha Academy</h2>
        <h4>RTE Students Report — {{ now()->format('d M Y') }}</h4>
    </div>
    <div class="meta">Total RTE Students: <strong>{{ $students->count() }}</strong></div>
    <table>
        <thead>
            <tr><th>#</th><th>Student Name</th><th>Class / Div</th><th>Admission No</th><th>RTE Doc No</th><th>Date of Birth</th><th>Father's Name</th></tr>
        </thead>
        <tbody>
            @foreach($students as $i => $student)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                <td>{{ $student->class_name }} {{ $student->section_name }}</td>
                <td>{{ $student->dga_admission_no ?? $student->general_id ?? '—' }}</td>
                <td>{{ $student->admission->rte_doc_no ?? '—' }}</td>
                <td>{{ $student->birthday ? \Carbon\Carbon::parse($student->birthday)->format('d M Y') : '—' }}</td>
                <td>{{ $student->admission->father_name ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">Generated on {{ now()->format('d M Y, h:i A') }}</div>
</body>
</html>
