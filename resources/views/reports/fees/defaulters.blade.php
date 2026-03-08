@extends('layouts.app')
@section('title', 'Fee Defaulters Report')
@section('content')
<div class="container-fluid">
    <div class="card mb-3">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2 text-warning"></i>Fee Defaulters Report</h5>
            <a href="?pdf=1" class="btn btn-sm btn-light"><i class="fas fa-download me-1"></i> Download PDF</a>
        </div>
    </div>
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
        <div class="card-body">
            @if(empty($defaulters))
                <p class="text-center text-success py-4"><i class="fas fa-check-circle fa-2x mb-2"></i><br>No defaulters! All students are up to date.</p>
            @else
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Student Name</th><th>Class / Div</th><th>Category</th><th>Admission No</th><th class="text-end">Total Due ₹</th><th class="text-end">Total Paid ₹</th><th class="text-end">Balance ₹</th><th class="text-center">Action</th>
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
@endsection
