@extends('layouts.app')
@section('title', 'Learning Outcomes & Chapters')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">

                    <h4 class="mb-1"><i class="bi bi-journal-bookmark me-1"></i> Learning Outcomes & Chapters</h4>
                    <p class="text-muted small mb-3">Month-wise chapters and learning outcomes covered during the academic session.</p>

                    @include('session-messages')

                    {{-- ── SUBJECT TEACHER SECTION ── --}}
                    @if($subjectAssignments->isNotEmpty())
                    <h6 class="text-uppercase text-muted small fw-bold mb-2 border-bottom pb-1">
                        <i class="bi bi-pencil-square me-1"></i> My Entries
                    </h6>

                    @php $groupedAssignments = $subjectAssignments->groupBy('class_id'); @endphp

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
                                $key      = $assignment->class_id . '_' . $assignment->subject_id;
                                $outcomes = $myOutcomes->get($key, collect());
                                $total    = $outcomes->count();
                            @endphp
                            <div class="border-bottom px-3 py-2">
                                <div class="d-flex align-items-center justify-content-between mb-0">
                                    <div>
                                        <span class="fw-semibold">{{ $assignment->subject->name }}</span>
                                        @if($total > 0)
                                            <span class="badge bg-light text-dark ms-2 small">{{ $total }} {{ Str::plural('chapter', $total) }}</span>
                                        @else
                                            <span class="badge bg-warning text-dark ms-2 small">No entries yet</span>
                                        @endif
                                    </div>
                                    <div class="d-flex gap-2">
                                        @if($total > 0)
                                        <a href="{{ route('monthly-outcomes.print', ['class_id' => $assignment->class_id, 'subject_id' => $assignment->subject_id]) }}"
                                           target="_blank" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-printer"></i> Print
                                        </a>
                                        @endif
                                        <a href="{{ route('monthly-outcomes.create', ['class_id' => $assignment->class_id, 'section_id' => $assignment->section_id, 'subject_id' => $assignment->subject_id]) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-plus"></i> Add Entry
                                        </a>
                                    </div>
                                </div>

                                @if($outcomes->isNotEmpty())
                                <div class="table-responsive mt-2">
                                    <table class="table table-sm table-bordered mb-0" style="font-size:0.85rem;">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:90px;">Month</th>
                                                <th style="width:50px;" class="text-center">Days</th>
                                                <th style="width:60px;">Ch. No.</th>
                                                <th>Chapter Name</th>
                                                <th>Learning Outcomes</th>
                                                <th style="width:95px;">Start Date</th>
                                                <th style="width:95px;">End Date</th>
                                                <th style="width:70px;" class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($outcomes as $outcome)
                                            <tr>
                                                <td class="fw-semibold">{{ \App\Models\MonthlyOutcome::MONTHS[$outcome->month] }}</td>
                                                <td class="text-center">{{ $outcome->number_of_days }}</td>
                                                <td>{{ $outcome->chapter_number ?? '—' }}</td>
                                                <td>{{ $outcome->chapter_name }}</td>
                                                <td class="text-muted small">{{ $outcome->learning_outcomes ?? '—' }}</td>
                                                <td class="small">{{ $outcome->start_date ? $outcome->start_date->format('d M Y') : '—' }}</td>
                                                <td class="small">{{ $outcome->end_date ? $outcome->end_date->format('d M Y') : '—' }}</td>
                                                <td class="text-center">
                                                    <a href="{{ route('monthly-outcomes.edit', $outcome->id) }}" class="btn btn-xs btn-outline-secondary py-0 px-1" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <form method="POST" action="{{ route('monthly-outcomes.destroy', $outcome->id) }}" class="d-inline"
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
                    @if($ctAssignment)
                    <h6 class="text-uppercase text-muted small fw-bold mb-2 border-bottom pb-1 mt-4">
                        <i class="bi bi-eye me-1"></i>
                        My Class — {{ $ctAssignment->schoolClass->class_name }} {{ $ctAssignment->section->section_name }}
                        <span class="text-secondary fw-normal ms-1">(view only)</span>
                    </h6>

                    @if($ctOutcomes && $ctOutcomes->isNotEmpty())
                        @foreach($ctOutcomes as $subjectId => $subjectOutcomes)
                        @php $subject = $subjectOutcomes->first()->subject; @endphp
                        <div class="card mb-3">
                            <div class="card-header py-2 bg-light">
                                <strong>{{ $subject->name ?? '—' }}</strong>
                                <small class="text-muted ms-2">by {{ optional($subjectOutcomes->first()->teacher)->first_name }} {{ optional($subjectOutcomes->first()->teacher)->last_name }}</small>
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
                                                <th>Learning Outcomes</th>
                                                <th style="width:95px;">Start Date</th>
                                                <th style="width:95px;">End Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($subjectOutcomes as $outcome)
                                            <tr>
                                                <td class="fw-semibold">{{ \App\Models\MonthlyOutcome::MONTHS[$outcome->month] }}</td>
                                                <td class="text-center">{{ $outcome->number_of_days }}</td>
                                                <td>{{ $outcome->chapter_number ?? '—' }}</td>
                                                <td>{{ $outcome->chapter_name }}</td>
                                                <td class="text-muted small">{{ $outcome->learning_outcomes ?? '—' }}</td>
                                                <td class="small">{{ $outcome->start_date ? $outcome->start_date->format('d M Y') : '—' }}</td>
                                                <td class="small">{{ $outcome->end_date ? $outcome->end_date->format('d M Y') : '—' }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="alert alert-light text-muted">No Learning Outcomes entries have been added for your class yet.</div>
                    @endif
                    @endif

                    @if($subjectAssignments->isEmpty() && !$ctAssignment)
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
