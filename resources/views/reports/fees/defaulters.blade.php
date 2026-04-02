@extends('layouts.app')
@section('title', 'Fee Defaulters Report')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <div class="d-flex align-items-center mb-3">
                        <a href="{{ route('home') }}" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <h4 class="mb-0">Fee Defaulters Report</h4>
                    </div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item active">Fee Defaulters</li>
                        </ol>
                    </nav>

                    <div class="container-fluid px-0">
                        <form method="GET" action="{{ route('reports.fees.defaulters') }}" class="mb-3">
                            <div class="row g-2 align-items-center">
                                <div class="col-auto">
                                    <label class="form-label mb-0">Academic Year:</label>
                                </div>
                                <div class="col-auto">
                                    <select name="session_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                        @foreach($sessions as $s)
                                            <option value="{{ $s->id }}" {{ $s->id == $selectedSessionId ? 'selected' : '' }}>{{ $s->session_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @if($selectedSession)
                                <div class="col-auto">
                                    <span class="text-muted small">Showing: <strong>{{ $selectedSession->session_name }}</strong></span>
                                </div>
                                @endif
                            </div>
                        </form>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="card text-white bg-danger">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Total Defaulters</h6>
                                        <h3>{{ count($defaulters) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-white bg-warning">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Total Outstanding</h6>
                                        <h3>₹{{ number_format(collect($defaulters)->sum('balance'), 2) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-white bg-secondary">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Avg. Balance Due</h6>
                                        <h3>₹{{ count($defaulters) ? number_format(collect($defaulters)->avg('balance'), 2) : '0.00' }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2 text-warning"></i>Defaulters List</h5>
                                <a href="?pdf=1" class="btn btn-sm btn-light"><i class="fas fa-download me-1"></i> Download PDF</a>
                            </div>
                            <div class="card-body">
                                @if(empty($defaulters))
                                    <p class="text-center text-success py-4"><i class="fas fa-check-circle fa-2x mb-2"></i><br>No defaulters! All students are up to date.</p>
                                @else
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Student Name</th>
                                                <th>Class / Div</th>
                                                <th>Category</th>
                                                <th>Admission No</th>
                                                <th class="text-end">Total Due ₹</th>
                                                <th class="text-end">Total Paid ₹</th>
                                                <th class="text-end">Balance ₹</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($defaulters as $d)
                                            <tr>
                                                <td>{{ $d->first_name }} {{ $d->last_name }}</td>
                                                <td>{{ $d->class_name ?? '—' }} {{ $d->section_name ?? '' }}</td>
                                                <td><span class="badge bg-secondary">{{ strtoupper($d->fee_category ?? 'general') }}</span></td>
                                                <td>{{ $d->dga_admission_no ?? $d->general_id ?? '—' }}</td>
                                                <td class="text-end">₹{{ number_format($d->total_due, 2) }}</td>
                                                <td class="text-end">₹{{ number_format($d->total_paid, 2) }}</td>
                                                <td class="text-end fw-bold text-danger">₹{{ number_format($d->balance, 2) }}</td>
                                                <td class="text-center">
                                                    <a href="{{ route('fees.ledger', $d->student_id) }}" class="btn btn-sm btn-outline-primary">Ledger</a>
                                                    <a href="{{ route('fees.create', $d->student_id) }}" class="btn btn-sm btn-outline-success">Pay</a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-danger fw-bold">
                                                <td colspan="6" class="text-end">Total Outstanding</td>
                                                <td class="text-end">₹{{ number_format(collect($defaulters)->sum('balance'), 2) }}</td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
