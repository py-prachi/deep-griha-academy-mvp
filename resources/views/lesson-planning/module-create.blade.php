@extends('layouts.app')
@section('title', 'Add Module')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4 pe-4">

                    <nav aria-label="breadcrumb" class="mb-2">
                        <ol class="breadcrumb small">
                            <li class="breadcrumb-item"><a href="{{ route('lesson-planning.index') }}">Lesson Planning</a></li>
                            <li class="breadcrumb-item active">Add Module</li>
                        </ol>
                    </nav>

                    <h4 class="mb-1"><i class="bi bi-layers me-1"></i> Add Module</h4>
                    <p class="text-muted small mb-3">
                        {{ $assignment->schoolClass->class_name }}
                        {{ $assignment->section->section_name }} &mdash;
                        {{ $assignment->subject->name }}
                    </p>

                    @include('session-messages')

                    <div class="card shadow-sm" style="max-width:800px;">
                        <div class="card-body">
                            <form method="POST" action="{{ route('lesson-planning.modules.store') }}" id="module-form">
                                @csrf
                                <input type="hidden" name="class_id"   value="{{ $assignment->class_id }}">
                                <input type="hidden" name="section_id" value="{{ $assignment->section_id }}">
                                <input type="hidden" name="subject_id" value="{{ $assignment->subject_id }}">

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Date Written</label>
                                    <input type="date" name="date_written" class="form-control @error('date_written') is-invalid @enderror"
                                           value="{{ old('date_written') }}">
                                    @error('date_written')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Topic/Topics, Chapter/Chapters <span class="text-danger">*</span></label>
                                    <textarea name="topic" rows="6" style="min-height:150px;" class="form-control @error('topic') is-invalid @enderror"
                                              required>{{ old('topic') }}</textarea>
                                    @error('topic')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Learning Outcome</label>
                                    <textarea name="learning_outcome" rows="6" style="min-height:150px;" class="form-control @error('learning_outcome') is-invalid @enderror">{{ old('learning_outcome') }}</textarea>
                                    @error('learning_outcome')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Assessment</label>
                                    <textarea name="assessment" rows="6" style="min-height:140px;" class="form-control @error('assessment') is-invalid @enderror">{{ old('assessment') }}</textarea>
                                    @error('assessment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Rubric</label>
                                    <textarea name="rubric" rows="6" style="min-height:140px;" class="form-control @error('rubric') is-invalid @enderror">{{ old('rubric') }}</textarea>
                                    @error('rubric')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Objectives</label>
                                    <textarea name="objectives" rows="6" style="min-height:140px;" class="form-control @error('objectives') is-invalid @enderror">{{ old('objectives') }}</textarea>
                                    @error('objectives')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Duration and Flow of Days</label>
                                    <textarea name="duration_and_flow" rows="7" style="min-height:170px;" class="form-control @error('duration_and_flow') is-invalid @enderror"
                                              placeholder="e.g. Day 1 – Intro video on super senses&#10;Day 2 – Discussion and textbook&#10;Day 3 – Q&A and writing">{{ old('duration_and_flow') }}</textarea>
                                    @error('duration_and_flow')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Materials</label>
                                    <textarea name="materials" rows="6" style="min-height:140px;" class="form-control @error('materials') is-invalid @enderror">{{ old('materials') }}</textarea>
                                    @error('materials')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="d-flex gap-2 mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-1"></i> Save Module
                                    </button>
                                    <a href="{{ route('lesson-planning.index') }}" class="btn btn-outline-secondary">Cancel</a>
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
@include('lesson-planning._draft-autosave', [
    'formId' => 'module-form',
    'draftKey' => 'dga_draft_module_create_' . $assignment->class_id . '_' . $assignment->section_id . '_' . $assignment->subject_id,
])
@endsection
