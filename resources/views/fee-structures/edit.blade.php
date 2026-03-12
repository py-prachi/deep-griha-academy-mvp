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
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
