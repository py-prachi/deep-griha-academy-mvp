@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-box-arrow-right"></i> Exited Students
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item active">Exited Students</li>
                        </ol>
                    </nav>

                    @include('session-messages')

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>{{ count($exits) }} record(s) in this tab</span>
                        <a href="{{ route('exits.create') }}" class="btn btn-danger btn-sm">
                            <i class="bi bi-box-arrow-right"></i> Record Student Exit
                        </a>
                    </div>

                    {{-- TABS --}}
                    <ul class="nav nav-tabs mb-3">
                        <li class="nav-item">
                            <a class="nav-link {{ $tab == 'genuine' ? 'active' : '' }}" href="{{ route('exits.index', ['tab' => 'genuine']) }}">
                                Genuine Exits <span class="badge bg-dark ms-1">{{ $counts['genuine'] }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $tab == 'correction' ? 'active' : '' }}" href="{{ route('exits.index', ['tab' => 'correction']) }}">
                                Corrections <span class="badge bg-secondary ms-1">{{ $counts['correction'] }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $tab == 'uncategorized' ? 'active' : '' }}" href="{{ route('exits.index', ['tab' => 'uncategorized']) }}">
                                Needs Review
                                @if($counts['uncategorized'] > 0)
                                    <span class="badge bg-warning text-dark ms-1">{{ $counts['uncategorized'] }}</span>
                                @else
                                    <span class="badge bg-success ms-1">0</span>
                                @endif
                            </a>
                        </li>
                    </ul>

                    @if($tab == 'genuine')
                        <p class="text-muted small">Students who actually left the school. These count as attrition in reports and are expected to have a Leaving Certificate.</p>
                    @elseif($tab == 'correction')
                        <p class="text-muted small">Confirmed admissions removed to fix a mistake (wrong record, duplicate entry, etc.) rather than a real departure. No Leaving Certificate needed; excluded from exit counts in reports.</p>
                    @else
                        <p class="text-muted small">Exit records created before this categorization existed. Review each and mark it Genuine or Correction below, then save — this updates the counts shown in reports.</p>
                    @endif

                    @if($tab == 'uncategorized' && count($exits) > 0)
                        {{-- Bulk categorize form: each row defaults to "Genuine" (checked) since that
                             matches historical reality — the correction workflow is new. Admin unchecks
                             only the ones that were actually record corrections, then saves once. --}}
                        <form method="POST" action="{{ route('exits.categorizeBulk') }}">
                            @csrf
                            <div class="card shadow-sm">
                                <div class="card-body p-0">
                                    <table class="table table-hover table-sm mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Student Name</th>
                                                <th>Class</th>
                                                <th>Exit Date</th>
                                                <th>Reason</th>
                                                <th class="text-center">Genuine Exit?</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($exits as $exit)
                                                @php $admission = $exit->admission; @endphp
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>
                                                        {{ $admission ? $admission->student_name : '—' }}
                                                        <input type="hidden" name="exit_ids[]" value="{{ $exit->id }}">
                                                    </td>
                                                    <td>{{ $admission && $admission->schoolClass ? $admission->schoolClass->class_name : '—' }}</td>
                                                    <td>{{ $exit->exit_date ? $exit->exit_date->format('d/m/Y') : '—' }}</td>
                                                    <td>
                                                        @if($exit->reason_for_leaving)
                                                            {{ \Illuminate\Support\Str::limit($exit->reason_for_leaving, 40) }}
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="checkbox" class="form-check-input" name="genuine[]" value="{{ $exit->id }}" checked title="Checked = Genuine Exit, Unchecked = Correction">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="card-footer d-flex justify-content-between align-items-center">
                                    <small class="text-muted">Uncheck a row only if that "exit" was actually a mistaken record being removed, not a real departure.</small>
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="bi bi-check2-circle"></i> Save Categorization
                                    </button>
                                </div>
                            </div>
                        </form>
                    @else
                        <div class="card shadow-sm">
                            <div class="card-body p-0">
                                <table class="table table-hover table-sm mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Student Name</th>
                                            <th>Class</th>
                                            <th>Section</th>
                                            <th>Exit Date</th>
                                            <th>Reason</th>
                                            <th>Rating</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($exits as $exit)
                                            @php $admission = $exit->admission; @endphp
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    {{ $admission ? $admission->student_name : '—' }}
                                                    @if($admission && $admission->dga_admission_no)
                                                        <br><small class="text-muted">{{ $admission->dga_admission_no }}</small>
                                                    @elseif($admission && $admission->general_id)
                                                        <br><small class="text-muted">{{ $admission->general_id }}</small>
                                                    @endif
                                                </td>
                                                <td>{{ $admission && $admission->schoolClass ? $admission->schoolClass->class_name : '—' }}</td>
                                                <td>{{ $admission && $admission->section ? $admission->section->section_name : '—' }}</td>
                                                <td>{{ $exit->exit_date ? $exit->exit_date->format('d/m/Y') : '—' }}</td>
                                                <td>
                                                    @if($exit->reason_for_leaving)
                                                        {{ \Illuminate\Support\Str::limit($exit->reason_for_leaving, 40) }}
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($exit->rating)
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <i class="bi bi-star{{ $i <= $exit->rating ? '-fill text-warning' : '' }}"></i>
                                                        @endfor
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('exits.show', $exit->id) }}" class="btn btn-outline-secondary btn-sm">View</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-4">No records in this tab.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
