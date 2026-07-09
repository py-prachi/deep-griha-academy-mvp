@extends('layouts.app')
@section('title', 'Select Lesson Plan for Slot')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4" style="max-width:720px;">

                    <div class="d-flex align-items-center mb-2">
                        <a href="{{ route('timetable.teacher') }}" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <div>
                            <h5 class="mb-0">Select Lesson Plan</h5>
                            <div class="text-muted small">
                                {{ optional($assignment->subject)->name }}
                                &nbsp;·&nbsp;
                                {{ optional($assignment->schoolClass)->class_name }} {{ optional($assignment->section)->section_name }}
                                &nbsp;·&nbsp;
                                <strong>{{ $scheduledDate ? \Carbon\Carbon::parse($scheduledDate)->format('l, d M Y') : '' }}</strong>
                            </div>
                        </div>
                    </div>

                    @include('session-messages')

                    @if($plans->isEmpty())
                    <div class="alert alert-light border text-muted">
                        No lesson plans found for this class and subject.
                        <a href="{{ route('lesson-planning.lessons.create', ['class_id' => $assignment->class_id, 'section_id' => $assignment->section_id, 'subject_id' => $assignment->subject_id, 'scheduled_date' => $scheduledDate]) }}" class="alert-link">
                            Create one now
                        </a>
                    </div>
                    @else

                    <p class="text-muted small mb-3">
                        Plans whose <em>Date of Execution</em> matches <strong>{{ $dateDisplay }}</strong> are highlighted.
                        Click <strong>Use this plan</strong> to link it to that date — or click the topic to view the full plan.
                    </p>

                    <div class="list-group shadow-sm">
                        @foreach($plans as $plan)
                        @php
                            $isMatch = $dateDisplay && str_contains($plan->date_execution ?? '', $dateDisplay);
                            $alreadyLinked = $plan->scheduled_date && $plan->scheduled_date->format('Y-m-d') === $scheduledDate;
                        @endphp
                        <div class="list-group-item {{ $isMatch ? 'border-start border-4 border-success' : '' }} {{ $alreadyLinked ? 'list-group-item-success' : '' }}">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">
                                        {{ $plan->chapter_topic ?? '(No topic)' }}
                                        @if($alreadyLinked)
                                            <span class="badge bg-success ms-1"><i class="bi bi-check-lg me-1"></i>Linked</span>
                                        @elseif($isMatch)
                                            <span class="badge bg-success bg-opacity-75 ms-1">Date matches</span>
                                        @endif
                                    </div>
                                    <div class="small text-muted mt-1">
                                        @if($plan->date_execution)
                                            <span class="me-3"><i class="bi bi-calendar me-1"></i>Execution: {{ $plan->date_execution }}</span>
                                        @endif
                                        @if($plan->date_written)
                                            <span><i class="bi bi-pencil me-1"></i>Written: {{ $plan->date_written->format('d M Y') }}</span>
                                        @endif
                                        @if($plan->scheduled_date)
                                            <span class="ms-3 text-success"><i class="bi bi-link-45deg me-1"></i>Scheduled: {{ $plan->scheduled_date->format('d M Y') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex gap-2 flex-shrink-0 align-items-center">
                                    <a href="{{ route('lesson-planning.lessons.edit', $plan->id) }}"
                                       class="btn btn-sm btn-outline-secondary" title="View plan">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if(!$alreadyLinked && $scheduledDate)
                                    <form method="POST" action="{{ route('lesson-planning.lessons.set-date', $plan->id) }}">
                                        @csrf
                                        <input type="hidden" name="scheduled_date" value="{{ $scheduledDate }}">
                                        <button type="submit" class="btn btn-sm {{ $isMatch ? 'btn-success' : 'btn-outline-primary' }}">
                                            Use this plan
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="mt-3">
                        <a href="{{ route('lesson-planning.lessons.create', ['class_id' => $assignment->class_id, 'section_id' => $assignment->section_id, 'subject_id' => $assignment->subject_id, 'scheduled_date' => $scheduledDate]) }}"
                           class="btn btn-sm btn-outline-success">
                            <i class="bi bi-plus-lg me-1"></i> Create new plan for this date
                        </a>
                    </div>

                    @endif

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
