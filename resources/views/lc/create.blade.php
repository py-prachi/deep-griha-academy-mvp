@extends('layouts.app')

@section('title', 'Issue Leaving Certificate')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center mb-3">
        <a href="{{ route('lc.index') }}" class="btn btn-sm btn-outline-secondary me-2">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="mb-0">Issue Leaving Certificate</h4>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- Pass route URL safely into JS via a data attribute (avoids Blade-in-JS-template-literal issues) --}}
    <form method="POST" action="{{ route('lc.store') }}" id="lcForm">
        @csrf
        <div class="row g-3">

            {{-- ── LEFT COLUMN ─────────────────────────────────────────────── --}}
            <div class="col-md-8">

                {{-- Student Selection --}}
                <div class="card mb-3">
                    <div class="card-header bg-light fw-semibold">Select Student (Confirmed Admissions only)</div>
                    <div class="card-body">
                        <select name="admission_id" id="admissionSelect"
                                data-info-url="{{ route('lc.student-info') }}"
                                class="form-select @error('admission_id') is-invalid @enderror" required>
                            <option value="">— choose student —</option>
                            @foreach($admissions as $a)
                                <option value="{{ $a->id }}"
                                    {{ old('admission_id', ($admission ? $admission->id : '')) == $a->id ? 'selected' : '' }}>
                                    {{ $a->student_name }} ({{ $a->schoolClass->class_name ?? '' }}{{ $a->section ? ' - ' . $a->section->section_name : '' }})
                                    {{ $a->dga_admission_no ? '| ' . $a->dga_admission_no : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('admission_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Warnings --}}
                <div id="feeWarningBox" class="alert alert-warning d-none mb-3">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    <strong>Fees Outstanding</strong> — this student has
                    <strong id="feeDueAmount"></strong> due. LC can still be issued.
                </div>
                <div id="existingLcWarning" class="alert alert-danger d-none mb-3">
                    <i class="bi bi-x-circle-fill me-1"></i>
                    <strong>LC Already Issued</strong> — LC number
                    <strong id="existingLcNo"></strong> already exists for this student.
                </div>

                {{-- Student Details --}}
                <div class="card mb-3">
                    <div class="card-header bg-light fw-semibold">Student Details</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Name of Pupil</label>
                                <input type="text" name="pupil_name" id="fPupilName" class="form-control"
                                       value="{{ old('pupil_name', ($admission ? $admission->student_name : '')) }}"
                                       placeholder="Full name as on certificate">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Name of Mother</label>
                                <input type="text" name="mother_name" id="fMotherName" class="form-control"
                                       value="{{ old('mother_name', ($admission ? $admission->mother_name : '')) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nationality</label>
                                <input type="text" name="nationality" id="fNationality" class="form-control"
                                       value="{{ old('nationality', 'Indian') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Race and Caste</label>
                                <input type="text" name="race_and_caste" id="fCaste" class="form-control"
                                       value="{{ old('race_and_caste', ($admission ? $admission->caste : '')) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Place of Birth</label>
                                <input type="text" name="place_of_birth" id="fPlaceOfBirth" class="form-control"
                                       value="{{ old('place_of_birth', ($admission ? $admission->place_of_birth : '')) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="date_of_birth" id="fDob" class="form-control"
                                       value="{{ old('date_of_birth', ($admission && $admission->date_of_birth ? $admission->date_of_birth->format('Y-m-d') : '')) }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Academic Details --}}
                <div class="card mb-3">
                    <div class="card-header bg-light fw-semibold">Academic Details</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Last School Attended</label>
                                <input type="text" name="last_school_attended" class="form-control"
                                       value="{{ old('last_school_attended', 'Deep Griha Academy') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date of Admission</label>
                                <input type="date" name="date_of_admission" id="fAdmDate" class="form-control"
                                       value="{{ old('date_of_admission', ($admission && $admission->confirmed_date ? $admission->confirmed_date->format('Y-m-d') : '')) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Progress</label>
                                <input type="text" name="progress" class="form-control"
                                       value="{{ old('progress') }}"
                                       placeholder="e.g. Satisfactory, Good">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Conduct <span class="text-danger">*</span></label>
                                <select name="conduct" class="form-select" required>
                                    @foreach(['Good','Very Good','Excellent','Satisfactory','Fair'] as $c)
                                        <option value="{{ $c }}" {{ old('conduct','Good') == $c ? 'selected' : '' }}>{{ $c }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date of Leaving <span class="text-danger">*</span></label>
                                <input type="date" name="date_of_leaving"
                                       class="form-control @error('date_of_leaving') is-invalid @enderror"
                                       value="{{ old('date_of_leaving', date('Y-m-d')) }}" required>
                                @error('date_of_leaving')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Standard in Which Studying</label>
                                <input type="text" name="standard_studying" id="fStandard" class="form-control"
                                       value="{{ old('standard_studying') }}"
                                       placeholder="e.g. Class 5 - A">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">And Since When</label>
                                <input type="date" name="studying_since" class="form-control"
                                       value="{{ old('studying_since') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Reason for Leaving School</label>
                                <input type="text" name="reason_for_leaving" class="form-control"
                                       value="{{ old('reason_for_leaving') }}"
                                       placeholder="e.g. Family relocation, TC, etc.">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Remark</label>
                                <textarea name="remarks" class="form-control" rows="2">{{ old('remarks') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Issue Details --}}
                <div class="card mb-3">
                    <div class="card-header bg-light fw-semibold">Issue Details</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">LC Number</label>
                                <input type="text" class="form-control bg-light" value="{{ $nextLcNumber }}" readonly disabled>
                                <div class="form-text">Auto-assigned</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Issue Date <span class="text-danger">*</span></label>
                                <input type="date" name="issue_date"
                                       class="form-control @error('issue_date') is-invalid @enderror"
                                       value="{{ old('issue_date', date('Y-m-d')) }}" required>
                                @error('issue_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Place</label>
                                <input type="text" name="issue_place" class="form-control"
                                       value="{{ old('issue_place', 'Pune') }}">
                            </div>
                        </div>
                    </div>
                </div>

            </div>{{-- /left --}}

            {{-- ── RIGHT COLUMN ────────────────────────────────────────────── --}}
            <div class="col-md-4">
                <div class="card position-sticky" style="top: 80px;">
                    <div class="card-header bg-primary text-white fw-semibold">Summary</div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0 small">
                            <tr><td class="text-muted">LC No.</td><td><strong>{{ $nextLcNumber }}</strong></td></tr>
                            <tr><td class="text-muted">Student</td><td id="previewName">—</td></tr>
                            <tr><td class="text-muted">Admission No.</td><td id="previewAdmNo">—</td></tr>
                            <tr><td class="text-muted">Class</td><td id="previewClass">—</td></tr>
                        </table>
                        <hr>
                        <p class="text-muted small mb-0">LC number is permanent once issued.</p>
                    </div>
                    <div class="card-footer">
                        <button type="submit" id="submitBtn" class="btn btn-primary w-100">
                            <i class="bi bi-check2-circle me-1"></i> Issue LC
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var select       = document.getElementById('admissionSelect');
    var feeWarn      = document.getElementById('feeWarningBox');
    var feeDue       = document.getElementById('feeDueAmount');
    var lcWarn       = document.getElementById('existingLcWarning');
    var lcNo         = document.getElementById('existingLcNo');
    var submitBtn    = document.getElementById('submitBtn');
    var previewName  = document.getElementById('previewName');
    var previewAdmNo = document.getElementById('previewAdmNo');
    var previewClass = document.getElementById('previewClass');

    // Route URL stored in data attribute — avoids Blade rendering inside JS template literals
    var infoUrl = select.getAttribute('data-info-url');

    var fields = {
        pupil_name:  document.getElementById('fPupilName'),
        mother_name: document.getElementById('fMotherName'),
        nationality: document.getElementById('fNationality'),
        caste:       document.getElementById('fCaste'),
        place_birth: document.getElementById('fPlaceOfBirth'),
        dob:         document.getElementById('fDob'),
        adm_date:    document.getElementById('fAdmDate'),
        standard:    document.getElementById('fStandard'),
    };

    function resetPreview() {
        feeWarn.classList.add('d-none');
        lcWarn.classList.add('d-none');
        submitBtn.disabled = false;
        previewName.textContent  = '—';
        previewAdmNo.textContent = '—';
        previewClass.textContent = '—';
    }

    function loadStudentInfo(aid) {
        if (!aid) {
            resetPreview();
            return;
        }

        fetch(infoUrl + '?admission_id=' + aid)
            .then(function (r) { return r.json(); })
            .then(function (d) {
                var a = d.admission;

                // Populate form fields
                if (fields.pupil_name  && a.student_name)   fields.pupil_name.value  = a.student_name;
                if (fields.mother_name && a.mother_name)     fields.mother_name.value = a.mother_name;
                if (fields.nationality && a.nationality)     fields.nationality.value = a.nationality;
                if (fields.caste       && a.caste)           fields.caste.value       = a.caste;
                if (fields.place_birth && a.place_of_birth)  fields.place_birth.value = a.place_of_birth;
                if (fields.dob         && a.date_of_birth)   fields.dob.value         = a.date_of_birth;
                if (fields.adm_date    && a.confirmed_date)  fields.adm_date.value    = a.confirmed_date;
                if (fields.standard    && a.class_label)     fields.standard.value    = a.class_label;

                // Update preview panel
                previewName.textContent  = a.student_name || '—';
                previewAdmNo.textContent = a.general_id || a.dga_admission_no || '—';
                previewClass.textContent = a.class_label || '—';

                // Fee warning
                if (d.fee_check && d.fee_check.has_due) {
                    feeDue.textContent = '₹' + parseFloat(d.fee_check.amount).toFixed(2);
                    feeWarn.classList.remove('d-none');
                } else {
                    feeWarn.classList.add('d-none');
                }

                // Duplicate LC warning
                if (d.has_lc) {
                    lcNo.textContent = d.lc_number;
                    lcWarn.classList.remove('d-none');
                    submitBtn.disabled = true;
                } else {
                    lcWarn.classList.add('d-none');
                    submitBtn.disabled = false;
                }
            })
            .catch(function (e) { console.error('Admission info error:', e); });
    }

    // Attach listener FIRST
    select.addEventListener('change', function () {
        loadStudentInfo(this.value);
    });

    // Auto-trigger AFTER listener is attached (handles pre-selected admission_id from admission show page)
    if (select.value) {
        loadStudentInfo(select.value);
    }
});
</script>
@endpush
