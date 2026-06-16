@extends('layouts.app')
@section('title', 'Lesson Plans')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">

                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <h4 class="mb-0"><i class="bi bi-journal-text me-1"></i> Lesson Plans</h4>
                    </div>
                    <p class="text-muted small mb-3">Month-wise syllabus plans entered by subject teachers.</p>

                    @include('session-messages')

                    @if($plans->isEmpty())
                        <div class="alert alert-light text-muted">No lesson plans have been entered yet for this session.</div>
                    @else
                    @foreach($classes as $class)
                    @php $classPlans = $plans->get($class->id); @endphp
                    @if($classPlans)
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-primary text-white py-2">
                            <strong><i class="bi bi-building me-1"></i> {{ $class->class_name }}</strong>
                        </div>
                        <div class="card-body p-0">
                            @php $bySubject = $classPlans->groupBy('subject_id'); @endphp
                            @foreach($bySubject as $subjectId => $subjectPlans)
                            @php $subject = $subjectPlans->first()->subject; @endphp
                            <div class="border-bottom px-3 pt-2 pb-1">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <div class="fw-semibold text-secondary small text-uppercase">
                                        {{ $subject->name ?? '—' }}
                                        <small class="text-muted fw-normal text-lowercase ms-1">
                                            — {{ optional($subjectPlans->first()->teacher)->first_name }} {{ optional($subjectPlans->first()->teacher)->last_name }}
                                        </small>
                                    </div>
                                    @php $st = $subjectPlans->first(); @endphp
                                    <a href="{{ route('lesson-plans.print', ['teacher_id' => $st->teacher_id, 'class_id' => $st->class_id, 'subject_id' => $st->subject_id]) }}"
                                       target="_blank" class="btn btn-xs btn-outline-secondary py-0 px-2" style="font-size:0.8rem;">
                                        <i class="bi bi-printer"></i> Print
                                    </a>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0" style="font-size:0.82rem;">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:90px;">Month</th>
                                                <th style="width:50px;" class="text-center">Days</th>
                                                <th style="width:60px;">Ch. No.</th>
                                                <th>Chapter Name</th>
                                                <th>Learning Standard</th>
                                                <th style="width:90px;">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($subjectPlans as $plan)
                                            <tr>
                                                <td class="fw-semibold">{{ \App\Models\LessonPlan::MONTHS[$plan->month] }}</td>
                                                <td class="text-center">{{ $plan->days_allocated }}</td>
                                                <td>{{ $plan->chapter_number ?? '—' }}</td>
                                                <td>{{ $plan->chapter_name }}</td>
                                                <td class="text-muted small">{{ $plan->learning_standards ?? '—' }}</td>
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
                            @endforeach
                        </div>
                    </div>
                    @endif
                    @endforeach
                    @endif

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
