@extends('layouts.app')

@section('title', 'Leaving Certificates')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Leaving Certificates</h4>
        <a href="{{ route('lc.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i> Issue LC
        </a>
    </div>

    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('lc.index') }}" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Search by LC no or student name"
                           value="{{ $filters['search'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label form-label-sm mb-0">From</label>
                    <input type="date" name="from_date" class="form-control form-control-sm"
                           value="{{ $filters['from_date'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label form-label-sm mb-0">To</label>
                    <input type="date" name="to_date" class="form-control form-control-sm"
                           value="{{ $filters['to_date'] ?? '' }}">
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button class="btn btn-sm btn-outline-primary w-100">Search</button>
                    <a href="{{ route('lc.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            @if($lcs->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-file-earmark-text fs-1 d-block mb-2"></i>
                    No leaving certificates found.
                </div>
            @else
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>LC No.</th>
                            <th>Student</th>
                            <th>Admission No.</th>
                            <th>Class</th>
                            <th>Issue Date</th>
                            <th>Date of Leaving</th>
                            <th>Fees</th>
                            <th>Issued By</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lcs as $lc)
                        <tr>
                            <td><strong>{{ $lc->lc_number }}</strong></td>
                            <td>{{ $lc->pupil_name ?? ($lc->admission ? $lc->admission->student_name : '—') ?? '—' }}</td>
                            <td>{{ ($lc->admission ? $lc->admission->dga_admission_no : '—') ?? '—' }}</td>
                            <td>{{ $lc->standard_studying ?? '—' }}</td>
                            <td>{{ $lc->issue_date ? $lc->issue_date->format('d M Y') : '—' }}</td>
                            <td>{{ $lc->date_of_leaving ? $lc->date_of_leaving->format('d M Y') : '—' }}</td>
                            <td>
                                @if($lc->fees_due > 0)
                                    <span class="badge bg-warning text-dark">⚠ ₹{{ number_format($lc->fees_due, 0) }} due</span>
                                @else
                                    <span class="badge bg-success">Cleared</span>
                                @endif
                            </td>
                            <td>{{ ($lc->issuedBy ? $lc->issuedBy->name : '—') ?? '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('lc.show', $lc->id) }}" class="btn btn-sm btn-outline-secondary me-1">View</a>
                                <a href="{{ route('lc.pdf', $lc->id) }}" class="btn btn-sm btn-outline-danger" target="_blank">
                                    <i class="bi bi-file-earmark-pdf"></i> PDF
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-3 py-2">{{ $lcs->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
