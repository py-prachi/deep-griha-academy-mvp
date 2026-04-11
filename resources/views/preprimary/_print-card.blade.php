@php
    $showTerm1 = true;
    $showTerm2 = true;
    // Determine which terms actually have any data for this student
    $hasT1 = isset($grades[1]) && count($grades[1]) > 0;
    $hasT2 = isset($grades[2]) && count($grades[2]) > 0;
@endphp

{{-- ── HEADER ── --}}
<div class="rc-header">
    <div class="school-name">DEEP GRIHA ACADEMY</div>
    <div class="school-address">Tadiwala Road, Pune – 411 001 &nbsp;|&nbsp; Tel: (020) 26055540</div>
    <div class="rc-title">Progress Report Card</div>
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

{{-- ── SKILL CHECKLIST TABLE ── --}}
<div class="marks-section">
    <h3>Skill-based Assessment</h3>
    <table class="marks-table">
        <thead>
            <tr>
                <th style="text-align:left; width:55%;">Skill / Competency</th>
                <th style="background:#d0e8d0; width:22%;">Term I</th>
                <th style="background:#d0e0f0; width:23%;">Term II</th>
            </tr>
        </thead>
        <tbody>
            @foreach($skills as $category => $skillList)
            <tr>
                <td colspan="3" style="background:#f0f0f0; font-weight:bold; font-size:10px; padding:3px 6px;">
                    {{ $category }}
                </td>
            </tr>
            @foreach($skillList as $code => $label)
            <tr>
                <td class="subject-name">{{ $label }}</td>
                <td class="grade-cell" style="background:#f5fbf5;">
                    {{ isset($grades[1][$code]) ? $grades[1][$code] : '—' }}
                </td>
                <td class="grade-cell" style="background:#f0f5ff;">
                    {{ isset($grades[2][$code]) ? $grades[2][$code] : '—' }}
                </td>
            </tr>
            @endforeach
            @endforeach
        </tbody>
    </table>
</div>

{{-- ── GRADE KEY ── --}}
<div class="grade-key">
    <div class="key-title">Evaluation Key</div>
    <div class="key-items">
        <span>E = Excellent (70–100%)</span>
        <span>S = Satisfactory (50–70%)</span>
        <span>I = Improvement needed (30–50%)</span>
        <span>D = Still developing (0–30%)</span>
    </div>
</div>

{{-- ── NARRATIVES ── --}}
@php
    $obs1 = isset($narratives[1]) ? $narratives[1] : null;
    $obs2 = isset($narratives[2]) ? $narratives[2] : null;
@endphp
<div class="remarks-section two-col">
    <div class="remarks-box">
        <div class="rm-title">Term I — Remarks</div>
        @if($obs1 && $obs1->does_well_in)
            <p style="font-size:10px;margin-bottom:3px;"><strong>Does well in:</strong> {{ $obs1->does_well_in }}</p>
        @endif
        @if($obs1 && $obs1->needs_improvement)
            <p style="font-size:10px;margin-bottom:3px;"><strong>Needs to improve:</strong> {{ $obs1->needs_improvement }}</p>
        @endif
        @if($obs1 && $obs1->remarks)
            <p style="font-size:10px;margin-bottom:0;white-space:pre-wrap;">{{ $obs1->remarks }}</p>
        @endif
        @if(!$obs1 || (!$obs1->does_well_in && !$obs1->needs_improvement && !$obs1->remarks))
            <span style="color:#999;font-style:italic;font-size:10px;">No remarks entered.</span>
        @endif
    </div>
    <div class="remarks-box">
        <div class="rm-title">Term II — Remarks</div>
        @if($obs2 && $obs2->does_well_in)
            <p style="font-size:10px;margin-bottom:3px;"><strong>Does well in:</strong> {{ $obs2->does_well_in }}</p>
        @endif
        @if($obs2 && $obs2->needs_improvement)
            <p style="font-size:10px;margin-bottom:3px;"><strong>Needs to improve:</strong> {{ $obs2->needs_improvement }}</p>
        @endif
        @if($obs2 && $obs2->remarks)
            <p style="font-size:10px;margin-bottom:0;white-space:pre-wrap;">{{ $obs2->remarks }}</p>
        @endif
        @if(!$obs2 || (!$obs2->does_well_in && !$obs2->needs_improvement && !$obs2->remarks))
            <span style="color:#999;font-style:italic;font-size:10px;">No remarks entered.</span>
        @endif
    </div>
</div>

{{-- ── SIGNATURES ── --}}
<div class="signatures">
    <div class="sig-box">Class Teacher's Signature</div>
    <div class="sig-box">Parent's Signature</div>
    <div class="sig-box">Headmistress</div>
</div>
