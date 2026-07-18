@extends('layouts.app')
@section('title', 'Lesson Planning')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4 pe-4">

                    <h4 class="mb-1"><i class="bi bi-journal-text me-1"></i> Lesson Planning</h4>
                    <p class="text-muted small mb-3">Module and lesson plans for your subject assignments.</p>

                    @include('session-messages')

                    @if(session('status'))
                    <script>
                        // A save/update just succeeded — any locally-cached draft for lesson
                        // planning forms is stale now, so drop it to avoid a false "restore?" prompt.
                        (function () {
                            try {
                                Object.keys(localStorage)
                                    .filter(function (k) { return k.indexOf('dga_draft_') === 0; })
                                    .forEach(function (k) { localStorage.removeItem(k); });
                            } catch (e) {}
                        })();
                    </script>
                    @endif

                    {{-- ── SUBJECT TEACHER SECTION ── --}}
                    @if($subjectAssignments->isNotEmpty())
                    <h6 class="text-uppercase text-muted small fw-bold mb-2 border-bottom pb-1">
                        <i class="bi bi-pencil-square me-1"></i> My Subject Assignments
                    </h6>

                    @php $groupedAssignments = $subjectAssignments->groupBy('class_id'); @endphp

                    @foreach($groupedAssignments as $classId => $assignments)
                    @php $firstAssignment = $assignments->first(); @endphp
                    <div class="card mb-4 border-primary">
                        <div class="card-header bg-primary text-white py-2">
                            <strong>
                                <i class="bi bi-building me-1"></i>
                                {{ $firstAssignment->schoolClass->class_name }}
                                {{ $firstAssignment->section->section_name }}
                            </strong>
                        </div>
                        <div class="card-body p-0">
                            @foreach($assignments as $assignment)
                            @php
                                $groupKey  = $assignment->class_id . '_' . $assignment->section_id . '_' . $assignment->subject_id;
                                $modules   = $myModules->get($groupKey, collect());
                                $lessons   = $myLessons->get($groupKey, collect());
                            @endphp
                            <div class="border-bottom px-3 pt-3 pb-2">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h6 class="mb-0 fw-semibold">{{ $assignment->subject->name }}</h6>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('lesson-planning.modules.create', ['class_id' => $assignment->class_id, 'section_id' => $assignment->section_id, 'subject_id' => $assignment->subject_id]) }}"
                                           class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-plus-circle me-1"></i>Add Module
                                        </a>
                                        <a href="{{ route('lesson-planning.lessons.create', ['class_id' => $assignment->class_id, 'section_id' => $assignment->section_id, 'subject_id' => $assignment->subject_id]) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-plus-circle me-1"></i>Add Lesson Plan
                                        </a>
                                    </div>
                                </div>

                                {{-- Modules --}}
                                @if($modules->isNotEmpty())
                                <p class="text-muted small fw-semibold mb-1 mt-2">Modules ({{ $modules->count() }})</p>
                                <div class="table-responsive mb-2">
                                    <table class="table table-sm table-bordered mb-0" style="font-size:0.83rem;">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:100px;">Date Written</th>
                                                <th>Topic / Chapters</th>
                                                <th style="width:100px;">Duration & Flow</th>
                                                <th style="width:90px;" class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($modules as $module)
                                            <tr>
                                                <td class="small">{{ $module->date_written ? $module->date_written->format('d M Y') : '—' }}</td>
                                                <td>{{ Str::limit($module->topic, 80) }}</td>
                                                <td class="small">{{ Str::limit($module->duration_and_flow, 40) ?? '—' }}</td>
                                                <td class="text-center">
                                                    <a href="{{ route('lesson-planning.print', ['module_id' => $module->id]) }}"
                                                       target="_blank" class="btn btn-xs btn-outline-secondary py-0 px-1" title="Print">
                                                        <i class="bi bi-printer"></i>
                                                    </a>
                                                    <a href="{{ route('lesson-planning.modules.edit', $module->id) }}"
                                                       class="btn btn-xs btn-outline-secondary py-0 px-1" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <form method="POST" action="{{ route('lesson-planning.modules.destroy', $module->id) }}"
                                                          class="d-inline" onsubmit="return confirm('Delete this module?')">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-xs btn-outline-danger py-0 px-1" title="Delete">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @else
                                <p class="text-muted small fst-italic mb-2">No modules yet.</p>
                                @endif

                                {{-- Lesson Plans --}}
                                @if($lessons->isNotEmpty())
                                <p class="text-muted small fw-semibold mb-1 mt-2">Lesson Plans ({{ $lessons->count() }})</p>
                                <div class="table-responsive mb-2">
                                    <table class="table table-sm table-bordered mb-0" style="font-size:0.83rem;">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:100px;">Date Written</th>
                                                <th style="width:120px;">Date of Execution</th>
                                                <th>Chapter / Topic</th>
                                                <th style="width:80px;" class="text-center">Status</th>
                                                <th style="width:110px;" class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($lessons as $lesson)
                                            <tr>
                                                <td class="small">{{ $lesson->date_written ? $lesson->date_written->format('d M Y') : '—' }}</td>
                                                <td class="small">{{ $lesson->date_execution ?: '—' }}</td>
                                                <td>{{ Str::limit($lesson->chapter_topic, 70) ?? '—' }}</td>
                                                <td class="text-center">
                                                    @if($lesson->status === 'completed')
                                                        <span class="badge bg-success">Done</span>
                                                    @else
                                                        <span class="badge bg-warning text-dark">Planned</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('lesson-planning.print', ['lesson_id' => $lesson->id]) }}"
                                                       target="_blank" class="btn btn-xs btn-outline-secondary py-0 px-1" title="Print">
                                                        <i class="bi bi-printer"></i>
                                                    </a>
                                                    <a href="{{ route('lesson-planning.lessons.edit', $lesson->id) }}"
                                                       class="btn btn-xs btn-outline-secondary py-0 px-1" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    @if($lesson->status !== 'completed')
                                                    <form method="POST" action="{{ route('lesson-planning.lessons.complete', $lesson->id) }}"
                                                          class="d-inline">
                                                        @csrf
                                                        <button class="btn btn-xs btn-outline-success py-0 px-1" title="Mark as completed">
                                                            <i class="bi bi-check2"></i>
                                                        </button>
                                                    </form>
                                                    @endif
                                                    <form method="POST" action="{{ route('lesson-planning.lessons.destroy', $lesson->id) }}"
                                                          class="d-inline" onsubmit="return confirm('Delete this lesson plan?')">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-xs btn-outline-danger py-0 px-1" title="Delete">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @else
                                <p class="text-muted small fst-italic mb-2">No lesson plans yet.</p>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                    @endif

                    {{-- ── CLASS TEACHER VIEW (read-only) ── --}}
                    @if($ctAssignment)
                    <h6 class="text-uppercase text-muted small fw-bold mb-2 border-bottom pb-1 mt-4">
                        <i class="bi bi-eye me-1"></i>
                        My Class — {{ $ctAssignment->schoolClass->class_name }} {{ $ctAssignment->section->section_name }}
                        <span class="text-secondary fw-normal ms-1">(view only)</span>
                    </h6>

                    @if($ctLessons && $ctLessons->isNotEmpty())
                        @foreach($ctLessons as $subjectId => $subjectLessons)
                        @php $subject = $subjectLessons->first()->subject; @endphp
                        <div class="card mb-3">
                            <div class="card-header py-2 bg-light">
                                <strong>{{ $subject->name ?? '—' }}</strong>
                                <small class="text-muted ms-2">
                                    by {{ optional($subjectLessons->first()->teacher)->first_name }}
                                    {{ optional($subjectLessons->first()->teacher)->last_name }}
                                </small>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0" style="font-size:0.83rem;">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:100px;">Date Written</th>
                                                <th style="width:100px;">Date Execution</th>
                                                <th>Chapter / Topic</th>
                                                <th>Module</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($subjectLessons as $lesson)
                                            <tr>
                                                <td class="small">{{ $lesson->date_written ? $lesson->date_written->format('d M Y') : '—' }}</td>
                                                <td class="small">{{ $lesson->date_execution ?: '—' }}</td>
                                                <td>{{ Str::limit($lesson->chapter_topic, 70) ?? '—' }}</td>
                                                <td class="small text-muted">{{ optional($lesson->module)->topic ? Str::limit($lesson->module->topic, 40) : '—' }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="alert alert-light text-muted">No lesson plans have been added for your class yet.</div>
                    @endif
                    @endif

                    @if($subjectAssignments->isEmpty() && !$ctAssignment)
                    <div class="alert alert-warning">
                        You have no subject assignments for Class 1–8 in the current session.
                        Ask the admin to assign you via <a href="{{ route('academics.teacher-assignments') }}">Teacher Assignments</a>.
                    </div>
                    @endif

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
