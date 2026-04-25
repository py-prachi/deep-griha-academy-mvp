@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="mb-0"><i class="bi bi-heart-pulse me-1"></i> Counselling</h4>
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
                                            <th>Student</th>
                                            <th>Class</th>
                                            <th>Since</th>
                                            <th>Reason</th>
                                            <th>Remarks</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($active as $c)
                                        <tr>
                                            <td>
                                                <span class="fw-semibold">{{ optional($c->student)->first_name }} {{ optional($c->student)->last_name }}</span>
                                            </td>
                                            <td class="text-nowrap">{{ $c->class_name }} {{ $c->section_name }}</td>
                                            <td class="text-nowrap">{{ \Carbon\Carbon::parse($c->start_date)->format('d M Y') }}</td>
                                            <td>{{ $c->reason ?: '—' }}</td>
                                            <td style="max-width:220px;">
                                                <span class="small">{{ $c->remarks ?: '—' }}</span>
                                            </td>
                                            <td class="text-center text-nowrap">
                                                <button class="btn btn-sm btn-outline-secondary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editModal{{ $c->id }}">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>
                                                <button class="btn btn-sm btn-outline-success ms-1"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#endModal{{ $c->id }}">
                                                    <i class="bi bi-check2-circle"></i> End
                                                </button>
                                            </td>
                                        </tr>

                                        {{-- Edit modal --}}
                                        <div class="modal fade" id="editModal{{ $c->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <form method="POST" action="{{ route('counselling.update', $c->id) }}">
                                                    @csrf @method('PUT')
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Edit — {{ optional($c->student)->first_name }} {{ optional($c->student)->last_name }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Reason</label>
                                                                <input type="text" name="reason" class="form-control" value="{{ $c->reason }}" placeholder="e.g. Behavioural, Academic, Social">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Remarks / Notes</label>
                                                                <textarea name="remarks" class="form-control" rows="4" placeholder="Session notes, observations...">{{ $c->remarks }}</textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary">Save</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        {{-- End modal --}}
                                        <div class="modal fade" id="endModal{{ $c->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <form method="POST" action="{{ route('counselling.end', $c->id) }}">
                                                    @csrf
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">End Counselling — {{ optional($c->student)->first_name }} {{ optional($c->student)->last_name }}</h5>
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
                                            <th>Student</th>
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
                                            <td>
                                                <span class="fw-semibold">{{ optional($c->student)->first_name }} {{ optional($c->student)->last_name }}</span>
                                            </td>
                                            <td class="text-nowrap">{{ $c->class_name }} {{ $c->section_name }}</td>
                                            <td class="text-nowrap small">
                                                {{ \Carbon\Carbon::parse($c->start_date)->format('d M Y') }}
                                                &rarr;
                                                {{ \Carbon\Carbon::parse($c->end_date)->format('d M Y') }}
                                            </td>
                                            <td>{{ $c->reason ?: '—' }}</td>
                                            <td style="max-width:220px;">
                                                <span class="small">{{ $c->remarks ?: '—' }}</span>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-secondary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editModal{{ $c->id }}">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>
                                            </td>
                                        </tr>

                                        {{-- Edit modal for past --}}
                                        <div class="modal fade" id="editModal{{ $c->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <form method="POST" action="{{ route('counselling.update', $c->id) }}">
                                                    @csrf @method('PUT')
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Edit — {{ optional($c->student)->first_name }} {{ optional($c->student)->last_name }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Reason</label>
                                                                <input type="text" name="reason" class="form-control" value="{{ $c->reason }}">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Remarks / Notes</label>
                                                                <textarea name="remarks" class="form-control" rows="4">{{ $c->remarks }}</textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary">Save</button>
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
                        <label class="form-label">Student <span class="text-danger">*</span></label>
                        <select name="student_user_id" class="form-select" required>
                            <option value="">— Select student —</option>
                            @foreach($students as $p)
                                @if($p->student)
                                <option value="{{ $p->student->id }}">
                                    {{ $p->student->first_name }} {{ $p->student->last_name }}
                                    — {{ optional($p->schoolClass)->class_name }} {{ optional($p->section)->section_name }}
                                </option>
                                @endif
                            @endforeach
                        </select>
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
@endsection
