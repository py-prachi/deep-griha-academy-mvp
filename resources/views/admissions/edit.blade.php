@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-pen"></i> Edit Admission — {{ $admission->student_name }}
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{route('admissions.index')}}">Admissions</a></li>
                            <li class="breadcrumb-item"><a href="{{route('admissions.show', $admission->id)}}">{{ $admission->student_name }}</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </nav>
                    @include('session-messages')

                    <form method="POST" action="{{ route('admissions.update', $admission->id) }}">
                        @csrf
                        @method('PUT')

                        {{-- Student Info --}}
                        <div class="bg-white border shadow-sm p-4 mb-4">
                            <h5 class="mb-3 border-bottom pb-2"><i class="bi bi-person"></i> Student Information</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="student_name" class="form-control" value="{{ old('student_name', $admission->student_name) }}" required>
                                </div>
                                @php
                                    $isPrePrimary = in_array(optional($admission->schoolClass)->class_name, ['Nursery', 'Lower KG', 'Upper KG']);
                                @endphp
                                @if($admission->dga_admission_no)
                                <div class="col-md-3">
                                    <label class="form-label">DGA Admission No.</label>
                                    <input type="text" class="form-control" value="{{ $admission->dga_admission_no }}" disabled>
                                    <small class="text-muted">Auto-generated, not editable</small>
                                </div>
                                @endif
                                @if(!$isPrePrimary)
                                <div class="col-md-3">
                                    <label class="form-label">General ID <span class="text-muted small">(ZP / SARAL)</span></label>
                                    <input type="text" name="general_id" class="form-control {{ $admission->general_id ? '' : 'border-warning' }}"
                                        value="{{ old('general_id', $admission->general_id) }}"
                                        placeholder="11-digit ZP ID" maxlength="11" pattern="\d{11}" inputmode="numeric">
                                    @if(!$admission->general_id)
                                        <small class="text-warning"><i class="bi bi-exclamation-triangle me-1"></i>Not set yet</small>
                                    @endif
                                </div>
                                @endif
                                <div class="col-md-3">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', optional($admission->date_of_birth)->format('Y-m-d')) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Gender</label>
                                    <select name="gender" class="form-select">
                                        <option value="male"   {{ old('gender', $admission->gender) == 'male'   ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender', $admission->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Caste</label>
                                    <input type="text" name="caste" class="form-control" value="{{ old('caste', $admission->caste) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Religion</label>
                                    <input type="text" name="religion" class="form-control" value="{{ old('religion', $admission->religion) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Nationality</label>
                                    <input type="text" name="nationality" class="form-control" value="{{ old('nationality', $admission->nationality) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Place of Birth</label>
                                    <input type="text" name="place_of_birth" class="form-control" value="{{ old('place_of_birth', $admission->place_of_birth) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Language at Home</label>
                                    <input type="text" name="language_spoken_at_home" class="form-control" value="{{ old('language_spoken_at_home', $admission->language_spoken_at_home) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Previous School</label>
                                    <input type="text" name="previous_school" class="form-control" value="{{ old('previous_school', $admission->previous_school) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Aadhaar No.</label>
                                    <input type="text" name="aadhaar_no" class="form-control" maxlength="12" pattern="\d{12}" placeholder="12-digit number" value="{{ old('aadhaar_no', $admission->aadhaar_no) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">PEN ID</label>
                                    <input type="text" name="pen_id" class="form-control" placeholder="Permanent Education Number" value="{{ old('pen_id', $admission->pen_id) }}">
                                </div>
                            </div>
                        </div>

                        {{-- Family --}}
                        <div class="bg-white border shadow-sm p-4 mb-4">
                            <h5 class="mb-3 border-bottom pb-2"><i class="bi bi-people"></i> Family Information</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Father's Name</label>
                                    <input type="text" name="father_name" class="form-control" value="{{ old('father_name', $admission->father_name) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Father's Occupation</label>
                                    <input type="text" name="father_occupation" class="form-control" value="{{ old('father_occupation', $admission->father_occupation) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Mother's Name</label>
                                    <input type="text" name="mother_name" class="form-control" value="{{ old('mother_name', $admission->mother_name) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Mother's Occupation</label>
                                    <input type="text" name="mother_occupation" class="form-control" value="{{ old('mother_occupation', $admission->mother_occupation) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Sibling Name &amp; Age</label>
                                    <input type="text" name="sibling_name_age" class="form-control" value="{{ old('sibling_name_age', $admission->sibling_name_age) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Guardian Name</label>
                                    <input type="text" name="guardian_name" class="form-control" value="{{ old('guardian_name', $admission->guardian_name) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Guardian Occupation</label>
                                    <input type="text" name="guardian_occupation" class="form-control" value="{{ old('guardian_occupation', $admission->guardian_occupation) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Guardian Address</label>
                                    <input type="text" name="guardian_address" class="form-control" value="{{ old('guardian_address', $admission->guardian_address) }}">
                                </div>
                            </div>
                        </div>

                        {{-- Contact --}}
                        <div class="bg-white border shadow-sm p-4 mb-4">
                            <h5 class="mb-3 border-bottom pb-2"><i class="bi bi-geo-alt"></i> Address &amp; Contact</h5>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label">Full Address</label>
                                    <textarea name="full_address" class="form-control" rows="2">{{ old('full_address', $admission->full_address) }}</textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Village</label>
                                    <input type="text" name="village" class="form-control" value="{{ old('village', $admission->village) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Distance from School</label>
                                    <input type="text" name="distance_from_school" class="form-control" value="{{ old('distance_from_school', $admission->distance_from_school) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Father's Phone <span class="text-danger">*</span></label>
                                    <input type="text" name="father_phone" class="form-control" value="{{ old('father_phone', $admission->father_phone) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Mother's Phone</label>
                                    <input type="text" name="mother_phone" class="form-control" value="{{ old('mother_phone', $admission->mother_phone) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Emergency Contact</label>
                                    <input type="text" name="contact_emergency" class="form-control" value="{{ old('contact_emergency', $admission->contact_emergency) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">City <span class="text-danger">*</span></label>
                                    <input type="text" name="city" class="form-control" value="{{ old('city', $admission->city) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">PIN Code</label>
                                    <input type="text" name="zip" class="form-control" value="{{ old('zip', $admission->zip) }}">
                                </div>
                            </div>
                        </div>

                        {{-- Transport & Medical --}}
                        <div class="bg-white border shadow-sm p-4 mb-4">
                            <h5 class="mb-3 border-bottom pb-2"><i class="bi bi-heart-pulse"></i> Transport &amp; Medical</h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Transport Required?</label>
                                    <select name="transport_required" class="form-select">
                                        <option value="0" {{ old('transport_required', $admission->transport_required) == '0' ? 'selected' : '' }}>No</option>
                                        <option value="1" {{ old('transport_required', $admission->transport_required) == '1' ? 'selected' : '' }}>Yes</option>
                                    </select>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Allergies / Medical Conditions</label>
                                    <textarea name="allergies_medical" class="form-control" rows="2">{{ old('allergies_medical', $admission->allergies_medical) }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Doctor's Name &amp; Phone</label>
                                    <input type="text" name="doctor_name_phone" class="form-control" value="{{ old('doctor_name_phone', $admission->doctor_name_phone) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Blood Group <span class="text-muted small">(optional)</span></label>
                                    <select name="blood_type" class="form-select">
                                        <option value="">-- Not Known --</option>
                                        <option value="A+"  {{ old("blood_type", $admission->blood_type) == "A+"  ? "selected" : "" }}>A+</option>
                                        <option value="A-"  {{ old("blood_type", $admission->blood_type) == "A-"  ? "selected" : "" }}>A-</option>
                                        <option value="B+"  {{ old("blood_type", $admission->blood_type) == "B+"  ? "selected" : "" }}>B+</option>
                                        <option value="B-"  {{ old("blood_type", $admission->blood_type) == "B-"  ? "selected" : "" }}>B-</option>
                                        <option value="O+"  {{ old("blood_type", $admission->blood_type) == "O+"  ? "selected" : "" }}>O+</option>
                                        <option value="O-"  {{ old("blood_type", $admission->blood_type) == "O-"  ? "selected" : "" }}>O-</option>
                                        <option value="AB+" {{ old("blood_type", $admission->blood_type) == "AB+" ? "selected" : "" }}>AB+</option>
                                        <option value="AB-" {{ old("blood_type", $admission->blood_type) == "AB-" ? "selected" : "" }}>AB-</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Class & Section (editable for enquiry/pending only) --}}
                        @if(in_array($admission->status, ['enquiry', 'pending']))
                        <div class="bg-white border shadow-sm p-4 mb-4">
                            <h5 class="mb-3 border-bottom pb-2"><i class="bi bi-building"></i> Class &amp; Section</h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Class <span class="text-danger">*</span></label>
                                    <select name="class_id" id="editClassSelect" class="form-select" required>
                                        <option value="">-- Select Class --</option>
                                        @foreach($school_classes as $sc)
                                            <option value="{{ $sc->id }}" {{ old('class_id', $admission->class_id) == $sc->id ? 'selected' : '' }}>
                                                {{ $sc->class_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Section</label>
                                    <select name="section_id" id="editSectionSelect" class="form-select">
                                        <option value="">-- Select Section --</option>
                                        @foreach($sections as $sec)
                                            <option value="{{ $sec->id }}" {{ old('section_id', $admission->section_id) == $sec->id ? 'selected' : '' }}>
                                                {{ $sec->section_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Fee Category (editable for all statuses) --}}
                        <div class="bg-white border shadow-sm p-4 mb-4">
                            <h5 class="mb-3 border-bottom pb-2"><i class="bi bi-tag"></i> Fee Category</h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Category</label>
                                    <select name="fee_category" id="editFeeCategorySelect" class="form-select">
                                        <option value="">-- Not set --</option>
                                        <option value="general"  {{ old('fee_category', $admission->fee_category) === 'general'  ? 'selected' : '' }}>General</option>
                                        <option value="rte"      {{ old('fee_category', $admission->fee_category) === 'rte'      ? 'selected' : '' }}>RTE (₹0 tuition)</option>
                                        <option value="coc"      {{ old('fee_category', $admission->fee_category) === 'coc'      ? 'selected' : '' }}>CoC (Boys only)</option>
                                        <option value="discount" {{ old('fee_category', $admission->fee_category) === 'discount' ? 'selected' : '' }}>Discount</option>
                                    </select>
                                </div>
                                <div class="col-md-4" id="editDiscountPctField" style="{{ old('fee_category', $admission->fee_category) === 'discount' ? '' : 'display:none;' }}">
                                    <label class="form-label">Discount % <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" name="discount_percentage" id="editDiscountPct" class="form-control"
                                               step="0.01" min="0" max="100"
                                               value="{{ old('discount_percentage', $admission->discount_percentage) }}"
                                               placeholder="e.g. 50">
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <div class="form-text">Applied on General category tuition fee for this student's class.</div>
                                </div>
                                <div class="col-md-4" id="editRteAppNoField" style="{{ old('fee_category', $admission->fee_category) === 'rte' ? '' : 'display:none;' }}">
                                    <label class="form-label">RTE Application No. <span class="text-muted small">(e.g. 26MS011157)</span></label>
                                    <input type="text" name="rte_application_no" id="editRteAppNo" class="form-control text-uppercase"
                                           maxlength="20" pattern="[A-Za-z0-9]+"
                                           value="{{ old('rte_application_no', $admission->rte_application_no) }}"
                                           placeholder="e.g. 26MS011157">
                                    <div class="form-text">Alphanumeric. Stored in uppercase.</div>
                                </div>
                            </div>
                        </div>

                        {{-- Fee Override (admin only, confirmed students) --}}
                        @if($admission->status === 'confirmed')
                        <div class="bg-white border shadow-sm p-4 mb-4">
                            <h5 class="mb-3 border-bottom pb-2"><i class="bi bi-cash-coin"></i> Fee Override <span class="badge bg-warning text-dark fw-normal ms-1" style="font-size:0.75rem;">Admin only</span></h5>
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <label class="form-label">Custom Tuition Fee (₹)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" name="custom_tuition_fee" class="form-control"
                                               step="1" min="0"
                                               value="{{ old('custom_tuition_fee', $admission->custom_tuition_fee) }}"
                                               placeholder="Leave blank to use fee structure">
                                    </div>
                                    <div class="form-text">
                                        @if($admission->custom_tuition_fee)
                                            Currently set to <strong>₹{{ number_format($admission->custom_tuition_fee, 0) }}</strong> (overriding fee structure). Clear to revert to standard fee.
                                        @else
                                            Leave blank to use the standard fee structure for this student's category and class.
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <label class="form-label">Reason for Fee Change</label>
                                    <input type="text" name="fee_note" class="form-control"
                                           value="{{ old('fee_note', $admission->fee_note) }}"
                                           placeholder="e.g. Sibling discount applied — elder sibling Raj in Class 4">
                                    <div class="form-text">This note appears on the student's fee ledger.</div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="d-flex gap-2 mb-5">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-save"></i> Save Changes
                            </button>
                            <a href="{{ route('admissions.show', $admission->id) }}" class="btn btn-outline-secondary btn-lg">Cancel</a>
                        </div>
                    </form>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
<script>
document.getElementById('editFeeCategorySelect').addEventListener('change', function() {
    var discountField = document.getElementById('editDiscountPctField');
    var discountInput = document.getElementById('editDiscountPct');
    var rteField      = document.getElementById('editRteAppNoField');

    if (this.value === 'discount') {
        discountField.style.display = '';
        discountInput.required = true;
        rteField.style.display = 'none';
    } else if (this.value === 'rte') {
        rteField.style.display = '';
        discountField.style.display = 'none';
        discountInput.required = false;
        discountInput.value = '';
    } else {
        discountField.style.display = 'none';
        discountInput.required = false;
        discountInput.value = '';
        rteField.style.display = 'none';
    }
});
</script>
@if(in_array($admission->status, ['enquiry', 'pending']))
<script>
document.getElementById('editClassSelect').addEventListener('change', function() {
    var classId = this.value;
    var sectionSelect = document.getElementById('editSectionSelect');
    sectionSelect.innerHTML = '<option value="">Loading...</option>';
    fetch("{{ route('get.sections.courses.by.classId') }}?class_id=" + classId)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            sectionSelect.innerHTML = '<option value="">-- Select Section --</option>';
            (data.sections || []).forEach(function(s) {
                sectionSelect.innerHTML += '<option value="' + s.id + '">' + s.section_name + '</option>';
            });
        });
});
</script>
@endif
@endsection
