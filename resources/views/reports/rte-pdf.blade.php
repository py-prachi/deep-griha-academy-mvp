<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        h2, h4 { margin: 0; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 12px; }
        .meta { margin-bottom: 10px; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #222; color: #fff; padding: 5px 6px; text-align: left; font-size: 9px; }
        td { padding: 4px 6px; border-bottom: 1px solid #eee; font-size: 9px; }
        .footer { margin-top: 16px; font-size: 9px; color: #888; text-align: right; }
    </style>
</head>
<body>
    @php
        $categoryLabel = match($category ?? 'rte') {
            'discount' => 'Discount',
            'coc'      => 'COC (Child of Christ)',
            default    => 'RTE',
        };
        $sessionName = $selectedSession->session_name ?? '';
    @endphp

    <div class="header">
        <h2>Deep Griha Academy</h2>
        <h4>{{ $categoryLabel }} Students Report — {{ $sessionName }}</h4>
    </div>
    <div class="meta">
        Total {{ $categoryLabel }} Students: <strong>{{ $students->count() }}</strong>
        &nbsp;&nbsp;|&nbsp;&nbsp; Generated on {{ now()->format('d M Y, h:i A') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Student Name</th>
                <th>Class / Div</th>
                <th>Admission No</th>
                @if(($category ?? 'rte') === 'rte')
                    <th>RTE Doc No</th>
                    <th>Date of Birth</th>
                @elseif(($category ?? 'rte') === 'discount')
                    <th>Gender</th>
                    <th>Discount %</th>
                @else
                    <th>Date of Birth</th>
                @endif
                <th>Father's Name</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $i => $student)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                <td>{{ $student->class_name }} {{ $student->section_name }}</td>
                <td>{{ $student->dga_admission_no ?? $student->general_id ?? '—' }}</td>
                @if(($category ?? 'rte') === 'rte')
                    <td>{{ $student->admission->rte_doc_no ?? '—' }}</td>
                    <td>{{ $student->birthday ? \Carbon\Carbon::parse($student->birthday)->format('d M Y') : '—' }}</td>
                @elseif(($category ?? 'rte') === 'discount')
                    <td>{{ $student->gender ?? '—' }}</td>
                    <td>{{ $student->admission->discount_percentage !== null ? $student->admission->discount_percentage . '%' : '—' }}</td>
                @else
                    <td>{{ $student->birthday ? \Carbon\Carbon::parse($student->birthday)->format('d M Y') : '—' }}</td>
                @endif
                <td>{{ $student->admission->father_name ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">Generated on {{ now()->format('d M Y, h:i A') }}</div>
</body>
</html>
