@extends('layouts.app')
@section('title', 'Category Reimbursements')
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
                        <h4 class="mb-0">Category Reimbursements</h4>
                        <span class="text-muted small ms-3">Track govt / COC lump sum receipts per session</span>
                    </div>

                    @include('session-messages')

                    <div class="container-fluid px-0">
                        {{-- Session filter --}}
                        <form method="GET" action="{{ route('fees.categoryReceipts') }}" class="mb-3">
                            <input type="hidden" name="tab" value="{{ $activeTab }}">
                            <div class="row g-2 align-items-center">
                                <div class="col-auto">
                                    <label class="form-label mb-0">Academic Year:</label>
                                </div>
                                <div class="col-auto">
                                    <select name="session_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                        @foreach($sessions as $s)
                                            <option value="{{ $s->id }}" {{ $s->id == $selectedSessionId ? 'selected' : '' }}>{{ $s->session_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @if($selectedSession)
                                <div class="col-auto">
                                    <span class="text-muted small">Showing: <strong>{{ $selectedSession->session_name }}</strong></span>
                                </div>
                                @endif
                            </div>
                        </form>

                        {{-- Summary cards --}}
                        <div class="row mb-4 g-3">
                            <div class="col-md-3">
                                <div class="card text-white bg-success">
                                    <div class="card-body text-center py-3">
                                        <h6 class="card-title mb-1">RTE Students</h6>
                                        <h3 class="mb-0">{{ $rteStudents->count() }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card {{ $rteTotalReceived >= $rteTotalDue && $rteTotalDue > 0 ? 'text-white bg-success' : 'bg-warning text-dark' }}">
                                    <div class="card-body text-center py-3">
                                        <h6 class="card-title mb-1">RTE Reimbursement</h6>
                                        <h5 class="mb-0">₹{{ number_format($rteTotalReceived, 0) }} / ₹{{ number_format($rteTotalDue, 0) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-white bg-info">
                                    <div class="card-body text-center py-3">
                                        <h6 class="card-title mb-1">COC Students</h6>
                                        <h3 class="mb-0">{{ $cocStudents->count() }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card {{ $cocTotalReceived >= $cocTotalDue && $cocTotalDue > 0 ? 'text-white bg-success' : 'bg-warning text-dark' }}">
                                    <div class="card-body text-center py-3">
                                        <h6 class="card-title mb-1">COC Reimbursement</h6>
                                        <h5 class="mb-0">₹{{ number_format($cocTotalReceived, 0) }} / ₹{{ number_format($cocTotalDue, 0) }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Tabs --}}
                        <ul class="nav nav-tabs mb-3" id="categoryTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $activeTab === 'rte' ? 'active' : '' }}" id="rte-tab" data-bs-toggle="tab" data-bs-target="#rte-pane" type="button" role="tab">
                                    <i class="bi bi-bank me-1"></i> RTE — Govt Reimbursement
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $activeTab === 'coc' ? 'active' : '' }}" id="coc-tab" data-bs-toggle="tab" data-bs-target="#coc-pane" type="button" role="tab">
                                    <i class="bi bi-building me-1"></i> COC Reimbursement
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="categoryTabsContent">

                            {{-- ── RTE Tab ──────────────────────────────────────── --}}
                            <div class="tab-pane fade {{ $activeTab === 'rte' ? 'show active' : '' }}" id="rte-pane" role="tabpanel">
                                @php
                                    $rteBalance = $rteTotalDue - $rteTotalReceived;
                                    $rteSettled = $rteTotalReceived >= $rteTotalDue && $rteTotalDue > 0;
                                @endphp
                                <div class="card mb-4 border-{{ $rteSettled ? 'success' : 'warning' }}">
                                    <div class="card-header bg-{{ $rteSettled ? 'success' : 'warning' }} {{ $rteSettled ? 'text-white' : 'text-dark' }} d-flex justify-content-between align-items-center">
                                        <strong><i class="bi bi-bank me-1"></i> Government RTE Reimbursement — {{ $selectedSession->session_name ?? '' }}</strong>
                                        @if($rteSettled)
                                            <span class="badge bg-white text-success">Fully Received</span>
                                        @else
                                            <span class="badge bg-white text-warning">Pending</span>
                                        @endif
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3 mb-3">
                                            <div class="col-md-4 text-center">
                                                <div class="text-muted small">Total Due from Govt</div>
                                                <div class="fs-5 fw-bold">₹{{ number_format($rteTotalDue, 0) }}</div>
                                                <div class="text-muted small">{{ $rteStudents->count() }} students</div>
                                            </div>
                                            <div class="col-md-4 text-center">
                                                <div class="text-muted small">Total Received</div>
                                                <div class="fs-5 fw-bold text-success">₹{{ number_format($rteTotalReceived, 0) }}</div>
                                                <div class="text-muted small">{{ $rteReceipts->count() }} receipt(s)</div>
                                            </div>
                                            <div class="col-md-4 text-center">
                                                <div class="text-muted small">Balance</div>
                                                <div class="fs-5 fw-bold {{ $rteBalance > 0 ? 'text-danger' : 'text-success' }}">
                                                    ₹{{ number_format(abs($rteBalance), 0) }}
                                                    @if($rteBalance < 0) <small class="text-muted">(excess)</small> @endif
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Existing receipts --}}
                                        @if($rteReceipts->isNotEmpty())
                                        <table class="table table-sm table-bordered mb-3">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Date Received</th>
                                                    <th class="text-end">Amount</th>
                                                    <th>Remark</th>
                                                    <th class="text-center" style="width:60px;">Del</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($rteReceipts as $r)
                                                <tr>
                                                    <td>{{ $r->received_date->format('d M Y') }}</td>
                                                    <td class="text-end text-success fw-semibold">₹{{ number_format($r->amount_received, 0) }}</td>
                                                    <td>{{ $r->remark ?: '—' }}</td>
                                                    <td class="text-center">
                                                        <form method="POST" action="{{ route('reports.bulkReceipt.delete', $r->id) }}" class="d-inline">
                                                            @csrf @method('DELETE')
                                                            <button class="btn btn-xs btn-outline-danger py-0 px-1" style="font-size:0.75rem;"
                                                                onclick="return confirm('Delete this receipt?')">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        @endif

                                        {{-- Record new receipt --}}
                                        <form method="POST" action="{{ route('reports.bulkReceipt.store') }}" class="row g-2 align-items-end">
                                            @csrf
                                            <input type="hidden" name="session_id" value="{{ $selectedSessionId }}">
                                            <input type="hidden" name="fee_category" value="rte">
                                            <div class="col-md-3">
                                                <label class="form-label small mb-1">Date Received</label>
                                                <input type="date" name="received_date" class="form-control form-control-sm" required value="{{ date('Y-m-d') }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small mb-1">Amount Received (₹)</label>
                                                <input type="number" name="amount_received" class="form-control form-control-sm" required min="1" placeholder="e.g. 845640">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small mb-1">Remark</label>
                                                <input type="text" name="remark" class="form-control form-control-sm" placeholder="e.g. Govt transfer received in school account">
                                            </div>
                                            <div class="col-md-2">
                                                <button type="submit" class="btn btn-sm btn-success w-100">
                                                    <i class="bi bi-plus-circle me-1"></i> Record
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            {{-- ── COC Tab ───────────────────────────────────────── --}}
                            <div class="tab-pane fade {{ $activeTab === 'coc' ? 'show active' : '' }}" id="coc-pane" role="tabpanel">
                                @php
                                    $cocBalance = $cocTotalDue - $cocTotalReceived;
                                    $cocSettled = $cocTotalReceived >= $cocTotalDue && $cocTotalDue > 0;
                                @endphp
                                <div class="card mb-4 border-{{ $cocSettled ? 'success' : 'warning' }}">
                                    <div class="card-header bg-{{ $cocSettled ? 'success' : 'warning' }} {{ $cocSettled ? 'text-white' : 'text-dark' }} d-flex justify-content-between align-items-center">
                                        <strong><i class="bi bi-building me-1"></i> COC Reimbursement — {{ $selectedSession->session_name ?? '' }}</strong>
                                        @if($cocSettled)
                                            <span class="badge bg-white text-success">Fully Received</span>
                                        @else
                                            <span class="badge bg-white text-warning">Pending</span>
                                        @endif
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3 mb-3">
                                            <div class="col-md-4 text-center">
                                                <div class="text-muted small">Total Due from COC</div>
                                                <div class="fs-5 fw-bold">₹{{ number_format($cocTotalDue, 0) }}</div>
                                                <div class="text-muted small">{{ $cocStudents->count() }} students</div>
                                            </div>
                                            <div class="col-md-4 text-center">
                                                <div class="text-muted small">Total Received</div>
                                                <div class="fs-5 fw-bold text-success">₹{{ number_format($cocTotalReceived, 0) }}</div>
                                                <div class="text-muted small">{{ $cocReceipts->count() }} receipt(s)</div>
                                            </div>
                                            <div class="col-md-4 text-center">
                                                <div class="text-muted small">Balance</div>
                                                <div class="fs-5 fw-bold {{ $cocBalance > 0 ? 'text-danger' : 'text-success' }}">
                                                    ₹{{ number_format(abs($cocBalance), 0) }}
                                                    @if($cocBalance < 0) <small class="text-muted">(excess)</small> @endif
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Existing receipts --}}
                                        @if($cocReceipts->isNotEmpty())
                                        <table class="table table-sm table-bordered mb-3">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Date Received</th>
                                                    <th class="text-end">Amount</th>
                                                    <th>Remark</th>
                                                    <th class="text-center" style="width:60px;">Del</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($cocReceipts as $r)
                                                <tr>
                                                    <td>{{ $r->received_date->format('d M Y') }}</td>
                                                    <td class="text-end text-success fw-semibold">₹{{ number_format($r->amount_received, 0) }}</td>
                                                    <td>{{ $r->remark ?: '—' }}</td>
                                                    <td class="text-center">
                                                        <form method="POST" action="{{ route('reports.bulkReceipt.delete', $r->id) }}" class="d-inline">
                                                            @csrf @method('DELETE')
                                                            <button class="btn btn-xs btn-outline-danger py-0 px-1" style="font-size:0.75rem;"
                                                                onclick="return confirm('Delete this receipt?')">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        @endif

                                        {{-- Record new receipt --}}
                                        <form method="POST" action="{{ route('reports.bulkReceipt.store') }}" class="row g-2 align-items-end">
                                            @csrf
                                            <input type="hidden" name="session_id" value="{{ $selectedSessionId }}">
                                            <input type="hidden" name="fee_category" value="coc">
                                            <div class="col-md-3">
                                                <label class="form-label small mb-1">Date Received</label>
                                                <input type="date" name="received_date" class="form-control form-control-sm" required value="{{ date('Y-m-d') }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small mb-1">Amount Received (₹)</label>
                                                <input type="number" name="amount_received" class="form-control form-control-sm" required min="1" placeholder="e.g. 291600">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small mb-1">Remark</label>
                                                <input type="text" name="remark" class="form-control form-control-sm" placeholder="e.g. Internal transfer from COC account">
                                            </div>
                                            <div class="col-md-2">
                                                <button type="submit" class="btn btn-sm btn-info w-100">
                                                    <i class="bi bi-plus-circle me-1"></i> Record
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        </div>{{-- /tab-content --}}
                    </div>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
