@extends('layouts.master')
@section('title', 'Record Payment')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-9">

            {{-- Student Summary --}}
            <div class="card mb-3 border-primary">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mb-1">{{ $student->first_name }} {{ $student->last_name }}</h5>
                            <p class="mb-0 text-muted">
                                {{ $promotion->section->schoolClass->class_name ?? '—' }}
                                {{ $promotion->section->section_name ?? '' }} &nbsp;|&nbsp;
                                {{ ucfirst($student->fee_category ?? 'general') }}
                            </p>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="text-muted small">Balance Remaining</div>
                            <h4 class="text-{{ $balance > 0 ? 'danger' : 'success' }} mb-0">
                                ₹{{ number_format($balance, 2) }}
                            </h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Record Payment</h5>
                    <a href="{{ route('fees.ledger', $student->id) }}" class="btn btn-secondary btn-sm">Back</a>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('fees.store', $student->id) }}" method="POST">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Payment Date <span class="text-danger">*</span></label>
                                <input type="date" name="payment_date" value="{{ old('payment_date', today()->toDateString()) }}"
                                    class="form-control @error('payment_date') is-invalid @enderror" required>
                                @error('payment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Amount Paid (₹) <span class="text-danger">*</span></label>
                                <input type="number" name="amount_paid" step="0.01" min="1"
                                    value="{{ old('amount_paid') }}"
                                    class="form-control @error('amount_paid') is-invalid @enderror" required>
                                @error('amount_paid')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Payment Mode <span class="text-danger">*</span></label>
                                <select name="payment_mode" id="payment_mode"
                                    class="form-select @error('payment_mode') is-invalid @enderror" required>
                                    <option value="cash"   {{ old('payment_mode') == 'cash'   ? 'selected' : '' }}>Cash</option>
                                    <option value="qr"     {{ old('payment_mode') == 'qr'     ? 'selected' : '' }}>QR / UPI</option>
                                    <option value="cheque" {{ old('payment_mode') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                </select>
                                @error('payment_mode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6" id="transaction_ref_div" style="display:none">
                                <label class="form-label fw-bold">Transaction Reference</label>
                                <input type="text" name="transaction_ref" value="{{ old('transaction_ref') }}"
                                    class="form-control @error('transaction_ref') is-invalid @enderror">
                                @error('transaction_ref')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- Cheque fields --}}
                        <div id="cheque_fields" style="display:none">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Cheque No</label>
                                    <input type="text" name="cheque_no" value="{{ old('cheque_no') }}" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Cheque Date</label>
                                    <input type="date" name="cheque_date" value="{{ old('cheque_date') }}" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Bank Name</label>
                                    <input type="text" name="bank_name" value="{{ old('bank_name') }}" class="form-control">
                                </div>
                            </div>
                        </div>

                        {{-- Line items --}}
                        <hr>
                        <h6 class="text-muted mb-3">What is this payment for? <small>(optional — tick all that apply)</small></h6>
                        <div class="row">
                            @foreach($lineItemLabels as $key => $label)
                            <div class="col-md-6 mb-2">
                                <div class="input-group">
                                    <div class="input-group-text">
                                        <input type="checkbox" class="line-item-check" data-key="{{ $key }}"
                                            onchange="toggleLineItem('{{ $key }}', this.checked)">
                                    </div>
                                    <span class="input-group-text" style="min-width:180px">{{ $label }}</span>
                                    <input type="number" name="line_items[{{ $key }}]" id="li_{{ $key }}"
                                        step="0.01" min="0" value="0"
                                        class="form-control" disabled>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="mb-3 mt-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save"></i> Record Payment & Generate Challan
                            </button>
                            <a href="{{ route('fees.ledger', $student->id) }}" class="btn btn-secondary btn-lg">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleLineItem(key, checked) {
    const input = document.getElementById('li_' + key);
    input.disabled = !checked;
    if (!checked) input.value = 0;
}

document.getElementById('payment_mode').addEventListener('change', function() {
    const mode = this.value;
    document.getElementById('cheque_fields').style.display      = mode === 'cheque' ? 'block' : 'none';
    document.getElementById('transaction_ref_div').style.display = mode === 'qr'     ? 'block' : 'none';
});
</script>
@endsection
