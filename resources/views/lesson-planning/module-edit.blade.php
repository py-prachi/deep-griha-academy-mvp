@extends('layouts.app')
@section('title', 'Edit Module')
@push('styles')
<link href="{{ asset('css/vendor/quill/quill.snow.css') }}" rel="stylesheet">
<link href="{{ asset('css/rich-textarea.css') }}" rel="stylesheet">
@endpush
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
                            <form method="POST" action="{{ route('lesson-planning.modules.update', $module->id) }}" id="module-form">
                                @csrf @method('PUT')

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Date Written</label>
                                    <input type="date" name="date_written" class="form-control @error('date_written') is-invalid @enderror"
                                           value="{{ old('date_written', $module->date_written ? $module->date_written->format('Y-m-d') : '') }}">
                                    @error('date_written')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Topic/Topics, Chapter/Chapters <span class="text-danger">*</span></label>
                                    <x-rich-textarea name="topic" :value="old('topic', $module->topic)" :min-height="150" :required="true" />
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Learning Outcome</label>
                                    <x-rich-textarea name="learning_outcome" :value="old('learning_outcome', $module->learning_outcome)" :min-height="150" />
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Assessment</label>
                                    <x-rich-textarea name="assessment" :value="old('assessment', $module->assessment)" :min-height="140" />
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Rubric</label>
                                    <x-rich-textarea name="rubric" :value="old('rubric', $module->rubric)" :min-height="140" />
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Objectives</label>
                                    <x-rich-textarea name="objectives" :value="old('objectives', $module->objectives)" :min-height="140" />
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Duration and Flow of Days</label>
                                    <x-rich-textarea name="duration_and_flow" :value="old('duration_and_flow', $module->duration_and_flow)" :min-height="170"
                                        placeholder="e.g. Day 1 – Intro video, Day 2 – Discussion, Day 3 – Q&A" />
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Materials</label>
                                    <x-rich-textarea name="materials" :value="old('materials', $module->materials)" :min-height="140" />
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
@include('lesson-planning._draft-autosave', [
    'formId' => 'module-form',
    'draftKey' => 'dga_draft_module_edit_' . $module->id,
])
@push('scripts')
<script src="{{ asset('js/vendor/quill/quill.min.js') }}"></script>
<script src="{{ asset('js/rich-textarea.js') }}"></script>
@endpush
@endsection
