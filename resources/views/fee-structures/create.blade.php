@extends('layouts.app')
@section('title', 'Create Fee Structure')
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
                        <h4 class="mb-0">Create Fee Structure</h4>
                    </div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('fee-structures.index') }}">Fee Structures</a></li>
                            <li class="breadcrumb-item active">Create</li>
                        </ol>
                    </nav>

                    <div class="card">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Create Fee Structure</h5>
                        </div>
                        <div class="card-body">
                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                                </div>
                            @endif
                            <form method="POST" action="{{ route('fee-structures.store') }}">
                                @csrf
                                <input type="hidden" name="academic_year" value="{{ $session->session_name }}">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Class <span class="text-danger">*</span></label>
                                        <select name="class_id" class="form-select" required>
                                            <option value="">Select class</option>
                                            @foreach($school_classes as $class)
                                                <option value="{{ $class->id }}">{{ $class->class_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Fee Category <span class="text-danger">*</span></label>
                                        <select name="fee_category" class="form-select" required>
                                            <option value="">Select category</option>
                                            @foreach($categories as $key => $label)
                                                <option value="{{ $key }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Academic Year</label>
                                        <input type="text" class="form-control" value="{{ $session->session_name }}" readonly>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Admission Fee (₹)</label>
                                        <input type="number" name="admission_fee" class="form-control" step="0.01" min="0" value="0">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Tuition Fee — Boys (₹)</label>
                                        <input type="number" name="tuition_fee" id="tuition_fee" class="form-control" step="0.01" min="0" value="0"
                                            oninput="calcGirls(this.value)">
                                    </div>
                                    <div class="col-md-3" id="girls_fee_row" style="display:none;">
                                        <label class="form-label fw-bold">Tuition Fee — Girls (₹) <span class="text-muted fw-normal" style="font-size:0.82rem;">(auto 75%, editable)</span></label>
                                        <input type="number" name="girls_tuition_fee" id="girls_tuition_fee" class="form-control" step="0.01" min="0" value="0">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Transport Fee (₹)</label>
                                        <input type="number" name="transport_fee" class="form-control" step="0.01" min="0" value="0">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Other Fee (₹)</label>
                                        <input type="number" name="other_fee" class="form-control" step="0.01" min="0" value="0">
                                    </div>
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-success">Save Fee Structure</button>
                                        <a href="{{ route('fee-structures.index') }}" class="btn btn-secondary ms-2">Cancel</a>
                                    </div>
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
var catSelect = document.querySelector('select[name="fee_category"]');
var girlsRow  = document.getElementById('girls_fee_row');
catSelect.addEventListener('change', function() {
    girlsRow.style.display = this.value === 'general' ? '' : 'none';
});
function calcGirls(val) {
    var girls = document.getElementById('girls_tuition_fee');
    if (document.querySelector('select[name="fee_category"]').value === 'general') {
        girls.value = val ? Math.round(parseFloat(val) * 0.75 * 100) / 100 : 0;
    }
}
</script>
@endsection
