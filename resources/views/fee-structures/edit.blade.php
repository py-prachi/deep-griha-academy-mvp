@extends('layouts.app')
@section('title', 'Edit Fee Structure')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <div class="d-flex align-items-center mb-3">
                        <a href="{{ route('fee-structures.index') }}" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <h4 class="mb-0">Edit Fee Structure</h4>
                    </div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('fee-structures.index') }}">Fee Structures</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </nav>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Edit Fee Structure</h5>
                        </div>
                        <div class="card-body">
                            @if(session('error'))
                                <div class="alert alert-danger">{{ session('error') }}</div>
                            @endif

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Class</label>
                                    <input type="text" class="form-control" value="{{ $feeStructure->schoolClass->class_name ?? '—' }}" disabled>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Category</label>
                                    <input type="text" class="form-control" value="{{ $categories[$feeStructure->fee_category] ?? $feeStructure->fee_category }}" disabled>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Academic Year</label>
                                    <input type="text" class="form-control" value="{{ $feeStructure->academic_year }}" disabled>
                                </div>
                            </div>

                            <form action="{{ route('fee-structures.update', $feeStructure->id) }}" method="POST">
                                @csrf @method('PUT')
                                <hr><h6 class="text-muted mb-3">Fee Components</h6>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Admission Fee (₹)</label>
                                        <input type="number" name="admission_fee" id="admission_fee" step="0.01" min="0"
                                            value="{{ old('admission_fee', $feeStructure->admission_fee) }}"
                                            class="form-control" oninput="recalcTotals()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Tuition Fee{{ $feeStructure->fee_category === 'general' ? ' — Boys' : '' }} (₹)</label>
                                        <input type="number" name="tuition_fee" id="tuition_fee" step="0.01" min="0"
                                            value="{{ old('tuition_fee', $feeStructure->tuition_fee) }}"
                                            class="form-control"
                                            oninput="calcGirls(this.value)">
                                    </div>
                                </div>
                                @if($feeStructure->fee_category === 'general')
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Tuition Fee — Girls (₹) <span class="text-muted" style="font-size:0.82rem;">(auto 75% of boys, editable)</span></label>
                                        <input type="number" name="girls_tuition_fee" id="girls_tuition_fee" step="0.01" min="0"
                                            value="{{ old('girls_tuition_fee', $feeStructure->girls_tuition_fee ?? round($feeStructure->tuition_fee * 0.75, 2)) }}"
                                            class="form-control" oninput="recalcTotals()">
                                    </div>
                                </div>
                                @else
                                <input type="hidden" name="girls_tuition_fee" value="">
                                @endif
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Transport Fee (₹)</label>
                                        <input type="number" name="transport_fee" id="transport_fee" step="0.01" min="0"
                                            value="{{ old('transport_fee', $feeStructure->transport_fee) }}"
                                            class="form-control" oninput="recalcTotals()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Other Fee (₹)</label>
                                        <input type="number" name="other_fee" id="other_fee" step="0.01" min="0"
                                            value="{{ old('other_fee', $feeStructure->other_fee) }}"
                                            class="form-control" oninput="recalcTotals()">
                                    </div>
                                </div>
                                <div class="alert alert-info" id="totals_display">
                                    @if($feeStructure->fee_category === 'general')
                                    Boys Total: <strong id="boys_total_display">—</strong>
                                    &nbsp;|&nbsp;
                                    Girls Total: <strong id="girls_total_display">—</strong>
                                    @else
                                    Total: <strong id="boys_total_display">—</strong>
                                    @endif
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">Update Fee Structure</button>
                                    <a href="{{ route('fee-structures.index') }}" class="btn btn-secondary">Cancel</a>
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
function v(id) { return parseFloat(document.getElementById(id) && document.getElementById(id).value || 0) || 0; }
function fmt(n) { return '₹' + n.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}); }

function calcGirls(val) {
    var girls = document.getElementById('girls_tuition_fee');
    if (girls) {
        girls.value = val ? Math.round(parseFloat(val) * 0.75 * 100) / 100 : 0;
    }
    recalcTotals();
}

function recalcTotals() {
    var admission  = v('admission_fee');
    var boysTuition = v('tuition_fee');
    var transport  = v('transport_fee');
    var other      = v('other_fee');
    var boysTotal  = admission + boysTuition + transport + other;

    var boysEl = document.getElementById('boys_total_display');
    if (boysEl) boysEl.textContent = fmt(boysTotal);

    var girlsInput = document.getElementById('girls_tuition_fee');
    var girlsEl    = document.getElementById('girls_total_display');
    if (girlsInput && girlsEl) {
        var girlsTuition = parseFloat(girlsInput.value) || 0;
        girlsEl.textContent = fmt(admission + girlsTuition + transport + other);
    }
}

// Run on page load so totals show immediately
document.addEventListener('DOMContentLoaded', recalcTotals);
</script>
@endsection
