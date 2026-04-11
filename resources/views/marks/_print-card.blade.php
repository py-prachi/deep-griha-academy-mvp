@php
    $marksSubjects  = $subjects->where('mark_type', 'marks')->values();
    $gradeSubjects  = $subjects->where('mark_type', 'grade_only')->values();
    $showTerm1      = in_array(1, $publishedTerms);
    $showTerm2      = in_array(2, $publishedTerms);
    $bothTerms      = $showTerm1 && $showTerm2;
@endphp

{{-- ── HEADER ── --}}
<div class="rc-header">
    <div class="school-name">DEEP GRIHA ACADEMY</div>
    <div class="school-address">Tadiwala Road, Pune – 411 001 &nbsp;|&nbsp; Tel: (020) 26055540</div>
    <div class="rc-title">
        Progress Report Card
        @if(!$bothTerms && $showTerm1) — Term I @endif
        @if(!$bothTerms && $showTerm2) — Term II @endif
    </div>
</div>

{{-- ── STUDENT INFO ── --}}
<div class="student-info">
    <div class="info-row">
        <span class="label">Student Name:</span>
        <span>{{ $student->first_name }} {{ $student->last_name }}</span>
    </div>
    <div class="info-row">
        <span class="label">Class:</span>
        <span>{{ $schoolClass->class_name ?? '' }} — {{ $section->section_name ?? '' }}</span>
    </div>
    <div class="info-row">
        <span class="label">Roll No.:</span>
        <span>{{ $promotion->roll_number ?? '—' }}</span>
    </div>
    <div class="info-row">
        <span class="label">Class Teacher:</span>
        <span>{{ $ctName ?: '—' }}</span>
    </div>
</div>

{{-- ── ATTENDANCE ── --}}
<table class="attendance-table">
    <thead>
        <tr>
            <th>Days Present</th>
            <th>Total Working Days</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $presentCount }}</td>
            <td>{{ $totalWorkingDays }}</td>
        </tr>
    </tbody>
</table>

{{-- ── MARKS TABLE ── --}}
<div class="marks-section">
    <h3>Subject-wise Performance</h3>
    <table class="marks-table">
        <thead>
            <tr>
                <th rowspan="2" style="text-align:left; width:150px;">Subject</th>
                @if($showTerm1)
                <th colspan="3" style="background:#d0e8d0;">Term I</th>
                @endif
                @if($showTerm2)
                <th colspan="3" style="background:#d0e0f0;">Term II</th>
                @endif
            </tr>
            <tr class="sub-header">
                @if($showTerm1)
                <th style="background:#d0e8d0;">Internal</th>
                <th style="background:#d0e8d0;">Written</th>
                <th style="background:#d0e8d0;">Grade</th>
                @endif
                @if($showTerm2)
                <th style="background:#d0e0f0;">Internal</th>
                <th style="background:#d0e0f0;">Written</th>
                <th style="background:#d0e0f0;">Grade</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($marksSubjects as $subject)
            @php
                $rows = $marks->get($subject->id);
                $m1   = ($rows && $showTerm1) ? $rows->firstWhere('term', 1) : null;
                $m2   = ($rows && $showTerm2) ? $rows->firstWhere('term', 2) : null;
            @endphp
            <tr>
                <td class="subject-name">{{ $subject->name }}</td>
                @if($showTerm1)
                <td class="grade-cell">{{ $m1 ? ($m1->internal_total ?? '—') : '—' }}</td>
                <td class="grade-cell">{{ $m1 ? ($m1->written_total ?? '—') : '—' }}</td>
                <td class="grade-cell">{{ $m1 ? ($m1->grade ?? '—') : '—' }}</td>
                @endif
                @if($showTerm2)
                <td class="grade-cell">{{ $m2 ? ($m2->internal_total ?? '—') : '—' }}</td>
                <td class="grade-cell">{{ $m2 ? ($m2->written_total ?? '—') : '—' }}</td>
                <td class="grade-cell">{{ $m2 ? ($m2->grade ?? '—') : '—' }}</td>
                @endif
            </tr>
            @endforeach

            @if($gradeSubjects->isNotEmpty())
            <tr>
                @php $spanCols = 1 + ($showTerm1 ? 3 : 0) + ($showTerm2 ? 3 : 0); @endphp
                <td colspan="{{ $spanCols }}" style="background:#f9f9f9;font-size:10px;color:#555;padding:3px 6px;">
                    Co-Scholastic Activities (Grade only)
                </td>
            </tr>
            @foreach($gradeSubjects as $subject)
            @php
                $rows = $marks->get($subject->id);
                $m1   = ($rows && $showTerm1) ? $rows->firstWhere('term', 1) : null;
                $m2   = ($rows && $showTerm2) ? $rows->firstWhere('term', 2) : null;
            @endphp
            <tr>
                <td class="subject-name">{{ $subject->name }}</td>
                @if($showTerm1)
                <td colspan="2" style="text-align:center;color:#999;font-size:9px;">—</td>
                <td class="grade-cell">{{ $m1 ? ($m1->grade ?? '—') : '—' }}</td>
                @endif
                @if($showTerm2)
                <td colspan="2" style="text-align:center;color:#999;font-size:9px;">—</td>
                <td class="grade-cell">{{ $m2 ? ($m2->grade ?? '—') : '—' }}</td>
                @endif
            </tr>
            @endforeach
            @endif
        </tbody>
    </table>
</div>

{{-- ── GRADE KEY ── --}}
<div class="grade-key">
    <div class="key-title">Evaluation Key</div>
    <div class="key-items">
        <span>A1 = 91–100%</span>
        <span>A2 = 81–90%</span>
        <span>B1 = 71–80%</span>
        <span>B2 = 61–70%</span>
        <span>C1 = 51–60%</span>
        <span>C2 = 41–50%</span>
        <span>D = 33–40%</span>
        <span>E = Below 33%</span>
    </div>
</div>

{{-- ── DESCRIPTIVE REMARKS ── --}}
<div class="remarks-section {{ $bothTerms ? 'two-col' : 'one-col' }}">
    @if($showTerm1)
    @php $obs1 = $observations->get(1); @endphp
    <div class="remarks-box">
        <div class="rm-title">Descriptive Remarks — Term I</div>
        @if($obs1 && $obs1->remarks)
            <div class="remarks-text">{{ $obs1->remarks }}</div>
        @else
            <div style="color:#999;font-style:italic;font-size:10px;">No remarks entered.</div>
        @endif
    </div>
    @endif
    @if($showTerm2)
    @php $obs2 = $observations->get(2); @endphp
    <div class="remarks-box">
        <div class="rm-title">Descriptive Remarks — Term II</div>
        @if($obs2 && $obs2->remarks)
            <div class="remarks-text">{{ $obs2->remarks }}</div>
        @else
            <div style="color:#999;font-style:italic;font-size:10px;">No remarks entered.</div>
        @endif
    </div>
    @endif
</div>

{{-- ── SIGNATURES ── --}}
<div class="signatures">
    <div class="sig-box">Class Teacher's Signature</div>
    <div class="sig-box">Parent's Signature</div>
    <div class="sig-box">Headmistress</div>
</div>
