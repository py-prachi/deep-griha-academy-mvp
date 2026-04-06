@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <div class="d-flex align-items-center mb-3">
                        <a href="{{ route('teacher.list.show') }}" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <h4 class="mb-0"><i class="bi bi-person-plus me-1"></i> Add Teacher</h4>
                    </div>

                    @include('session-messages')

                    <div class="alert alert-info py-2 small">
                        <i class="bi bi-info-circle me-1"></i>
                        Login credentials (email and password) are auto-generated when the teacher is saved.
                        They will be shown on this page after saving — share them with the teacher.
                    </div>

                    <div class="card" style="max-width:680px;">
                        <div class="card-body">
                            <form class="row g-3" action="{{ route('school.teacher.create') }}" method="POST">
                                @csrf

                                {{-- Name --}}
                                <div class="col-md-6">
                                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" name="first_name" required value="{{ old('first_name') }}" autocomplete="off">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" name="last_name" required value="{{ old('last_name') }}" autocomplete="off">
                                </div>

                                {{-- Gender + Phone --}}
                                <div class="col-md-4">
                                    <label class="form-label">Gender <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm" name="gender" required>
                                        <option value="" disabled selected>Select</option>
                                        <option value="Male"   {{ old('gender') == 'Male'   ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Phone <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" name="phone" required value="{{ old('phone') }}" autocomplete="off">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Nationality</label>
                                    <input type="text" class="form-control form-control-sm" name="nationality" value="{{ old('nationality', 'Indian') }}" autocomplete="off">
                                </div>

                                {{-- Address (optional) --}}
                                <div class="col-md-8">
                                    <label class="form-label">Address</label>
                                    <input type="text" class="form-control form-control-sm" name="address" value="{{ old('address') }}" autocomplete="off">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">City</label>
                                    <input type="text" class="form-control form-control-sm" name="city" value="{{ old('city') }}" autocomplete="off">
                                </div>

                                {{-- Photo --}}
                                <div class="col-md-6">
                                    <label class="form-label">Photo</label>
                                    <input class="form-control form-control-sm" type="file" onchange="previewFile()">
                                    <div id="previewPhoto"></div>
                                    <input type="hidden" id="photoHiddenInput" name="photo" value="">
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="bi bi-person-plus me-1"></i> Add Teacher
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

@include('components.photos.photo-input')
@endsection
