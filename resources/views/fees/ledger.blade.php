@extends('layouts.master')
@section('title', 'Fee Ledger')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">

            {{-- Student Info Card --}}
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-1">{{ $student->first_name }} {{ $student->last_name }}</h4>
                            <p class="mb-0 text-muted">
                                {{ $promotion->section->schoolClass->class_name ?? '—' }}
                                {{ $promotion->section->section_name ?? '' }} &nbsp;|&nbsp;
                                Category: <strong>{{ ucfirst($student->fee_category ?? 'general') }}</strong> &nbsp;|&nbsp;
                                Admission No: <strong>{{ $student->dga_admission_no ?? $student->general_id ?? '—' }}</strong>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="{{ route('fees.create', $student->id) }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Record Payment
                            </a>
                            <a href="{{ route('students.profile', $student->id) }}" class="btn btn-outline-secondary ms-1">
                                Back to Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Fee Summary Cards --}}
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="card text-white bg-primary">
                        <div class="card-body text-center">
                            <h6 class="card-title">Total Due</h6>
                            <h3>₹{{ number_format($totalDue, 2) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-success">
                        <div class="card-body text-center">
                            <h6 class="card-title">Total Paid</h6>
                            <h3>₹{{ number_format($totalPaid, 2) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white {{ $balance > 0 ? 'bg-danger' : 'bg-success' }}">
                        <div class="card-body text-center">
                            <h6 class="card-title">Balance Remaining</h6>
                            <h3>₹{{ number_format($balance, 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Fee Structure Breakdown --}}
            @if($feeStructure)
            <div class="card mb-3">
                <div class="card-header"><strong>Fee Structure Breakdown</strong></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center border-end">
                            <div class="text-muted small">Admission Fee</div>
                            <div class="fw-bold">₹{{ number_format($feeStructure->admission_fee, 2) }}</div>
                        </div>
                        <div class="col-md-3 text-center border-end">
                            <div class="text-muted small">Tuition Fee</div>
                            <div class="fw-bold">₹{{ number_format($feeStructure->tuition_fee, 2) }}</div>
                        </div>
                        <div class="col-md-3 text-center border-end">
                            <div class="text-muted small">Transport Fee</div>
                            <div class="fw-bold">₹{{ number_format($feeStructure->transport_fee, 2) }}</div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="text-muted small">Other Fee</div>
                            <div class="fw-bold">₹{{ number_format($feeStructure->other_fee, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div class="alert alert-warning">
                No fee structure found for this student's class and category. Please <a href="{{ route('fee-structures.create') }}">set up a fee structure</a> first.
            </div>
            @endif

            {{-- Payment History --}}
            <div class="card">
                <div class="card-header"><strong>Payment History</strong></div>
                <div class="card-body">
                    @if($payments->isEmpty())
                        <p class="text-muted text-center py-3">No payments recorded yet.</p>
                    @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Challan No</th>
                                    <th>Date</th>
                                    <th>Mode</th>
                                    <th>Items Paid For</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payments as $payment)
                                <tr>
                                    <td><strong>{{ str_pad($payment->challan_no, 4, '0', STR_PAD_LEFT) }}</strong></td>
                                    <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $payment->payment_mode == 'cash' ? 'secondary' : ($payment->payment_mode == 'qr' ? 'info' : 'warning') }}">
                                            {{ strtoupper($payment->payment_mode) }}
                                        </span>
                                        @if($payment->is_internal_transfer)
                                            <span class="badge bg-dark">Internal Transfer</span>
                                        @endif
                                    </td>
                                    <td>
                                        @foreach($payment->lineItems as $item)
                                            <span class="badge bg-light text-dark">{{ \App\Models\FeeLineItem::descriptionLabels()[$item->description] ?? $item->description }}</span>
                                        @endforeach
                                    </td>
                                    <td class="text-end fw-bold">₹{{ number_format($payment->amount_paid, 2) }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('fees.challan', $payment->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                                        <a href="{{ route('fees.challan.pdf', $payment->id) }}" class="btn btn-sm btn-outline-secondary">PDF</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-success">
                                    <td colspan="4" class="text-end fw-bold">Total Paid</td>
                                    <td class="text-end fw-bold">₹{{ number_format($totalPaid, 2) }}</td>
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
@endsection
