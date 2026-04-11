@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h5 class="mb-1"><i class="bi bi-check2-square me-1"></i> Pre-Primary Assessment</h5>
                    <p class="text-muted small mb-3">Select a class and section to continue.</p>

                    @include('session-messages')

                    <div class="card" style="max-width:460px;">
                        <div class="card-body">
                            <form method="GET" action="{{ route('preprimary.entry') }}" class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Class <span class="text-danger">*</span></label>
                                    <select name="class_id" id="pp-class" class="form-select form-select-sm"
                                        onchange="loadPPSections(this)" required>
                                        <option value="" disabled selected>Select class</option>
                                        @foreach($ppClasses as $c)
                                            <option value="{{ $c->id }}">{{ $c->class_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Section <span class="text-danger">*</span></label>
                                    <select name="section_id" id="pp-section" class="form-select form-select-sm" required>
                                        <option value="" disabled selected>Select class first</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Term <span class="text-danger">*</span></label>
                                    <select name="term" class="form-select form-select-sm" required>
                                        <option value="1" {{ $term == 1 ? 'selected' : '' }}>Term 1</option>
                                        <option value="2" {{ $term == 2 ? 'selected' : '' }}>Term 2</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-sm btn-primary">
                                        <i class="bi bi-arrow-right me-1"></i> Go to Skill Entry
                                    </button>
                                </div>
                            </form>
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
function loadPPSections(select) {
    var classId = select.value;
    var target = document.getElementById('pp-section');
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
