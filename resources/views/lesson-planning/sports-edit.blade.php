@extends('layouts.app')
@section('title', 'Edit Sports Lesson Plan')
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
                            <li class="breadcrumb-item active">Edit Sports Plan</li>
                        </ol>
                    </nav>

                    <h4 class="mb-1"><i class="bi bi-trophy me-1"></i> Sports Lesson Plan</h4>
                    <p class="text-muted small mb-3">
                        Grade {{ optional($lesson->schoolClass)->class_name }}
                        {{ optional($lesson->section)->section_name }}
                    </p>

                    @include('session-messages')

                    <div class="card shadow-sm" style="max-width:800px;">
                        <div class="card-body">
                            <form method="POST" action="{{ route('lesson-planning.lessons.update', $lesson->id) }}" id="sports-form">
                                @csrf @method('PUT')
                                <input type="hidden" name="module_required" value="0">

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Date of Planning</label>
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
                                               value="{{ old('date_execution', $lesson->date_execution) }}" placeholder="e.g. 9/6/2026">
                                        @error('date_execution')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Time</label>
                                        <input type="text" name="period_timing" class="form-control @error('period_timing') is-invalid @enderror"
                                               value="{{ old('period_timing', $lesson->period_timing) }}" placeholder="e.g. 9:45 AM">
                                        @error('period_timing')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Outdoor &amp; Indoor</label>
                                        @php $lt = old('lesson_type', $lesson->lesson_type); @endphp
                                        <select name="lesson_type" class="form-select @error('lesson_type') is-invalid @enderror">
                                            <option value="">— Select —</option>
                                            <option value="Outdoor" {{ $lt === 'Outdoor' ? 'selected' : '' }}>Outdoor</option>
                                            <option value="Indoor"  {{ $lt === 'Indoor'  ? 'selected' : '' }}>Indoor</option>
                                            <option value="Both"    {{ $lt === 'Both'    ? 'selected' : '' }}>Both</option>
                                        </select>
                                        @error('lesson_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Name of the Game</label>
                                    <input type="text" name="chapter_topic" class="form-control @error('chapter_topic') is-invalid @enderror"
                                           value="{{ old('chapter_topic', $lesson->chapter_topic) }}" placeholder="e.g. Fun game">
                                    @error('chapter_topic')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Objective</label>
                                    <textarea name="objective" rows="2" class="form-control @error('objective') is-invalid @enderror">{{ old('objective', $lesson->objective) }}</textarea>
                                    @error('objective')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Introduction</label>
                                    <textarea name="hook" rows="3" class="form-control @error('hook') is-invalid @enderror">{{ old('hook', $lesson->hook) }}</textarea>
                                    @error('hook')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Practical</label>
                                    <textarea name="practical_notes" rows="3" class="form-control @error('practical_notes') is-invalid @enderror">{{ old('practical_notes', $lesson->practical_notes) }}</textarea>
                                    @error('practical_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check2 me-1"></i> Update Plan
                                    </button>
                                    <a href="{{ route('lesson-planning.print', ['lesson_id' => $lesson->id]) }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-printer me-1"></i> Print
                                    </a>
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
    'formId' => 'sports-form',
    'draftKey' => 'dga_draft_sports_edit_' . $lesson->id,
])
@endsection
