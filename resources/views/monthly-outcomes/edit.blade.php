@extends('layouts.app')
@section('title', 'Edit Learning Outcomes Entry')
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
                        <h4 class="mb-0">Edit Learning Outcomes Entry</h4>
                    </div>

                    <p class="text-muted small mb-3">
                        <strong>{{ $outcome->schoolClass->class_name }} {{ $outcome->section->section_name }}</strong>
                        &nbsp;·&nbsp; {{ $outcome->subject->name }}
                    </p>

                    @include('session-messages')

                    <div class="card" style="max-width:720px;">
                        <div class="card-body">
                            <form method="POST" action="{{ route('monthly-outcomes.update', $outcome->id) }}">
                                @csrf @method('PUT')

                                <div class="row g-3 mb-3">
                                    <div class="col-md-5">
                                        <label class="form-label">Month <span class="text-danger">*</span></label>
                                        <select name="month" class="form-select @error('month') is-invalid @enderror" required>
                                            @foreach(\App\Models\MonthlyOutcome::MONTHS as $val => $label)
                                                <option value="{{ $val }}" {{ old('month', $outcome->month) == $val ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error('month')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Number of Days <span class="text-danger">*</span></label>
                                        <input type="number" name="number_of_days" class="form-control @error('number_of_days') is-invalid @enderror"
                                               value="{{ old('number_of_days', $outcome->number_of_days) }}" min="1" max="31" required>
                                        @error('number_of_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Chapter No.</label>
                                        <input type="text" name="chapter_number" class="form-control @error('chapter_number') is-invalid @enderror"
                                               value="{{ old('chapter_number', $outcome->chapter_number) }}">
                                        @error('chapter_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-9">
                                        <label class="form-label">Chapter Name <span class="text-danger">*</span></label>
                                        <input type="text" name="chapter_name" class="form-control @error('chapter_name') is-invalid @enderror"
                                               value="{{ old('chapter_name', $outcome->chapter_name) }}" required>
                                        @error('chapter_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Learning Outcomes</label>
                                    <textarea name="learning_outcomes" class="form-control @error('learning_outcomes') is-invalid @enderror"
                                              rows="3">{{ old('learning_outcomes', $outcome->learning_outcomes) }}</textarea>
                                    @error('learning_outcomes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <label class="form-label">Start Date <span class="text-muted small">(tentative)</span></label>
                                        <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror"
                                               value="{{ old('start_date', optional($outcome->start_date)->format('Y-m-d')) }}">
                                        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">End Date <span class="text-muted small">(tentative)</span></label>
                                        <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror"
                                               value="{{ old('end_date', optional($outcome->end_date)->format('Y-m-d')) }}">
                                        @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i> Update</button>
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
