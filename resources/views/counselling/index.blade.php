@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="mb-0"><i class="bi bi-journal-medical me-1"></i> Counselling</h4>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                            <i class="bi bi-plus-lg me-1"></i> Add Student
                        </button>
                    </div>

                    @include('session-messages')

                    {{-- Tabs --}}
                    <ul class="nav nav-tabs mb-0" id="counsellingTabs">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#activeTab">
                                <i class="bi bi-person-fill-check me-1"></i> Active
                                @if($active->count())
                                    <span class="badge bg-danger ms-1">{{ $active->count() }}</span>
                                @endif
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pastTab">
                                <i class="bi bi-clock-history me-1"></i> Past
                                @if($past->count())
                                    <span class="badge bg-secondary ms-1">{{ $past->count() }}</span>
                                @endif
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content border border-top-0 bg-white shadow-sm p-3 mb-4">

                        {{-- Active --}}
                        <div class="tab-pane fade show active" id="activeTab">
                            @if($active->isEmpty())
                                <p class="text-muted mb-0 py-2">No students currently in counselling.</p>
                            @else
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle mb-0">
                                    <thead class="table-secondary">
                                        <tr>
                                            <th>Student ID <span class="text-muted fw-normal small">(hover for name)</span></th>
                                            <th>Class</th>
                                            <th>Since</th>
                                            <th>Reason</th>
                                            <th>Latest Remark</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($active as $c)
                                        <tr>
                                            <td class="fw-semibold font-monospace"
                                                title="{{ $c->student_name }}"
                                                style="cursor:default;">{{ $c->display_id }}</td>
                                            <td class="text-nowrap">{{ $c->class_name }} {{ $c->section_name }}</td>
                                            <td class="text-nowrap">{{ \Carbon\Carbon::parse($c->start_date)->format('d M Y') }}</td>
                                            <td>{{ $c->reason ?: '—' }}</td>
                                            <td style="max-width:200px;">
                                                @php
                                                    $latestRemark = $c->remarkLogs->last();
                                                    $displayRemark = $latestRemark ? $latestRemark->remark : $c->remarks;
                                                @endphp
                                                <span class="small">{{ $displayRemark ?: '—' }}</span>
                                                @if($c->remarkLogs->count() > 1)
                                                    <br><span class="badge bg-light text-muted" style="font-size:0.65rem;">+{{ $c->remarkLogs->count() - 1 }} more</span>
                                                @endif
                                            </td>
                                            <td class="text-center text-nowrap">
                                                <button class="btn btn-sm btn-outline-secondary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editModal{{ $c->id }}">
                                                    <i class="bi bi-pencil"></i> Remarks
                                                </button>
                                                <button class="btn btn-sm btn-outline-success ms-1"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#endModal{{ $c->id }}">
                                                    <i class="bi bi-check2-circle"></i> End
                                                </button>
                                            </td>
                                        </tr>

                                        {{-- Edit/Remarks modal --}}
                                        <div class="modal fade" id="editModal{{ $c->id }}" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">
                                                            <i class="bi bi-journal-text me-1"></i>
                                                            Counselling — <span class="font-monospace">{{ $c->display_id }}</span>
                                                            <small class="text-muted ms-1">{{ $c->class_name }} {{ $c->section_name }}</small>
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">

                                                        {{-- Reason field --}}
                                                        <form method="POST" action="{{ route('counselling.update', $c->id) }}" class="mb-3">
                                                            @csrf @method('PUT')
                                                            <div class="row g-2 align-items-end">
                                                                <div class="col">
                                                                    <label class="form-label small mb-1">Reason</label>
                                                                    <input type="text" name="reason" class="form-control form-control-sm"
                                                                        value="{{ $c->reason }}"
                                                                        placeholder="e.g. Behavioural, Academic, Social">
                                                                </div>
                                                                <div class="col-auto">
                                                                    <button type="submit" class="btn btn-sm btn-outline-primary">Save Reason</button>
                                                                </div>
                                                            </div>
                                                        </form>

                                                        <hr class="my-2">

                                                        {{-- Remarks history --}}
                                                        @php
                                                            $totalRemarks = ($c->remarks ? 1 : 0) + $c->remarkLogs->count();
                                                        @endphp
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <h6 class="text-muted small mb-0"><i class="bi bi-clock-history me-1"></i> Remarks History</h6>
                                                            @if($totalRemarks > 0)
                                                            <span class="badge bg-secondary">{{ $totalRemarks }} {{ Str::plural('entry', $totalRemarks) }}</span>
                                                            @endif
                                                        </div>

                                                        <div style="max-height:280px;overflow-y:auto;" class="border rounded p-2 bg-light mb-2">
                                                        @if($c->remarks)
                                                        <div class="border rounded p-2 mb-2 bg-white">
                                                            <div class="d-flex justify-content-between">
                                                                <small class="text-muted fw-semibold">Initial Remark</small>
                                                                <small class="text-muted">{{ \Carbon\Carbon::parse($c->start_date)->format('d M Y') }}</small>
                                                            </div>
                                                            <div class="small mt-1">{{ $c->remarks }}</div>
                                                        </div>
                                                        @endif

                                                        @foreach($c->remarkLogs as $r)
                                                        <div class="border rounded p-2 mb-2 bg-white">
                                                            <div class="d-flex justify-content-between align-items-start">
                                                                <div>
                                                                    <small class="text-muted fw-semibold">{{ optional($r->createdBy)->first_name ?? 'Admin' }}</small>
                                                                    @if($r->class_name)
                                                                    <span class="badge bg-light text-dark border ms-1" style="font-size:0.65rem;">{{ $r->class_name }}{{ $r->section_name ? ' '.$r->section_name : '' }}</span>
                                                                    @endif
                                                                </div>
                                                                <small class="text-muted text-nowrap ms-2">{{ \Carbon\Carbon::parse($r->remark_date)->format('d M Y') }}</small>
                                                            </div>
                                                            <div class="small mt-1">{{ $r->remark }}</div>
                                                        </div>
                                                        @endforeach

                                                        @if($totalRemarks === 0)
                                                        <p class="text-muted small mb-0">No remarks yet.</p>
                                                        @endif
                                                        </div>

                                                        <hr class="my-2">

                                                        {{-- Add new remark --}}
                                                        <h6 class="small mb-2"><i class="bi bi-plus-circle me-1"></i> Add Remark</h6>
                                                        <form method="POST" action="{{ route('counselling.remark', $c->id) }}">
                                                            @csrf
                                                            <div class="mb-2">
                                                                <label class="form-label small mb-1">Date <span class="text-danger">*</span></label>
                                                                <input type="date" name="remark_date" class="form-control form-control-sm"
                                                                    value="{{ now()->format('Y-m-d') }}" required>
                                                            </div>
                                                            <div class="mb-2">
                                                                <label class="form-label small mb-1">Remark <span class="text-danger">*</span></label>
                                                                <textarea name="remark" class="form-control form-control-sm" rows="3"
                                                                    placeholder="Session notes, observations..." required></textarea>
                                                            </div>
                                                            <button type="submit" class="btn btn-sm btn-primary">
                                                                <i class="bi bi-plus-lg me-1"></i> Add Remark
                                                            </button>
                                                        </form>

                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- End modal --}}
                                        <div class="modal fade" id="endModal{{ $c->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <form method="POST" action="{{ route('counselling.end', $c->id) }}">
                                                    @csrf
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">End Counselling — <span class="font-monospace">{{ $c->display_id }}</span></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">End Date</label>
                                                                <input type="date" name="end_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-success">Mark as Ended</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif
                        </div>

                        {{-- Past --}}
                        <div class="tab-pane fade" id="pastTab">
                            @if($past->isEmpty())
                                <p class="text-muted mb-0 py-2">No past counselling records.</p>
                            @else
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle mb-0">
                                    <thead class="table-secondary">
                                        <tr>
                                            <th>Student ID <span class="text-muted fw-normal small">(hover for name)</span></th>
                                            <th>Class</th>
                                            <th>Period</th>
                                            <th>Reason</th>
                                            <th>Remarks</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($past as $c)
                                        <tr class="table-light">
                                            <td class="fw-semibold font-monospace"
                                                title="{{ $c->student_name }}"
                                                style="cursor:default;">{{ $c->display_id }}</td>
                                            <td class="text-nowrap">{{ $c->class_name }} {{ $c->section_name }}</td>
                                            <td class="text-nowrap small">
                                                {{ \Carbon\Carbon::parse($c->start_date)->format('d M Y') }}
                                                &rarr;
                                                {{ \Carbon\Carbon::parse($c->end_date)->format('d M Y') }}
                                            </td>
                                            <td>{{ $c->reason ?: '—' }}</td>
                                            <td style="max-width:200px;">
                                                @php $latestRemark = $c->remarkLogs->last(); @endphp
                                                <span class="small">{{ $latestRemark ? $latestRemark->remark : ($c->remarks ?: '—') }}</span>
                                                @if($c->remarkLogs->count() > 1)
                                                    <br><span class="badge bg-light text-muted" style="font-size:0.65rem;">+{{ $c->remarkLogs->count() - 1 }} more</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-secondary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editModalPast{{ $c->id }}">
                                                    <i class="bi bi-eye"></i> View
                                                </button>
                                            </td>
                                        </tr>

                                        {{-- View modal for past --}}
                                        <div class="modal fade" id="editModalPast{{ $c->id }}" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">
                                                            Counselling — <span class="font-monospace">{{ $c->display_id }}</span>
                                                            <small class="text-muted ms-1">{{ $c->class_name }} {{ $c->section_name }}</small>
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p class="text-muted small mb-3">
                                                            Reason: <strong>{{ $c->reason ?: '—' }}</strong> &nbsp;|&nbsp;
                                                            Period: {{ \Carbon\Carbon::parse($c->start_date)->format('d M Y') }} → {{ \Carbon\Carbon::parse($c->end_date)->format('d M Y') }}
                                                        </p>

                                                        @php $totalRemarksPast = ($c->remarks ? 1 : 0) + $c->remarkLogs->count(); @endphp
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <h6 class="text-muted small mb-0"><i class="bi bi-clock-history me-1"></i> Remarks History</h6>
                                                            @if($totalRemarksPast > 0)
                                                            <span class="badge bg-secondary">{{ $totalRemarksPast }} {{ Str::plural('entry', $totalRemarksPast) }}</span>
                                                            @endif
                                                        </div>

                                                        <div style="max-height:320px;overflow-y:auto;" class="border rounded p-2 bg-light">
                                                        @if($c->remarks)
                                                        <div class="border rounded p-2 mb-2 bg-white">
                                                            <div class="d-flex justify-content-between">
                                                                <small class="text-muted fw-semibold">Initial Remark</small>
                                                                <small class="text-muted">{{ \Carbon\Carbon::parse($c->start_date)->format('d M Y') }}</small>
                                                            </div>
                                                            <div class="small mt-1">{{ $c->remarks }}</div>
                                                        </div>
                                                        @endif

                                                        @foreach($c->remarkLogs as $r)
                                                        <div class="border rounded p-2 mb-2 bg-white">
                                                            <div class="d-flex justify-content-between align-items-start">
                                                                <div>
                                                                    <small class="text-muted fw-semibold">{{ optional($r->createdBy)->first_name ?? 'Admin' }}</small>
                                                                    @if($r->class_name)
                                                                    <span class="badge bg-light text-dark border ms-1" style="font-size:0.65rem;">{{ $r->class_name }}{{ $r->section_name ? ' '.$r->section_name : '' }}</span>
                                                                    @endif
                                                                </div>
                                                                <small class="text-muted text-nowrap ms-2">{{ \Carbon\Carbon::parse($r->remark_date)->format('d M Y') }}</small>
                                                            </div>
                                                            <div class="small mt-1">{{ $r->remark }}</div>
                                                        </div>
                                                        @endforeach

                                                        @if($totalRemarksPast === 0)
                                                        <p class="text-muted small mb-0">No remarks recorded.</p>
                                                        @endif
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif
                        </div>

                    </div>{{-- tab-content --}}

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>

{{-- Add Student Modal --}}
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('counselling.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-person-plus me-1"></i> Add to Counselling</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Search Student by Name <span class="text-danger">*</span></label>
                        <input type="text" id="studentSearch" class="form-control mb-1"
                            placeholder="Type student name to search..." autocomplete="off">
                        <input type="hidden" name="student_user_id" id="studentId" required>
                        <div id="studentResults" class="list-group" style="max-height:200px;overflow-y:auto;display:none;"></div>
                        <div id="studentSelected" class="alert alert-success py-1 px-2 small mt-1" style="display:none;"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason <span class="text-muted small">(optional)</span></label>
                        <input type="text" name="reason" class="form-control" placeholder="e.g. Behavioural, Academic, Social, Emotional">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Initial Remarks <span class="text-muted small">(optional)</span></label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="Any notes about the counselling need..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add to Counselling</button>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
var studentList = [
    @foreach($students as $p)
    @if($p->student)
    {
        id: {{ $p->student->id }},
        name: "{{ addslashes($p->student->first_name . ' ' . $p->student->last_name) }}",
        class: "{{ optional($p->schoolClass)->class_name }} {{ optional($p->section)->section_name }}",
        display_id: "{{ optional($p->student->admission)->general_id ?? optional($p->student->admission)->dga_admission_no ?? '' }}"
    },
    @endif
    @endforeach
];

var searchInput    = document.getElementById('studentSearch');
var studentIdInput = document.getElementById('studentId');
var resultsBox     = document.getElementById('studentResults');
var selectedBox    = document.getElementById('studentSelected');

searchInput.addEventListener('input', function() {
    var q = this.value.trim().toLowerCase();
    resultsBox.innerHTML = '';
    selectedBox.style.display = 'none';
    studentIdInput.value = '';

    if (q.length < 2) { resultsBox.style.display = 'none'; return; }

    var matches = studentList.filter(function(s) {
        return s.name.toLowerCase().indexOf(q) !== -1;
    }).slice(0, 10);

    if (matches.length === 0) {
        resultsBox.innerHTML = '<div class="list-group-item text-muted small">No students found</div>';
        resultsBox.style.display = 'block';
        return;
    }

    matches.forEach(function(s) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'list-group-item list-group-item-action small py-1';
        btn.innerHTML = '<strong>' + s.name + '</strong> <span class="text-muted">— ' + s.class + (s.display_id ? ' (' + s.display_id + ')' : '') + '</span>';
        btn.addEventListener('click', function() {
            studentIdInput.value = s.id;
            searchInput.value = '';
            resultsBox.style.display = 'none';
            selectedBox.innerHTML = '&#10003; <strong>' + s.name + '</strong> — ' + s.class + (s.display_id ? ' (' + s.display_id + ')' : '');
            selectedBox.style.display = 'block';
        });
        resultsBox.appendChild(btn);
    });
    resultsBox.style.display = 'block';
});

document.getElementById('addModal').addEventListener('hidden.bs.modal', function() {
    searchInput.value = '';
    studentIdInput.value = '';
    resultsBox.style.display = 'none';
    selectedBox.style.display = 'none';
});
</script>
@endsection
