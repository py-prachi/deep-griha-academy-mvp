@extends('layouts.app')
@section('title', 'Edit Learning Standard Entry')
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
                        <h4 class="mb-0">Edit Learning Standard Entry</h4>
                    </div>

                    <p class="text-muted small mb-3">
                        <strong>{{ $plan->schoolClass->class_name }} {{ $plan->section->section_name }}</strong>
                        &nbsp;·&nbsp; {{ $plan->subject->name }}
                    </p>

                    @include('session-messages')

                    <div class="card" style="max-width:680px;">
                        <div class="card-body">
                            <form method="POST" action="{{ route('lesson-plans.update', $plan->id) }}">
                                @csrf @method('PUT')

                                <div class="row g-3 mb-3">
                                    <div class="col-md-5">
                                        <label class="form-label">Month <span class="text-danger">*</span></label>
                                        <select name="month" class="form-select @error('month') is-invalid @enderror" required>
                                            @foreach(\App\Models\LessonPlan::MONTHS as $val => $label)
                                                <option value="{{ $val }}" {{ old('month', $plan->month) == $val ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error('month')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Days Allocated <span class="text-danger">*</span></label>
                                        <input type="number" name="days_allocated" class="form-control @error('days_allocated') is-invalid @enderror"
                                               value="{{ old('days_allocated', $plan->days_allocated) }}" min="1" max="31" required>
                                        @error('days_allocated')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Chapter No.</label>
                                        <input type="text" name="chapter_number" class="form-control @error('chapter_number') is-invalid @enderror"
                                               value="{{ old('chapter_number', $plan->chapter_number) }}">
                                        @error('chapter_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-9">
                                        <label class="form-label">Chapter Name <span class="text-danger">*</span></label>
                                        <input type="text" name="chapter_name" class="form-control @error('chapter_name') is-invalid @enderror"
                                               value="{{ old('chapter_name', $plan->chapter_name) }}" required>
                                        @error('chapter_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Learning Standards / Outcome</label>
                                    <textarea name="learning_standards" class="form-control @error('learning_standards') is-invalid @enderror"
                                              rows="3">{{ old('learning_standards', $plan->learning_standards) }}</textarea>
                                    @error('learning_standards')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                        @foreach(\App\Models\LessonPlan::STATUS_LABELS as $val => $label)
                                            <option value="{{ $val }}" {{ old('status', $plan->status) == $val ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i> Update</button>
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
