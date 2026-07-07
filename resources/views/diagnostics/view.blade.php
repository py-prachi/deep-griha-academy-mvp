@extends('layouts.app')
@section('title', 'Diagnostic Results — View')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4 pe-4">

                    <div class="d-flex align-items-center mb-2">
                        <a href="{{ route('diagnostics.index') }}" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <div>
                            <h4 class="mb-0">{{ \App\Models\DiagnosticResult::ASSESSMENT_TYPES[$assessmentType] }}</h4>
                            <p class="text-muted small mb-0">
                                {{ $class->class_name }} {{ $section->section_name }}
                                &nbsp;·&nbsp; {{ $subject->name }}
                                <span class="text-muted ms-1">(view only)</span>
                            </p>
                        </div>
                    </div>

                    {{-- Assessment type switcher --}}
                    <div class="mb-2 d-flex flex-wrap gap-1">
                        @foreach(\App\Models\DiagnosticResult::ASSESSMENT_TYPES as $typeKey => $typeLabel)
                        <a href="{{ route('diagnostics.view', [
                                'class_id'        => $class->id,
                                'section_id'      => $section->id,
                                'subject_id'      => $subject->id,
                                'assessment_type' => $typeKey,
                            ]) }}"
                           class="btn btn-sm {{ $typeKey === $assessmentType ? 'btn-primary' : 'btn-outline-secondary' }}">
                            {{ $typeLabel }}
                        </a>
                        @endforeach
                    </div>

                    {{-- Subject switcher --}}
                    @if($subjects->count() > 1)
                    <div class="mb-3 d-flex flex-wrap gap-1 border-top pt-2">
                        <span class="text-muted small me-1 align-self-center">Subject:</span>
                        @foreach($subjects as $s)
                        <a href="{{ route('diagnostics.view', [
                                'class_id'        => $class->id,
                                'section_id'      => $section->id,
                                'subject_id'      => $s->id,
                                'assessment_type' => $assessmentType,
                            ]) }}"
                           class="btn btn-sm {{ $s->id == $subject->id ? 'btn-dark' : 'btn-outline-dark' }}">
                            {{ $s->name }}
                        </a>
                        @endforeach
                    </div>
                    @endif

                    @if($promotions->isEmpty())
                        <div class="alert alert-light text-muted">No students found for this class and section.</div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle" style="font-size:0.88rem;">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:45px;" class="text-center">Roll</th>
                                    <th>Student Name</th>
                                    <th style="width:130px;">Marks Obtained</th>
                                    @if($assessmentType === 'diagnostic')
                                    <th>Needs</th>
                                    @endif
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($promotions as $promotion)
                                @php $result = $results->get($promotion->student_id); @endphp
                                <tr class="{{ !$result ? 'text-muted' : '' }}">
                                    <td class="text-center">{{ $promotion->roll_number ?? '—' }}</td>
                                    <td>
                                        {{ optional($promotion->student)->first_name }}
                                        {{ optional($promotion->student)->last_name }}
                                    </td>
                                    <td>{{ $result->marks_obtained ?? '—' }}</td>
                                    @if($assessmentType === 'diagnostic')
                                    <td>{{ $result->needs ?? '—' }}</td>
                                    @endif
                                    <td class="text-muted small">{{ $result->notes ?? '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
