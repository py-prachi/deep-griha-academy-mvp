@extends('layouts.app')
@section('title', 'Learning Standard')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">

                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <h4 class="mb-0"><i class="bi bi-journal-text me-1"></i> Learning Standard</h4>
                    </div>
                    <p class="text-muted small mb-3">Month-wise syllabus plans entered by subject teachers.</p>

                    @include('session-messages')

                    @if($plans->isEmpty())
                        <div class="alert alert-light text-muted">No Learning Standard entries have been entered yet for this session.</div>
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
                                                <th>Learning Standards / Outcome</th>
                                                <th style="width:90px;">Status</th>
                                                <th>Admin Remark</th>
                                                <th style="width:50px;" class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($subjectPlans as $plan)
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
                                                <td class="small">{{ $plan->admin_remark ?: '—' }}</td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-xs btn-outline-primary py-0 px-1" title="Add/Edit Remark"
                                                            data-bs-toggle="modal" data-bs-target="#planRemarkModal{{ $plan->id }}">
                                                        <i class="bi bi-chat-left-text"></i>
                                                    </button>
                                                </td>
                                            </tr>

                                            <div class="modal fade" id="planRemarkModal{{ $plan->id }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form method="POST" action="{{ route('lesson-plans.remark', $plan->id) }}">
                                                            @csrf
                                                            <div class="modal-header">
                                                                <h6 class="modal-title">Admin Remark — {{ $plan->chapter_name }}</h6>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <textarea name="admin_remark" class="form-control" rows="4"
                                                                          placeholder="Add a remark for the teacher…">{{ $plan->admin_remark }}</textarea>
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
