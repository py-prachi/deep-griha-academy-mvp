@extends('layouts.app')
@section('title', 'Add Learning Outcomes Entry')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">

                    <div class="d-flex align-items-center mb-3">
                        <a href="{{ route('monthly-outcomes.index') }}" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <h4 class="mb-0">Add Learning Outcomes Entry</h4>
                    </div>

                    <p class="text-muted small mb-3">
                        <strong>{{ $assignment->schoolClass->class_name }} {{ $assignment->section->section_name }}</strong>
                        &nbsp;·&nbsp; {{ $assignment->subject->name }}
                    </p>

                    @include('session-messages')

                    <div class="card" style="max-width:720px;">
                        <div class="card-body">
                            <form method="POST" action="{{ route('monthly-outcomes.store') }}">
                                @csrf
                                <input type="hidden" name="class_id"   value="{{ $assignment->class_id }}">
                                <input type="hidden" name="section_id" value="{{ $assignment->section_id }}">
                                <input type="hidden" name="subject_id" value="{{ $assignment->subject_id }}">

                                <div class="row g-3 mb-3">
                                    <div class="col-md-5">
                                        <label class="form-label">Month <span class="text-danger">*</span></label>
                                        <select name="month" class="form-select @error('month') is-invalid @enderror" required>
                                            <option value="" disabled selected>Select month</option>
                                            @foreach(\App\Models\MonthlyOutcome::MONTHS as $val => $label)
                                                <option value="{{ $val }}" {{ old('month') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error('month')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Number of Days <span class="text-danger">*</span></label>
                                        <input type="number" name="number_of_days" class="form-control @error('number_of_days') is-invalid @enderror"
                                               value="{{ old('number_of_days') }}" min="1" max="31" required>
                                        <div class="form-text">Teaching days for this chapter</div>
                                        @error('number_of_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Chapter No.</label>
                                        <input type="text" name="chapter_number" class="form-control @error('chapter_number') is-invalid @enderror"
                                               value="{{ old('chapter_number') }}" placeholder="e.g. 3">
                                        @error('chapter_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-9">
                                        <label class="form-label">Chapter Name <span class="text-danger">*</span></label>
                                        <input type="text" name="chapter_name" class="form-control @error('chapter_name') is-invalid @enderror"
                                               value="{{ old('chapter_name') }}" placeholder="Can be in English, Hindi or Marathi" required>
                                        @error('chapter_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Learning Outcomes</label>
                                    <textarea name="learning_outcomes" class="form-control @error('learning_outcomes') is-invalid @enderror"
                                              rows="3" placeholder="Describe the learning outcomes to be covered (can be in English, Hindi or Marathi)">{{ old('learning_outcomes') }}</textarea>
                                    @error('learning_outcomes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <label class="form-label">Start Date <span class="text-muted small">(tentative)</span></label>
                                        <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror"
                                               value="{{ old('start_date') }}">
                                        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">End Date <span class="text-muted small">(tentative)</span></label>
                                        <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror"
                                               value="{{ old('end_date') }}">
                                        @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i> Save Entry</button>
                                    <a href="{{ route('monthly-outcomes.index') }}" class="btn btn-outline-secondary">Cancel</a>
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
@endsection
