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
@endsection
