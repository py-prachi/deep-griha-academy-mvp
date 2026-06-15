@extends('layouts.app')
@section('title', 'Year-End Fee Settlement')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">

                    <div class="d-flex align-items-center mb-1">
                        <a href="{{ url('academics/settings') }}" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <h4 class="mb-0"><i class="bi bi-cash-coin me-1"></i> Year-End Fee Settlement — {{ $session->session_name }}</h4>
                    </div>
                    <p class="text-muted small mb-3 ps-5">Review all outstanding balances before creating a new academic session. Add a settlement remark for each student.</p>

                    @include('session-messages')

                    @php
                        $totalStudents  = count($defaulters);
                        $settledCount   = $settledMap->count();
                        $unsettledCount = $totalStudents - $settledCount;
                    @endphp

                    {{-- Progress summary --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="card border-{{ $unsettledCount === 0 ? 'success' : 'warning' }} h-100">
                                <div class="card-body text-center">
                                    <div style="font-size:2rem;" class="text-{{ $unsettledCount === 0 ? 'success' : 'warning' }}">
                                        <i class="bi bi-{{ $unsettledCount === 0 ? 'check-circle-fill' : 'exclamation-circle-fill' }}"></i>
                                    </div>
                                    <h5 class="mt-1 mb-0">{{ $unsettledCount === 0 ? 'All Settled' : $unsettledCount . ' Pending' }}</h5>
                                    <small class="text-muted">{{ $settledCount }} of {{ $totalStudents }} resolved</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8 d-flex align-items-center">
                            @if($unsettledCount === 0)
                            <div class="alert alert-success mb-0 w-100">
                                <i class="bi bi-check-circle me-1"></i>
                                <strong>All outstanding balances have been addressed.</strong>
                                You can now go back and create the new academic session.
                                <a href="{{ url('academics/settings') }}" class="btn btn-sm btn-success ms-2">
                                    <i class="bi bi-arrow-left me-1"></i> Back to Settings
                                </a>
                            </div>
                            @else
                            <div class="alert alert-warning mb-0 w-100">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                <strong>{{ $unsettledCount }} student(s)</strong> still have unresolved balances.
                                Add a remark for each — waived, paid offline, carried forward, or other.
                            </div>
                            @endif
                        </div>
                    </div>

                    @if($totalStudents === 0)
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle me-1"></i> No outstanding balances for this session. You're good to create a new session.
                    </div>
                    @else

                    <div class="table-responsive bg-white shadow-sm rounded">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Student</th>
                                    <th>Class</th>
                                    <th class="text-end">Total Due</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end text-warning">Balance</th>
                                    <th>Settlement</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($defaulters as $d)
                                @php $settlement = $settledMap->get($d->student_id); @endphp
                                <tr class="{{ $settlement ? 'table-light' : '' }}">
                                    <td>
                                        <div class="fw-semibold">{{ $d->first_name }} {{ $d->last_name }}</div>
                                        <small class="text-muted font-monospace">{{ $d->general_id ?? $d->dga_admission_no ?? '—' }}</small>
                                    </td>
                                    <td class="text-nowrap small">{{ $d->class_name }} {{ $d->section_name }}</td>
                                    <td class="text-end small">₹{{ number_format($d->total_due, 0) }}</td>
                                    <td class="text-end small text-success">₹{{ number_format($d->total_paid, 0) }}</td>
                                    <td class="text-end fw-bold text-danger">₹{{ number_format($d->balance, 0) }}</td>
                                    <td style="min-width:360px;">
                                        @if($settlement)
                                            <div class="d-flex align-items-start gap-2">
                                                <span class="badge {{ \App\Models\FeeSettlement::$typeBadges[$settlement->settlement_type] }} mt-1">
                                                    {{ \App\Models\FeeSettlement::$typeLabels[$settlement->settlement_type] }}
                                                </span>
                                                <div class="small flex-grow-1">{{ $settlement->remark }}</div>
                                                <form method="POST" action="{{ route('school.session.year-end.unsettle', $d->student_id) }}" class="d-inline">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-xs btn-outline-secondary py-0 px-1" style="font-size:0.7rem;" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <form method="POST" action="{{ route('school.session.year-end.settle', $d->student_id) }}" class="d-flex gap-2 align-items-start">
                                                @csrf
                                                <input type="hidden" name="outstanding_amount" value="{{ $d->balance }}">
                                                <select name="settlement_type" class="form-select form-select-sm" style="width:160px;" required>
                                                    <option value="" disabled selected>Type...</option>
                                                    @foreach(\App\Models\FeeSettlement::$typeLabels as $val => $label)
                                                    <option value="{{ $val }}">{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                                <input type="text" name="remark" class="form-control form-control-sm" placeholder="Remark (required)" required>
                                                <button type="submit" class="btn btn-sm btn-primary text-nowrap">
                                                    <i class="bi bi-check2 me-1"></i> Settle
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
