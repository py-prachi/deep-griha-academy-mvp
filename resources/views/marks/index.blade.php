@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <div class="d-flex align-items-center mb-3">
                        <h4 class="mb-0"><i class="bi bi-pencil-square me-1"></i> Enter Marks</h4>
                    </div>
                    @include('session-messages')

                    @if($userRole === 'teacher' && !$ct && $subjects->isEmpty())
                    {{-- Teacher with no assignments at all --}}
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        You are not assigned as a Class Teacher or Subject Teacher for any class this session.
                        Please contact the admin.
                    </div>

                    @elseif($userRole === 'teacher' && $ct)
                    {{-- ── CT VIEW ── --}}
                    <div class="alert alert-info py-2 small mb-3">
                        <i class="bi bi-person-badge me-1"></i>
                        You are the <strong>Class Teacher</strong> for
                        <strong>{{ $ct->schoolClass->class_name ?? 'your class' }} — {{ $ct->section->section_name ?? '' }}</strong>.
                        You can enter marks for all subjects.
                    </div>

                    <div class="card mb-3" style="max-width:500px;">
                        <div class="card-header py-2 small fw-semibold">
                            {{ $ct->schoolClass->class_name ?? '' }} — {{ $ct->section->section_name ?? '' }}
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('marks.entry') }}" class="row g-3">
                                <input type="hidden" name="class_id" value="{{ $ct->class_id }}">
                                <input type="hidden" name="section_id" value="{{ $ct->section_id }}">
                                <div class="col-12">
                                    <label class="form-label">Subject <span class="text-danger">*</span></label>
                                    <select name="subject_id" class="form-select form-select-sm" required>
                                        <option value="" disabled selected>Select subject</option>
                                        @foreach($subjects as $s)
                                            <option value="{{ $s->id }}">{{ $s->name }}@if($s->mark_type === 'grade_only') (Grade only)@endif</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Term <span class="text-danger">*</span></label>
                                    <select name="term" class="form-select form-select-sm" required>
                                        <option value="1">Term 1</option>
                                        <option value="2">Term 2</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-sm btn-primary"><i class="bi bi-arrow-right me-1"></i> Go to Mark Entry</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Subject teacher panel for other classes (if any) --}}
                    @if($otherSubjects->isNotEmpty())
                    <div class="card" style="max-width:560px;">
                        <div class="card-header py-2 small fw-semibold text-muted">
                            <i class="bi bi-book me-1"></i> Also assigned as Subject Teacher
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('marks.entry') }}" class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Subject <span class="text-danger">*</span></label>
                                    <select name="subject_id" id="st-subject-ct" class="form-select form-select-sm"
                                        onchange="loadMyClassSections(this, 'st-classsection-ct', 'st-class-id-ct', 'st-section-id-ct')" required>
                                        <option value="" disabled selected>Select subject</option>
                                        @foreach($otherSubjects as $s)
                                            <option value="{{ $s->id }}"
                                                data-assignments="{{ json_encode($subjectAssignments->get($s->id, collect())->filter(function($a) use ($ct) { return $a['class_id'] != $ct->class_id; })->values()->all()) }}">
                                                {{ $s->name }}@if($s->mark_type === 'grade_only') (Grade only)@endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Class &amp; Section <span class="text-danger">*</span></label>
                                    <select id="st-classsection-ct" class="form-select form-select-sm"
                                        onchange="splitClassSection(this, 'st-class-id-ct', 'st-section-id-ct')" required>
                                        <option value="" disabled selected>Select subject first</option>
                                    </select>
                                    <input type="hidden" name="class_id" id="st-class-id-ct">
                                    <input type="hidden" name="section_id" id="st-section-id-ct">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Term <span class="text-danger">*</span></label>
                                    <select name="term" class="form-select form-select-sm" required>
                                        <option value="1">Term 1</option>
                                        <option value="2">Term 2</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-sm btn-primary"><i class="bi bi-arrow-right me-1"></i> Go to Mark Entry</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif

                    @elseif($userRole === 'teacher')
                    {{-- ── SUBJECT TEACHER VIEW: subject list with their class/section ── --}}
                    <p class="text-muted small mb-3">Select the subject and class you are assigned to:</p>
                    <div class="card" style="max-width:560px;">
                        <div class="card-body">
                            <form method="GET" action="{{ route('marks.entry') }}" class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Subject <span class="text-danger">*</span></label>
                                    <select name="subject_id" id="st-subject" class="form-select form-select-sm"
                                        onchange="loadMyClassSections(this)" required>
                                        <option value="" disabled selected>Select subject</option>
                                        @foreach($subjects as $s)
                                            <option value="{{ $s->id }}"
                                                data-assignments="{{ json_encode($subjectAssignments->get($s->id, collect())->values()->all()) }}">
                                                {{ $s->name }}
                                                @if($s->mark_type === 'grade_only')(Grade only)@endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Class &amp; Section <span class="text-danger">*</span></label>
                                    <select name="" id="st-classsection" class="form-select form-select-sm"
                                        onchange="splitClassSection(this)" required>
                                        <option value="" disabled selected>Select subject first</option>
                                    </select>
                                    <input type="hidden" name="class_id" id="st-class-id">
                                    <input type="hidden" name="section_id" id="st-section-id">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Term <span class="text-danger">*</span></label>
                                    <select name="term" class="form-select form-select-sm" required>
                                        <option value="1">Term 1</option>
                                        <option value="2">Term 2</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-sm btn-primary">
                                        <i class="bi bi-arrow-right me-1"></i> Go to Mark Entry
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    @else
                    {{-- ── ADMIN VIEW: full selector ── --}}
                    <div class="card" style="max-width:560px;">
                        <div class="card-body">
                            <form method="GET" action="{{ route('marks.entry') }}" class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Subject <span class="text-danger">*</span></label>
                                    <select name="subject_id" class="form-select form-select-sm" required>
                                        <option value="" disabled selected>Select subject</option>
                                        @foreach($subjects as $s)
                                            <option value="{{ $s->id }}">
                                                {{ $s->name }}
                                                @if($s->mark_type === 'grade_only')(Grade only)@endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Class <span class="text-danger">*</span></label>
                                    <select name="class_id" id="mk-class" class="form-select form-select-sm"
                                        onchange="loadSections(this)" required>
                                        <option value="" disabled selected>Select class</option>
                                        @foreach($classes as $c)
                                            <option value="{{ $c->id }}">{{ $c->class_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Section <span class="text-danger">*</span></label>
                                    <select name="section_id" id="mk-section" class="form-select form-select-sm" required>
                                        <option value="" disabled selected>Section</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Term <span class="text-danger">*</span></label>
                                    <select name="term" class="form-select form-select-sm" required>
                                        <option value="1">Term 1</option>
                                        <option value="2">Term 2</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-sm btn-primary">
                                        <i class="bi bi-arrow-right me-1"></i> Go to Mark Entry
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif

                    <div class="mt-3">
                        <a href="{{ route('marks.review') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-grid-3x3-gap me-1"></i> CT Review Dashboard
                        </a>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
<script>
// Admin: load sections when class changes
var sectionsUrl = "{{ route('get.sections.courses.by.classId') }}";
function loadSections(select) {
    var classId = select.value;
    var target = document.getElementById('mk-section');
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

// Subject teacher: populate class/section dropdown from subject assignments
function loadMyClassSections(select, targetId, classHiddenId, sectionHiddenId) {
    targetId      = targetId      || 'st-classsection';
    var opt  = select.options[select.selectedIndex];
    var data = opt ? JSON.parse(opt.dataset.assignments || '[]') : [];
    var target = document.getElementById(targetId);
    target.options.length = 0;
    if (data.length === 0) {
        target.add(new Option('No assignment found', '', true, false));
        return;
    }
    target.add(new Option('Select class/section', '', true, false));
    data.forEach(function(a) {
        target.add(new Option(a.label, a.class_id + '|' + a.section_id));
    });
}

function splitClassSection(select, classHiddenId, sectionHiddenId) {
    classHiddenId   = classHiddenId   || 'st-class-id';
    sectionHiddenId = sectionHiddenId || 'st-section-id';
    var parts = select.value.split('|');
    document.getElementById(classHiddenId).value   = parts[0] || '';
    document.getElementById(sectionHiddenId).value = parts[1] || '';
}
</script>
@endsection
