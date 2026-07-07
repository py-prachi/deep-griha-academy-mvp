@extends('layouts.app')
@section('title', 'Diagnostic Test Results')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4 pe-4">

                    <h4 class="mb-1"><i class="bi bi-clipboard-data me-1"></i> Diagnostic Test Results</h4>
                    <p class="text-muted small mb-3">Select a class, subject, and assessment type to view results entered by teachers.</p>

                    @include('session-messages')

                    @php
                        $assessmentTypes    = \App\Models\DiagnosticResult::ASSESSMENT_TYPES;
                        $selClassId         = request('class_id');
                        $selSectionId       = request('section_id');
                        $selSubjectId       = request('subject_id');
                        $selAssessmentType  = request('assessment_type');

                        // Subjects for selected class+section
                        $availableSubjects = collect();
                        if ($selClassId && $selSectionId) {
                            $availableSubjects = \App\Models\Subject::whereHas('subjectTeachers', fn($q) =>
                                $q->where('session_id', $sessionId)
                                  ->where('class_id', $selClassId)
                                  ->where('section_id', $selSectionId)
                            )->orderBy('sort_order')->get();
                        }
                    @endphp

                    {{-- Filter row --}}
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-body py-3">
                            <form method="GET" action="{{ route('diagnostics.index') }}" class="row g-2 align-items-end">
                                {{-- Class --}}
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold mb-1">Class</label>
                                    <select name="class_id" id="class_select" class="form-select form-select-sm">
                                        <option value="">— Select Class —</option>
                                        @foreach($classes as $class)
                                            @foreach($class->sections as $section)
                                            <option value="{{ $class->id }}"
                                                    data-section="{{ $section->id }}"
                                                    {{ ($selClassId == $class->id && $selSectionId == $section->id) ? 'selected' : '' }}>
                                                {{ $class->class_name }} {{ $section->section_name }}
                                            </option>
                                            @endforeach
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="section_id" id="section_id_hidden" value="{{ $selSectionId }}">
                                </div>

                                {{-- Subject --}}
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold mb-1">Subject</label>
                                    <select name="subject_id" class="form-select form-select-sm" {{ $availableSubjects->isEmpty() ? 'disabled' : '' }} onchange="this.form.submit()">
                                        <option value="">— Select Subject —</option>
                                        @foreach($availableSubjects as $subject)
                                        <option value="{{ $subject->id }}" {{ $selSubjectId == $subject->id ? 'selected' : '' }}>
                                            {{ $subject->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Assessment Type --}}
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold mb-1">Assessment Type</label>
                                    <select name="assessment_type" class="form-select form-select-sm" {{ !$selSubjectId ? 'disabled' : '' }} onchange="this.form.submit()">
                                        <option value="">— Select Type —</option>
                                        @foreach($assessmentTypes as $typeKey => $typeLabel)
                                        <option value="{{ $typeKey }}" {{ $selAssessmentType === $typeKey ? 'selected' : '' }}>
                                            {{ $typeLabel }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-auto">
                                    @if($selClassId && $selSectionId && $selSubjectId && $selAssessmentType)
                                    <a href="{{ route('diagnostics.index') }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-x"></i> Clear
                                    </a>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Results table --}}
                    @if($selClassId && $selSectionId && $selSubjectId && $selAssessmentType)
                    @php
                        $promotionRepo = new \App\Repositories\PromotionRepository();
                        $promotions    = $promotionRepo->getAll($sessionId, $selClassId, $selSectionId)
                            ->sortBy('roll_number')->values();

                        $results = \App\Models\DiagnosticResult::where('session_id', $sessionId)
                            ->where('class_id', $selClassId)
                            ->where('section_id', $selSectionId)
                            ->where('subject_id', $selSubjectId)
                            ->where('assessment_type', $selAssessmentType)
                            ->get()
                            ->keyBy('student_id');

                        $selClass   = $classes->firstWhere('id', $selClassId);
                        $selSection = $selClass ? $selClass->sections->firstWhere('id', $selSectionId) : null;
                        $selSubject = $availableSubjects->firstWhere('id', $selSubjectId);
                    @endphp

                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="fw-semibold mb-0">
                            {{ optional($selClass)->class_name }} {{ optional($selSection)->section_name }}
                            &nbsp;·&nbsp; {{ optional($selSubject)->name }}
                            &nbsp;·&nbsp; {{ $assessmentTypes[$selAssessmentType] }}
                        </h6>
                        <span class="text-muted small">
                            {{ $results->count() }} / {{ $promotions->count() }} students filled
                        </span>
                    </div>

                    @if($promotions->isEmpty())
                        <div class="alert alert-light text-muted">No students found for this class and section.</div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle" style="font-size:0.88rem;">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:45px;" class="text-center">Roll</th>
                                    <th>Student Name</th>
                                    <th style="width:140px;">Marks Obtained</th>
                                    @if($selAssessmentType === 'diagnostic')
                                    <th>Needs</th>
                                    @endif
                                    <th>Notes</th>
                                    <th style="width:120px;" class="text-muted small">Entered by</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($promotions as $promotion)
                                @php $result = $results->get($promotion->student_id); @endphp
                                <tr class="{{ !$result ? 'text-muted' : '' }}">
                                    <td class="text-center">{{ $promotion->roll_number ?? '—' }}</td>
                                    <td>
                                        {{ optional($promotion->student)->first_name }}
                                        {{ optional($promotion->student)->last_name }}
                                    </td>
                                    <td>{{ $result->marks_obtained ?? '—' }}</td>
                                    @if($selAssessmentType === 'diagnostic')
                                    <td>{{ $result->needs ?? '—' }}</td>
                                    @endif
                                    <td class="text-muted small">{{ $result->notes ?? '—' }}</td>
                                    <td class="text-muted small">
                                        {{ $result ? optional($result->teacher)->first_name . ' ' . optional($result->teacher)->last_name : '—' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    @elseif($selClassId && $selSectionId && $selSubjectId)
                        <div class="alert alert-info">Select an assessment type to view results.</div>
                    @elseif($selClassId && $selSectionId)
                        <div class="alert alert-info">Select a subject to continue.</div>
                    @else
                        <div class="alert alert-light text-muted">Select a class to get started.</div>
                    @endif

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('class_select').addEventListener('change', function () {
    const selected = this.options[this.selectedIndex];
    document.getElementById('section_id_hidden').value = selected.getAttribute('data-section') || '';
    // Clear downstream selects so stale values don't carry over
    const form = this.closest('form');
    form.querySelector('select[name="subject_id"]').value = '';
    const atSel = form.querySelector('select[name="assessment_type"]');
    if (atSel) atSel.value = '';
    form.submit();
});
</script>
@endpush
@endsection
