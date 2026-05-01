@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h1 class="display-6 mb-0"><i class="bi bi-table me-2"></i>
                            {{ auth()->user()->role === 'admin' ? 'Event Report' : 'My Activities' }}
                        </h1>
                        <a href="{{ route('events.show') }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-calendar-plus me-1"></i> Log Activity
                        </a>
                    </div>

                    {{-- Filters --}}
                    <form method="GET" action="{{ route('events.report') }}" class="bg-white p-3 rounded shadow-sm mb-4">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small mb-1">Activity Type</label>
                                <input type="text" name="activity_type" class="form-control form-control-sm"
                                    value="{{ request('activity_type') }}" placeholder="e.g. Home Visit">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small mb-1">From</label>
                                <input type="date" name="date_from" class="form-control form-control-sm"
                                    value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small mb-1">To</label>
                                <input type="date" name="date_to" class="form-control form-control-sm"
                                    value="{{ request('date_to') }}">
                            </div>
                            @if(auth()->user()->role === 'admin')
                            <div class="col-md-3">
                                <label class="form-label small mb-1">Teacher</label>
                                <select name="created_by" class="form-select form-select-sm">
                                    <option value="">All teachers</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}" {{ request('created_by') == $teacher->id ? 'selected' : '' }}>
                                            {{ $teacher->first_name }} {{ $teacher->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            <div class="col-md-2 d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                                <a href="{{ route('events.report') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                            </div>
                        </div>
                    </form>

                    @if($events->isEmpty())
                        <p class="text-muted">No activities found for the selected filters.</p>
                    @else
                    <div class="table-responsive bg-white shadow-sm rounded">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Title</th>
                                    <th>Activity Type</th>
                                    <th>Location</th>
                                    <th>Duration</th>
                                    <th>Participants</th>
                                    @if(auth()->user()->role === 'admin')
                                    <th>Logged by</th>
                                    @endif
                                    <th class="text-center">Photo</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($events as $event)
                                <tr>
                                    <td class="text-nowrap">{{ \Carbon\Carbon::parse($event->start)->format('d M Y') }}</td>
                                    <td class="fw-semibold">{{ $event->title }}</td>
                                    <td>{{ $event->activity_type ?: '—' }}</td>
                                    <td>{{ $event->location ?: '—' }}</td>
                                    <td>{{ $event->duration ?: '—' }}</td>
                                    <td>
                                        {{ $event->participants ?: '—' }}
                                        @if($event->participant_count)
                                            <span class="badge bg-light text-dark ms-1">{{ $event->participant_count }}</span>
                                        @endif
                                    </td>
                                    @if(auth()->user()->role === 'admin')
                                    <td>{{ $event->creator ? $event->creator->first_name . ' ' . $event->creator->last_name : '—' }}</td>
                                    @endif
                                    <td class="text-center">
                                        @if($event->photo_url)
                                            <a href="{{ $event->photo_url }}" target="_blank">
                                                <img src="{{ $event->photo_url }}" alt="photo" style="height:40px;width:56px;object-fit:cover;" class="rounded">
                                            </a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                            data-bs-toggle="modal" data-bs-target="#reportDetailModal{{ $event->id }}">
                                            View
                                        </button>
                                    </td>
                                </tr>

                                {{-- Detail modal for this row --}}
                                <div class="modal fade" id="reportDetailModal{{ $event->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-semibold">{{ $event->title }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body small">
                                                <div class="row g-2">
                                                    <div class="col-md-6">
                                                        <span class="text-muted">Date</span>
                                                        <div>{{ \Carbon\Carbon::parse($event->start)->format('d M Y') }}
                                                            @if($event->end && $event->end !== $event->start)
                                                                – {{ \Carbon\Carbon::parse($event->end)->format('d M Y') }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                    @if($event->activity_type)
                                                    <div class="col-md-6">
                                                        <span class="text-muted">Activity Type</span>
                                                        <div>{{ $event->activity_type }}</div>
                                                    </div>
                                                    @endif
                                                    @if($event->location)
                                                    <div class="col-md-6">
                                                        <span class="text-muted">Location</span>
                                                        <div>{{ $event->location }}</div>
                                                    </div>
                                                    @endif
                                                    @if($event->duration)
                                                    <div class="col-md-6">
                                                        <span class="text-muted">Duration</span>
                                                        <div>{{ $event->duration }}</div>
                                                    </div>
                                                    @endif
                                                    @if($event->participants)
                                                    <div class="col-md-8">
                                                        <span class="text-muted">Participants</span>
                                                        <div>{{ $event->participants }}</div>
                                                    </div>
                                                    @endif
                                                    @if($event->participant_count)
                                                    <div class="col-md-4">
                                                        <span class="text-muted">Count</span>
                                                        <div>{{ $event->participant_count }}</div>
                                                    </div>
                                                    @endif
                                                    @if($event->description)
                                                    <div class="col-12">
                                                        <span class="text-muted">Description</span>
                                                        <div>{{ $event->description }}</div>
                                                    </div>
                                                    @endif
                                                    @if($event->purpose)
                                                    <div class="col-12">
                                                        <span class="text-muted">Purpose / Action Taken</span>
                                                        <div>{{ $event->purpose }}</div>
                                                    </div>
                                                    @endif
                                                    @if($event->skills_values)
                                                    <div class="col-12">
                                                        <span class="text-muted">Skills / Values</span>
                                                        <div>{{ $event->skills_values }}</div>
                                                    </div>
                                                    @endif
                                                    @if($event->outcome)
                                                    <div class="col-12">
                                                        <span class="text-muted">Outcome</span>
                                                        <div>{{ $event->outcome }}</div>
                                                    </div>
                                                    @endif
                                                    @if(auth()->user()->role === 'admin')
                                                    <div class="col-12">
                                                        <span class="text-muted">Logged by</span>
                                                        <div>{{ $event->creator ? $event->creator->first_name . ' ' . $event->creator->last_name : '—' }}</div>
                                                    </div>
                                                    @endif
                                                    @if($event->photo_url)
                                                    <div class="col-12">
                                                        <span class="text-muted">Photo</span>
                                                        <div class="mt-1">
                                                            <img src="{{ $event->photo_url }}" class="img-fluid rounded" style="max-height:300px;" alt="Event photo">
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $events->links() }}
                    </div>
                    @endif
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
