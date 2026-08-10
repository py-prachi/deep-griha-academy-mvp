@extends('layouts.app')
@section('title', 'Learning Standard')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">

                    <h4 class="mb-1"><i class="bi bi-journal-text me-1"></i> Learning Standard</h4>
                    <p class="text-muted small mb-3">Month-wise syllabus plan for the current academic session.</p>

                    @include('session-messages')

                    {{-- ── SUBJECT TEACHER SECTION ── --}}
                    @if($subjectAssignments->isNotEmpty())
                    <h6 class="text-uppercase text-muted small fw-bold mb-2 border-bottom pb-1">
                        <i class="bi bi-pencil-square me-1"></i> My Learning Standard
                    </h6>

                    @php
                        $groupedAssignments = $subjectAssignments->groupBy('class_id');
                    @endphp

                    @foreach($groupedAssignments as $classId => $assignments)
                    @php $firstAssignment = $assignments->first(); @endphp
                    <div class="card mb-3 border-primary">
                        <div class="card-header bg-primary text-white py-2 d-flex justify-content-between align-items-center">
                            <strong>
                                <i class="bi bi-building me-1"></i>
                                {{ $firstAssignment->schoolClass->class_name }}
                                {{ $firstAssignment->section->section_name }}
                            </strong>
                        </div>
                        <div class="card-body p-0">
                            @foreach($assignments as $assignment)
                            @php
                                $key   = $assignment->class_id . '_' . $assignment->subject_id;
                                $plans = $myPlans->get($key, collect());
                                $completedCount = $plans->where('status', 'completed')->count();
                                $totalCount     = $plans->count();
                            @endphp
                            <div class="border-bottom px-3 py-2">
                                <div class="d-flex align-items-center justify-content-between mb-0">
                                    <div>
                                        <span class="fw-semibold">{{ $assignment->subject->name }}</span>
                                        @if($totalCount > 0)
                                            <span class="badge bg-light text-dark ms-2 small">{{ $completedCount }}/{{ $totalCount }} completed</span>
                                        @else
                                            <span class="badge bg-warning text-dark ms-2 small">No entries yet</span>
                                        @endif
                                    </div>
                                    <div class="d-flex gap-2">
                                        @if($totalCount > 0)
                                        <a href="{{ route('lesson-plans.print', ['class_id' => $assignment->class_id, 'subject_id' => $assignment->subject_id]) }}"
                                           target="_blank" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-printer"></i> Print
                                        </a>
                                        @endif
                                        <a href="{{ route('lesson-plans.create', ['class_id' => $assignment->class_id, 'section_id' => $assignment->section_id, 'subject_id' => $assignment->subject_id]) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-plus"></i> Add Entry
                                        </a>
                                    </div>
                                </div>

                                @if($plans->isNotEmpty())
                                <div class="table-responsive mt-2">
                                    <table class="table table-sm table-bordered mb-0" style="font-size:0.85rem;">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:90px;">Month</th>
                                                <th style="width:50px;" class="text-center">Days</th>
                                                <th style="width:60px;">Ch. No.</th>
                                                <th>Chapter Name</th>
                                                <th>Learning Standards / Outcome</th>
                                                <th style="width:90px;">Status</th>
                                                <th style="width:70px;" class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($plans as $plan)
                                            <tr>
                                                <td class="fw-semibold">{{ \App\Models\LessonPlan::MONTHS[$plan->month] }}</td>
                                                <td class="text-center">{{ $plan->days_allocated }}</td>
                                                <td>{{ $plan->chapter_number ?? '—' }}</td>
                                                <td>{{ $plan->chapter_name }}</td>
                                                <td class="text-muted small">{!! \App\Support\RichText::render($plan->learning_standards) !!}</td>
                                                <td>
                                                    <span class="badge {{ $plan->status === 'completed' ? 'bg-success' : 'bg-warning text-dark' }}">
                                                        {{ \App\Models\LessonPlan::STATUS_LABELS[$plan->status] }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('lesson-plans.edit', $plan->id) }}" class="btn btn-xs btn-outline-secondary py-0 px-1" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <form method="POST" action="{{ route('lesson-plans.destroy', $plan->id) }}" class="d-inline"
                                                          onsubmit="return confirm('Delete this entry?')">
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
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                    @endif

                    {{-- ── CLASS TEACHER VIEW SECTION ── --}}
                    {{-- A teacher may be CT for more than one class — show a block per class.
                         Pre-Primary CTs teach every subject themselves, so their block is
                         editable across all subjects (not just ones they're explicitly
                         assigned to); Class 1-8 CTs stay view-only, as before. --}}
                    @foreach($ctAssignments as $ctAssignment)
                    @php
                        $ctPlans         = $ctPlansByAssignment[$ctAssignment->id] ?? collect();
                        $isPP            = $ctIsPrePrimary[$ctAssignment->id] ?? false;
                        $ctExtraSubjects = $ctSubjectsByAssignment[$ctAssignment->id] ?? collect();
                    @endphp
                    {{-- Pre-Primary: skip the block entirely once every subject is already
                         covered by her own assignments in "My Learning Standard" above. --}}
                    @if($isPP && $ctExtraSubjects->isEmpty())
                        @continue
                    @endif
                    <h6 class="text-uppercase text-muted small fw-bold mb-2 border-bottom pb-1 mt-4">
                        <i class="bi {{ $isPP ? 'bi-pencil-square' : 'bi-eye' }} me-1"></i>
                        My Class — {{ $ctAssignment->schoolClass->class_name }} {{ $ctAssignment->section->section_name }}
                        @unless($isPP)
                        <span class="text-secondary fw-normal ms-1">(view only)</span>
                        @endunless
                    </h6>

                    @if($isPP)
                        {{-- Editable: subjects not already covered by an explicit assignment above --}}
                        @foreach($ctExtraSubjects as $subject)
                        @php
                            $plans = $ctPlans->get($subject->id, collect());
                            $completedCount = $plans->where('status', 'completed')->count();
                            $totalCount     = $plans->count();
                        @endphp
                        <div class="card mb-3">
                            <div class="card-header py-2 bg-light d-flex align-items-center justify-content-between">
                                <div>
                                    <strong>{{ $subject->name }}</strong>
                                    @if($totalCount > 0)
                                        <span class="badge bg-light text-dark ms-2 small">{{ $completedCount }}/{{ $totalCount }} completed</span>
                                    @else
                                        <span class="badge bg-warning text-dark ms-2 small">No entries yet</span>
                                    @endif
                                </div>
                                <div class="d-flex gap-2">
                                    @if($totalCount > 0)
                                    <a href="{{ route('lesson-plans.print', ['class_id' => $ctAssignment->class_id, 'subject_id' => $subject->id]) }}"
                                       target="_blank" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-printer"></i> Print
                                    </a>
                                    @endif
                                    <a href="{{ route('lesson-plans.create', ['class_id' => $ctAssignment->class_id, 'section_id' => $ctAssignment->section_id, 'subject_id' => $subject->id]) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-plus"></i> Add Entry
                                    </a>
                                </div>
                            </div>
                            @if($plans->isNotEmpty())
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0" style="font-size:0.85rem;">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:90px;">Month</th>
                                                <th style="width:50px;" class="text-center">Days</th>
                                                <th style="width:60px;">Ch. No.</th>
                                                <th>Chapter Name</th>
                                                <th>Learning Standards / Outcome</th>
                                                <th style="width:90px;">Status</th>
                                                <th style="width:120px;">Logged by</th>
                                                <th style="width:70px;" class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($plans as $plan)
                                            <tr>
                                                <td class="fw-semibold">{{ \App\Models\LessonPlan::MONTHS[$plan->month] }}</td>
                                                <td class="text-center">{{ $plan->days_allocated }}</td>
                                                <td>{{ $plan->chapter_number ?? '—' }}</td>
                                                <td>{{ $plan->chapter_name }}</td>
                                                <td class="text-muted small">{!! \App\Support\RichText::render($plan->learning_standards) !!}</td>
                                                <td>
                                                    <span class="badge {{ $plan->status === 'completed' ? 'bg-success' : 'bg-warning text-dark' }}">
                                                        {{ \App\Models\LessonPlan::STATUS_LABELS[$plan->status] }}
                                                    </span>
                                                </td>
                                                <td class="text-muted small">{{ optional($plan->teacher)->first_name }} {{ optional($plan->teacher)->last_name }}</td>
                                                <td class="text-center">
                                                    <a href="{{ route('lesson-plans.edit', $plan->id) }}" class="btn btn-xs btn-outline-secondary py-0 px-1" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <form method="POST" action="{{ route('lesson-plans.destroy', $plan->id) }}" class="d-inline"
                                                          onsubmit="return confirm('Delete this entry?')">
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
                            </div>
                            @endif
                        </div>
                        @endforeach
                    @elseif($ctPlans->isNotEmpty())
                        {{-- Class 1-8: view only --}}
                        @foreach($ctPlans as $subjectId => $plans)
                        @php $subject = $plans->first()->subject; @endphp
                        <div class="card mb-3">
                            <div class="card-header py-2 bg-light">
                                <strong>{{ $subject->name ?? '—' }}</strong>
                                <small class="text-muted ms-2">by {{ optional($plans->first()->teacher)->first_name }} {{ optional($plans->first()->teacher)->last_name }}</small>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0" style="font-size:0.85rem;">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:90px;">Month</th>
                                                <th style="width:50px;" class="text-center">Days</th>
                                                <th style="width:60px;">Ch. No.</th>
                                                <th>Chapter Name</th>
                                                <th>Learning Standards / Outcome</th>
                                                <th style="width:90px;">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($plans as $plan)
                                            <tr>
                                                <td class="fw-semibold">{{ \App\Models\LessonPlan::MONTHS[$plan->month] }}</td>
                                                <td class="text-center">{{ $plan->days_allocated }}</td>
                                                <td>{{ $plan->chapter_number ?? '—' }}</td>
                                                <td>{{ $plan->chapter_name }}</td>
                                                <td class="text-muted small">{!! \App\Support\RichText::render($plan->learning_standards) !!}</td>
                                                <td>
                                                    <span class="badge {{ $plan->status === 'completed' ? 'bg-success' : 'bg-warning text-dark' }}">
                                                        {{ \App\Models\LessonPlan::STATUS_LABELS[$plan->status] }}
                                                    </span>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="alert alert-light text-muted">No Learning Standard entries have been added for your class yet.</div>
                    @endif
                    @endforeach

                    @if($subjectAssignments->isEmpty() && $ctAssignments->isEmpty())
                    <div class="alert alert-warning">
                        You have no subject assignments or class teacher assignment for the current session.
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
