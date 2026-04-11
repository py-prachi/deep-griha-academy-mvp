@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <div class="d-flex align-items-center mb-2">
                        <a href="{{ route('marks.review', ['class_id'=>$schoolClass->id,'section_id'=>$section->id]) }}"
                            class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <h5 class="mb-0">
                            <i class="bi bi-pencil-square me-1"></i>
                            {{ $subject->name }} — {{ $schoolClass->class_name }} {{ $section->section_name }}
                            <span class="badge bg-secondary ms-1">Term {{ $term }}</span>
                            @if($subject->mark_type === 'grade_only')
                                <span class="badge bg-info ms-1">Grade Only</span>
                            @endif
                        </h5>
                    </div>

                    @include('session-messages')

                    @if($subject->mark_type === 'marks')
                    <div class="alert alert-light py-2 small border mb-3" style="max-width:960px;">
                        <div class="mb-1">
                            <strong>Enter marks in each column. Int, Writ, Total and Grade are calculated automatically.</strong>
                        </div>
                        <div class="d-flex gap-3 flex-wrap">
                            <span><strong>Internal /{{ $config['internal_max'] }}:</strong>
                                Oral /{{ $config['oral_internal'] }} + Activity /{{ $config['activity_internal'] }} + Test /{{ $config['test'] }} + HW /{{ $config['hw'] }}</span>
                            <span><strong>Written /{{ $config['written_max'] }}:</strong>
                                Oral /{{ $config['oral_written'] }} + Activity /{{ $config['activity_written'] }} + Writing /{{ $config['writing'] }}</span>
                            <span class="text-muted">Tick <strong>AB</strong> below any mark if student was absent for that component.</span>
                        </div>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('marks.store') }}">
                        @csrf
                        <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                        <input type="hidden" name="class_id" value="{{ $schoolClass->id }}">
                        <input type="hidden" name="section_id" value="{{ $section->id }}">
                        <input type="hidden" name="term" value="{{ $term }}">

                        {{-- ── EXAM DATES SECTION ─────────────────────────────────────── --}}
                        <div class="card mb-3 border-primary" style="max-width:960px;">
                            <div class="card-header bg-primary text-white py-2 small">
                                <i class="bi bi-calendar3 me-1"></i>
                                <strong>Exam Dates</strong>
                                <span class="text-white-50 ms-2">— Enter once; applies to all students</span>
                            </div>
                            <div class="card-body py-2">
                                @if($subject->mark_type === 'marks')
                                <div class="row g-2 mb-1">
                                    <div class="col-12"><span class="text-muted small fw-semibold">Internal Assessment</span></div>
                                    @foreach([
                                        'oral_internal'     => 'Oral (/'. $config['oral_internal'] .')',
                                        'activity_internal' => 'Activity (/'. $config['activity_internal'] .')',
                                        'test'              => 'Test (/'. $config['test'] .')',
                                        'hw'                => 'HW (/'. $config['hw'] .')',
                                    ] as $comp => $label)
                                    <div class="col-auto">
                                        <label class="form-label form-label-sm mb-0 small">{{ $label }}</label>
                                        <input type="date" name="exam_dates[{{ $comp }}]"
                                            class="form-control form-control-sm"
                                            style="width:145px;"
                                            value="{{ isset($existingDates[$comp]) ? $existingDates[$comp]->exam_date->format('Y-m-d') : '' }}">
                                    </div>
                                    @endforeach
                                </div>
                                <div class="row g-2">
                                    <div class="col-12"><span class="text-muted small fw-semibold">Written Exam</span></div>
                                    @foreach([
                                        'oral_written'      => 'Oral (/'. $config['oral_written'] .')',
                                        'activity_written'  => 'Activity (/'. $config['activity_written'] .')',
                                        'writing'           => 'Writing (/'. $config['writing'] .')',
                                    ] as $comp => $label)
                                    <div class="col-auto">
                                        <label class="form-label form-label-sm mb-0 small">{{ $label }}</label>
                                        <input type="date" name="exam_dates[{{ $comp }}]"
                                            class="form-control form-control-sm"
                                            style="width:145px;"
                                            value="{{ isset($existingDates[$comp]) ? $existingDates[$comp]->exam_date->format('Y-m-d') : '' }}">
                                    </div>
                                    @endforeach
                                </div>
                                @else
                                <div class="row g-2">
                                    <div class="col-auto">
                                        <label class="form-label form-label-sm mb-0 small">Assessment Date</label>
                                        <input type="date" name="exam_dates[exam]"
                                            class="form-control form-control-sm"
                                            style="width:145px;"
                                            value="{{ isset($existingDates['exam']) ? $existingDates['exam']->exam_date->format('Y-m-d') : '' }}">
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- ── STUDENT MARKS TABLE ─────────────────────────────────────── --}}
                        <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle" style="font-size:0.82rem;">
                            <thead class="table-dark">
                                <tr>
                                    <th rowspan="2" style="min-width:140px;vertical-align:middle;">Student</th>
                                    @if($subject->mark_type === 'marks')
                                    <th colspan="4" class="text-center border-end">Internal Assessment</th>
                                    <th rowspan="2" class="text-center" style="vertical-align:middle;background:#dbeafe;color:#1e40af;">Int<br><small>/{{ $config['internal_max'] }}</small><br><small style="font-size:0.6rem;font-weight:normal;opacity:0.7;">&#8721; auto</small></th>
                                    <th colspan="3" class="text-center border-end">Written Exam</th>
                                    <th rowspan="2" class="text-center" style="vertical-align:middle;background:#dbeafe;color:#1e40af;">Writ<br><small>/{{ $config['written_max'] }}</small><br><small style="font-size:0.6rem;font-weight:normal;opacity:0.7;">&#8721; auto</small></th>
                                    <th rowspan="2" class="text-center" style="vertical-align:middle;background:#d1fae5;color:#065f46;">Total<br><small>/100</small><br><small style="font-size:0.6rem;font-weight:normal;opacity:0.7;">&#8721; auto</small></th>
                                    <th rowspan="2" class="text-center" style="vertical-align:middle;background:#d1fae5;color:#065f46;">Grade<br><small style="font-size:0.6rem;font-weight:normal;opacity:0.7;">auto</small></th>
                                    @else
                                    <th rowspan="2" class="text-center" style="vertical-align:middle;">Grade</th>
                                    <th rowspan="2" class="text-center text-danger" style="vertical-align:middle;">Absent</th>
                                    @endif
                                </tr>
                                @if($subject->mark_type === 'marks')
                                <tr>
                                    <th class="text-center" title="Oral Internal">Oral<br><small>/{{ $config['oral_internal'] }}</small></th>
                                    <th class="text-center" title="Activity Internal">Act<br><small>/{{ $config['activity_internal'] }}</small></th>
                                    <th class="text-center" title="Test">Test<br><small>/{{ $config['test'] }}</small></th>
                                    <th class="text-center border-end" title="Homework">HW<br><small>/{{ $config['hw'] }}</small></th>
                                    <th class="text-center" title="Oral Written">Oral<br><small>/{{ $config['oral_written'] }}</small></th>
                                    <th class="text-center" title="Activity Written">Act<br><small>/{{ $config['activity_written'] }}</small></th>
                                    <th class="text-center border-end" title="Writing">Writing<br><small>/{{ $config['writing'] }}</small></th>
                                </tr>
                                @endif
                            </thead>
                            <tbody>
                                @forelse($promotions as $promo)
                                @php
                                    $student        = $promo->student;
                                    $existing       = $existingMarks->get($student->id);
                                    $absentList     = $existing ? ($existing->absent_components ?? []) : [];
                                    $isFullyAbsent  = $existing && $existing->grade === 'AB' && in_array('exam', $absentList);

                                    // Helper: is a specific component absent?
                                    $abComp = function($comp) use ($absentList) {
                                        return in_array($comp, $absentList);
                                    };
                                @endphp
                                <tr class="mark-row" data-student="{{ $student->id }}">
                                    <td>{{ $student->first_name }} {{ $student->last_name }}</td>

                                    @if($subject->mark_type === 'marks')
                                    @php
                                        $components = [
                                            'oral_internal'     => ['max' => $config['oral_internal'],     'val' => $existing->oral_internal ?? null],
                                            'activity_internal' => ['max' => $config['activity_internal'], 'val' => $existing->activity_internal ?? null],
                                            'test'              => ['max' => $config['test'],              'val' => $existing->test ?? null],
                                            'hw'                => ['max' => $config['hw'],                'val' => $existing->hw ?? null],
                                            'oral_written'      => ['max' => $config['oral_written'],      'val' => $existing->oral_written ?? null],
                                            'activity_written'  => ['max' => $config['activity_written'],  'val' => $existing->activity_written ?? null],
                                            'writing'           => ['max' => $config['writing'],           'val' => $existing->writing ?? null],
                                        ];
                                        $borders = ['hw' => true, 'writing' => true]; // visual separators
                                    @endphp

                                    @php
                                        $internalComps = ['oral_internal','activity_internal','test','hw'];
                                        $writtenComps  = ['oral_written','activity_written','writing'];
                                    @endphp

                                    {{-- ── 4 Internal inputs ── --}}
                                    @foreach($internalComps as $comp)
                                    @php
                                        $cfg        = $components[$comp];
                                        $compAbsent = $abComp($comp);
                                        $incomplete = $existing && !$compAbsent && $cfg['val'] === null;
                                    @endphp
                                    <td class="p-1{{ $comp === 'hw' ? ' border-end' : '' }}" style="min-width:52px;">
                                        <input type="number" step="0.5" min="0" max="{{ $cfg['max'] }}"
                                            class="form-control form-control-sm mark-input p-1 text-center mb-1{{ $incomplete ? ' border-warning' : '' }}"
                                            name="marks[{{ $student->id }}][{{ $comp }}]"
                                            value="{{ (!$compAbsent && $cfg['val'] !== null) ? $cfg['val'] : '' }}"
                                            {{ $compAbsent ? 'disabled' : '' }}
                                            data-max="{{ $cfg['max'] }}"
                                            data-comp="{{ $comp }}"
                                            data-had-existing="{{ $existing ? '1' : '0' }}"
                                            style="{{ $compAbsent ? 'display:none;' : ($incomplete ? 'background:#fff8e1;' : '') }}">
                                        @if($compAbsent)
                                            <div class="text-center"><span class="badge bg-warning text-dark" style="font-size:0.7rem;">AB</span></div>
                                        @endif
                                        <div class="text-center mt-1">
                                            <input type="checkbox"
                                                class="form-check-input comp-absent-check"
                                                name="marks[{{ $student->id }}][absent][{{ $comp }}]"
                                                value="1" title="Absent"
                                                {{ $compAbsent ? 'checked' : '' }}
                                                data-comp="{{ $comp }}" data-student="{{ $student->id }}">
                                            <label class="text-danger" style="font-size:0.65rem;cursor:pointer;">AB</label>
                                        </div>
                                    </td>
                                    @endforeach

                                    {{-- ── Int total (auto) — must be here, between internal and written ── --}}
                                    <td class="text-center fw-bold int-total" style="background:#dbeafe;color:#1e40af;cursor:default;min-width:48px;" title="Auto sum of internal marks">
                                        {{ $existing ? $existing->internal_total : '—' }}
                                    </td>

                                    {{-- ── 3 Written inputs ── --}}
                                    @foreach($writtenComps as $comp)
                                    @php
                                        $cfg        = $components[$comp];
                                        $compAbsent = $abComp($comp);
                                        $incomplete = $existing && !$compAbsent && $cfg['val'] === null;
                                    @endphp
                                    <td class="p-1{{ $comp === 'writing' ? ' border-end' : '' }}" style="min-width:52px;">
                                        <input type="number" step="0.5" min="0" max="{{ $cfg['max'] }}"
                                            class="form-control form-control-sm mark-input p-1 text-center mb-1{{ $incomplete ? ' border-warning' : '' }}"
                                            name="marks[{{ $student->id }}][{{ $comp }}]"
                                            value="{{ (!$compAbsent && $cfg['val'] !== null) ? $cfg['val'] : '' }}"
                                            {{ $compAbsent ? 'disabled' : '' }}
                                            data-max="{{ $cfg['max'] }}"
                                            data-comp="{{ $comp }}"
                                            data-had-existing="{{ $existing ? '1' : '0' }}"
                                            style="{{ $compAbsent ? 'display:none;' : ($incomplete ? 'background:#fff8e1;' : '') }}">
                                        @if($compAbsent)
                                            <div class="text-center"><span class="badge bg-warning text-dark" style="font-size:0.7rem;">AB</span></div>
                                        @endif
                                        <div class="text-center mt-1">
                                            <input type="checkbox"
                                                class="form-check-input comp-absent-check"
                                                name="marks[{{ $student->id }}][absent][{{ $comp }}]"
                                                value="1" title="Absent"
                                                {{ $compAbsent ? 'checked' : '' }}
                                                data-comp="{{ $comp }}" data-student="{{ $student->id }}">
                                            <label class="text-danger" style="font-size:0.65rem;cursor:pointer;">AB</label>
                                        </div>
                                    </td>
                                    @endforeach

                                    {{-- ── Writ / Total / Grade (auto) ── --}}
                                    <td class="text-center fw-bold writ-total"  style="background:#dbeafe;color:#1e40af;cursor:default;min-width:48px;" title="Auto sum of written marks">
                                        {{ $existing ? $existing->written_total : '—' }}
                                    </td>
                                    <td class="text-center fw-bold grand-total" style="background:#d1fae5;color:#065f46;cursor:default;min-width:48px;" title="Int + Writ">
                                        {{ $existing ? $existing->grand_total : '—' }}
                                    </td>
                                    <td class="text-center fw-bold grade-display" style="background:#d1fae5;color:#065f46;cursor:default;min-width:48px;" title="Grade">
                                        {{ $existing ? $existing->grade : '—' }}
                                    </td>

                                    @else
                                    {{-- Grade only: single absent flag --}}
                                    <td>
                                        <select name="marks[{{ $student->id }}][grade]"
                                            class="form-select form-select-sm p-1"
                                            {{ $isFullyAbsent ? 'disabled' : '' }}>
                                            <option value="">—</option>
                                            @foreach(['A1','A2','B1','B2','C1','C2','D','E'] as $g)
                                                <option value="{{ $g }}"
                                                    {{ ($existing && !$isFullyAbsent && $existing->grade === $g) ? 'selected' : '' }}>
                                                    {{ $g }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($isFullyAbsent)
                                            <span class="badge bg-warning text-dark mt-1">AB</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input grade-absent-check"
                                            name="marks[{{ $student->id }}][absent]"
                                            value="1"
                                            {{ $isFullyAbsent ? 'checked' : '' }}
                                            title="Student was absent">
                                    </td>
                                    @endif
                                </tr>
                                @empty
                                <tr><td colspan="14" class="text-muted text-center">No students found in this section.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-save me-1"></i> Save Marks
                            </button>
                            <a href="{{ route('marks.review', ['class_id'=>$schoolClass->id,'section_id'=>$section->id]) }}"
                                class="btn btn-outline-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>

<script>
var grandMax   = {{ $config['grand_max'] ?? 100 }};
var gradeScale = [
    {min:91,max:100,grade:'A1'},{min:81,max:90,grade:'A2'},
    {min:71,max:80,grade:'B1'},{min:61,max:70,grade:'B2'},
    {min:51,max:60,grade:'C1'},{min:41,max:50,grade:'C2'},
    {min:33,max:40,grade:'D'},{min:0,max:32,grade:'E'}
];

function calcGrade(pct) {
    for (var i = 0; i < gradeScale.length; i++) {
        if (pct >= gradeScale[i].min && pct <= gradeScale[i].max) return gradeScale[i].grade;
    }
    return 'E';
}

function fmt(n) { return (n % 1 === 0) ? String(n) : n.toFixed(1); }

function recalcRow(tr) {
    // Collect all mark-input number fields in this row, keyed by data-comp
    var inputs = {};
    tr.querySelectorAll('input.mark-input[data-comp]').forEach(function(inp) {
        inputs[inp.dataset.comp] = inp;
    });

    var intComps  = ['oral_internal','activity_internal','test','hw'];
    var writComps = ['oral_written','activity_written','writing'];
    var intTotal = 0, writTotal = 0;

    intComps.forEach(function(c) {
        var inp = inputs[c];
        if (inp && !inp.disabled) intTotal += parseFloat(inp.value) || 0;
    });
    writComps.forEach(function(c) {
        var inp = inputs[c];
        if (inp && !inp.disabled) writTotal += parseFloat(inp.value) || 0;
    });

    var grand = intTotal + writTotal;
    var pct   = grandMax > 0 ? (grand / grandMax * 100) : 0;

    var intCell   = tr.querySelector('td.int-total');
    var writCell  = tr.querySelector('td.writ-total');
    var totCell   = tr.querySelector('td.grand-total');
    var gradeCell = tr.querySelector('td.grade-display');

    if (intCell)   intCell.textContent   = fmt(intTotal);
    if (writCell)  writCell.textContent  = fmt(writTotal);
    if (totCell)   totCell.textContent   = fmt(grand);
    if (gradeCell) gradeCell.textContent = grand > 0 ? calcGrade(pct) : '—';
}

// ── Run recalc on page load for every mark-row that has existing data ──
document.querySelectorAll('tr.mark-row').forEach(function(tr) {
    var hasInput = tr.querySelector('input.mark-input');
    if (hasInput) recalcRow(tr);
});

// ── Live recalc on every input/change event ──
document.querySelectorAll('input.mark-input').forEach(function(inp) {
    ['input','change'].forEach(function(evt) {
        inp.addEventListener(evt, function() {
            var max = parseFloat(this.dataset.max);
            if (!isNaN(max) && parseFloat(this.value) > max) {
                this.value = max;
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
            if (this.value !== '') {
                this.classList.remove('border-warning');
                this.style.background = '';
            } else if (this.dataset.hadExisting === '1') {
                this.classList.add('border-warning');
                this.style.background = '#fff8e1';
            }
            recalcRow(this.closest('tr'));
        });
    });
});

// ── Per-component AB checkbox ──
document.querySelectorAll('.comp-absent-check').forEach(function(chk) {
    chk.addEventListener('change', function() {
        var cell = this.closest('td');
        var inp  = cell.querySelector('input.mark-input');
        var row  = this.closest('tr');

        if (this.checked) {
            if (inp) { inp.value = ''; inp.disabled = true; inp.style.display = 'none'; }
            if (!cell.querySelector('.absent-badge')) {
                var b = document.createElement('div');
                b.className = 'text-center absent-badge';
                b.innerHTML = '<span class="badge bg-warning text-dark" style="font-size:0.7rem;">AB</span>';
                cell.insertBefore(b, cell.querySelector('.text-center.mt-1'));
            }
        } else {
            if (inp) { inp.disabled = false; inp.style.display = ''; }
            var badge = cell.querySelector('.absent-badge');
            if (badge) badge.remove();
        }
        recalcRow(row);
    });
});

// ── Grade-only absent checkbox ──
document.querySelectorAll('.grade-absent-check').forEach(function(chk) {
    chk.addEventListener('change', function() {
        var select = this.closest('tr').querySelector('select');
        if (select) select.disabled = this.checked;
    });
});
</script>
@endsection
