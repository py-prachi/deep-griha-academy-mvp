@extends('layouts.app')
@section('title', 'Fee Structures')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <div class="d-flex align-items-center mb-3">
                        <a href="{{ url('home') }}" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <h4 class="mb-0">Fee Structures</h4>
                        <a href="{{ route('fee-structures.create') }}" class="btn btn-primary btn-sm ms-auto">
                            <i class="fas fa-plus"></i> Add Fee Structure
                        </a>
                    </div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item active">Fee Structures</li>
                        </ol>
                    </nav>
                    <div class="card">
                        <div class="card-body">
                <div class="card-body">
                    @if(session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    @if($feeStructures->isEmpty())
                        <p class="text-muted text-center py-4">No fee structures set up yet. <a href="{{ route('fee-structures.create') }}">Add one now.</a></p>
                    @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Class</th>
                                    <th>Category</th>
                                    <th>Academic Year</th>
                                    <th class="text-end">Admission Fee</th>
                                    <th class="text-end">Tuition Fee</th>
                                    <th class="text-end">Transport Fee</th>
                                    <th class="text-end">Other Fee</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($feeStructures as $fs)
                                <tr>
                                    <td>{{ $fs->schoolClass->class_name ?? '—' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $fs->fee_category == 'rte' ? 'success' : ($fs->fee_category == 'coc' ? 'info' : ($fs->fee_category == 'discount' ? 'warning' : 'primary')) }}">
                                            {{ $categories[$fs->fee_category] ?? $fs->fee_category }}
                                        </span>
                                    </td>
                                    <td>{{ $fs->academic_year }}</td>
                                    <td class="text-end">₹{{ number_format($fs->admission_fee, 2) }}</td>
                                    <td class="text-end">₹{{ number_format($fs->tuition_fee, 2) }}</td>
                                    <td class="text-end">₹{{ number_format($fs->transport_fee, 2) }}</td>
                                    <td class="text-end">₹{{ number_format($fs->other_fee, 2) }}</td>
                                    <td class="text-end fw-bold">₹{{ number_format($fs->total_fee, 2) }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('fee-structures.edit', $fs->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                        <form action="{{ route('fee-structures.destroy', $fs->id) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Delete this fee structure?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
                        </div>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
