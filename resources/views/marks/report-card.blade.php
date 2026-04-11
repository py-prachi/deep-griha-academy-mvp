@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h5 class="mb-1"><i class="bi bi-file-earmark-text me-1"></i> My Marks</h5>

                    @if(!empty($error))
                        <div class="alert alert-warning mt-3">{{ $error }}</div>
                    @else

                    <p class="text-muted small mb-3">
                        {{ $schoolClass->class_name ?? '' }}
                        @if($promotion->section) — {{ $promotion->section->section_name }} @endif
                        @if($promotion->roll_number) &nbsp;| Roll No. {{ $promotion->roll_number }} @endif
                    </p>

                    @php
                        $marksSubjects = $subjects->where('mark_type', 'marks');
                        $gradeSubjects = $subjects->where('mark_type', 'grade_only');
                        $anyPublished = !empty($publishedTerms);
                    @endphp

                    @if(!$anyPublished)
                        <div class="alert alert-info" style="max-width:600px;">
                            <i class="bi bi-clock me-1"></i>
                            Your report cards will appear here once published by your school.
                        </div>
                    @else

                    @foreach([1, 2] as $term)
                    @if(!in_array($term, $publishedTerms))
                        {{-- Not yet published — skip silently --}}
                    @else
                    <div class="card mb-4" style="max-width:760px;">
                        <div class="card-header py-2 fw-semibold">Term {{ $term }}</div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-bordered mb-0" style="font-size:0.83rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th style="min-width:140px;">Subject</th>
                                        <th class="text-center">Oral<br><small class="text-muted">/{{ $config['oral_internal'] }}</small></th>
                                        <th class="text-center">Act<br><small class="text-muted">/{{ $config['activity_internal'] }}</small></th>
                                        <th class="text-center">Test<br><small class="text-muted">/{{ $config['test'] }}</small></th>
                                        <th class="text-center">HW<br><small class="text-muted">/{{ $config['hw'] }}</small></th>
                                        <th class="text-center table-warning">Int<br><small class="text-muted">/{{ $config['internal_max'] }}</small></th>
                                        <th class="text-center">Oral<br><small class="text-muted">/{{ $config['oral_written'] }}</small></th>
                                        <th class="text-center">Act<br><small class="text-muted">/{{ $config['activity_written'] }}</small></th>
                                        <th class="text-center">Writing<br><small class="text-muted">/{{ $config['writing'] }}</small></th>
                                        <th class="text-center table-warning">Writ<br><small class="text-muted">/{{ $config['written_max'] }}</small></th>
                                        <th class="text-center table-success">Total<br><small class="text-muted">/100</small></th>
                                        <th class="text-center table-success">Grade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($marksSubjects as $subject)
                                    @php $rows = $marks->get($subject->id); $m = $rows ? $rows->firstWhere('term', $term) : null; @endphp
                                    <tr>
                                        <td>{{ $subject->name }}</td>
                                        @if($m)
                                            @foreach(['oral_internal','activity_internal','test','hw'] as $comp)
                                            <td class="text-center">
                                                @if($m->isComponentAbsent($comp)) <span class="badge bg-warning text-dark" style="font-size:0.65rem;">AB</span>
                                                @elseif($m->{$comp} !== null) {{ $m->{$comp} }}
                                                @else <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            @endforeach
                                            <td class="text-center table-warning fw-bold">{{ $m->internal_total ?? '—' }}</td>
                                            @foreach(['oral_written','activity_written','writing'] as $comp)
                                            <td class="text-center">
                                                @if($m->isComponentAbsent($comp)) <span class="badge bg-warning text-dark" style="font-size:0.65rem;">AB</span>
                                                @elseif($m->{$comp} !== null) {{ $m->{$comp} }}
                                                @else <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            @endforeach
                                            <td class="text-center table-warning fw-bold">{{ $m->written_total ?? '—' }}</td>
                                            <td class="text-center table-success fw-bold">{{ $m->grand_total ?? '—' }}</td>
                                            <td class="text-center table-success fw-bold">{{ $m->grade ?? '—' }}</td>
                                        @else
                                            <td colspan="11" class="text-center text-muted">Not entered</td>
                                        @endif
                                    </tr>
                                    @empty
                                    @endforelse

                                    @if($gradeSubjects->isNotEmpty())
                                    <tr class="table-light">
                                        <td colspan="12" class="small text-muted py-1 ps-2">Grade-only subjects</td>
                                    </tr>
                                    @foreach($gradeSubjects as $subject)
                                    @php $rows = $marks->get($subject->id); $m = $rows ? $rows->firstWhere('term', $term) : null; @endphp
                                    <tr>
                                        <td>{{ $subject->name }}</td>
                                        <td colspan="10" class="text-center text-muted small">—</td>
                                        <td class="text-center table-success fw-bold">
                                            @if($m) {{ $m->grade ?? '—' }} @else <span class="text-muted">—</span> @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        {{-- Remarks --}}
                        @php $obs = $observations->get($term); @endphp
                        @if($obs && $obs->remarks)
                        <div class="card-footer bg-light py-2 px-3">
                            <p class="small fw-semibold mb-1">Teacher's Remarks</p>
                            <p class="small mb-0" style="white-space:pre-wrap;">{{ $obs->remarks }}</p>
                        </div>
                        @endif
                    </div>
                    @endif
                    @endforeach

                    @endif {{-- anyPublished --}}

                    @endif {{-- error --}}
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
