@extends('layouts.app')
@section('title', 'Diagnostic Test Results')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">

                    <h4 class="mb-1"><i class="bi bi-clipboard-data me-1"></i> Diagnostic Test Results</h4>
                    <p class="text-muted small mb-3">Enter assessment results per subject and assessment type.</p>

                    @include('session-messages')

                    @php
                        $assessmentTypes = \App\Models\DiagnosticResult::ASSESSMENT_TYPES;
                    @endphp

                    {{-- ── SUBJECT TEACHER SECTION ── --}}
                    @if($subjectAssignments->isNotEmpty())
                    <h6 class="text-uppercase text-muted small fw-bold mb-2 border-bottom pb-1">
                        <i class="bi bi-pencil-square me-1"></i> My Subjects
                    </h6>

                    @php $groupedAssignments = $subjectAssignments->groupBy('class_id'); @endphp

                    @foreach($groupedAssignments as $classId => $assignments)
                    @php $first = $assignments->first(); @endphp
                    <div class="card mb-3 border-primary">
                        <div class="card-header bg-primary text-white py-2">
                            <strong><i class="bi bi-building me-1"></i>
                            {{ $first->schoolClass->class_name }} {{ $first->section->section_name }}</strong>
                        </div>
                        <div class="card-body p-0">
                            @foreach($assignments as $assignment)
                            <div class="border-bottom px-3 py-2">
                                <div class="fw-semibold mb-2">{{ $assignment->subject->name }}</div>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($assessmentTypes as $typeKey => $typeLabel)
                                    <a href="{{ route('diagnostics.entry', [
                                            'class_id'        => $assignment->class_id,
                                            'section_id'      => $assignment->section_id,
                                            'subject_id'      => $assignment->subject_id,
                                            'assessment_type' => $typeKey,
                                        ]) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil me-1"></i>{{ $typeLabel }}
                                    </a>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                    @endif

                    {{-- ── CLASS TEACHER VIEW SECTION ── --}}
                    {{-- A teacher may be CT for more than one class (e.g. one teacher
                         covering both Nursery and Lower KG) — show a block per class.
                         Pre-Primary CTs teach every subject themselves, so their block
                         is editable across all subjects; Class 1-8 stays view-only. --}}
                    @foreach($ctAssignments as $ctAssignment)
                    @php
                        $isPP            = $ctIsPrePrimary[$ctAssignment->id] ?? false;
                        $ctExtraSubjects = $ctSubjectsByAssignment[$ctAssignment->id] ?? collect();
                    @endphp
                    {{-- Pre-Primary: skip once every subject is already covered above --}}
                    @if($isPP && $ctExtraSubjects->isEmpty())
                        @continue
                    @endif
                    <h6 class="text-uppercase text-muted small fw-bold mb-2 border-bottom pb-1 mt-4">
                        <i class="bi {{ $isPP ? 'bi-pencil-square' : 'bi-eye' }} me-1"></i>
                        My Class — {{ $ctAssignment->schoolClass->class_name }} {{ $ctAssignment->section->section_name }}
                        @unless($isPP)
                        <span class="text-secondary fw-normal ms-1">(view only)</span>
                        @endunless
                    </h6>

                    @if($isPP)
                        <div class="card mb-3">
                            <div class="card-body p-0">
                                @foreach($ctExtraSubjects as $subject)
                                <div class="border-bottom px-3 py-2">
                                    <div class="fw-semibold mb-2">{{ $subject->name }}</div>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($assessmentTypes as $typeKey => $typeLabel)
                                        <a href="{{ route('diagnostics.entry', [
                                                'class_id'        => $ctAssignment->class_id,
                                                'section_id'      => $ctAssignment->section_id,
                                                'subject_id'      => $subject->id,
                                                'assessment_type' => $typeKey,
                                            ]) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil me-1"></i>{{ $typeLabel }}
                                        </a>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        @php
                            $ctViewSubjects = \App\Models\Subject::whereHas('subjectTeachers', fn($q) =>
                                $q->where('session_id', $sessionId)
                                  ->where('class_id', $ctAssignment->class_id)
                                  ->where('section_id', $ctAssignment->section_id)
                            )->orderBy('sort_order')->get();
                        @endphp
                        @if($ctViewSubjects->isNotEmpty())
                        <div class="card mb-3">
                            <div class="card-body p-0">
                                @foreach($ctViewSubjects as $subject)
                                <div class="border-bottom px-3 py-2">
                                    <div class="fw-semibold mb-2 text-secondary">{{ $subject->name }}</div>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($assessmentTypes as $typeKey => $typeLabel)
                                        <a href="{{ route('diagnostics.view', [
                                                'class_id'        => $ctAssignment->class_id,
                                                'section_id'      => $ctAssignment->section_id,
                                                'subject_id'      => $subject->id,
                                                'assessment_type' => $typeKey,
                                            ]) }}"
                                           class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-eye me-1"></i>{{ $typeLabel }}
                                        </a>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @else
                            <div class="alert alert-light text-muted">No subjects assigned to this class yet.</div>
                        @endif
                    @endif
                    @endforeach

                    @if($subjectAssignments->isEmpty() && $ctAssignments->isEmpty())
                    <div class="alert alert-warning">
                        You have no subject assignments for the current session.
                        Ask the admin to assign you via <a href="{{ route('academics.teacher-assignments') }}">Teacher Assignments</a>.
                    </div>
                    @endif

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
