@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-box-arrow-right"></i> Record Student Exit
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('exits.index') }}">Exited Students</a></li>
                            <li class="breadcrumb-item active">Record Exit</li>
                        </ol>
                    </nav>

                    @include('session-messages')

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row">
                        {{-- Student Search --}}
                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3">
                                <div class="card-header bg-light fw-semibold">Select Student</div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Search by Name</label>
                                        <div class="position-relative">
                                            <input type="text" id="studentSearch" class="form-control"
                                                placeholder="Type student name..."
                                                value="{{ $admission ? $admission->student_name . ' (' . ($admission->schoolClass ? $admission->schoolClass->class_name : '') . ')' : '' }}"
                                                autocomplete="off">
                                            <ul id="searchResults" class="list-group position-absolute w-100 shadow"
                                                style="z-index:1000; max-height:220px; overflow-y:auto; display:none;"></ul>
                                        </div>
                                        <div id="studentData" style="display:none;"
                                            data-students="{{ json_encode($students->map(function($s) { return ['id' => $s->id, 'name' => $s->student_name, 'cls' => $s->schoolClass ? $s->schoolClass->class_name : '']; })->values()) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Student Info Preview --}}
                            <div class="card shadow-sm" id="studentInfoCard" style="{{ $admission ? '' : 'display:none;' }}">
                                <div class="card-header bg-light fw-semibold">Student Details</div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr><th>Name</th><td id="previewName">{{ $admission ? $admission->student_name : '' }}</td></tr>
                                        <tr><th>Class</th><td id="previewClass">{{ $admission && $admission->schoolClass ? $admission->schoolClass->class_name : '' }}</td></tr>
                                        <tr><th>Section</th><td id="previewSection">{{ $admission && $admission->section ? $admission->section->section_name : '' }}</td></tr>
                                        <tr><th>DOB</th><td id="previewDob">{{ $admission && $admission->date_of_birth ? $admission->date_of_birth->format('d/m/Y') : '' }}</td></tr>
                                        <tr><th>Father</th><td id="previewFather">{{ $admission ? ($admission->father_name ?? '—') : '' }}</td></tr>
                                        <tr><th>Mother</th><td id="previewMother">{{ $admission ? ($admission->mother_name ?? '—') : '' }}</td></tr>
                                        <tr><th>Mobile</th><td id="previewMobile">{{ $admission ? ($admission->contact_mobile ?? '—') : '' }}</td></tr>
                                        <tr><th>Admitted</th><td id="previewAdmitted">{{ $admission && $admission->confirmed_date ? $admission->confirmed_date->format('d/m/Y') : '' }}</td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Exit Form --}}
                        <div class="col-md-8">
                            <div class="card shadow-sm">
                                <div class="card-header bg-danger text-white fw-semibold">
                                    <i class="bi bi-box-arrow-right"></i> Deep Griha Academy — School Exit Form
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('exits.store') }}">
                                        @csrf
                                        <input type="hidden" name="admission_id" id="admissionIdInput"
                                            value="{{ $admission ? $admission->id : '' }}">

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Student Name</label>
                                                <input type="text" class="form-control" id="displayStudentName"
                                                    value="{{ $admission ? $admission->student_name : '' }}" readonly>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Std.</label>
                                                <input type="text" class="form-control" id="displayStd"
                                                    value="{{ $admission && $admission->schoolClass ? $admission->schoolClass->class_name : '' }}" readonly>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Date of Leaving <span class="text-danger">*</span></label>
                                                <input type="date" name="exit_date" class="form-control"
                                                    value="{{ old('exit_date') }}" required>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">What is your primary reason for leaving?</label>
                                            <textarea name="reason_for_leaving" class="form-control" rows="2">{{ old('reason_for_leaving') }}</textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">What did you like most about Deep Griha Academy?</label>
                                            <textarea name="liked_most" class="form-control" rows="2">{{ old('liked_most') }}</textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">What did you like least about Deep Griha Academy?</label>
                                            <textarea name="liked_least" class="form-control" rows="2">{{ old('liked_least') }}</textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Suggestions to make school better?</label>
                                            <textarea name="suggestions" class="form-control" rows="2">{{ old('suggestions') }}</textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Rate your experience (1 = Poor, 5 = Excellent)</label>
                                            <div class="d-flex gap-3 mt-1">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="rating"
                                                            id="star{{ $i }}" value="{{ $i }}"
                                                            {{ old('rating') == $i ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="star{{ $i }}">{{ $i }} ★</label>
                                                    </div>
                                                @endfor
                                            </div>
                                        </div>

                                        <hr>
                                        <h6 class="text-muted mb-3">Parent / Guardian</h6>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Name of Parent / Guardian</label>
                                                <input type="text" name="parent_name" class="form-control"
                                                    value="{{ old('parent_name') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Contact Details</label>
                                                <input type="text" name="parent_contact" class="form-control"
                                                    value="{{ old('parent_contact') }}">
                                            </div>
                                        </div>

                                        <hr>
                                        <h6 class="text-muted mb-3">DGA Staff</h6>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Name of DGA Staff</label>
                                                <input type="text" name="staff_name" class="form-control"
                                                    value="{{ old('staff_name') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Date of Form Submission</label>
                                                <input type="date" name="form_submitted_at" class="form-control"
                                                    value="{{ old('form_submitted_at', date('Y-m-d')) }}">
                                            </div>
                                        </div>

                                        <div class="alert alert-warning py-2">
                                            <i class="bi bi-exclamation-triangle"></i>
                                            <strong>Note:</strong> Marking a student as exited removes them from the active student list. This cannot be undone.
                                        </div>

                                        <div class="mt-3 d-flex gap-2">
                                            <button type="submit" class="btn btn-danger" id="submitBtn" {{ $admission ? '' : 'disabled' }}>
                                                <i class="bi bi-box-arrow-right"></i> Mark as Exited
                                            </button>
                                            <a href="{{ route('exits.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var admissionInput = document.getElementById('admissionIdInput');
    var submitBtn      = document.getElementById('submitBtn');
    var infoCard       = document.getElementById('studentInfoCard');
    var searchInput    = document.getElementById('studentSearch');
    var resultsList    = document.getElementById('searchResults');

    // All students from data attribute
    var allStudents = JSON.parse(document.getElementById('studentData').getAttribute('data-students') || '[]');

    if (admissionInput.value) {
        submitBtn.disabled = false;
    }

    searchInput.addEventListener('input', function () {
        var q = this.value.trim().toLowerCase();
        resultsList.innerHTML = '';

        if (q.length < 1) {
            resultsList.style.display = 'none';
            return;
        }

        var matches = allStudents.filter(function (s) {
            return s.name.toLowerCase().indexOf(q) !== -1;
        });

        if (matches.length === 0) {
            resultsList.style.display = 'none';
            return;
        }

        matches.forEach(function (s) {
            var li = document.createElement('li');
            li.className = 'list-group-item list-group-item-action';
            li.style.cursor = 'pointer';
            li.textContent = s.name + ' (' + s.cls + ')';
            li.addEventListener('mousedown', function (e) {
                e.preventDefault(); // prevent input blur before click fires
                selectStudent(s.id, s.name, s.cls);
            });
            resultsList.appendChild(li);
        });

        resultsList.style.display = 'block';
    });

    searchInput.addEventListener('blur', function () {
        setTimeout(function () { resultsList.style.display = 'none'; }, 150);
    });

    function selectStudent(admissionId, name, cls) {
        searchInput.value = name + ' (' + cls + ')';
        resultsList.style.display = 'none';
        admissionInput.value = '';
        submitBtn.disabled = true;

        fetch('{{ route("exits.studentInfo") }}?admission_id=' + admissionId, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.error) {
                alert(data.error);
                infoCard.style.display = 'none';
                clearPreview();
                return;
            }
            admissionInput.value   = admissionId;
            submitBtn.disabled     = false;
            infoCard.style.display = '';

            document.getElementById('previewName').textContent     = data.student_name;
            document.getElementById('previewClass').textContent    = data.class_name;
            document.getElementById('previewSection').textContent  = data.section_name;
            document.getElementById('previewDob').textContent      = data.dob;
            document.getElementById('previewFather').textContent   = data.father_name;
            document.getElementById('previewMother').textContent   = data.mother_name;
            document.getElementById('previewMobile').textContent   = data.contact_mobile;
            document.getElementById('previewAdmitted').textContent = data.confirmed_date;

            document.getElementById('displayStudentName').value = data.student_name;
            document.getElementById('displayStd').value         = data.class_name;
        })
        .catch(function() {
            alert('Could not load student info. Please try again.');
        });
    }

    function clearPreview() {
        ['previewName','previewClass','previewSection','previewDob',
         'previewFather','previewMother','previewMobile','previewAdmitted'].forEach(function(id) {
            document.getElementById(id).textContent = '';
        });
        document.getElementById('displayStudentName').value = '';
        document.getElementById('displayStd').value = '';
    }
});
</script>
@endpush
