@extends('layouts.app')
@section('title', 'Teacher Assignments')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <div class="d-flex align-items-center mb-3">
                        <a href="{{ url('academics/settings') }}" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <h4 class="mb-0"><i class="bi bi-person-badge me-1"></i> Teacher Assignments — {{ $session->session_name }}</h4>
                    </div>

                    @include('session-messages')

                    @if($teachers->isEmpty())
                    <div class="alert alert-warning">No teachers found. Add teacher accounts first.</div>
                    @endif

                    <div class="row g-4">
                        {{-- CLASS TEACHERS --}}
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-dark text-white">
                                    <strong><i class="bi bi-person-check me-1"></i> Class Teachers</strong>
                                    <small class="ms-2 opacity-75">One per section — takes attendance</small>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('academics.saveClassTeacher') }}" class="mb-3">
                                        @csrf
                                        <input type="hidden" name="session_id" value="{{ $sessionId }}">
                                        <div class="row g-2">
                                            <div class="col-12">
                                                <select name="teacher_id" class="form-select form-select-sm" required>
                                                    <option value="" disabled selected>Select teacher</option>
                                                    @foreach($teachers as $t)
                                                        <option value="{{ $t->id }}">{{ $t->first_name }} {{ $t->last_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <select name="class_id" class="form-select form-select-sm ct-class-select" onchange="loadSections(this, 'ct-section')" required>
                                                    <option value="" disabled selected>Class</option>
                                                    @foreach($schoolClasses as $c)
                                                        <option value="{{ $c->id }}">{{ $c->class_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <select name="section_id" class="form-select form-select-sm" id="ct-section" required>
                                                    <option value="" disabled selected>Section</option>
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <button class="btn btn-sm btn-success w-100">
                                                    <i class="bi bi-plus me-1"></i> Assign Class Teacher
                                                </button>
                                            </div>
                                        </div>
                                    </form>

                                    {{-- Current class teachers --}}
                                    @if($classTeachers->isEmpty())
                                        <p class="text-muted small text-center">No class teachers assigned yet.</p>
                                    @else
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-secondary">
                                            <tr><th>Class</th><th>Section</th><th>Teacher</th><th></th></tr>
                                        </thead>
                                        <tbody>
                                            @foreach($classTeachers->sortBy(fn($ct) => $ct->class_id) as $ct)
                                            <tr>
                                                <td>{{ $ct->schoolClass->class_name ?? '—' }}</td>
                                                <td>{{ $ct->section->section_name ?? '—' }}</td>
                                                <td>{{ $ct->teacher->first_name ?? '' }} {{ $ct->teacher->last_name ?? '' }}</td>
                                                <td class="text-center">
                                                    <form method="POST" action="{{ route('academics.removeClassTeacher', $ct) }}"
                                                        onsubmit="return confirm('Remove this class teacher?')">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-sm btn-outline-danger py-0">
                                                            <i class="bi bi-x"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- SUBJECT TEACHERS --}}
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-dark text-white">
                                    <strong><i class="bi bi-journal-text me-1"></i> Subject Teachers</strong>
                                    <small class="ms-2 opacity-75">Enters marks, gives assignments</small>
                                </div>
                                <div class="card-body">
                                    @if($subjects->isEmpty())
                                        <div class="alert alert-warning py-2 small">
                                            <a href="{{ route('subjects.index') }}">Set up subjects first →</a>
                                        </div>
                                    @else
                                    <form method="POST" action="{{ route('academics.saveSubjectTeacher') }}" class="mb-3">
                                        @csrf
                                        <input type="hidden" name="session_id" value="{{ $sessionId }}">
                                        <div class="row g-2">
                                            <div class="col-12">
                                                <select name="teacher_id" class="form-select form-select-sm" required>
                                                    <option value="" disabled selected>Select teacher</option>
                                                    @foreach($teachers as $t)
                                                        <option value="{{ $t->id }}">{{ $t->first_name }} {{ $t->last_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <select name="subject_id" class="form-select form-select-sm" required>
                                                    <option value="" disabled selected>Select subject</option>
                                                    @foreach($subjects as $s)
                                                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <select name="class_id" class="form-select form-select-sm" onchange="loadSections(this, 'st-section')" required>
                                                    <option value="" disabled selected>Class</option>
                                                    @foreach($schoolClasses as $c)
                                                        <option value="{{ $c->id }}">{{ $c->class_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <select name="section_id" class="form-select form-select-sm" id="st-section" required>
                                                    <option value="" disabled selected>Section</option>
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <button class="btn btn-sm btn-success w-100">
                                                    <i class="bi bi-plus me-1"></i> Assign Subject Teacher
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                    @endif

                                    {{-- Current subject teachers --}}
                                    @if($subjectTeachers->isEmpty())
                                        <p class="text-muted small text-center">No subject teachers assigned yet.</p>
                                    @else
                                    <div style="max-height:350px;overflow-y:auto;">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-secondary">
                                            <tr><th>Subject</th><th>Class</th><th>Sec</th><th>Teacher</th><th></th></tr>
                                        </thead>
                                        <tbody>
                                            @foreach($subjectTeachers->sortBy(fn($st) => $st->class_id) as $st)
                                            <tr>
                                                <td><small>{{ $st->subject->name ?? '—' }}</small></td>
                                                <td><small>{{ $st->schoolClass->class_name ?? '—' }}</small></td>
                                                <td><small>{{ $st->section->section_name ?? '—' }}</small></td>
                                                <td><small>{{ $st->teacher->first_name ?? '' }}</small></td>
                                                <td class="text-center">
                                                    <form method="POST" action="{{ route('academics.removeSubjectTeacher', $st) }}"
                                                        onsubmit="return confirm('Remove?')">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-sm btn-outline-danger py-0">
                                                            <i class="bi bi-x"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
<script>
var sectionsUrl = "{{ route('get.sections.courses.by.classId') }}";
function loadSections(select, targetId) {
    var classId = select.value;
    var target = document.getElementById(targetId);
    target.options.length = 0;
    target.add(new Option('Loading...', '', true, false));
    fetch(sectionsUrl + '?class_id=' + classId)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            target.options.length = 0;
            target.add(new Option('Select section', '', true, false));
            (data.sections || []).forEach(function(s) {
                target.add(new Option(s.section_name, s.id));
            });
        });
}
</script>
@endsection
