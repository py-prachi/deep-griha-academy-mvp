@extends('layouts.app')
@section('title', 'Enter Diagnostic Results')
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
                            </p>
                        </div>
                    </div>

                    @include('session-messages')

                    {{-- Assessment type switcher --}}
                    <div class="mb-3 d-flex flex-wrap gap-1">
                        @foreach(\App\Models\DiagnosticResult::ASSESSMENT_TYPES as $typeKey => $typeLabel)
                        <a href="{{ route('diagnostics.entry', [
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

                    @if($promotions->isEmpty())
                        <div class="alert alert-warning">No students found for this class and section.</div>
                    @else
                    <form method="POST" action="{{ route('diagnostics.save') }}">
                        @csrf
                        <input type="hidden" name="class_id"        value="{{ $class->id }}">
                        <input type="hidden" name="section_id"      value="{{ $section->id }}">
                        <input type="hidden" name="subject_id"      value="{{ $subject->id }}">
                        <input type="hidden" name="assessment_type" value="{{ $assessmentType }}">

                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle" style="font-size:0.88rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:45px;" class="text-center">Roll</th>
                                        <th>Student Name</th>
                                        <th style="width:130px;">Marks Obtained</th>
                                        @if($assessmentType === 'diagnostic')
                                        <th style="width:220px;">Needs <span class="text-muted fw-normal">(what the student needs to work on)</span></th>
                                        @endif
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($promotions as $promotion)
                                    @php
                                        $studentId = $promotion->student_id;
                                        $result    = $existing->get($studentId);
                                    @endphp
                                    <tr>
                                        <td class="text-center text-muted">{{ $promotion->roll_number ?? '—' }}</td>
                                        <td>
                                            {{ optional($promotion->student)->first_name }}
                                            {{ optional($promotion->student)->last_name }}
                                        </td>
                                        <td>
                                            <input type="text"
                                                   name="results[{{ $studentId }}][marks_obtained]"
                                                   class="form-control form-control-sm"
                                                   value="{{ old('results.'.$studentId.'.marks_obtained', $result->marks_obtained ?? '') }}"
                                                   placeholder="e.g. 18/25">
                                        </td>
                                        @if($assessmentType === 'diagnostic')
                                        <td>
                                            <input type="text"
                                                   name="results[{{ $studentId }}][needs]"
                                                   class="form-control form-control-sm"
                                                   value="{{ old('results.'.$studentId.'.needs', $result->needs ?? '') }}"
                                                   placeholder="e.g. Reading fluency">
                                        </td>
                                        @else
                                        <input type="hidden" name="results[{{ $studentId }}][needs]" value="">
                                        @endif
                                        <td>
                                            <input type="text"
                                                   name="results[{{ $studentId }}][notes]"
                                                   class="form-control form-control-sm"
                                                   value="{{ old('results.'.$studentId.'.notes', $result->notes ?? '') }}"
                                                   placeholder="Optional notes">
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check2 me-1"></i> Save Results
                            </button>
                            <a href="{{ route('diagnostics.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                    @endif

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
