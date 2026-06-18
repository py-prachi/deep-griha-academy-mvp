@extends('layouts.app')
@section('title', 'Student Information Report')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <div class="d-flex align-items-center mb-2">
                        <a href="{{ route('home') }}" class="btn btn-sm btn-outline-secondary me-2"><i class="bi bi-arrow-left"></i></a>
                        <h4 class="mb-0">Student Information Report</h4>
                    </div>
                    <p class="text-muted small mb-3">Choose filters and select the fields you need. The same view downloads as PDF.</p>

                    {{-- ── FILTER + FIELD SELECTOR FORM ── --}}
                    <form method="GET" action="{{ route('reports.students') }}" id="reportForm">

                        <div class="card mb-3 border-0 shadow-sm">
                            <div class="card-header bg-dark text-white py-2">
                                <i class="bi bi-funnel me-1"></i> Filters &amp; Fields
                            </div>
                            <div class="card-body pb-2">

                                {{-- Row 1: Filters --}}
                                <div class="row g-2 mb-3">
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold mb-1">Academic Year</label>
                                        <select name="session_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                            @foreach($sessions as $s)
                                                <option value="{{ $s->id }}" {{ $s->id == $selectedSessionId ? 'selected' : '' }}>{{ $s->session_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold mb-1">Class</label>
                                        <select name="class_id" class="form-select form-select-sm">
                                            <option value="">All Classes</option>
                                            @foreach($schoolClasses as $class)
                                                <option value="{{ $class->id }}" {{ $classFilter == $class->id ? 'selected' : '' }}>{{ $class->class_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold mb-1">Fee Category</label>
                                        <select name="fee_category" class="form-select form-select-sm">
                                            <option value="">All Categories</option>
                                            <option value="general"  {{ $categoryFilter === 'general'  ? 'selected' : '' }}>General</option>
                                            <option value="rte"      {{ $categoryFilter === 'rte'      ? 'selected' : '' }}>RTE</option>
                                            <option value="coc"      {{ $categoryFilter === 'coc'      ? 'selected' : '' }}>COC</option>
                                            <option value="discount" {{ $categoryFilter === 'discount' ? 'selected' : '' }}>Discount</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Row 2: Field checkboxes grouped --}}
                                @php
                                    $groups = ['Student' => [], 'Family' => [], 'Address' => [], 'Other' => []];
                                    foreach ($availableFields as $key => $meta) {
                                        $groups[$meta['group']][$key] = $meta['label'];
                                    }
                                @endphp

                                @foreach($groups as $groupName => $groupFields)
                                <div class="mb-2">
                                    <div class="d-flex align-items-center mb-1">
                                        <span class="badge bg-secondary me-2">{{ $groupName }}</span>
                                        <small class="text-muted">
                                            <a href="#" class="text-decoration-none select-group" data-group="{{ $groupName }}">Select all</a> /
                                            <a href="#" class="text-decoration-none deselect-group" data-group="{{ $groupName }}">None</a>
                                        </small>
                                    </div>
                                    <div class="row g-1">
                                        @foreach($groupFields as $fieldKey => $fieldLabel)
                                        <div class="col-6 col-md-3 col-lg-2">
                                            <div class="form-check form-check-sm">
                                                <input class="form-check-input field-cb group-{{ $groupName }}"
                                                       type="checkbox" name="fields[]"
                                                       value="{{ $fieldKey }}" id="f_{{ $fieldKey }}"
                                                       {{ in_array($fieldKey, $selectedFields) ? 'checked' : '' }}>
                                                <label class="form-check-label small" for="f_{{ $fieldKey }}">{{ $fieldLabel }}</label>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach

                            </div>
                            <div class="card-footer bg-white d-flex gap-2">
                                <button type="submit" name="generate" value="1" class="btn btn-primary btn-sm">
                                    <i class="bi bi-table me-1"></i> Generate Report
                                </button>
                                <a href="{{ route('reports.students') }}" class="btn btn-outline-secondary btn-sm">Reset</a>

                                @if($formSubmitted && $students !== null && $students->count() > 0)
                                @php
                                    $pdfParams = array_merge(
                                        ['pdf' => 1, 'session_id' => $selectedSessionId],
                                        $classFilter    ? ['class_id'     => $classFilter]    : [],
                                        $categoryFilter ? ['fee_category' => $categoryFilter] : [],
                                        ['fields' => $selectedFields]
                                    );
                                @endphp
                                <a href="{{ route('reports.students') }}?{{ http_build_query($pdfParams) }}"
                                   class="btn btn-outline-dark btn-sm ms-auto">
                                    <i class="bi bi-download me-1"></i> Download PDF
                                </a>
                                @endif
                            </div>
                        </div>
                    </form>

                    {{-- ── RESULTS TABLE ── --}}
                    @if($formSubmitted && $students !== null)
                        @if($students->isEmpty())
                            <div class="alert alert-info">No students found for the selected filters.</div>
                        @else
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">{{ $students->count() }} student(s) found
                                @if($selectedSession) — {{ $selectedSession->session_name }} @endif
                            </span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm align-middle" style="font-size:0.85rem;">
                                <thead class="table-dark">
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
                                            @elseif($fieldKey === 'admission_reg_id')
                                                {{ $student->admission ? ($student->admission->dga_admission_no ?? $student->admission->general_id ?? '—') : '—' }}
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
                        </div>
                        @endif
                    @elseif(!$formSubmitted)
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-table" style="font-size:2.5rem;"></i>
                            <p class="mt-2">Select the fields you need above and click <strong>Generate Report</strong>.</p>
                        </div>
                    @endif

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.select-group').forEach(function(link) {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelectorAll('.group-' + this.dataset.group).forEach(function(cb) { cb.checked = true; });
    });
});
document.querySelectorAll('.deselect-group').forEach(function(link) {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelectorAll('.group-' + this.dataset.group).forEach(function(cb) { cb.checked = false; });
    });
});
</script>
@endsection
