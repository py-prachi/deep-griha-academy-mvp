@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    @php $admission = $exit->admission; @endphp

                    <h1 class="display-6 mb-3">
                        <i class="bi bi-box-arrow-right"></i> Exit Record
                        — {{ $admission ? $admission->student_name : '—' }}
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('exits.index') }}">Exited Students</a></li>
                            <li class="breadcrumb-item active">Exit Record</li>
                        </ol>
                    </nav>

                    @include('session-messages')

                    {{-- LC PROMPT BANNER --}}
                    @if($admission)
                        @if($lc)
                            <div class="alert alert-success d-flex align-items-center justify-content-between py-2 mb-3">
                                <span>
                                    <i class="bi bi-file-earmark-check-fill me-1"></i>
                                    Leaving Certificate <strong>{{ $lc->lc_number }}</strong> has been issued for this student.
                                </span>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('lc.show', $lc->id) }}" class="btn btn-sm btn-outline-success">View LC</a>
                                    <a href="{{ route('lc.pdf', $lc->id) }}" class="btn btn-sm btn-success" target="_blank">Download PDF</a>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning d-flex align-items-center justify-content-between py-2 mb-3">
                                <span>
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                    No Leaving Certificate has been issued yet for this student.
                                </span>
                                <a href="{{ route('lc.create', ['admission_id' => $admission->id]) }}"
                                   class="btn btn-sm btn-warning">
                                    <i class="bi bi-file-earmark-plus"></i> Issue Leaving Certificate
                                </a>
                            </div>
                        @endif
                    @endif

                    <div class="row">
                        <div class="col-md-5">
                            <div class="card shadow-sm mb-3">
                                <div class="card-header bg-light fw-semibold">Student Info</div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr><th>Name</th><td>{{ $admission ? $admission->student_name : '—' }}</td></tr>
                                        <tr><th>Class</th><td>{{ $admission && $admission->schoolClass ? $admission->schoolClass->class_name : '—' }}</td></tr>
                                        <tr><th>Section</th><td>{{ $admission && $admission->section ? $admission->section->section_name : '—' }}</td></tr>
                                        <tr><th>Admission No</th><td>
                                            @if($admission)
                                                {{ $admission->dga_admission_no ? $admission->dga_admission_no : ($admission->general_id ? $admission->general_id : '—') }}
                                            @else
                                                —
                                            @endif
                                        </td></tr>
                                        <tr><th>Date of Admission</th><td>{{ $admission && $admission->confirmed_date ? $admission->confirmed_date->format('d/m/Y') : '—' }}</td></tr>
                                        <tr><th>Date of Leaving</th><td>{{ $exit->exit_date ? $exit->exit_date->format('d/m/Y') : '—' }}</td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-7">
                            <div class="card shadow-sm mb-3">
                                <div class="card-header bg-light fw-semibold">Exit Form Details</div>
                                <div class="card-body">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-5">Reason for Leaving</dt>
                                        <dd class="col-sm-7">{{ $exit->reason_for_leaving ?? '—' }}</dd>

                                        <dt class="col-sm-5">Liked Most</dt>
                                        <dd class="col-sm-7">{{ $exit->liked_most ?? '—' }}</dd>

                                        <dt class="col-sm-5">Liked Least</dt>
                                        <dd class="col-sm-7">{{ $exit->liked_least ?? '—' }}</dd>

                                        <dt class="col-sm-5">Suggestions</dt>
                                        <dd class="col-sm-7">{{ $exit->suggestions ?? '—' }}</dd>

                                        <dt class="col-sm-5">Rating</dt>
                                        <dd class="col-sm-7">
                                            @if($exit->rating)
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="bi bi-star{{ $i <= $exit->rating ? '-fill text-warning' : '' }}"></i>
                                                @endfor
                                                ({{ $exit->rating }}/5)
                                            @else
                                                —
                                            @endif
                                        </dd>

                                        <dt class="col-sm-5">Parent / Guardian</dt>
                                        <dd class="col-sm-7">{{ $exit->parent_name ?? '—' }}</dd>

                                        <dt class="col-sm-5">Parent Contact</dt>
                                        <dd class="col-sm-7">{{ $exit->parent_contact ?? '—' }}</dd>

                                        <dt class="col-sm-5">DGA Staff</dt>
                                        <dd class="col-sm-7">{{ $exit->staff_name ?? '—' }}</dd>

                                        <dt class="col-sm-5">Form Submitted</dt>
                                        <dd class="col-sm-7">{{ $exit->form_submitted_at ? $exit->form_submitted_at->format('d/m/Y') : '—' }}</dd>
                                    </dl>
                                </div>
                            </div>

                            <a href="{{ route('exits.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
