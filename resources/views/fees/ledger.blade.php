@extends('layouts.app')
@section('title', 'Fee Ledger')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">

                    @php
                        $referer  = request()->headers->get('referer', '');
                        $backUrl  = url()->previous();
                        $backLabel = 'Back';
                        if (str_contains($referer, 'defaulters')) {
                            $backLabel = 'Back to Defaulters';
                            $backUrl   = route('reports.fees.defaulters');
                        } elseif (str_contains($referer, 'profile')) {
                            $backLabel = 'Back to Profile';
                            $backUrl   = route('student.profile.show', $student->id);
                        } elseif (str_contains($referer, 'fees/create') || str_contains($referer, 'fees/store')) {
                            $backLabel = 'Back to Defaulters';
                            $backUrl   = route('reports.fees.defaulters');
                        }
                    @endphp

                    <div class="d-flex align-items-center mb-3">
                        <a href="{{ $backUrl }}" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <h4 class="mb-0">Fee Ledger — {{ $student->first_name }} {{ $student->last_name }}</h4>
                    </div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            @if(str_contains($referer, 'defaulters'))
                                <li class="breadcrumb-item"><a href="{{ route('reports.fees.defaulters') }}">Defaulters</a></li>
                            @endif
                            <li class="breadcrumb-item active">Fee Ledger</li>
                        </ol>
                    </nav>

                    <div class="container-fluid px-0">

                        {{-- Student Info Card --}}
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h5 class="mb-1">{{ $student->first_name }} {{ $student->last_name }}</h5>
                                        <p class="mb-0 text-muted">
                                            @if($promotion && $promotion->section)
                                                {{ $promotion->section->schoolClass->class_name ?? '—' }}
                                                {{ $promotion->section->section_name ?? '' }}
                                            @elseif($student->admission && $student->admission->schoolClass)
                                                {{ $student->admission->schoolClass->class_name }}
                                                {{ $student->admission->section ? $student->admission->section->section_name : '' }}
                                            @else
                                                —
                                            @endif
                                            &nbsp;|&nbsp;
                                            Category: <strong>{{ ucfirst($student->fee_category ?? 'general') }}</strong> &nbsp;|&nbsp;
                                            Admission No: <strong>{{ optional($student->admission)->dga_admission_no ?? optional($student->admission)->general_id ?? $student->dga_admission_no ?? $student->general_id ?? '—' }}</strong>
                                        </p>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <a href="{{ route('fees.create', $student->id) }}" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Record Payment
                                        </a>
                                        <a href="{{ $backUrl }}" class="btn btn-outline-secondary ms-1">
                                            {{ $backLabel }}
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
                                @if(($isBulkGovtSettled ?? false) && $balance > 0)
                                <div class="card text-white bg-success">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Balance Remaining</h6>
                                        <h3>₹0</h3>
                                        <small class="opacity-75">Government Funded</small>
                                    </div>
                                </div>
                                @else
                                <div class="card text-white {{ $balance > 0 ? 'bg-danger' : 'bg-success' }}">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Balance Remaining</h6>
                                        <h3>₹{{ number_format($balance, 2) }}</h3>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- Fee Structure Breakdown --}}
                        @if($feeStructure)
                        <div class="card mb-3">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <strong>Fee Structure Breakdown</strong>
                                @if($student->admission && $student->admission->custom_tuition_fee > 0)
                                    <span class="badge bg-info text-dark">Custom Fee Applied</span>
                                @elseif(($discountPct ?? 0) > 0)
                                    <span class="badge bg-warning text-dark">{{ $discountPct }}% Discount Applied</span>
                                @endif
                            </div>
                            <div class="card-body">
                                @if($student->admission && $student->admission->fee_note)
                                <div class="alert alert-light border-start border-4 border-warning py-2 mb-3 small">
                                    <i class="bi bi-info-circle me-1"></i> <strong>Fee Note:</strong> {{ $student->admission->fee_note }}
                                </div>
                                @endif
                                <div class="row">
                                    <div class="col-md-4 text-center border-end">
                                        <div class="text-muted small">Tuition Fee</div>
                                        @if($student->admission && $student->admission->custom_tuition_fee > 0)
                                            <div class="text-muted text-decoration-line-through small">Standard rate</div>
                                            <div class="fw-bold text-info">₹{{ number_format($effectiveTuition ?? 0, 2) }}</div>
                                        @elseif(($discountPct ?? 0) > 0)
                                            <div class="text-muted text-decoration-line-through small">₹{{ number_format($feeStructure->tuitionFeeForGender($student->gender ?? 'Male'), 2) }}</div>
                                            <div class="fw-bold text-success">₹{{ number_format($effectiveTuition ?? 0, 2) }}</div>
                                        @else
                                            <div class="fw-bold">₹{{ number_format($effectiveTuition ?? $feeStructure->tuitionFeeForGender($student->gender ?? 'Male'), 2) }}</div>
                                        @endif
                                    </div>
                                    <div class="col-md-4 text-center border-end">
                                        <div class="text-muted small">Transport Fee</div>
                                        <div class="fw-bold">₹{{ number_format($feeStructure->transport_fee, 2) }}</div>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <div class="text-muted small">Other Fee</div>
                                        <div class="fw-bold">₹{{ number_format($feeStructure->other_fee, 2) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="alert alert-warning">
                            No fee structure found for this student's class. Please <a href="{{ route('fee-structures.create') }}">set up a General fee structure</a> first.
                        </div>
                        @endif

                        {{-- Previous Year Carried-Forward Dues --}}
                        @if(isset($rollovers) && $rollovers->count() > 0)
                        <div class="card mb-3 border-warning">
                            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                                <strong><i class="bi bi-arrow-return-right me-1"></i> Previous Year Outstanding (Carried Forward)</strong>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Session</th>
                                            <th class="text-end">Rolled Over</th>
                                            <th class="text-end">Recovered</th>
                                            <th class="text-end">Still Outstanding</th>
                                            <th>Remark</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($rollovers as $rollover)
                                        <tr class="{{ $rollover->remaining > 0 ? 'table-warning' : 'table-light' }}">
                                            <td class="fw-semibold">{{ optional($rollover->session)->session_name ?? '—' }}</td>
                                            <td class="text-end">₹{{ number_format($rollover->outstanding_amount, 0) }}</td>
                                            <td class="text-end text-success">₹{{ number_format($rollover->recovered, 0) }}</td>
                                            <td class="text-end fw-bold {{ $rollover->remaining > 0 ? 'text-danger' : 'text-success' }}">
                                                ₹{{ number_format($rollover->remaining, 0) }}
                                            </td>
                                            <td class="small text-muted">{{ $rollover->remark }}</td>
                                            <td>
                                                @if($rollover->remaining > 0)
                                                <button class="btn btn-sm btn-warning text-dark" type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#rollover-form-{{ $rollover->id }}">
                                                    <i class="bi bi-cash me-1"></i> Record Recovery
                                                </button>
                                                @else
                                                <span class="badge bg-success"><i class="bi bi-check2-circle me-1"></i> Cleared</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @if($rollover->remaining > 0)
                                        <tr class="collapse" id="rollover-form-{{ $rollover->id }}">
                                            <td colspan="6" class="bg-light px-3 py-3">
                                                <form method="POST" action="{{ route('fees.rollover.store', [$student->id, $rollover->id]) }}">
                                                    @csrf
                                                    <div class="row g-2 align-items-end">
                                                        <div class="col-md-2">
                                                            <label class="form-label form-label-sm fw-semibold">Date</label>
                                                            <input type="date" name="payment_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label form-label-sm fw-semibold">Amount <span class="text-muted fw-normal">(max ₹{{ number_format($rollover->remaining, 0) }})</span></label>
                                                            <input type="number" name="amount_paid" class="form-control form-control-sm"
                                                                value="{{ $rollover->remaining }}" min="1" max="{{ $rollover->remaining }}" step="1" required>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label form-label-sm fw-semibold">Mode</label>
                                                            <select name="payment_mode" class="form-select form-select-sm rollover-mode-select" data-rollover="{{ $rollover->id }}" required>
                                                                <option value="cash">Cash</option>
                                                                <option value="qr">QR / UPI</option>
                                                                <option value="cheque">Cheque</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label form-label-sm fw-semibold">Notes (optional)</label>
                                                            <input type="text" name="notes" class="form-control form-control-sm" placeholder="e.g. paid by father">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <button type="submit" class="btn btn-sm btn-warning text-dark w-100"
                                                                onclick="return confirm('Record recovery for {{ optional($rollover->session)->session_name }} outstanding fees?')">
                                                                <i class="bi bi-check2 me-1"></i> Confirm Recovery
                                                            </button>
                                                        </div>
                                                    </div>
                                                    {{-- QR / UPI field --}}
                                                    <div class="row g-2 mt-1 rollover-qr-{{ $rollover->id }}" style="display:none">
                                                        <div class="col-md-4">
                                                            <label class="form-label form-label-sm fw-semibold">UPI Transaction ID <span class="text-danger">*</span></label>
                                                            <input type="text" name="transaction_ref" class="form-control form-control-sm" placeholder="e.g. 123456789012">
                                                        </div>
                                                    </div>
                                                    {{-- Cheque fields --}}
                                                    <div class="row g-2 mt-1 rollover-cheque-{{ $rollover->id }}" style="display:none">
                                                        <div class="col-md-3">
                                                            <label class="form-label form-label-sm fw-semibold">Cheque No <span class="text-danger">*</span></label>
                                                            <input type="text" name="cheque_no" class="form-control form-control-sm">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label form-label-sm fw-semibold">Cheque Date <span class="text-danger">*</span></label>
                                                            <input type="date" name="cheque_date" class="form-control form-control-sm">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label form-label-sm fw-semibold">Bank Name <span class="text-danger">*</span></label>
                                                            <input type="text" name="bank_name" class="form-control form-control-sm">
                                                        </div>
                                                    </div>
                                                </form>
                                            </td>
                                        </tr>
                                        @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
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
            @include('layouts.footer')
        </div>
    </div>
</div>

@if(isset($rollovers) && $rollovers->count() > 0)
<script>
document.querySelectorAll('.rollover-mode-select').forEach(function(select) {
    var rid = select.dataset.rollover;
    select.addEventListener('change', function() {
        document.querySelector('.rollover-qr-'     + rid).style.display = this.value === 'qr'     ? '' : 'none';
        document.querySelector('.rollover-cheque-' + rid).style.display = this.value === 'cheque' ? '' : 'none';
    });
});
</script>
@endif

@endsection
