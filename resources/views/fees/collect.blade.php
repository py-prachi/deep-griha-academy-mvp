@extends('layouts.app')
@section('title', 'Collect Fee')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h4 class="mb-3"><i class="bi bi-cash-stack me-1"></i> Collect Fee</h4>

                    @include('session-messages')

                    {{-- Search bar --}}
                    <div class="card mb-4">
                        <div class="card-body py-3">
                            <form method="GET" action="{{ route('fees.collect') }}" id="searchForm">
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                                            <input type="text"
                                                   class="form-control form-control-lg"
                                                   name="q"
                                                   id="searchInput"
                                                   value="{{ $search }}"
                                                   placeholder="Type student name..."
                                                   autocomplete="off"
                                                   autofocus>
                                            @if($search)
                                            <a href="{{ route('fees.collect') }}" class="btn btn-outline-secondary">
                                                <i class="bi bi-x-lg"></i>
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <button type="submit" class="btn btn-primary btn-lg">Search</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Results --}}
                    @if($search && strlen($search) >= 2)
                        @if($students->isEmpty())
                            <div class="alert alert-info">No active students found matching "<strong>{{ $search }}</strong>".</div>
                        @else
                            <div class="card">
                                <div class="card-header bg-dark text-white">
                                    <strong>{{ $students->count() }} student(s) found</strong>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered mb-0 align-middle">
                                        <thead class="table-secondary">
                                            <tr>
                                                <th>Name</th>
                                                <th>Class / Section</th>
                                                <th>Category</th>
                                                <th class="text-end">Total Due</th>
                                                <th class="text-end">Paid</th>
                                                <th class="text-end">Balance</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($students as $student)
                                            @php
                                                $balance  = $student->_balance;
                                                $rowClass = $balance > 0 ? 'table-warning' : ($balance < 0 ? 'table-info' : '');
                                            @endphp
                                            <tr class="{{ $rowClass }}">
                                                <td>
                                                    <strong>{{ $student->first_name }} {{ $student->last_name }}</strong>
                                                    @if($student->admission)
                                                        <br><small class="text-muted">{{ $student->admission->dga_admission_no ?? $student->admission->general_id ?? '—' }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($student->_promotion)
                                                        {{ $student->_promotion->schoolClass ? $student->_promotion->schoolClass->class_name : '—' }}
                                                        / {{ $student->_promotion->section ? $student->_promotion->section->section_name : '—' }}
                                                    @elseif($student->admission && $student->admission->schoolClass)
                                                        {{ $student->admission->schoolClass->class_name }}
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                <td><span class="badge bg-secondary">{{ strtoupper($student->fee_category ?? 'general') }}</span></td>
                                                <td class="text-end">₹{{ number_format($student->_totalDue, 0) }}</td>
                                                <td class="text-end">₹{{ number_format($student->_totalPaid, 0) }}</td>
                                                <td class="text-end fw-bold {{ $balance > 0 ? 'text-danger' : ($balance < 0 ? 'text-info' : 'text-success') }}">
                                                    ₹{{ number_format(abs($balance), 0) }}
                                                    @if($balance < 0) <small>(advance)</small>@endif
                                                    @if($balance == 0 && $student->_totalDue > 0) <i class="bi bi-check-circle-fill text-success"></i>@endif
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('fees.create', $student->id) }}?back=collect&q={{ urlencode($search) }}"
                                                       class="btn btn-sm btn-success me-1">
                                                        <i class="bi bi-plus-circle me-1"></i>Collect
                                                    </a>
                                                    <a href="{{ route('fees.ledger', $student->id) }}"
                                                       class="btn btn-sm btn-outline-secondary">
                                                        <i class="bi bi-clock-history me-1"></i>Ledger
                                                    </a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    @elseif(!$search)
                        <p class="text-muted">Start typing a student name above to search.</p>
                    @endif

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>

<script>
    // Auto-submit after 400ms pause while typing
    var searchTimer;
    document.getElementById('searchInput').addEventListener('input', function() {
        clearTimeout(searchTimer);
        var val = this.value.trim();
        if (val.length >= 2) {
            searchTimer = setTimeout(function() {
                document.getElementById('searchForm').submit();
            }, 400);
        }
    });
</script>
@endsection
