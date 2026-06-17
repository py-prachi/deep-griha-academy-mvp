@extends('layouts.app')
@section('title', 'Add Lesson Plan Entry')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">

                    <div class="d-flex align-items-center mb-3">
                        <a href="{{ route('lesson-plans.index') }}" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <h4 class="mb-0">Add Lesson Plan Entry</h4>
                    </div>

                    <p class="text-muted small mb-3">
                        <strong>{{ $assignment->schoolClass->class_name }} {{ $assignment->section->section_name }}</strong>
                        &nbsp;·&nbsp; {{ $assignment->subject->name }}
                    </p>

                    @include('session-messages')

                    <div class="card" style="max-width:680px;">
                        <div class="card-body">
                            <form method="POST" action="{{ route('lesson-plans.store') }}">
                                @csrf
                                <input type="hidden" name="class_id"   value="{{ $assignment->class_id }}">
                                <input type="hidden" name="section_id" value="{{ $assignment->section_id }}">
                                <input type="hidden" name="subject_id" value="{{ $assignment->subject_id }}">

                                <div class="row g-3 mb-3">
                                    <div class="col-md-5">
                                        <label class="form-label">Month <span class="text-danger">*</span></label>
                                        <select name="month" class="form-select @error('month') is-invalid @enderror" required>
                                            <option value="" disabled selected>Select month</option>
                                            @foreach(\App\Models\LessonPlan::MONTHS as $val => $label)
                                                <option value="{{ $val }}" {{ old('month') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error('month')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Days Allocated <span class="text-danger">*</span></label>
                                        <input type="number" name="days_allocated" class="form-control @error('days_allocated') is-invalid @enderror"
                                               value="{{ old('days_allocated') }}" min="1" max="31" required>
                                        <div class="form-text">Days allocated for this chapter</div>
                                        @error('days_allocated')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                                    <label class="form-label">Learning Standards / Outcome</label>
                                    <textarea name="learning_standards" class="form-control @error('learning_standards') is-invalid @enderror"
                                              rows="3" placeholder="Can be in English, Hindi or Marathi">{{ old('learning_standards') }}</textarea>
                                    @error('learning_standards')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                        @foreach(\App\Models\LessonPlan::STATUS_LABELS as $val => $label)
                                            <option value="{{ $val }}" {{ old('status', 'planned') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i> Save Entry</button>
                                    <a href="{{ route('lesson-plans.index') }}" class="btn btn-outline-secondary">Cancel</a>
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
