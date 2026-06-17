@extends('layouts.app')
@section('title', 'Fee Structure Overview')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">

                    <div class="d-flex align-items-center mb-3 no-print">
                        <a href="{{ route('fee-structures.index') }}" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <h4 class="mb-0">Fee Structure Overview</h4>
                        <a href="{{ route('fee-structures.print') }}" target="_blank" class="btn btn-outline-primary btn-sm ms-auto">
                            <i class="bi bi-printer me-1"></i> Print / PDF
                        </a>
                    </div>
                    <nav aria-label="breadcrumb" class="no-print">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('fee-structures.index') }}">Fee Structures</a></li>
                            <li class="breadcrumb-item active">Overview</li>
                        </ol>
                    </nav>

                    {{-- Print header (only visible when printing) --}}
                    <div class="print-only text-center mb-3">
                        <h5 class="fw-bold mb-0">Deep Griha Academy</h5>
                        <p class="mb-0 text-muted">Fee Structure — {{ $session ? $session->session_name : '' }}</p>
                        <small class="text-muted">Printed on {{ date('d M Y') }}</small>
                    </div>

                    @if(empty($structured))
                        <div class="alert alert-warning">No fee structures set up for the current session.</div>
                    @else
                    <div class="card mb-4">
                        <div class="card-header bg-dark text-white d-flex align-items-center no-print">
                            <span class="fw-bold">Academic Year: {{ $session ? $session->session_name : '—' }}</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm overview-table mb-0">
                                    <thead>
                                        <tr class="table-dark">
                                            <th rowspan="2" class="align-middle">Class</th>
                                            {{-- General: boys + girls --}}
                                            <th colspan="2" class="text-center">General</th>
                                            <th class="text-center">RTE</th>
                                            <th class="text-center">COC</th>
                                        </tr>
                                        <tr class="table-secondary">
                                            <th class="text-center small">Boys Total</th>
                                            <th class="text-center small">Girls Total</th>
                                            <th class="text-center small">Total</th>
                                            <th class="text-center small">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($classes as $class)
                                        @php
                                            $gen  = $structured[$class->id]['general'] ?? null;
                                            $rte  = $structured[$class->id]['rte']     ?? null;
                                            $coc  = $structured[$class->id]['coc']     ?? null;

                                            $girlsFee   = $gen ? ($gen->girls_tuition_fee !== null ? $gen->girls_tuition_fee : round($gen->tuition_fee * 0.75) + 50) : null;
                                            $girlsTotal = $gen ? ($girlsFee + $gen->transport_fee + $gen->other_fee) : null;
                                        @endphp
                                        <tr>
                                            <td class="fw-semibold">{{ $class->class_name }}</td>

                                            {{-- General boys --}}
                                            <td class="text-end">
                                                @if($gen)
                                                    <span class="fw-bold">₹{{ number_format($gen->total_fee, 0) }}</span>
                                                    <div class="text-muted small fee-breakdown">
                                                        Tuit: ₹{{ number_format($gen->tuition_fee,0) }}
                                                        @if($gen->transport_fee > 0) &nbsp;|&nbsp; Trans: ₹{{ number_format($gen->transport_fee,0) }} @endif
                                                        @if($gen->other_fee > 0) &nbsp;|&nbsp; Other: ₹{{ number_format($gen->other_fee,0) }} @endif
                                                    </div>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>

                                            {{-- General girls --}}
                                            <td class="text-end">
                                                @if($gen)
                                                    <span class="fw-bold">₹{{ number_format($girlsTotal, 0) }}</span>
                                                    <div class="text-muted small fee-breakdown">
                                                        Tuit: ₹{{ number_format($girlsFee,0) }}
                                                        @if($gen->transport_fee > 0) &nbsp;|&nbsp; Trans: ₹{{ number_format($gen->transport_fee,0) }} @endif
                                                        @if($gen->other_fee > 0) &nbsp;|&nbsp; Other: ₹{{ number_format($gen->other_fee,0) }} @endif
                                                    </div>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>

                                            {{-- RTE --}}
                                            <td class="text-center">
                                                @if($rte)
                                                    <span class="badge bg-success">₹{{ number_format($rte->total_fee, 0) }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>

                                            {{-- COC --}}
                                            <td class="text-center">
                                                @if($coc)
                                                    <span class="badge bg-info text-dark">₹{{ number_format($coc->tuition_fee, 0) }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <p class="text-muted small no-print">
                        <i class="bi bi-info-circle me-1"></i>
                        Totals shown are annual. Girls total uses girls_tuition_fee where set, otherwise same as boys.
                        RTE fees are government-reimbursed.
                    </p>
                    @endif

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>

@push('styles')
<style>
    .fee-breakdown { font-size: 0.70rem; line-height: 1.2; }
    .overview-table th, .overview-table td { vertical-align: middle; padding: 0.4rem 0.6rem; }

    .print-only { display: none; }

    @media print {
        .no-print, nav, .left-menu, footer { display: none !important; }
        .print-only { display: block !important; }
        .container, .row, .col-lg-10, .col-xl-10 { width: 100% !important; max-width: 100% !important; padding: 0 !important; margin: 0 !important; }
        .card { border: none !important; }
        .card-header { background: #333 !important; color: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .table-dark th { background: #333 !important; color: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .table-secondary th { background: #eee !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .overview-table { font-size: 0.80rem; }
        .fee-breakdown { font-size: 0.68rem; }
        body { font-size: 12px; }
    }
</style>
@endpush
@endsection
