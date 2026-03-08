@extends('layouts.app')
@section('title', 'Daily Collection Report')
@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-calendar-day me-2"></i>Daily Collection Report</h5>
                    <a href="?date={{ $date }}&pdf=1" class="btn btn-sm btn-light">
                        <i class="fas fa-download me-1"></i> Download PDF
                    </a>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Date</label>
                            <input type="date" name="date" value="{{ $date }}" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body text-center">
                    <h6 class="card-title">Total Collected</h6>
                    <h3>₹{{ number_format($total, 2) }}</h3>
                    <small>{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-primary">
                <div class="card-body text-center">
                    <h6 class="card-title">No. of Payments</h6>
                    <h3>{{ $payments->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-secondary">
                <div class="card-body text-center">
                    <h6 class="card-title">Cash / QR / Cheque</h6>
                    <h3>
                        {{ $payments->where('payment_mode','cash')->count() }} /
                        {{ $payments->where('payment_mode','qr')->count() }} /
                        {{ $payments->where('payment_mode','cheque')->count() }}
                    </h3>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            @if($payments->isEmpty())
                <p class="text-center text-muted py-4">No payments recorded for {{ \Carbon\Carbon::parse($date)->format('d M Y') }}.</p>
            @else
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Challan No</th>
                            <th>Student Name</th>
                            <th>Class / Div</th>
                            <th>Category</th>
                            <th>Mode</th>
                            <th>Items</th>
                            <th class="text-end">Amount ₹</th>
                            <th class="text-center">Challan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                        <tr>
                            <td><strong>{{ str_pad($payment->challan_no, 4, '0', STR_PAD_LEFT) }}</strong></td>
                            <td>{{ $payment->student->first_name ?? '—' }} {{ $payment->student->last_name ?? '' }}</td>
                            <td>
                                {{ optional(optional(optional($payment->student)->promotions)->first())->section->schoolClass->class_name ?? '—' }}
                                {{ optional(optional(optional($payment->student)->promotions)->first())->section->section_name ?? '' }}
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ $payment->is_internal_transfer ? 'COC/Internal' : ucfirst($payment->student->fee_category ?? 'general') }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $payment->payment_mode == 'cash' ? 'secondary' : ($payment->payment_mode == 'qr' ? 'info' : 'warning') }}">
                                    {{ strtoupper($payment->payment_mode) }}
                                </span>
                            </td>
                            <td>
                                @foreach($payment->lineItems as $item)
                                    <span class="badge bg-light text-dark border">{{ \App\Models\FeeLineItem::descriptionLabels()[$item->description] ?? $item->description }}</span>
                                @endforeach
                            </td>
                            <td class="text-end fw-bold">₹{{ number_format($payment->amount_paid, 2) }}</td>
                            <td class="text-center">
                                <a href="{{ route('fees.challan', $payment->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-success fw-bold">
                            <td colspan="6" class="text-end">Total Collected</td>
                            <td class="text-end">₹{{ number_format($total, 2) }}</td>
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
