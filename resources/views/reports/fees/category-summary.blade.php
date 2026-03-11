@extends('layouts.app')
@section('title', 'Category-wise Fee Summary')
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
                        <h4 class="mb-0">Category-wise Fee Summary</h4>
                    </div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item active">Category Summary</li>
                        </ol>
                    </nav>

                    <div class="container-fluid px-0">
                        <div class="card mb-3">
                            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Category-wise Fee Summary</h5>
                                <a href="?pdf=1" class="btn btn-sm btn-light"><i class="fas fa-download me-1"></i> Download PDF</a>
                            </div>
                        </div>

                        <div class="row mb-3">
                            @foreach($summary as $row)
                            <div class="col-md-3">
                                <div class="card border-primary mb-3">
                                    <div class="card-header bg-primary text-white text-center">
                                        <strong>{{ strtoupper($row->fee_category ?? 'General') }}</strong>
                                    </div>
                                    <div class="card-body text-center">
                                        <div class="row">
                                            <div class="col-6 border-end">
                                                <div class="text-muted small">Students</div>
                                                <div class="fw-bold fs-5">{{ $row->student_count }}</div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-muted small">Collected</div>
                                                <div class="fw-bold fs-5">₹{{ number_format($row->total_collected, 2) }}</div>
                                            </div>
                                        </div>
                                        <hr class="my-2">
                                        <div class="row">
                                            <div class="col-6 border-end">
                                                <div class="text-muted small">Total Due</div>
                                                <div class="fw-bold">₹{{ number_format($row->total_due, 2) }}</div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-muted small">Outstanding</div>
                                                <div class="fw-bold text-{{ $row->total_balance > 0 ? 'danger' : 'success' }}">₹{{ number_format($row->total_balance, 2) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="card">
                            <div class="card-header"><strong>Detailed Breakdown</strong></div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Category</th>
                                                <th class="text-end">Students</th>
                                                <th class="text-end">Total Due ₹</th>
                                                <th class="text-end">Total Collected ₹</th>
                                                <th class="text-end">Outstanding ₹</th>
                                                <th class="text-end">Collection %</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($summary as $row)
                                            @php $pct = $row->total_due > 0 ? round(($row->total_collected / $row->total_due) * 100, 1) : 0; @endphp
                                            <tr>
                                                <td><span class="badge bg-secondary fs-6">{{ strtoupper($row->fee_category ?? 'General') }}</span></td>
                                                <td class="text-end">{{ $row->student_count }}</td>
                                                <td class="text-end">₹{{ number_format($row->total_due, 2) }}</td>
                                                <td class="text-end text-success fw-bold">₹{{ number_format($row->total_collected, 2) }}</td>
                                                <td class="text-end text-{{ $row->total_balance > 0 ? 'danger' : 'success' }} fw-bold">₹{{ number_format($row->total_balance, 2) }}</td>
                                                <td class="text-end">
                                                    <div class="progress" style="height:18px;">
                                                        <div class="progress-bar bg-{{ $pct >= 90 ? 'success' : ($pct >= 50 ? 'warning' : 'danger') }}" style="width:{{ $pct }}%">{{ $pct }}%</div>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-dark fw-bold">
                                                <td>Total</td>
                                                <td class="text-end">{{ collect($summary)->sum('student_count') }}</td>
                                                <td class="text-end">₹{{ number_format(collect($summary)->sum('total_due'), 2) }}</td>
                                                <td class="text-end">₹{{ number_format(collect($summary)->sum('total_collected'), 2) }}</td>
                                                <td class="text-end">₹{{ number_format(collect($summary)->sum('total_balance'), 2) }}</td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
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
