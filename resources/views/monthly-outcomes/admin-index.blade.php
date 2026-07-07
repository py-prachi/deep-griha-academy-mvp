@extends('layouts.app')
@section('title', 'Learning Outcomes & Chapters')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">

                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <h4 class="mb-0"><i class="bi bi-journal-bookmark me-1"></i> Learning Outcomes & Chapters</h4>
                    </div>
                    <p class="text-muted small mb-3">Month-wise chapters and learning outcomes entered by subject teachers.</p>

                    @include('session-messages')

                    @if($outcomes->isEmpty())
                        <div class="alert alert-light text-muted">No Learning Outcomes entries have been entered yet for this session.</div>
                    @else
                    @foreach($classes as $class)
                    @php $classOutcomes = $outcomes->get($class->id); @endphp
                    @if($classOutcomes)
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-primary text-white py-2">
                            <strong><i class="bi bi-building me-1"></i> {{ $class->class_name }}</strong>
                        </div>
                        <div class="card-body p-0">
                            @php $bySubject = $classOutcomes->groupBy('subject_id'); @endphp
                            @foreach($bySubject as $subjectId => $subjectOutcomes)
                            @php $subject = $subjectOutcomes->first()->subject; @endphp
                            <div class="border-bottom px-3 pt-2 pb-1">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <div class="fw-semibold text-secondary small text-uppercase">
                                        {{ $subject->name ?? '—' }}
                                        <small class="text-muted fw-normal text-lowercase ms-1">
                                            — {{ optional($subjectOutcomes->first()->teacher)->first_name }} {{ optional($subjectOutcomes->first()->teacher)->last_name }}
                                        </small>
                                    </div>
                                    @php $first = $subjectOutcomes->first(); @endphp
                                    <a href="{{ route('monthly-outcomes.print', ['teacher_id' => $first->teacher_id, 'class_id' => $first->class_id, 'subject_id' => $first->subject_id]) }}"
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
