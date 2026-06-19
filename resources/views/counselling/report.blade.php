@extends('layouts.app')
@section('title', 'Counselling Reports')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">

                    <div class="d-flex align-items-center mb-1">
                        <a href="{{ route('counselling.index') }}" class="btn btn-sm btn-outline-secondary me-2"><i class="bi bi-arrow-left"></i></a>
                        <h4 class="mb-0">Counselling Reports</h4>
                    </div>
                    <p class="text-muted small mb-3">
                        <i class="bi bi-shield-lock me-1"></i>
                        Student names are <strong>not shown</strong> in any report. Use the Student ID to look up a specific student if needed.
                    </p>

                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white pb-0 pt-3">
                            <ul class="nav nav-tabs card-header-tabs flex-nowrap overflow-auto" role="tablist">
                                <li class="nav-item flex-shrink-0">
                                    <button class="nav-link {{ $activeTab==='active' ? 'active' : '' }}"
                                            data-bs-toggle="tab" data-bs-target="#tab-active" type="button">
                                        <i class="bi bi-person-check-fill me-1 text-success"></i> Active Cases
                                    </button>
                                </li>
                                <li class="nav-item flex-shrink-0">
                                    <button class="nav-link {{ $activeTab==='classwise' ? 'active' : '' }}"
                                            data-bs-toggle="tab" data-bs-target="#tab-classwise" type="button">
                                        <i class="bi bi-bar-chart-fill me-1 text-primary"></i> Class-wise
                                    </button>
                                </li>
                                <li class="nav-item flex-shrink-0">
                                    <button class="nav-link {{ $activeTab==='reasonwise' ? 'active' : '' }}"
                                            data-bs-toggle="tab" data-bs-target="#tab-reasonwise" type="button">
                                        <i class="bi bi-pie-chart-fill me-1 text-warning"></i> Reason-wise
                                    </button>
                                </li>
                                <li class="nav-item flex-shrink-0">
                                    <button class="nav-link {{ $activeTab==='longduration' ? 'active' : '' }}"
                                            data-bs-toggle="tab" data-bs-target="#tab-longduration" type="button">
                                        <i class="bi bi-clock-fill me-1 text-danger"></i> Long Duration
                                    </button>
                                </li>
                                <li class="nav-item flex-shrink-0">
                                    <button class="nav-link {{ $activeTab==='monthly' ? 'active' : '' }}"
                                            data-bs-toggle="tab" data-bs-target="#tab-monthly" type="button">
                                        <i class="bi bi-calendar3 me-1 text-info"></i> Monthly Activity
                                    </button>
                                </li>
                                <li class="nav-item flex-shrink-0">
                                    <button class="nav-link {{ $activeTab==='closed' ? 'active' : '' }}"
                                            data-bs-toggle="tab" data-bs-target="#tab-closed" type="button">
                                        <i class="bi bi-check-circle-fill me-1 text-secondary"></i> Closed Cases
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <div class="card-body">
                            <div class="tab-content">

                                {{-- ══════════════════════════════════════════════════════════
                                     TAB 1 — ACTIVE CASES
                                ══════════════════════════════════════════════════════════ --}}
                                <div class="tab-pane fade {{ $activeTab==='active' ? 'show active' : '' }}" id="tab-active">
                                    <div class="alert alert-success py-2 mb-3" style="font-size:0.88rem;">
                                        <strong><i class="bi bi-info-circle me-1"></i>Active Cases List</strong> —
                                        Shows all students currently receiving counselling support. Use this to get an overview
                                        of who is in counselling, how long they have been, and how many session notes have been
                                        recorded. Days are colour-coded: <span class="badge bg-warning text-dark">30+ days</span>
                                        and <span class="badge bg-danger">90+ days</span> flag cases needing attention.
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted small">{{ $activeCases->count() }} active case(s)</span>
                                        <a href="{{ route('counselling.report', ['pdf'=>1,'tab'=>'active']) }}"
                                           class="btn btn-sm btn-outline-dark">
                                            <i class="bi bi-download me-1"></i> Download PDF
                                        </a>
                                    </div>

                                    @if($activeCases->isEmpty())
                                        <div class="alert alert-info">No active counselling cases at this time.</div>
                                    @else
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover table-sm align-middle" style="font-size:0.85rem;">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Student ID</th>
                                                    <th>Class</th>
                                                    <th>Reason</th>
                                                    <th>Start Date</th>
                                                    <th>Days Active</th>
                                                    <th>Session Notes</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($activeCases as $i => $c)
                                                <tr>
                                                    <td>{{ $i + 1 }}</td>
                                                    <td><strong>{{ $c->display_id }}</strong></td>
                                                    <td>{{ $c->class_name }}{{ $c->section_name !== '—' ? ' '.$c->section_name : '' }}</td>
                                                    <td>{{ $c->reason ?: '—' }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($c->start_date)->format('d M Y') }}</td>
                                                    <td>
                                                        @if($c->days_active >= 90)
                                                            <span class="badge bg-danger">{{ $c->days_active }} days</span>
                                                        @elseif($c->days_active >= 30)
                                                            <span class="badge bg-warning text-dark">{{ $c->days_active }} days</span>
                                                        @else
                                                            <span class="badge bg-secondary">{{ $c->days_active }} days</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">{{ $c->remarkLogs->count() }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @endif
                                </div>

                                {{-- ══════════════════════════════════════════════════════════
                                     TAB 2 — CLASS-WISE SUMMARY
                                ══════════════════════════════════════════════════════════ --}}
                                <div class="tab-pane fade {{ $activeTab==='classwise' ? 'show active' : '' }}" id="tab-classwise">
                                    <div class="alert alert-primary py-2 mb-3" style="font-size:0.88rem;">
                                        <strong><i class="bi bi-info-circle me-1"></i>Class-wise Summary</strong> —
                                        Shows the total number of counselling cases grouped by class, split into active
                                        (currently ongoing) and closed (completed). No individual student information
                                        is shown. Useful for identifying which classes have the highest support needs.
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted small">{{ $classwise->count() }} class(es) with counselling records</span>
                                        <a href="{{ route('counselling.report', ['pdf'=>1,'tab'=>'classwise']) }}"
                                           class="btn btn-sm btn-outline-dark">
                                            <i class="bi bi-download me-1"></i> Download PDF
                                        </a>
                                    </div>

                                    @if($classwise->isEmpty())
                                        <div class="alert alert-info">No counselling records found.</div>
                                    @else
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover table-sm align-middle" style="font-size:0.85rem; max-width:500px;">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Class</th>
                                                    <th class="text-center">Active</th>
                                                    <th class="text-center">Closed</th>
                                                    <th class="text-center">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($classwise as $i => $row)
                                                <tr>
                                                    <td>{{ $i + 1 }}</td>
                                                    <td>{{ $row['class'] }}</td>
                                                    <td class="text-center">
                                                        @if($row['active'] > 0)
                                                            <span class="badge bg-success">{{ $row['active'] }}</span>
                                                        @else
                                                            <span class="text-muted">0</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">{{ $row['closed'] }}</td>
                                                    <td class="text-center fw-bold">{{ $row['total'] }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="table-light">
                                                <tr>
                                                    <td colspan="2" class="fw-bold">Total</td>
                                                    <td class="text-center fw-bold">{{ $classwise->sum('active') }}</td>
                                                    <td class="text-center fw-bold">{{ $classwise->sum('closed') }}</td>
                                                    <td class="text-center fw-bold">{{ $classwise->sum('total') }}</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    @endif
                                </div>

                                {{-- ══════════════════════════════════════════════════════════
                                     TAB 3 — REASON-WISE SUMMARY
                                ══════════════════════════════════════════════════════════ --}}
                                <div class="tab-pane fade {{ $activeTab==='reasonwise' ? 'show active' : '' }}" id="tab-reasonwise">
                                    <div class="alert alert-warning py-2 mb-3" style="font-size:0.88rem;">
                                        <strong><i class="bi bi-info-circle me-1"></i>Reason-wise Summary</strong> —
                                        Groups all counselling cases by the type of concern entered when the case was created
                                        (e.g., Behavioural, Academic, Social, Emotional). Helps identify which categories are
                                        most common across the school. No individual student information is shown.
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted small">{{ $reasonwise->count() }} reason category(s)</span>
                                        <a href="{{ route('counselling.report', ['pdf'=>1,'tab'=>'reasonwise']) }}"
                                           class="btn btn-sm btn-outline-dark">
                                            <i class="bi bi-download me-1"></i> Download PDF
                                        </a>
                                    </div>

                                    @if($reasonwise->isEmpty())
                                        <div class="alert alert-info">No counselling records found.</div>
                                    @else
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover table-sm align-middle" style="font-size:0.85rem; max-width:550px;">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Reason / Category</th>
                                                    <th class="text-center">Active</th>
                                                    <th class="text-center">Closed</th>
                                                    <th class="text-center">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($reasonwise as $i => $row)
                                                <tr>
                                                    <td>{{ $i + 1 }}</td>
                                                    <td>{{ $row->reason }}</td>
                                                    <td class="text-center">
                                                        @if($row->active > 0)
                                                            <span class="badge bg-success">{{ $row->active }}</span>
                                                        @else
                                                            <span class="text-muted">0</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">{{ $row->closed }}</td>
                                                    <td class="text-center fw-bold">{{ $row->total }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="table-light">
                                                <tr>
                                                    <td colspan="2" class="fw-bold">Total</td>
                                                    <td class="text-center fw-bold">{{ $reasonwise->sum('active') }}</td>
                                                    <td class="text-center fw-bold">{{ $reasonwise->sum('closed') }}</td>
                                                    <td class="text-center fw-bold">{{ $reasonwise->sum('total') }}</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    @endif
                                </div>

                                {{-- ══════════════════════════════════════════════════════════
                                     TAB 4 — LONG-DURATION CASES
                                ══════════════════════════════════════════════════════════ --}}
                                <div class="tab-pane fade {{ $activeTab==='longduration' ? 'show active' : '' }}" id="tab-longduration">
                                    <div class="alert alert-danger py-2 mb-3" style="font-size:0.88rem;">
                                        <strong><i class="bi bi-info-circle me-1"></i>Long-duration Cases</strong> —
                                        Lists students who have been in active counselling for longer than the set number of
                                        days without the case being closed. Use this report to flag cases that may need review,
                                        a status update, or escalation. Change the threshold below to adjust the cut-off.
                                    </div>

                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                        <form method="GET" action="{{ route('counselling.report') }}" class="d-inline-flex align-items-center gap-2">
                                            <input type="hidden" name="tab" value="longduration">
                                            <label class="small mb-0">Show cases open for more than</label>
                                            <input type="number" name="threshold" value="{{ $threshold }}"
                                                   min="1" max="365" class="form-control form-control-sm" style="width:75px">
                                            <label class="small mb-0">days</label>
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">Apply</button>
                                        </form>
                                        <a href="{{ route('counselling.report') }}?pdf=1&tab=longduration&threshold={{ $threshold }}"
                                           class="btn btn-sm btn-outline-dark">
                                            <i class="bi bi-download me-1"></i> Download PDF
                                        </a>
                                    </div>

                                    @if($longDuration->isEmpty())
                                        <div class="alert alert-success">
                                            <i class="bi bi-check-circle me-1"></i>
                                            No active cases found open for {{ $threshold }}+ days.
                                        </div>
                                    @else
                                    <p class="text-muted small mb-2">{{ $longDuration->count() }} case(s) open for {{ $threshold }}+ days</p>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover table-sm align-middle" style="font-size:0.85rem;">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Student ID</th>
                                                    <th>Class</th>
                                                    <th>Reason</th>
                                                    <th>Start Date</th>
                                                    <th>Days Open</th>
                                                    <th>Session Notes</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($longDuration as $i => $c)
                                                <tr>
                                                    <td>{{ $i + 1 }}</td>
                                                    <td><strong>{{ $c->display_id }}</strong></td>
                                                    <td>{{ $c->class_name }}{{ $c->section_name !== '—' ? ' '.$c->section_name : '' }}</td>
                                                    <td>{{ $c->reason ?: '—' }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($c->start_date)->format('d M Y') }}</td>
                                                    <td><span class="badge bg-danger">{{ $c->days_active }} days</span></td>
                                                    <td class="text-center">{{ $c->remarkLogs->count() }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @endif
                                </div>

                                {{-- ══════════════════════════════════════════════════════════
                                     TAB 5 — MONTHLY ACTIVITY
                                ══════════════════════════════════════════════════════════ --}}
                                <div class="tab-pane fade {{ $activeTab==='monthly' ? 'show active' : '' }}" id="tab-monthly">
                                    <div class="alert alert-info py-2 mb-3" style="font-size:0.88rem;">
                                        <strong><i class="bi bi-info-circle me-1"></i>Monthly Activity Report</strong> —
                                        Shows a snapshot of counselling activity in a selected month: new cases opened, cases
                                        closed, and session notes recorded. The table below lists all cases that were active
                                        at any point during the selected month. Useful for monthly progress reviews.
                                    </div>

                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                        <form method="GET" action="{{ route('counselling.report') }}" class="d-inline-flex align-items-center gap-2">
                                            <input type="hidden" name="tab" value="monthly">
                                            <label class="small mb-0">Select Month:</label>
                                            <input type="month" name="month" value="{{ $month }}"
                                                   class="form-control form-control-sm" style="width:165px">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">Apply</button>
                                        </form>
                                        <a href="{{ route('counselling.report') }}?pdf=1&tab=monthly&month={{ $month }}"
                                           class="btn btn-sm btn-outline-dark">
                                            <i class="bi bi-download me-1"></i> Download PDF
                                        </a>
                                    </div>

                                    <h6 class="fw-bold mb-3">{{ $monthStart->format('F Y') }}</h6>
                                    <div class="row g-3 mb-4">
                                        <div class="col-sm-4">
                                            <div class="card border-success h-100">
                                                <div class="card-body py-3 text-center">
                                                    <div class="display-6 fw-bold text-success">{{ $monthlyOpened }}</div>
                                                    <div class="small text-muted mt-1">New Cases Opened</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="card border-secondary h-100">
                                                <div class="card-body py-3 text-center">
                                                    <div class="display-6 fw-bold text-secondary">{{ $monthlyClosed }}</div>
                                                    <div class="small text-muted mt-1">Cases Closed</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="card border-info h-100">
                                                <div class="card-body py-3 text-center">
                                                    <div class="display-6 fw-bold text-info">{{ $monthlyRemarks }}</div>
                                                    <div class="small text-muted mt-1">Session Notes Added</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @if($monthlyCases->isEmpty())
                                        <div class="alert alert-info">No counselling activity found in {{ $monthStart->format('F Y') }}.</div>
                                    @else
                                    <p class="text-muted small mb-2">{{ $monthlyCases->count() }} case(s) were active during {{ $monthStart->format('F Y') }}</p>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover table-sm align-middle" style="font-size:0.85rem;">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Student ID</th>
                                                    <th>Class</th>
                                                    <th>Reason</th>
                                                    <th>Start Date</th>
                                                    <th>Status</th>
                                                    <th>Session Notes</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($monthlyCases as $i => $c)
                                                <tr>
                                                    <td>{{ $i + 1 }}</td>
                                                    <td><strong>{{ $c->display_id }}</strong></td>
                                                    <td>{{ $c->class_name }}{{ $c->section_name !== '—' ? ' '.$c->section_name : '' }}</td>
                                                    <td>{{ $c->reason ?: '—' }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($c->start_date)->format('d M Y') }}</td>
                                                    <td>
                                                        @if($c->end_date)
                                                            <span class="badge bg-secondary">Closed {{ \Carbon\Carbon::parse($c->end_date)->format('d M') }}</span>
                                                        @else
                                                            <span class="badge bg-success">Active</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">{{ $c->remarkLogs->count() }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @endif
                                </div>

                                {{-- ══════════════════════════════════════════════════════════
                                     TAB 6 — CLOSED CASES HISTORY
                                ══════════════════════════════════════════════════════════ --}}
                                <div class="tab-pane fade {{ $activeTab==='closed' ? 'show active' : '' }}" id="tab-closed">
                                    <div class="alert alert-secondary py-2 mb-3" style="font-size:0.88rem;">
                                        <strong><i class="bi bi-info-circle me-1"></i>Closed Cases History</strong> —
                                        A complete history of all counselling sessions that have been formally closed,
                                        showing the duration and number of session notes recorded. Useful for reviewing
                                        past outcomes and understanding how long cases typically run.
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted small">{{ $closedCases->count() }} closed case(s)</span>
                                        <a href="{{ route('counselling.report', ['pdf'=>1,'tab'=>'closed']) }}"
                                           class="btn btn-sm btn-outline-dark">
                                            <i class="bi bi-download me-1"></i> Download PDF
                                        </a>
                                    </div>

                                    @if($closedCases->isEmpty())
                                        <div class="alert alert-info">No closed counselling cases yet.</div>
                                    @else
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover table-sm align-middle" style="font-size:0.85rem;">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Student ID</th>
                                                    <th>Class</th>
                                                    <th>Reason</th>
                                                    <th>Start Date</th>
                                                    <th>End Date</th>
                                                    <th>Duration</th>
                                                    <th>Session Notes</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($closedCases as $i => $c)
                                                <tr>
                                                    <td>{{ $i + 1 }}</td>
                                                    <td><strong>{{ $c->display_id }}</strong></td>
                                                    <td>{{ $c->class_name }}{{ $c->section_name !== '—' ? ' '.$c->section_name : '' }}</td>
                                                    <td>{{ $c->reason ?: '—' }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($c->start_date)->format('d M Y') }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($c->end_date)->format('d M Y') }}</td>
                                                    <td>{{ $c->duration_days }} days</td>
                                                    <td class="text-center">{{ $c->remarkLogs->count() }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @endif
                                </div>

                            </div>{{-- /tab-content --}}
                        </div>{{-- /card-body --}}
                    </div>{{-- /card --}}

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
