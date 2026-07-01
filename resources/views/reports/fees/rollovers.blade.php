@extends('layouts.app')
@section('title', 'Carried Forward Fees Report')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">

                    <h4 class="mb-1"><i class="bi bi-arrow-return-right me-1"></i> Carried Forward Fees Report</h4>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item active">Carried Forward Fees</li>
                        </ol>
                    </nav>

                    @include('session-messages')

                    {{-- Summary Cards --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="card border-warning">
                                <div class="card-body text-center">
                                    <div class="text-muted small">Total Rolled Over</div>
                                    <h4 class="text-warning mb-0">₹{{ number_format($totalRolledOver, 0) }}</h4>
                                    <small class="text-muted">{{ $rollovers->count() }} student(s)</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <div class="text-muted small">Recovered So Far</div>
                                    <h4 class="text-success mb-0">₹{{ number_format($totalRecovered, 0) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-danger">
                                <div class="card-body text-center">
                                    <div class="text-muted small">Still Outstanding</div>
                                    <h4 class="text-danger mb-0">₹{{ number_format($totalOutstanding, 0) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($rollovers->isEmpty())
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle me-1"></i> No carried-forward fees on record.
                    </div>
                    @else
                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Student</th>
                                            <th>Session (From)</th>
                                            <th>Remark</th>
                                            <th class="text-end">Rolled Over</th>
                                            <th class="text-end">Recovered</th>
                                            <th class="text-end">Outstanding</th>
                                            <th>Status</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($rollovers as $r)
                                        <tr class="{{ $r->remaining > 0 ? '' : 'table-light' }}">
                                            <td>
                                                <div class="fw-semibold">{{ optional($r->student)->first_name }} {{ optional($r->student)->last_name }}</div>
                                            </td>
                                            <td class="small">{{ optional($r->session)->session_name ?? '—' }}</td>
                                            <td class="small text-muted">{{ $r->remark }}</td>
                                            <td class="text-end">₹{{ number_format($r->outstanding_amount, 0) }}</td>
                                            <td class="text-end text-success">₹{{ number_format($r->recovered, 0) }}</td>
                                            <td class="text-end fw-bold {{ $r->remaining > 0 ? 'text-danger' : 'text-success' }}">
                                                ₹{{ number_format($r->remaining, 0) }}
                                            </td>
                                            <td>
                                                @if($r->remaining <= 0)
                                                    <span class="badge bg-success">Cleared</span>
                                                @else
                                                    <span class="badge bg-danger">Pending</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('fees.ledger', optional($r->student)->id) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i> Ledger
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-secondary">
                                        <tr>
                                            <td colspan="3" class="text-end fw-bold">Total</td>
                                            <td class="text-end fw-bold">₹{{ number_format($totalRolledOver, 0) }}</td>
                                            <td class="text-end fw-bold text-success">₹{{ number_format($totalRecovered, 0) }}</td>
                                            <td class="text-end fw-bold text-danger">₹{{ number_format($totalOutstanding, 0) }}</td>
                                            <td colspan="2"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
