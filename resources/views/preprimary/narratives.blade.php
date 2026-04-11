@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h5 class="mb-1">
                        <i class="bi bi-chat-left-text me-1"></i>
                        Narrative Remarks &mdash; Pre-Primary
                    </h5>
                    <p class="text-muted small mb-2">
                        {{ $schoolClass->class_name ?? '' }} &mdash; {{ $section->section_name ?? '' }}
                        &nbsp;|&nbsp; Term {{ $term }}
                    </p>

                    @include('session-messages')

                    <div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
                        <div>
                            <a href="{{ route('preprimary.narratives', ['class_id' => $class_id, 'section_id' => $section_id, 'term' => 1]) }}"
                                class="btn btn-sm {{ $term == 1 ? 'btn-primary' : 'btn-outline-secondary' }}">Term 1</a>
                            <a href="{{ route('preprimary.narratives', ['class_id' => $class_id, 'section_id' => $section_id, 'term' => 2]) }}"
                                class="btn btn-sm {{ $term == 2 ? 'btn-primary' : 'btn-outline-secondary' }}">Term 2</a>
                        </div>
                        <a href="{{ route('preprimary.entry', ['class_id' => $class_id, 'section_id' => $section_id, 'term' => $term]) }}"
                            class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Back to Skill Entry
                        </a>
                    </div>

                    <form method="POST" action="{{ route('preprimary.saveNarratives') }}">
                        @csrf
                        <input type="hidden" name="class_id" value="{{ $class_id }}">
                        <input type="hidden" name="section_id" value="{{ $section_id }}">
                        <input type="hidden" name="term" value="{{ $term }}">

                        @forelse($promotions as $p)
                        @php $obs = $narratives->get($p->student_id); @endphp
                        <div class="card mb-3" style="max-width:720px;">
                            <div class="card-header py-2 small fw-semibold">
                                {{ $p->student->first_name ?? '' }} {{ $p->student->last_name ?? '' }}
                                @if($p->roll_number)
                                    <span class="text-muted fw-normal">&nbsp;— Roll #{{ $p->roll_number }}</span>
                                @endif
                            </div>
                            <div class="card-body row g-3 py-2">
                                <div class="col-12 col-md-4">
                                    <label class="form-label small mb-1">Does well in</label>
                                    <textarea name="does_well_in[{{ $p->student_id }}]"
                                        class="form-control form-control-sm"
                                        rows="3"
                                        placeholder="e.g. Singing, Art, Sharing with friends…">{{ $obs ? $obs->does_well_in : '' }}</textarea>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small mb-1">Needs to improve in</label>
                                    <textarea name="needs_improvement[{{ $p->student_id }}]"
                                        class="form-control form-control-sm"
                                        rows="3"
                                        placeholder="e.g. Listening, Sitting still…">{{ $obs ? $obs->needs_improvement : '' }}</textarea>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small mb-1">Observations / Remarks</label>
                                    <textarea name="remarks[{{ $p->student_id }}]"
                                        class="form-control form-control-sm"
                                        rows="3"
                                        placeholder="General remarks…">{{ $obs ? $obs->remarks : '' }}</textarea>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="alert alert-info">No students found in this class and section.</div>
                        @endforelse

                        <div class="mt-2 mb-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Save Remarks
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
