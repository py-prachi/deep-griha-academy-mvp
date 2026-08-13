@extends('layouts.app')
@section('title', 'Lesson Planning — Admin')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4 pe-4">

                    <h4 class="mb-1"><i class="bi bi-journal-text me-1"></i> Lesson Planning</h4>
                    <p class="text-muted small mb-3">Select a class and subject to view modules and lesson plans.</p>

                    @include('session-messages')

                    @php
                        $availableSubjects = collect();
                        if ($selClassId && $selSectionId) {
                            $availableSubjects = \App\Models\Subject::whereHas('subjectTeachers', function ($q) use ($sessionId, $selClassId, $selSectionId) {
                                $q->where('session_id', $sessionId)
                                  ->where('class_id', $selClassId)
                                  ->where('section_id', $selSectionId);
                            })->orderBy('sort_order')->get();
                        }
                    @endphp

                    {{-- Filter --}}
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-body py-3">
                            <form method="GET" action="{{ route('lesson-planning.index') }}" class="row g-2 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold mb-1">Class</label>
                                    <select name="class_id" id="class_select" class="form-select form-select-sm">
                                        <option value="">— Select Class —</option>
                                        @foreach($classes as $class)
                                            @foreach($class->sections as $section)
                                            <option value="{{ $class->id }}"
                                                    data-section="{{ $section->id }}"
                                                    {{ ($selClassId == $class->id && $selSectionId == $section->id) ? 'selected' : '' }}>
                                                {{ $class->class_name }} {{ $section->section_name }}
                                            </option>
                                            @endforeach
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="section_id" id="section_id_hidden" value="{{ $selSectionId }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold mb-1">Subject</label>
                                    <select name="subject_id" class="form-select form-select-sm"
                                            {{ $availableSubjects->isEmpty() ? 'disabled' : '' }}
                                            onchange="this.form.submit()">
                                        <option value="">— Select Subject —</option>
                                        @foreach($availableSubjects as $subject)
                                        <option value="{{ $subject->id }}" {{ $selSubjectId == $subject->id ? 'selected' : '' }}>
                                            {{ $subject->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-auto">
                                    @if($selClassId && $selSectionId && $selSubjectId)
                                    <a href="{{ route('lesson-planning.index') }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-x"></i> Clear
                                    </a>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>

                    @if($selClassId && $selSectionId && $selSubjectId)

                    {{-- Modules table --}}
                    <h6 class="text-uppercase text-muted small fw-bold mb-2 border-bottom pb-1">
                        <i class="bi bi-layers me-1"></i> Modules ({{ $modules->count() }})
                    </h6>
                    @if($modules->isNotEmpty())
                    <div class="table-responsive mb-4">
                        <table class="table table-sm table-bordered table-hover" style="font-size:0.85rem;">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:100px;">Date Written</th>
                                    <th>Topic / Chapters</th>
                                    <th style="width:100px;">Duration</th>
                                    <th>Teacher</th>
                                    <th>Admin Remark</th>
                                    <th style="width:80px;" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($modules as $module)
                                <tr>
                                    <td class="small">{{ $module->date_written ? $module->date_written->format('d M Y') : '—' }}</td>
                                    <td>{{ Str::limit(strip_tags($module->topic ?? ''), 100) }}</td>
                                    <td class="small">{{ Str::limit(strip_tags($module->duration_and_flow ?? ''), 40) ?: '—' }}</td>
                                    <td class="small">{{ optional($module->teacher)->first_name }} {{ optional($module->teacher)->last_name }}</td>
                                    <td class="small">{{ $module->admin_remark ?: '—' }}</td>
                                    <td class="text-center text-nowrap">
                                        <a href="{{ route('lesson-planning.print', ['module_id' => $module->id]) }}"
                                           class="btn btn-xs btn-outline-secondary py-0 px-1" title="Print">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                        <button type="button" class="btn btn-xs btn-outline-primary py-0 px-1" title="Add/Edit Remark"
                                                data-bs-toggle="modal" data-bs-target="#moduleRemarkModal{{ $module->id }}">
                                            <i class="bi bi-chat-left-text"></i>
                                        </button>
                                    </td>
                                </tr>

                                <div class="modal fade" id="moduleRemarkModal{{ $module->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('lesson-planning.modules.remark', $module->id) }}">
                                                @csrf
                                                <div class="modal-header">
                                                    <h6 class="modal-title">Admin Remark — {{ Str::limit(strip_tags($module->topic ?? ''), 60) }}</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <textarea name="admin_remark" class="form-control" rows="4"
                                                              placeholder="Add a remark for the teacher…">{{ $module->admin_remark }}</textarea>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Save Remark</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted fst-italic mb-4">No modules for this class/subject.</p>
                    @endif

                    {{-- Lesson Plans table --}}
                    <h6 class="text-uppercase text-muted small fw-bold mb-2 border-bottom pb-1">
                        <i class="bi bi-journal-richtext me-1"></i> Lesson Plans ({{ $lessons->count() }})
                    </h6>
                    @if($lessons->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover" style="font-size:0.85rem;">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:100px;">Date Written</th>
                                    <th style="width:100px;">Date Execution</th>
                                    <th>Chapter / Topic</th>
                                    <th>Teacher</th>
                                    <th>Admin Remark</th>
                                    <th style="width:80px;" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lessons as $lesson)
                                <tr>
                                    <td class="small">{{ $lesson->date_written ? $lesson->date_written->format('d M Y') : '—' }}</td>
                                    <td class="small">{{ $lesson->date_execution ?: '—' }}</td>
                                    <td>{{ Str::limit(strip_tags($lesson->chapter_topic ?? ''), 80) ?: '—' }}</td>
                                    <td class="small">{{ optional($lesson->teacher)->first_name }} {{ optional($lesson->teacher)->last_name }}</td>
                                    <td class="small">{{ $lesson->admin_remark ?: '—' }}</td>
                                    <td class="text-center text-nowrap">
                                        <a href="{{ route('lesson-planning.print', ['lesson_id' => $lesson->id]) }}"
                                           class="btn btn-xs btn-outline-secondary py-0 px-1" title="Print">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                        <button type="button" class="btn btn-xs btn-outline-primary py-0 px-1" title="Add/Edit Remark"
                                                data-bs-toggle="modal" data-bs-target="#lessonRemarkModal{{ $lesson->id }}">
                                            <i class="bi bi-chat-left-text"></i>
                                        </button>
                                    </td>
                                </tr>

                                <div class="modal fade" id="lessonRemarkModal{{ $lesson->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('lesson-planning.lessons.remark', $lesson->id) }}">
                                                @csrf
                                                <div class="modal-header">
                                                    <h6 class="modal-title">Admin Remark — {{ Str::limit(strip_tags($lesson->chapter_topic ?? ''), 60) ?: 'Lesson Plan' }}</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <textarea name="admin_remark" class="form-control" rows="4"
                                                              placeholder="Add a remark for the teacher…">{{ $lesson->admin_remark }}</textarea>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Save Remark</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted fst-italic">No lesson plans for this class/subject.</p>
                    @endif

                    @endif

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>

<script>
document.getElementById('class_select').addEventListener('change', function () {
    var selected = this.options[this.selectedIndex];
    document.getElementById('section_id_hidden').value = selected.dataset.section || '';
    this.form.submit();
});
</script>
@endsection
