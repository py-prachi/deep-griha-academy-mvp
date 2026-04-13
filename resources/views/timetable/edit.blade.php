@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">

                    <h5 class="mb-1"><i class="bi bi-calendar4-week me-1"></i> Timetable</h5>
                    <p class="text-muted small mb-3">Set up periods per day, then assign subjects to each slot.</p>

                    @include('session-messages')

                    {{-- ── DEFAULT PERIOD SETUP ── --}}
                    <div class="mb-4">
                        <h6 class="text-uppercase text-muted small fw-bold border-bottom pb-1 mb-2">
                            <i class="bi bi-clock me-1"></i> Default Period Schedule
                            <span class="fw-normal text-secondary ms-1">(used for any day that has no custom schedule)</span>
                        </h6>
                        <div class="bg-white border shadow-sm p-3" style="max-width:560px;">
                            @include('timetable._period-table', ['periods' => $defaultPeriods, 'weekday' => 0])
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="addPeriod(0)">
                                <i class="bi bi-plus me-1"></i> Add Period
                            </button>
                            <span class="text-muted small ms-2">Changes save automatically.</span>
                        </div>
                    </div>

                    {{-- ── CLASS + SECTION PICKER ── --}}
                    <h6 class="text-uppercase text-muted small fw-bold border-bottom pb-1 mb-2">
                        <i class="bi bi-pencil-square me-1"></i> Assign Subjects to Timetable
                    </h6>
                    <form class="row g-2 mb-3 align-items-end" action="{{ route('timetable.edit') }}" method="GET">
                        <div class="col-auto">
                            <label class="form-label form-label-sm mb-1">Class</label>
                            <select onchange="getSections(this);" class="form-select form-select-sm" name="class_id" required>
                                <option value="" disabled {{ !$class_id ? 'selected' : '' }}>Select class</option>
                                @foreach ($school_classes as $school_class)
                                    <option value="{{ $school_class->id }}" {{ $school_class->id == $class_id ? 'selected' : '' }}>
                                        {{ $school_class->class_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <label class="form-label form-label-sm mb-1">Section</label>
                            <select class="form-select form-select-sm" id="section-select" name="section_id" required>
                                <option value="{{ $section_id }}">{{ $section_id ? 'Section' : 'Select section' }}</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search me-1"></i> Load</button>
                        </div>
                        @if($class_id && $section_id)
                        <div class="col-auto">
                            <a href="{{ route('timetable.show', ['class_id' => $class_id, 'section_id' => $section_id]) }}"
                                class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-table me-1"></i> Full Week View
                            </a>
                        </div>
                        @endif
                    </form>

                    @if($class_id && $section_id)

                    {{-- ── DAY TABS ── --}}
                    @php $dayShort = [1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat']; @endphp

                    <ul class="nav nav-tabs mb-0" id="dayTabs" role="tablist">
                        @foreach($days as $dayNum => $dayName)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $dayNum == $active_day ? 'active' : '' }}"
                                id="tab-{{ $dayNum }}"
                                data-bs-toggle="tab"
                                data-bs-target="#day-{{ $dayNum }}"
                                type="button" role="tab">
                                {{ $dayShort[$dayNum] }}
                                @php $hasCustom = \App\Models\TimetablePeriod::where('weekday', $dayNum)->exists(); @endphp
                                @if($hasCustom)
                                    <span class="badge bg-primary ms-1" style="font-size:0.6rem;" title="Custom schedule">✦</span>
                                @endif
                            </button>
                        </li>
                        @endforeach
                    </ul>

                    <div class="tab-content border border-top-0 bg-white p-3 shadow-sm mb-3">
                        @foreach($days as $dayNum => $dayName)
                        @php
                            $dayPeriods = $periodsByDay[$dayNum] ?? collect();
                            $hasCustomPeriods = \App\Models\TimetablePeriod::where('weekday', $dayNum)->exists();
                            $dayGrid = isset($grid[$dayNum]) ? $grid[$dayNum] : [];
                        @endphp
                        <div class="tab-pane fade {{ $dayNum == $active_day ? 'show active' : '' }}" id="day-{{ $dayNum }}" role="tabpanel">

                            {{-- Period schedule for this day --}}
                            <div class="mb-3">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="small fw-semibold text-muted">{{ $dayName }} schedule:</span>
                                    @if(!$hasCustomPeriods)
                                        <span class="badge bg-secondary" style="font-size:0.7rem;">Using default</span>
                                        <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2"
                                            style="font-size:0.75rem;"
                                            onclick="customiseDay({{ $dayNum }})">
                                            <i class="bi bi-pencil me-1"></i>Customise this day
                                        </button>
                                    @else
                                        <span class="badge bg-primary" style="font-size:0.7rem;">Custom schedule</span>
                                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2"
                                            style="font-size:0.75rem;"
                                            onclick="resetDay({{ $dayNum }})">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>Reset to default
                                        </button>
                                    @endif
                                </div>

                                <div id="period-setup-{{ $dayNum }}">
                                    @include('timetable._period-table', ['periods' => $dayPeriods, 'weekday' => $dayNum])
                                    @if($hasCustomPeriods)
                                    <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="addPeriod({{ $dayNum }})">
                                        <i class="bi bi-plus me-1"></i> Add Period
                                    </button>
                                    @endif
                                </div>
                            </div>

                            {{-- Subject assignment for this day --}}
                            <form method="POST" action="{{ route('timetable.save') }}">
                                @csrf
                                <input type="hidden" name="class_id" value="{{ $class_id }}">
                                <input type="hidden" name="section_id" value="{{ $section_id }}">
                                <input type="hidden" name="session_id" value="{{ $session_id }}">
                                <input type="hidden" name="weekday" value="{{ $dayNum }}">

                                <table class="table table-sm align-middle mb-2" style="max-width:400px;">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:160px;">Period</th>
                                            <th>Subject</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($dayPeriods as $period)
                                        <tr class="{{ $period->is_break ? 'table-light' : '' }}">
                                            <td class="small text-nowrap">
                                                <span class="fw-semibold">{{ $period->label }}</span>
                                                <span class="text-muted d-block" style="font-size:0.72rem;">{{ $period->start_time }}–{{ $period->end_time }}</span>
                                            </td>
                                            <td>
                                                @if($period->is_break)
                                                    <span class="text-muted small fst-italic">Break</span>
                                                @else
                                                    <select name="slots[{{ $period->id }}]" class="form-select form-select-sm" style="font-size:0.82rem;">
                                                        <option value="">— free —</option>
                                                        @foreach($classSubjects as $cs)
                                                            <option value="{{ $cs->id }}" {{ isset($dayGrid[$period->id]) && $dayGrid[$period->id] == $cs->id ? 'selected' : '' }}>
                                                                {{ optional($cs->subject)->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="bi bi-check2 me-1"></i> Save {{ $dayName }}
                                </button>
                            </form>

                        </div>
                        @endforeach
                    </div>

                    @else
                    <div class="alert alert-light border mt-2" style="max-width:420px;">
                        <i class="bi bi-arrow-up-circle me-1 text-muted"></i>
                        Select a class and section above to assign subjects.
                    </div>
                    @endif

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@push('scripts')
<script>
    var CSRF = '{{ csrf_token() }}';

    function getSections(obj, preselectedSectionId) {
        var class_id = obj.options[obj.selectedIndex].value;
        var url = "{{ route('get.sections.courses.by.classId') }}?class_id=" + class_id;
        fetch(url)
        .then(function(resp) { return resp.json(); })
        .then(function(data) {
            var sectionSelect = document.getElementById('section-select');
            sectionSelect.options.length = 0;
            data.sections.unshift({'id': 0, 'section_name': 'Please select a section'});
            data.sections.forEach(function(section, key) {
                sectionSelect[key] = new Option(section.section_name, section.id);
            });
            if (preselectedSectionId) sectionSelect.value = preselectedSectionId;
        }).catch(console.log);
    }

    document.addEventListener('DOMContentLoaded', function() {
        var classSelect = document.querySelector('select[name="class_id"]');
        var preselected = '{{ $section_id }}';
        if (classSelect && classSelect.value) getSections(classSelect, preselected);
    });

    // Debounced period field update
    var saveTimers = {};
    function updatePeriod(id, field, value) {
        clearTimeout(saveTimers[id + field]);
        saveTimers[id + field] = setTimeout(function() {
            fetch('{{ route("timetable.period.update") }}', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF},
                body: JSON.stringify({id: id, field: field, value: value})
            }).then(function(r){ return r.json(); }).then(function(d){
                if (!d.ok) alert('Save failed: ' + (d.error || 'unknown'));
            });
        }, 600);
    }

    function deletePeriod(id, btn) {
        if (!confirm('Delete this period? Any timetable slots using it will also be removed.')) return;
        fetch('{{ route("timetable.period.delete") }}', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF},
            body: JSON.stringify({id: id})
        }).then(function(r){ return r.json(); }).then(function(d){
            if (d.ok) { btn.closest('tr').remove(); renumberTable(btn.closest('table')); }
            else alert('Delete failed: ' + (d.error || 'unknown'));
        });
    }

    function addPeriod(weekday) {
        fetch('{{ route("timetable.period.add") }}', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF},
            body: JSON.stringify({weekday: weekday})
        }).then(function(r){ return r.json(); }).then(function(d){
            if (!d.ok) { alert('Add failed: ' + (d.error || 'unknown')); return; }
            // Find the tbody for this weekday
            var container = weekday === 0
                ? document.querySelector('#period-setup-default tbody')
                : document.querySelector('#period-setup-' + weekday + ' tbody');
            if (!container) { location.reload(); return; }
            var rowCount = container.rows.length + 1;
            var tr = document.createElement('tr');
            tr.innerHTML = periodRow(d.id, rowCount, d.label, d.start_time, d.end_time, d.is_break);
            container.appendChild(tr);
        });
    }

    function periodRow(id, num, label, start, end, isBreak) {
        return '<td class="text-muted small align-middle">' + num + '</td>' +
            '<td><input type="text" class="form-control form-control-sm" value="' + label + '" onchange="updatePeriod(' + id + ', \'label\', this.value)"></td>' +
            '<td><input type="time" class="form-control form-control-sm" value="' + start + '" onchange="updatePeriod(' + id + ', \'start_time\', this.value)"></td>' +
            '<td><input type="time" class="form-control form-control-sm" value="' + end + '" onchange="updatePeriod(' + id + ', \'end_time\', this.value)"></td>' +
            '<td class="text-center align-middle"><input type="checkbox" class="form-check-input"' + (isBreak ? ' checked' : '') + ' onchange="updatePeriod(' + id + ', \'is_break\', this.checked ? 1 : 0)"></td>' +
            '<td class="text-center align-middle"><button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="deletePeriod(' + id + ', this)"><i class="bi bi-trash" style="font-size:0.8rem;"></i></button></td>';
    }

    function renumberTable(table) {
        if (!table) return;
        var rows = table.querySelectorAll('tbody tr');
        rows.forEach(function(row, i) { if (row.cells[0]) row.cells[0].textContent = i + 1; });
    }

    function resetDay(weekday) {
        var dayNames = ['','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        if (!confirm('Reset ' + dayNames[weekday] + ' back to the default schedule? This will also clear any subject slots saved for this day.')) return;
        fetch('{{ route("timetable.period.reset-day") }}', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF},
            body: JSON.stringify({weekday: weekday})
        }).then(function(r){ return r.json(); }).then(function(d){
            if (d.ok) {
                var url = new URL(window.location.href);
                url.searchParams.set('active_day', weekday);
                window.location.href = url.toString();
            } else alert('Error: ' + (d.error || 'unknown'));
        });
    }

    function customiseDay(weekday) {
        if (!confirm('Copy the default schedule into ' + ['','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'][weekday] + '? You can then edit it independently.')) return;
        fetch('{{ route("timetable.period.copy-defaults") }}', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF},
            body: JSON.stringify({weekday: weekday})
        }).then(function(r){ return r.json(); }).then(function(d){
            if (d.ok) {
                var url = new URL(window.location.href);
                url.searchParams.set('active_day', weekday);
                window.location.href = url.toString();
            } else alert('Error: ' + (d.error || 'unknown'));
        });
    }
</script>
@endpush
@endsection
