@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-person-plus"></i> New Admission Inquiry
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{route('admissions.index')}}">Admissions</a></li>
                            <li class="breadcrumb-item active">New Inquiry</li>
                        </ol>
                    </nav>
                    @include('session-messages')

                    <form method="POST" action="{{ route('admissions.store') }}">
                        @csrf

                        {{-- Hidden fields --}}
                        <input type="hidden" name="academic_year" value="{{ $academic_year }}">
                        <input type="hidden" name="session_id" value="{{ $current_session_id }}">

                        {{-- SECTION 1: Student Info --}}
                        <div class="bg-white border shadow-sm p-4 mb-4">
                            <h5 class="mb-3 border-bottom pb-2"><i class="bi bi-person"></i> Student Information</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name of Student <span class="text-danger">*</span></label>
                                    <input type="text" name="student_name" class="form-control @error('student_name') is-invalid @enderror" value="{{ old('student_name') }}" required>
                                    @error('student_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Gender</label>
                                    <select name="gender" class="form-select">
                                        <option value="">Select</option>
                                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Caste</label>
                                    <input type="text" name="caste" class="form-control" value="{{ old('caste') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Religion</label>
                                    <input type="text" name="religion" class="form-control" value="{{ old('religion') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Nationality</label>
                                    <input type="text" name="nationality" class="form-control" value="{{ old('nationality', 'Indian') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Place of Birth</label>
                                    <input type="text" name="place_of_birth" class="form-control" value="{{ old('place_of_birth') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Language Spoken at Home</label>
                                    <input type="text" name="language_spoken_at_home" class="form-control" value="{{ old('language_spoken_at_home') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Previous School Last Attended</label>
                                    <input type="text" name="previous_school" class="form-control" value="{{ old('previous_school') }}">
                                </div>
                            </div>
                        </div>

                        {{-- SECTION 2: Class --}}
                        <div class="bg-white border shadow-sm p-4 mb-4">
                            <h5 class="mb-3 border-bottom pb-2"><i class="bi bi-diagram-3"></i> Class Information</h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Class Applying For <span class="text-danger">*</span></label>
                                    <select name="class_id" class="form-select @error('class_id') is-invalid @enderror" required>
                                        <option value="">Select Class</option>
                                        @foreach($school_classes as $class)
                                            <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>{{ $class->class_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('class_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Academic Year</label>
                                    <input type="text" class="form-control" value="{{ $academic_year }}" readonly>
                                </div>
                            </div>
                        </div>

                        {{-- SECTION 3: Family Info --}}
                        <div class="bg-white border shadow-sm p-4 mb-4">
                            <h5 class="mb-3 border-bottom pb-2"><i class="bi bi-people"></i> Family Information</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Father's Name</label>
                                    <input type="text" name="father_name" class="form-control" value="{{ old('father_name') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Father's Occupation</label>
                                    <input type="text" name="father_occupation" class="form-control" value="{{ old('father_occupation') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Mother's Name</label>
                                    <input type="text" name="mother_name" class="form-control" value="{{ old('mother_name') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Mother's Occupation</label>
                                    <input type="text" name="mother_occupation" class="form-control" value="{{ old('mother_occupation') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Sibling Name &amp; Age</label>
                                    <input type="text" name="sibling_name_age" class="form-control" value="{{ old('sibling_name_age') }}" placeholder="e.g. Raj, 8 years">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Guardian Name (if different)</label>
                                    <input type="text" name="guardian_name" class="form-control" value="{{ old('guardian_name') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Guardian Occupation</label>
                                    <input type="text" name="guardian_occupation" class="form-control" value="{{ old('guardian_occupation') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Guardian Address</label>
                                    <input type="text" name="guardian_address" class="form-control" value="{{ old('guardian_address') }}">
                                </div>
                            </div>
                        </div>

                        {{-- SECTION 4: Contact & Address --}}
                        <div class="bg-white border shadow-sm p-4 mb-4">
                            <h5 class="mb-3 border-bottom pb-2"><i class="bi bi-geo-alt"></i> Address &amp; Contact</h5>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label">Full Address</label>
                                    <textarea name="full_address" class="form-control" rows="2">{{ old('full_address') }}</textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Village</label>
                                    <input type="text" name="village" class="form-control" value="{{ old('village') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Distance from School</label>
                                    <input type="text" name="distance_from_school" class="form-control" value="{{ old('distance_from_school') }}" placeholder="e.g. 1.5 km">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Mobile <span class="text-danger">*</span></label>
                                    <input type="text" name="contact_mobile" class="form-control @error('contact_mobile') is-invalid @enderror" value="{{ old('contact_mobile') }}" required>
                                    @error('contact_mobile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Residence Phone</label>
                                    <input type="text" name="contact_residence" class="form-control" value="{{ old('contact_residence') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Emergency Contact</label>
                                    <input type="text" name="contact_emergency" class="form-control" value="{{ old('contact_emergency') }}">
                                </div>
                            </div>
                        </div>

                        {{-- SECTION 5: Transport & Medical --}}
                        <div class="bg-white border shadow-sm p-4 mb-4">
                            <h5 class="mb-3 border-bottom pb-2"><i class="bi bi-heart-pulse"></i> Transport &amp; Medical</h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Transport Required?</label>
                                    <select name="transport_required" class="form-select">
                                        <option value="0" {{ old('transport_required') == '0' ? 'selected' : '' }}>No</option>
                                        <option value="1" {{ old('transport_required') == '1' ? 'selected' : '' }}>Yes</option>
                                    </select>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Allergies / Medical Conditions</label>
                                    <textarea name="allergies_medical" class="form-control" rows="2">{{ old('allergies_medical') }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Doctor's Name &amp; Phone</label>
                                    <input type="text" name="doctor_name_phone" class="form-control" value="{{ old('doctor_name_phone') }}">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mb-5">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-check-circle"></i> Save Inquiry
                            </button>
                            <a href="{{ route('admissions.index') }}" class="btn btn-outline-secondary btn-lg">Cancel</a>
                        </div>

                    </form>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
