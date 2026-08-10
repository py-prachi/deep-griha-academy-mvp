@extends('layouts.app')
@section('title', 'Edit Lesson Plan')
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
                            <li class="breadcrumb-item active">Edit Lesson Plan</li>
                        </ol>
                    </nav>

                    <h4 class="mb-1"><i class="bi bi-journal-richtext me-1"></i> Edit Lesson Plan</h4>
                    <p class="text-muted small mb-3">
                        {{ $lesson->schoolClass->class_name }}
                        {{ $lesson->section->section_name }} &mdash;
                        {{ $lesson->subject->name }}
                    </p>

                    @include('session-messages')

                    <div class="card shadow-sm" style="max-width:900px;">
                        <div class="card-body">
                            <form method="POST" action="{{ route('lesson-planning.lessons.update', $lesson->id) }}" id="lesson-form">
                                @csrf @method('PUT')

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Date Written</label>
                                        <input type="date" name="date_written" class="form-control @error('date_written') is-invalid @enderror"
                                               value="{{ old('date_written', $lesson->date_written ? $lesson->date_written->format('Y-m-d') : '') }}">
                                        @error('date_written')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Scheduled Teaching Date</label>
                                        <input type="date" name="scheduled_date" class="form-control @error('scheduled_date') is-invalid @enderror"
                                               value="{{ old('scheduled_date', $lesson->scheduled_date ? $lesson->scheduled_date->format('Y-m-d') : request('scheduled_date', '')) }}">
                                        <div class="form-text">Used for timetable plan status.</div>
                                        @error('scheduled_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Date of Execution <span class="text-muted fw-normal">(display)</span></label>
                                        <input type="text" name="date_execution" class="form-control @error('date_execution') is-invalid @enderror"
                                               value="{{ old('date_execution', $lesson->date_execution) }}" placeholder="e.g. 3/4/2025 or 3/4/2025 &amp; 4/4/2025">
                                        @error('date_execution')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Linked to a Module?</label>
                                        @php $modRequired = old('module_required', $lesson->module_required ? '1' : '0'); @endphp
                                        <div class="d-flex gap-4 mt-1">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="module_required" id="mod_no" value="0"
                                                       {{ $modRequired == '0' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="mod_no">No</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="module_required" id="mod_yes" value="1"
                                                       {{ $modRequired == '1' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="mod_yes">Yes</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6" id="module-select-wrapper" style="{{ $modRequired == '1' ? '' : 'display:none;' }}">
                                        <label class="form-label fw-semibold">Select Module</label>
                                        <select name="module_id" class="form-select @error('module_id') is-invalid @enderror">
                                            <option value="">— Select Module —</option>
                                            @foreach($modules as $mod)
                                            <option value="{{ $mod->id }}"
                                                    {{ old('module_id', $lesson->module_id) == $mod->id ? 'selected' : '' }}>
                                                {{ $mod->date_written ? $mod->date_written->format('d M Y') . ' — ' : '' }}{{ Str::limit(strip_tags($mod->topic ?? ''), 60) }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('module_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Chapter / Topic</label>
                                    <x-rich-textarea name="chapter_topic" :value="old('chapter_topic', $lesson->chapter_topic)" :min-height="140" />
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Period Timing</label>
                                    <input type="text" name="period_timing" class="form-control @error('period_timing') is-invalid @enderror"
                                           value="{{ old('period_timing', $lesson->period_timing) }}" placeholder="e.g. 9:00–9:40 AM">
                                    @error('period_timing')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Learning Standard</label>
                                    <x-rich-textarea name="learning_standard" :value="old('learning_standard', $lesson->learning_standard)" :min-height="140" />
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Objective</label>
                                    <x-rich-textarea name="objective" :value="old('objective', $lesson->objective)" :min-height="140" />
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Material Needed</label>
                                    <x-rich-textarea name="material_needed" :value="old('material_needed', $lesson->material_needed)" :min-height="140" />
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Training Component</label>
                                    <x-rich-textarea name="training_component" :value="old('training_component', $lesson->training_component)" :min-height="140" />
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Student to Whom Responses Expected</label>
                                    <x-rich-textarea name="student_responses" :value="old('student_responses', $lesson->student_responses)" :min-height="140" />
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Hook</label>
                                        <x-rich-textarea name="hook" :value="old('hook', $lesson->hook)" :min-height="150" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Teach</label>
                                        <x-rich-textarea name="teach" :value="old('teach', $lesson->teach)" :min-height="150" />
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Guided Practice (CW)</label>
                                        <x-rich-textarea name="guided_practice" :value="old('guided_practice', $lesson->guided_practice)" :min-height="150" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Independent Practice (CW)</label>
                                        <x-rich-textarea name="independent_practice" :value="old('independent_practice', $lesson->independent_practice)" :min-height="150" />
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Closure</label>
                                    <x-rich-textarea name="closure" :value="old('closure', $lesson->closure)" :min-height="140" />
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Homework</label>
                                    <x-rich-textarea name="homework" :value="old('homework', $lesson->homework)" :min-height="140" />
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Any Other Note</label>
                                    <x-rich-textarea name="other_notes" :value="old('other_notes', $lesson->other_notes)" :min-height="140" />
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Remark</label>
                                    <x-rich-textarea name="remark" :value="old('remark', $lesson->remark)" :min-height="140" />
                                </div>

                                <div class="d-flex gap-2 mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-1"></i> Update Lesson Plan
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

<script>
(function () {
    var radios = document.querySelectorAll('input[name="module_required"]');
    var wrapper = document.getElementById('module-select-wrapper');
    radios.forEach(function (radio) {
        radio.addEventListener('change', function () {
            wrapper.style.display = (this.value === '1') ? '' : 'none';
        });
    });
})();
</script>
@include('lesson-planning._draft-autosave', [
    'formId' => 'lesson-form',
    'draftKey' => 'dga_draft_lesson_edit_' . $lesson->id,
])
@push('scripts')
<script src="{{ asset('js/vendor/quill/quill.min.js') }}"></script>
<script src="{{ asset('js/rich-textarea.js') }}"></script>
@endpush
@endsection
