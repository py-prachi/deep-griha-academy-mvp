@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <div class="d-flex align-items-center mb-3">
                        <h4 class="mb-0"><i class="bi bi-grid-3x3-gap me-1"></i> Marks Review Dashboard</h4>
                    </div>
                    @include('session-messages')

                    @if(!empty($error))
                        <div class="alert alert-warning">{{ $error }}</div>
                    @elseif(!empty($pick))
                        {{-- Admin: pick class/section first --}}
                        <div class="card" style="max-width:400px;">
                            <div class="card-body">
                                <form method="GET" action="{{ route('marks.review') }}" class="row g-2">
                                    <div class="col-12">
                                        <select name="class_id" id="rv-class" class="form-select form-select-sm"
                                            onchange="loadSections(this)" required>
                                            <option value="" disabled selected>Select class</option>
                                            @foreach($classes as $c)
                                                <option value="{{ $c->id }}">{{ $c->class_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <select name="section_id" id="rv-section" class="form-select form-select-sm" required>
                                            <option value="" disabled selected>Section</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <button class="btn btn-sm btn-primary w-100">View Status</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @else
                        <p class="text-muted small mb-2">
                            <strong>{{ $schoolClass->class_name ?? '' }}</strong> — {{ $section->section_name ?? '' }}
                            &nbsp;|&nbsp; {{ $studentCount }} students
                            @if(Auth::user()->role === 'admin')
                                &nbsp;|&nbsp; <a href="{{ route('marks.review') }}">Change class</a>
                            @endif
                        </p>

                        <div class="d-flex gap-3 mb-3 small">
                            <span><i class="bi bi-circle-fill text-success"></i> Started</span>
                            <span><i class="bi bi-circle text-danger"></i> Not started</span>
                        </div>

                        <div class="table-responsive">
                        <table class="table table-sm table-bordered" style="max-width:560px;">
                            <thead class="table-dark">
                                <tr>
                                    <th>Subject</th>
                                    <th class="text-center">Term 1</th>
                                    <th class="text-center">Term 2</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subjects as $subject)
                                <tr>
                                    <td>{{ $subject->name }}
                                        @if($subject->mark_type === 'grade_only')
                                            <small class="text-muted">(grade)</small>
                                        @endif
                                    </td>
                                    @foreach([1,2] as $term)
                                    @php
                                        $s = $statusGrid[$subject->id][$term] ?? ['status'=>'not_started','full'=>0,'partial'=>0,'total'=>0];
                                        $started = ($s['full'] + $s['partial']) > 0;
                                    @endphp
                                    <td class="text-center">
                                        @if($started)
                                            <i class="bi bi-circle-fill text-success"></i>
                                        @else
                                            <i class="bi bi-circle text-danger"></i>
                                        @endif
                                        <a href="{{ route('marks.entry', ['subject_id'=>$subject->id,'class_id'=>$class_id,'section_id'=>$section_id,'term'=>$term]) }}"
                                            class="btn btn-xs btn-outline-secondary py-0 px-1 ms-2" style="font-size:0.7rem;">
                                            Enter
                                        </a>
                                    </td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        </div>
                    @endif
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
<script>
var sectionsUrl = "{{ route('get.sections.courses.by.classId') }}";
function loadSections(select) {
    var classId = select.value;
    var target = document.getElementById('rv-section');
    if (!target) return;
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
