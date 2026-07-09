@extends('layouts.app')
@section('title', 'Edit Lesson Plan')
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
                            <form method="POST" action="{{ route('lesson-planning.lessons.update', $lesson->id) }}">
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
                                                {{ $mod->date_written ? $mod->date_written->format('d M Y') . ' — ' : '' }}{{ Str::limit($mod->topic, 60) }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('module_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Chapter / Topic</label>
                                    <textarea name="chapter_topic" rows="2" class="form-control @error('chapter_topic') is-invalid @enderror">{{ old('chapter_topic', $lesson->chapter_topic) }}</textarea>
                                    @error('chapter_topic')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Period Timing</label>
                                    <input type="text" name="period_timing" class="form-control @error('period_timing') is-invalid @enderror"
                                           value="{{ old('period_timing', $lesson->period_timing) }}" placeholder="e.g. 9:00–9:40 AM">
                                    @error('period_timing')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Learning Standard</label>
                                    <textarea name="learning_standard" rows="2" class="form-control @error('learning_standard') is-invalid @enderror">{{ old('learning_standard', $lesson->learning_standard) }}</textarea>
                                    @error('learning_standard')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Objective</label>
                                    <textarea name="objective" rows="2" class="form-control @error('objective') is-invalid @enderror">{{ old('objective', $lesson->objective) }}</textarea>
                                    @error('objective')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Material Needed</label>
                                    <textarea name="material_needed" rows="2" class="form-control @error('material_needed') is-invalid @enderror">{{ old('material_needed', $lesson->material_needed) }}</textarea>
                                    @error('material_needed')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Training Component</label>
                                    <textarea name="training_component" rows="2" class="form-control @error('training_component') is-invalid @enderror">{{ old('training_component', $lesson->training_component) }}</textarea>
                                    @error('training_component')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Student to Whom Responses Expected</label>
                                    <textarea name="student_responses" rows="2" class="form-control @error('student_responses') is-invalid @enderror">{{ old('student_responses', $lesson->student_responses) }}</textarea>
                                    @error('student_responses')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Hook</label>
                                        <textarea name="hook" rows="3" class="form-control @error('hook') is-invalid @enderror">{{ old('hook', $lesson->hook) }}</textarea>
                                        @error('hook')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Teach</label>
                                        <textarea name="teach" rows="3" class="form-control @error('teach') is-invalid @enderror">{{ old('teach', $lesson->teach) }}</textarea>
                                        @error('teach')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Guided Practice (CW)</label>
                                        <textarea name="guided_practice" rows="3" class="form-control @error('guided_practice') is-invalid @enderror">{{ old('guided_practice', $lesson->guided_practice) }}</textarea>
                                        @error('guided_practice')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Independent Practice (CW)</label>
                                        <textarea name="independent_practice" rows="3" class="form-control @error('independent_practice') is-invalid @enderror">{{ old('independent_practice', $lesson->independent_practice) }}</textarea>
                                        @error('independent_practice')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Closure</label>
                                    <textarea name="closure" rows="2" class="form-control @error('closure') is-invalid @enderror">{{ old('closure', $lesson->closure) }}</textarea>
                                    @error('closure')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Homework</label>
                                    <textarea name="homework" rows="2" class="form-control @error('homework') is-invalid @enderror">{{ old('homework', $lesson->homework) }}</textarea>
                                    @error('homework')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Any Other Note</label>
                                    <textarea name="other_notes" rows="2" class="form-control @error('other_notes') is-invalid @enderror">{{ old('other_notes', $lesson->other_notes) }}</textarea>
                                    @error('other_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Remark</label>
                                    <textarea name="remark" rows="2" class="form-control @error('remark') is-invalid @enderror">{{ old('remark', $lesson->remark) }}</textarea>
                                    @error('remark')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
@endsection
