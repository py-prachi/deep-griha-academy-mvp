@extends('layouts.app')
@section('title', 'Edit Module')
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
                            <li class="breadcrumb-item active">Edit Module</li>
                        </ol>
                    </nav>

                    <h4 class="mb-1"><i class="bi bi-layers me-1"></i> Edit Module</h4>
                    <p class="text-muted small mb-3">
                        {{ $module->schoolClass->class_name }}
                        {{ $module->section->section_name }} &mdash;
                        {{ $module->subject->name }}
                    </p>

                    @include('session-messages')

                    <div class="card shadow-sm" style="max-width:800px;">
                        <div class="card-body">
                            <form method="POST" action="{{ route('lesson-planning.modules.update', $module->id) }}">
                                @csrf @method('PUT')

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Date Written</label>
                                    <input type="date" name="date_written" class="form-control @error('date_written') is-invalid @enderror"
                                           value="{{ old('date_written', $module->date_written ? $module->date_written->format('Y-m-d') : '') }}">
                                    @error('date_written')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Topic/Topics, Chapter/Chapters <span class="text-danger">*</span></label>
                                    <textarea name="topic" rows="3" class="form-control @error('topic') is-invalid @enderror"
                                              required>{{ old('topic', $module->topic) }}</textarea>
                                    @error('topic')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Learning Outcome</label>
                                    <textarea name="learning_outcome" rows="3" class="form-control @error('learning_outcome') is-invalid @enderror">{{ old('learning_outcome', $module->learning_outcome) }}</textarea>
                                    @error('learning_outcome')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Assessment</label>
                                    <textarea name="assessment" rows="2" class="form-control @error('assessment') is-invalid @enderror">{{ old('assessment', $module->assessment) }}</textarea>
                                    @error('assessment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Rubric</label>
                                    <textarea name="rubric" rows="2" class="form-control @error('rubric') is-invalid @enderror">{{ old('rubric', $module->rubric) }}</textarea>
                                    @error('rubric')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Objectives</label>
                                    <textarea name="objectives" rows="2" class="form-control @error('objectives') is-invalid @enderror">{{ old('objectives', $module->objectives) }}</textarea>
                                    @error('objectives')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Duration and Flow of Days</label>
                                    <textarea name="duration_and_flow" rows="4" class="form-control @error('duration_and_flow') is-invalid @enderror"
                                              placeholder="e.g. Day 1 – Intro video&#10;Day 2 – Discussion&#10;Day 3 – Q&A">{{ old('duration_and_flow', $module->duration_and_flow) }}</textarea>
                                    @error('duration_and_flow')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Materials</label>
                                    <textarea name="materials" rows="2" class="form-control @error('materials') is-invalid @enderror">{{ old('materials', $module->materials) }}</textarea>
                                    @error('materials')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="d-flex gap-2 mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-1"></i> Update Module
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
@endsection
