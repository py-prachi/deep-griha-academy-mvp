@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    @php
                        $isAdmin = auth()->user()->role === 'admin';
                        $isMyActivities = request()->filled('created_by') && (int) request('created_by') === (int) auth()->id();
                    @endphp
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h1 class="display-6 mb-0"><i class="bi bi-table me-2"></i>
                            {{ $isMyActivities ? 'My Activities' : 'Event Report' }}
                        </h1>
                        <a href="{{ route('events.show') }}?open=1" class="btn btn-sm btn-primary">
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
                            <div class="col-md-3">
                                <label class="form-label small mb-1">Teacher</label>
                                <select name="created_by" class="form-select form-select-sm">
                                    <option value="">All teachers</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}" {{ request('created_by') == $teacher->id ? 'selected' : '' }}>
                                            {{ $teacher->full_name }}{{ (int)$teacher->id === (int)auth()->id() ? ' (me)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                                <a href="{{ route('events.report') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                            </div>
                        </div>
                        <div class="mt-2 text-end">
                            <a href="{{ route('events.report.pdf') }}?{{ http_build_query(request()->only(['activity_type','date_from','date_to','created_by'])) }}"
                               class="btn btn-sm btn-outline-danger" target="_blank">
                                <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF
                            </a>
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
                                    <th>Grade</th>
                                    <th>Location</th>
                                    <th>Duration</th>
                                    <th>Participants</th>
                                    <th>Logged by</th>
                                    <th class="text-center">Photo</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($events as $event)
                                @php $canEdit = $isAdmin || (int)$event->created_by === (int)auth()->id(); @endphp
                                <tr>
                                    <td class="text-nowrap">{{ \Carbon\Carbon::parse($event->start)->format('d M Y') }}</td>
                                    <td class="fw-semibold">{{ $event->title }}</td>
                                    <td>{{ $event->activity_type ?: '—' }}</td>
                                    <td>{{ $event->grade ?: '—' }}</td>
                                    <td>{{ $event->location ?: '—' }}</td>
                                    <td>{{ $event->duration ?: '—' }}</td>
                                    <td>
                                        {{ $event->participants ?: '—' }}
                                        @if($event->participant_count)
                                            <span class="badge bg-light text-dark ms-1">{{ $event->participant_count }}</span>
                                        @endif
                                    </td>
                                    <td>{{ optional($event->creator)->full_name ?? '—' }}</td>
                                    <td class="text-center">
                                        @if($event->photo_url)
                                            <a href="{{ $event->photo_url }}" target="_blank" class="btn btn-sm btn-outline-secondary py-0 px-1"><i class="bi bi-image"></i></a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">
                                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                            data-bs-toggle="modal" data-bs-target="#reportDetailModal{{ $event->id }}">
                                            View
                                        </button>
                                        @if($canEdit)
                                        <button type="button" class="btn btn-outline-primary btn-sm ms-1 btn-edit-activity"
                                            data-id="{{ $event->id }}"
                                            data-start="{{ \Carbon\Carbon::parse($event->start)->format('Y-m-d') }}"
                                            data-activity_type="{{ $event->activity_type }}"
                                            data-grade="{{ $event->grade }}"
                                            data-location="{{ $event->location }}"
                                            data-duration="{{ $event->duration }}"
                                            data-participants="{{ $event->participants }}"
                                            data-participant_count="{{ $event->participant_count }}"
                                            data-description="{{ $event->description }}"
                                            data-purpose="{{ $event->purpose }}"
                                            data-skills_values="{{ $event->skills_values }}"
                                            data-outcome="{{ $event->outcome }}"
                                            data-photo_url="{{ $event->photo_url }}"
                                            data-publish_to_calendar="{{ $event->publish_to_calendar ? 1 : 0 }}"
                                            title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        @endif
                                        <a href="{{ route('events.pdf', $event->id) }}" class="btn btn-outline-danger btn-sm ms-1" title="Download PDF">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </a>
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
                                                    @if($event->grade)
                                                    <div class="col-md-6">
                                                        <span class="text-muted">Grade</span>
                                                        <div>{{ $event->grade }}</div>
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
                                                        <span class="text-muted">Remarks</span>
                                                        <div>{{ $event->outcome }}</div>
                                                    </div>
                                                    @endif
                                                    <div class="col-12">
                                                        <span class="text-muted">Logged by</span>
                                                        <div>{{ optional($event->creator)->full_name ?? '—' }}</div>
                                                    </div>
                                                    @if($event->photo_url)
                                                    <div class="col-12">
                                                        <span class="text-muted">Pic</span>
                                                        <div class="mt-1">
                                                            <a href="{{ $event->photo_url }}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-image me-1"></i> View Photo</a>
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

{{-- Shared edit modal — populated per-row from the Edit button's data attributes --}}
<div class="modal fade" id="reportEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Activity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="reportEditForm">
                    @csrf
                    <input type="hidden" id="re_id" name="id">
                    <input type="hidden" id="re_start" name="start">
                    <input type="hidden" id="re_end" name="end">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Activity / Event's Name</label>
                            <input type="text" class="form-control" id="re_activity_type" name="activity_type">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Grade</label>
                            <select class="form-select" name="grade" id="re_grade">
                                @include('components.events.grade-options')
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Date of activity/event <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="re_activity_date" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description of activity/event</label>
                            <textarea class="form-control" id="re_description" name="description" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Purpose of activity/event</label>
                            <textarea class="form-control" id="re_purpose" name="purpose" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Place of activity/event</label>
                            <input type="text" class="form-control" id="re_location" name="location">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Duration of activity/event</label>
                            <input type="text" class="form-control" id="re_duration" name="duration">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Attendee's details</label>
                            <input type="text" class="form-control" id="re_participants" name="participants">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. of Attendants</label>
                            <input type="number" class="form-control" id="re_participant_count" name="participant_count" min="1">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Please mention if any training/DGS core values components applied</label>
                            <input type="text" class="form-control" id="re_skills_values" name="skills_values">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Pic (Google Drive link)</label>
                            <input type="url" class="form-control" id="re_photo_url" name="photo_url">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Remarks</label>
                            <textarea class="form-control" id="re_outcome" name="outcome" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="publish_to_calendar" id="re_publish" value="1">
                                <label class="form-check-label" for="re_publish">
                                    Publish to school calendar <span class="text-muted small">(visible to all teachers &amp; students)</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="reportEditForm" class="btn btn-primary"><i class="bi bi-check2 me-1"></i> Update Activity</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        $('.btn-edit-activity').on('click', function () {
            var d = $(this).data();
            $('#re_id').val(d.id);
            $('#re_activity_date').val(d.start);
            $('#re_start').val(d.start + ' 00:00:00');
            $('#re_end').val(d.start + ' 00:00:00');
            $('#re_activity_type').val(d.activity_type || '');
            $('#re_grade').val(d.grade || '');
            $('#re_location').val(d.location || '');
            $('#re_duration').val(d.duration || '');
            $('#re_participants').val(d.participants || '');
            $('#re_participant_count').val(d.participant_count || '');
            $('#re_description').val(d.description || '');
            $('#re_purpose').val(d.purpose || '');
            $('#re_skills_values').val(d.skillsValues || '');
            $('#re_outcome').val(d.outcome || '');
            $('#re_photo_url').val(d.photoUrl || '');
            $('#re_publish').prop('checked', !!d.publishToCalendar);
            new bootstrap.Modal(document.getElementById('reportEditModal')).show();
        });

        $('#reportEditForm').on('submit', function (e) {
            e.preventDefault();
            var dateVal = $('#re_activity_date').val();
            if (dateVal) {
                $('#re_start').val(dateVal + ' 00:00:00');
                $('#re_end').val(dateVal + ' 00:00:00');
            }
            var formData = new FormData(this);
            formData.append('type', 'edit');
            $.ajax({
                url: "{{ route('events.crud') }}",
                data: formData,
                type: 'POST',
                contentType: false,
                processData: false,
                success: function () {
                    window.location.reload();
                },
                error: function () {
                    alert('Failed to update. You may not have permission to edit this activity.');
                }
            });
        });
    });
</script>
@endsection
