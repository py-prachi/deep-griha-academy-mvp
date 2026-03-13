@extends('layouts.app')

@section('title', 'LC ' . $lc->lc_number)

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center">
                            <a href="{{ route('lc.index') }}" class="btn btn-sm btn-outline-secondary me-2">
                                <i class="bi bi-arrow-left"></i>
                            </a>
                            <h4 class="mb-0">Leaving Certificate — {{ $lc->lc_number }}</h4>
                        </div>
                        <a href="{{ route('lc.pdf', $lc->id) }}" class="btn btn-danger btn-sm" target="_blank">
                            <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF
                        </a>
                    </div>

                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('lc.index') }}">Leaving Certificates</a></li>
                            <li class="breadcrumb-item active">{{ $lc->lc_number }}</li>
                        </ol>
                    </nav>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($lc->fees_due > 0)
                        <div class="alert alert-warning mb-3">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            <strong>Note:</strong> At the time of issue, this student had
                            <strong>₹{{ number_format($lc->fees_due, 2) }}</strong> in outstanding fees.
                        </div>
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-light fw-semibold">Student Details</div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr><td class="text-muted" width="45%">Student Name</td><td><strong>{{ $lc->pupil_name ?? ($lc->admission ? $lc->admission->student_name : '—') }}</strong></td></tr>
                                        <tr>
                                            <td class="text-muted">Admission No.</td>
                                            <td>{{ $lc->admission ? ($lc->admission->dga_admission_no ?? $lc->admission->general_id ?? '—') : '—' }}</td>
                                        </tr>
                                        <tr><td class="text-muted">Date of Birth</td><td>{{ $lc->date_of_birth ? $lc->date_of_birth->format('d M Y') : '—' }}</td></tr>
                                        <tr><td class="text-muted">Mother's Name</td><td>{{ $lc->mother_name ?? '—' }}</td></tr>
                                        <tr><td class="text-muted">Father's Name</td><td>{{ ($lc->admission ? $lc->admission->father_name : '—') ?? '—' }}</td></tr>
                                        <tr><td class="text-muted">Caste</td><td>{{ $lc->race_and_caste ?? '—' }}</td></tr>
                                        <tr><td class="text-muted">Nationality</td><td>{{ $lc->nationality ?? '—' }}</td></tr>
                                        <tr><td class="text-muted">Place of Birth</td><td>{{ $lc->place_of_birth ?? '—' }}</td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-light fw-semibold">Certificate Details</div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr><td class="text-muted" width="45%">LC Number</td><td><strong>{{ $lc->lc_number }}</strong></td></tr>
                                        <tr><td class="text-muted">Issue Date</td><td>{{ $lc->issue_date ? $lc->issue_date->format('d M Y') : '—' }}</td></tr>
                                        <tr><td class="text-muted">Date of Admission</td><td>{{ $lc->date_of_admission ? $lc->date_of_admission->format('d M Y') : '—' }}</td></tr>
                                        <tr><td class="text-muted">Date of Leaving</td><td>{{ $lc->date_of_leaving ? $lc->date_of_leaving->format('d M Y') : '—' }}</td></tr>
                                        <tr><td class="text-muted">Last Class</td><td>{{ $lc->standard_studying ?? '—' }}</td></tr>
                                        <tr><td class="text-muted">Progress</td><td>{{ $lc->progress ?? '—' }}</td></tr>
                                        <tr><td class="text-muted">Conduct</td><td>{{ $lc->conduct }}</td></tr>
                                        <tr><td class="text-muted">Reason for Leaving</td><td>{{ $lc->reason_for_leaving ?? '—' }}</td></tr>
                                        <tr><td class="text-muted">Remarks</td><td>{{ $lc->remarks ?? '—' }}</td></tr>
                                        <tr>
                                            <td class="text-muted">Fees at Issue</td>
                                            <td>
                                                @if($lc->fees_due > 0)
                                                    <span class="text-warning fw-semibold">₹{{ number_format($lc->fees_due, 2) }} outstanding</span>
                                                @else
                                                    <span class="text-success">Cleared</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr><td class="text-muted">Issued On</td><td>{{ $lc->created_at ? $lc->created_at->format('d M Y, h:i A') : '—' }}</td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <a href="{{ route('admissions.show', $lc->admission_id) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-person me-1"></i> Back to Student Profile
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
