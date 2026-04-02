@extends('layouts.app')
@section('title', 'Record Payment')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">

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
                            <div class="text-muted small">Fee Balance Remaining</div>
                            <h4 class="text-{{ $balance > 0 ? 'danger' : 'success' }} mb-0">
                                ₹{{ number_format($balance, 2) }}
                            </h4>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Fee Structure Breakdown --}}
            @if($feeStructure)
            <div class="card mb-3 border-0 bg-light">
                <div class="card-body py-2">
                    <div class="row text-center">
                        <div class="col border-end">
                            <div class="text-muted small">Admission</div>
                            <div class="fw-bold">₹{{ number_format($feeStructure->admission_fee, 0) }}</div>
                        </div>
                        <div class="col border-end">
                            <div class="text-muted small">Tuition</div>
                            <div class="fw-bold">₹{{ number_format($feeStructure->tuition_fee, 0) }}</div>
                        </div>
                        <div class="col border-end">
                            <div class="text-muted small">Transport</div>
                            <div class="fw-bold">₹{{ number_format($feeStructure->transport_fee, 0) }}</div>
                        </div>
                        <div class="col border-end">
                            <div class="text-muted small">Other</div>
                            <div class="fw-bold">₹{{ number_format($feeStructure->other_fee, 0) }}</div>
                        </div>
                        <div class="col">
                            <div class="text-muted small">Total Due</div>
                            <div class="fw-bold text-primary">₹{{ number_format($feeStructure->total_fee, 0) }}</div>
                        </div>
                        <div class="col">
                            <div class="text-muted small">Paid</div>
                            <div class="fw-bold text-success">₹{{ number_format($totalPaid, 0) }}</div>
                        </div>
                        <div class="col">
                            <div class="text-muted small">Balance</div>
                            <div class="fw-bold {{ $balance > 0 ? 'text-danger' : 'text-success' }}">₹{{ number_format($balance, 0) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Record Payment</h5>
                    <a href="{{ route('fees.ledger', $student->id) }}" class="btn btn-secondary btn-sm">Back</a>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form action="{{ route('fees.store', $student->id) }}" method="POST" id="paymentForm">
                        @csrf

                        {{-- Payment Type Toggle --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold">Payment Type <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="payment_category"
                                        id="cat_fee" value="fee"
                                        {{ old('payment_category', 'fee') == 'fee' ? 'checked' : '' }}
                                        onchange="switchCategory('fee')">
                                    <label class="form-check-label" for="cat_fee">
                                        <span class="badge bg-primary">Fee Payment</span>
                                        <small class="text-muted ms-1">Reduces fee balance</small>
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="payment_category"
                                        id="cat_misc" value="misc"
                                        {{ old('payment_category') == 'misc' ? 'checked' : '' }}
                                        onchange="switchCategory('misc')">
                                    <label class="form-check-label" for="cat_misc">
                                        <span class="badge bg-warning text-dark">Misc Purchase</span>
                                        <small class="text-muted ms-1">Challan only, no balance deduction</small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Payment Date <span class="text-danger">*</span></label>
                                <input type="date" name="payment_date"
                                    value="{{ old('payment_date', today()->toDateString()) }}"
                                    class="form-control @error('payment_date') is-invalid @enderror" required>
                                @error('payment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Amount Paid (₹) <span class="text-danger">*</span></label>
                                <input type="number" name="amount_paid" id="amount_paid_field" step="0.01" min="1"
                                    value="{{ old('amount_paid') }}"
                                    class="form-control @error('amount_paid') is-invalid @enderror" required>
                                @error('amount_paid')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <small id="amount_paid_hint" class="text-success" style="display:none">
                                    <i class="bi bi-check-circle"></i> Auto-calculated from line items
                                </small>
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

                        <hr>

                        {{-- FEE LINE ITEMS --}}
                        <div id="fee_items_section">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-cash-stack"></i>
                                Fee breakdown <small class="text-muted fw-normal">(optional — tick all that apply)</small>
                            </h6>
                            <div class="row">
                                @foreach($feeLabels as $key => $label)
                                <div class="col-md-6 mb-2">
                                    <div class="input-group">
                                        <div class="input-group-text">
                                            <input type="checkbox" class="fee-item-check"
                                                data-key="{{ $key }}"
                                                onchange="toggleLineItem('{{ $key }}', this.checked)">
                                        </div>
                                        <span class="input-group-text" style="min-width:200px">{{ $label }}</span>
                                        <input type="number" name="line_items[{{ $key }}]" id="li_{{ $key }}"
                                            step="0.01" min="0" value="{{ old('line_items.'.$key, 0) }}"
                                            class="form-control" disabled>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- MISC LINE ITEMS --}}
                        <div id="misc_items_section" style="display:none">
                            <h6 class="text-warning mb-3">
                                <i class="bi bi-bag"></i>
                                Items purchased <small class="text-muted fw-normal">(optional — tick all that apply)</small>
                            </h6>
                            <div class="row">
                                @foreach($miscLabels as $key => $label)
                                <div class="col-md-6 mb-2">
                                    <div class="input-group">
                                        <div class="input-group-text">
                                            <input type="checkbox" class="misc-item-check"
                                                data-key="{{ $key }}"
                                                onchange="toggleLineItem('{{ $key }}', this.checked)">
                                        </div>
                                        <span class="input-group-text" style="min-width:200px">{{ $label }}</span>
                                        <input type="number" name="line_items[{{ $key }}]" id="li_{{ $key }}"
                                            step="0.01" min="0" value="{{ old('line_items.'.$key, 0) }}"
                                            class="form-control" disabled>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-3 mt-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-save"></i> Record Payment &amp; Generate Challan
                            </button>
                            <a href="{{ route('fees.ledger', $student->id) }}" class="btn btn-secondary btn-lg">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>

<script>
function switchCategory(category) {
    var feeSection  = document.getElementById('fee_items_section');
    var miscSection = document.getElementById('misc_items_section');

    if (category === 'fee') {
        feeSection.style.display  = 'block';
        miscSection.style.display = 'none';
        // Uncheck and disable all misc inputs
        document.querySelectorAll('.misc-item-check').forEach(function(cb) {
            cb.checked = false;
            toggleLineItem(cb.dataset.key, false);
        });
    } else {
        feeSection.style.display  = 'none';
        miscSection.style.display = 'block';
        // Uncheck and disable all fee inputs
        document.querySelectorAll('.fee-item-check').forEach(function(cb) {
            cb.checked = false;
            toggleLineItem(cb.dataset.key, false);
        });
    }
}

function toggleLineItem(key, checked) {
    var input = document.getElementById('li_' + key);
    if (input) {
        input.disabled = !checked;
        if (!checked) input.value = 0;
        // Re-attach or remove the oninput listener
        if (checked) {
            input.addEventListener('input', recalcTotal);
        } else {
            input.removeEventListener('input', recalcTotal);
        }
    }
    recalcTotal();
}

function recalcTotal() {
    var total = 0;
    // Sum all enabled (active) line item inputs
    document.querySelectorAll('input[name^="line_items"]').forEach(function(input) {
        if (!input.disabled) {
            var val = parseFloat(input.value) || 0;
            total += val;
        }
    });
    var amountField = document.getElementById('amount_paid_field');
    if (total > 0) {
        amountField.value = total.toFixed(2);
        amountField.readOnly = true;
        document.getElementById('amount_paid_hint').style.display = 'block';
    } else {
        amountField.readOnly = false;
        document.getElementById('amount_paid_hint').style.display = 'none';
    }
}

document.getElementById('payment_mode').addEventListener('change', function() {
    var mode = this.value;
    document.getElementById('cheque_fields').style.display       = mode === 'cheque' ? 'block' : 'none';
    document.getElementById('transaction_ref_div').style.display = mode === 'qr'     ? 'block' : 'none';
});

// Set initial state based on old() value (handles validation failure redirects)
(function() {
    var selected = document.querySelector('input[name="payment_category"]:checked');
    if (selected) switchCategory(selected.value);
})();
</script>
@endsection
