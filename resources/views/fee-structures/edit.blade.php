@extends('layouts.master')
@section('title', 'Edit Fee Structure')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Edit Fee Structure</h4>
                    <a href="{{ route('fee-structures.index') }}" class="btn btn-secondary btn-sm">Back</a>
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
                                <input type="number" name="admission_fee" step="0.01" min="0"
                                    value="{{ old('admission_fee', $feeStructure->admission_fee) }}"
                                    class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tuition Fee (₹)</label>
                                <input type="number" name="tuition_fee" step="0.01" min="0"
                                    value="{{ old('tuition_fee', $feeStructure->tuition_fee) }}"
                                    class="form-control">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Transport Fee (₹)</label>
                                <input type="number" name="transport_fee" step="0.01" min="0"
                                    value="{{ old('transport_fee', $feeStructure->transport_fee) }}"
                                    class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Other Fee (₹)</label>
                                <input type="number" name="other_fee" step="0.01" min="0"
                                    value="{{ old('other_fee', $feeStructure->other_fee) }}"
                                    class="form-control">
                            </div>
                        </div>
                        <div class="alert alert-info">
                            Current Total: <strong>₹{{ number_format($feeStructure->total_fee, 2) }}</strong> — will recalculate on save.
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
</div>
@endsection
