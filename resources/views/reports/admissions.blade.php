@extends('layouts.app')
@section('title', 'Admissions Report')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <div class="d-flex align-items-center mb-3">
                        <a href="{{ route('home') }}" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <h4 class="mb-0">Admissions Report</h4>
                    </div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item active">Admissions Report</li>
                        </ol>
                    </nav>

                    <div class="container-fluid px-0">
                        <div class="card mb-3">
                            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-user-graduate me-2"></i>Admissions Report — {{ $academic_year }}</h5>
                                <a href="?pdf=1" class="btn btn-sm btn-light"><i class="fas fa-download me-1"></i> Download PDF</a>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <div class="card text-white bg-secondary">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Inquiry</h6>
                                        <h3>{{ $summary['inquiry'] }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-white bg-warning">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Pending</h6>
                                        <h3>{{ $summary['pending'] }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-white bg-success">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Confirmed</h6>
                                        <h3>{{ $summary['confirmed'] }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-white bg-danger">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Cancelled</h6>
                                        <h3>{{ $summary['cancelled'] }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Name</th>
                                                <th>Class Applied</th>
                                                <th>Category</th>
                                                <th>DGA No / General ID</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($admissions as $admission)
                                            <tr class="{{ $admission->trashed() ? 'table-secondary text-muted' : '' }}">
                                                <td>{{ $admission->child_first_name }} {{ $admission->child_last_name }}</td>
                                                <td>{{ $admission->schoolClass ? $admission->schoolClass->class_name : '—' }}</td>
                                                <td><span class="badge bg-secondary">{{ strtoupper($admission->fee_category ?? 'general') }}</span></td>
                                                <td>{{ $admission->dga_admission_no ?? $admission->general_id ?? '—' }}</td>
                                                <td>{{ \Carbon\Carbon::parse($admission->created_at)->format('d M Y') }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $admission->status == 'confirmed' ? 'success' : ($admission->status == 'pending' ? 'warning' : ($admission->status == 'inquiry' ? 'secondary' : 'danger')) }}">
                                                        {{ ucfirst($admission->status) }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    @if(!$admission->trashed())
                                                        <a href="{{ route('admissions.show', $admission->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                                                    @else
                                                        <span class="text-muted small">Deleted</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
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
