@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <h5 class="mb-0"><i class="bi bi-chat-left-text me-1"></i> Descriptive Remarks — Term {{ $term }}</h5>
                        <a href="{{ route('marks.review', ['class_id'=>$class_id,'section_id'=>$section_id]) }}"
                            class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Back
                        </a>
                    </div>
                    <p class="text-muted small mb-3">
                        {{ $schoolClass->class_name ?? '' }} — {{ $section->section_name ?? '' }}
                    </p>
                    @include('session-messages')

                    <form method="POST" action="{{ route('marks.saveObservation') }}">
                        @csrf
                        <input type="hidden" name="class_id" value="{{ $class_id }}">
                        <input type="hidden" name="section_id" value="{{ $section_id }}">
                        <input type="hidden" name="term" value="{{ $term }}">

                        <div style="max-width:680px;">
                            @forelse($promotions->sortBy('roll_number') as $p)
                            @php
                                $obs = $existing->get($p->student_id);
                                $studentName = ($p->student->first_name ?? '') . ' ' . ($p->student->last_name ?? '');
                            @endphp
                            <div class="mb-3">
                                <label class="form-label small fw-semibold mb-1">
                                    @if($p->roll_number) {{ $p->roll_number }}. @endif
                                    {{ $studentName }}
                                </label>
                                <textarea name="remarks[{{ $p->student_id }}]"
                                    class="form-control form-control-sm"
                                    rows="2"
                                    placeholder="Write descriptive remarks for this student...">{{ $obs ? $obs->remarks : '' }}</textarea>
                            </div>
                            @empty
                                <p class="text-muted">No students found.</p>
                            @endforelse

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>Save Remarks
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
