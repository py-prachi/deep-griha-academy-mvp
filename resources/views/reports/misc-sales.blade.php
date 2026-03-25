@extends('layouts.app')
@section('title', 'Misc Sales Report')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0">Misc Sales Report</h4>
                    </div>

                    {{-- Date Range Filter --}}
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" action="{{ route('reports.miscSales') }}" class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">From</label>
                                    <input type="date" name="from" value="{{ $from }}" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">To</label>
                                    <input type="date" name="to" value="{{ $to }}" class="form-control">
                                </div>
                                <div class="col-md-4 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                    <a href="{{ route('reports.miscSales', array_merge(request()->all(), ['pdf'=>1])) }}"
                                        class="btn btn-outline-danger">
                                        <i class="bi bi-file-pdf"></i> PDF
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Summary by Item --}}
                    @if(count($summary) > 0)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Summary by Item</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item</th>
                                        <th class="text-center">No. of Transactions</th>
                                        <th class="text-end">Total Amount (₹)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($summary as $key => $row)
                                    <tr>
                                        <td>{{ $row['label'] }}</td>
                                        <td class="text-center">{{ $row['count'] }}</td>
                                        <td class="text-end">{{ number_format($row['total'], 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-warning fw-bold">
                                    <tr>
                                        <td colspan="2">Grand Total</td>
                                        <td class="text-end">₹{{ number_format($grandTotal, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    @endif

                    {{-- Transaction Detail --}}
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Transaction Detail
                                <span class="text-muted fw-normal">
                                    {{ \Carbon\Carbon::parse($from)->format('d M Y') }}
                                    to
                                    {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
                                </span>
                            </h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Challan No</th>
                                        <th>Date</th>
                                        <th>Student</th>
                                        <th>Class</th>
                                        <th>Items</th>
                                        <th>Mode</th>
                                        <th class="text-end">Amount (₹)</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($payments as $payment)
                                    <tr>
                                        <td>{{ str_pad($payment->challan_no, 4, '0', STR_PAD_LEFT) }}</td>
                                        <td>{{ $payment->payment_date->format('d M Y') }}</td>
                                        <td>
                                            {{ $payment->student ? $payment->student->first_name . ' ' . $payment->student->last_name : '—' }}
                                        </td>
                                        <td>
                                            @if($payment->student && $payment->student->admission)
                                                {{ $payment->student->admission->class_name ?? '—' }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            @if($payment->lineItems->count())
                                                @foreach($payment->lineItems as $item)
                                                    <span class="badge bg-warning text-dark me-1">
                                                        {{ \App\Models\FeeLineItem::descriptionLabels()[$item->description] ?? $item->description }}
                                                        @if($item->amount > 0)
                                                            — ₹{{ number_format($item->amount, 2) }}
                                                        @endif
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>{{ ucfirst($payment->payment_mode) }}</td>
                                        <td class="text-end fw-bold">{{ number_format($payment->amount_paid, 2) }}</td>
                                        <td>
                                            <a href="{{ route('fees.challan', $payment->id) }}"
                                                class="btn btn-sm btn-outline-secondary">Challan</a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            No misc sales found for this period.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
