@extends('layouts.master')
@section('title', 'Challan #{{ str_pad($payment->challan_no, 4, "0", STR_PAD_LEFT) }}')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Challan #{{ str_pad($payment->challan_no, 4, '0', STR_PAD_LEFT) }}</h4>
                <div class="d-flex gap-2">
                    <a href="{{ route('fees.challan.pdf', $payment->id) }}" class="btn btn-primary">
                        <i class="fas fa-download"></i> Download PDF
                    </a>
                    <a href="{{ route('fees.ledger', $student->id) }}" class="btn btn-secondary">Back to Ledger</a>
                </div>
            </div>

            {{-- 3-copy challan display --}}
            @foreach(['School Copy', 'Student Copy', 'Bank Copy'] as $copy)
            <div class="card mb-3 border-dark">
                <div class="card-header bg-dark text-white d-flex justify-content-between">
                    <span><strong>DEEP GRIHA SOCIETY (Deep Griha Academy)</strong></span>
                    <span class="badge bg-light text-dark">{{ $copy }}</span>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <small class="text-muted">Bank of Maharashtra, Kedgaon Branch</small><br>
                            <small class="text-muted">A/c No: 60228514625</small><br>
                            <small class="text-muted">A/p. Deulgaon gada Tal. Daund, Dist. Pune</small>
                        </div>
                        <div class="col-md-6 text-end">
                            <strong>Challan No: {{ str_pad($payment->challan_no, 4, '0', STR_PAD_LEFT) }}</strong><br>
                            <strong>Date: {{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</strong>
                        </div>
                    </div>
                    <hr class="my-2">
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <strong>Name:</strong> {{ $student->first_name }} {{ $student->last_name }}<br>
                            <strong>Class:</strong> {{ $promotion->section->schoolClass->class_name ?? '—' }}
                            &nbsp; <strong>Div:</strong> {{ $promotion->section->section_name ?? '—' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Mode:</strong>
                            @if($payment->payment_mode == 'cheque')
                                Cheque No: {{ $payment->cheque_no }} | {{ $payment->bank_name }}
                            @elseif($payment->payment_mode == 'qr')
                                QR/UPI | Ref: {{ $payment->transaction_ref ?? '—' }}
                            @else
                                Cash
                            @endif
                            @if($payment->is_internal_transfer)
                                <span class="badge bg-dark ms-1">DGA Internal Transfer</span>
                            @endif
                        </div>
                    </div>
                    <table class="table table-bordered table-sm mt-2">
                        <thead class="table-secondary">
                            <tr>
                                <th>Sr.</th>
                                <th>Description</th>
                                <th class="text-end">Amount ₹</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payment->lineItems as $i => $item)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ \App\Models\FeeLineItem::descriptionLabels()[$item->description] ?? $item->description }}</td>
                                <td class="text-end">{{ number_format($item->amount, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td>1</td>
                                <td>Fee Payment</td>
                                <td class="text-end">{{ number_format($payment->amount_paid, 2) }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="table-dark">
                                <td colspan="2" class="text-end"><strong>Total ₹</strong></td>
                                <td class="text-end"><strong>{{ number_format($payment->amount_paid, 2) }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                    @if($payment->notes)
                        <small class="text-muted">Notes: {{ $payment->notes }}</small>
                    @endif
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <small>Signature of School Clerk</small>
                            <div style="height:40px;border-bottom:1px solid #000;width:80%"></div>
                        </div>
                        <div class="col-md-6 text-end">
                            <small>Depositor's Signature</small>
                            <div style="height:40px;border-bottom:1px solid #000;width:80%;margin-left:auto"></div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach

        </div>
    </div>
</div>
@endsection
