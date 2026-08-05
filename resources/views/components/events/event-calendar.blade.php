<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

<div id='full_calendar_events'></div>

{{-- Detail modal (always present — used on dashboard and events page) --}}
<div class="modal fade" id="eventDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold" id="det_title"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 small">
                    <div class="col-md-6" id="det_type_row">
                        <span class="text-muted">Activity / Event's Name</span>
                        <div class="fw-semibold" id="det_type"></div>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted">Date</span>
                        <div class="fw-semibold" id="det_date"></div>
                    </div>
                    <div class="col-md-6" id="det_grade_row">
                        <span class="text-muted">Grade</span>
                        <div class="fw-semibold" id="det_grade"></div>
                    </div>
                    <div class="col-md-6" id="det_location_row">
                        <span class="text-muted">Location</span>
                        <div id="det_location"></div>
                    </div>
                    <div class="col-md-6" id="det_duration_row">
                        <span class="text-muted">Duration</span>
                        <div id="det_duration"></div>
                    </div>
                    <div class="col-md-8" id="det_participants_row">
                        <span class="text-muted">Participants</span>
                        <div id="det_participants"></div>
                    </div>
                    <div class="col-md-4" id="det_count_row">
                        <span class="text-muted">Count</span>
                        <div id="det_count"></div>
                    </div>
                    <div class="col-12" id="det_description_row">
                        <span class="text-muted">Description</span>
                        <div id="det_description"></div>
                    </div>
                    <div class="col-12" id="det_purpose_row">
                        <span class="text-muted">Purpose / Action Taken</span>
                        <div id="det_purpose"></div>
                    </div>
                    <div class="col-12" id="det_skills_row">
                        <span class="text-muted">Skills / Values</span>
                        <div id="det_skills"></div>
                    </div>
                    <div class="col-12" id="det_outcome_row">
                        <span class="text-muted">Remarks</span>
                        <div id="det_outcome"></div>
                    </div>
                    <div class="col-12" id="det_photo_row">
                        <span class="text-muted">Photo</span>
                        <div class="mt-1">
                            <a id="det_photo" href="" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-image me-1"></i> View Photo</a>
                        </div>
                    </div>
                </div>
            </div>
            @if($selectable == 'true')
            <div class="modal-footer" id="det_actions">
                <button type="button" class="btn btn-outline-danger btn-sm" id="btn_delete_event">
                    <i class="bi bi-trash me-1"></i> Delete
                </button>
            </div>
            @endif
        </div>
    </div>
</div>

@if($selectable == 'true')
{{-- Create modal --}}
<div class="modal fade" id="createEventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-calendar-plus me-2"></i>Log Activity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createEventForm">
                    @csrf
                    <input type="hidden" id="create_start" name="start">
                    <input type="hidden" id="create_end" name="end">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Activity / Event's Name</label>
                            <input type="text" class="form-control" name="activity_type" placeholder="e.g. Academics, Awareness Sessions">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Grade</label>
                            <select class="form-select" name="grade" id="create_grade">
                                @include('components.events.grade-options')
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Date of activity/event <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="create_activity_date" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description of activity/event</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="What happened during the activity?"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Purpose of activity/event</label>
                            <textarea class="form-control" name="purpose" rows="2" placeholder="Objectives or discussion points"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Place of activity/event</label>
                            <input type="text" class="form-control" name="location">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Duration of activity/event</label>
                            <input type="text" class="form-control" name="duration" placeholder="e.g. 45 minutes, 1 day">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Attendee's details</label>
                            <input type="text" class="form-control" name="participants" placeholder="e.g. 40 students + 3 teachers">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. of Attendants</label>
                            <input type="number" class="form-control" name="participant_count" min="1">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Please mention if any training/DGS core values components applied</label>
                            <input type="text" class="form-control" name="skills_values" placeholder="e.g. Participation, Equal Opportunity">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Pic (Google Drive link)</label>
                            <input type="url" class="form-control" name="photo_url" placeholder="Paste Google Drive share link">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Remarks</label>
                            <textarea class="form-control" name="outcome" rows="2" placeholder="Any remarks or observations"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="publish_to_calendar" id="create_publish" value="1">
                                <label class="form-check-label" for="create_publish">
                                    Publish to school calendar <span class="text-muted small">(visible to all teachers &amp; students)</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="createEventForm" class="btn btn-primary"><i class="bi bi-check2 me-1"></i> Save Activity</button>
            </div>
        </div>
    </div>
</div>

@endif

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    $(document).ready(function () {
        var SITEURL = "{{ url('/') }}";
        var currentUserId = {{ auth()->id() ?? 0 }};
        var isAdmin = {{ auth()->user() && auth()->user()->role === 'admin' ? 'true' : 'false' }};

        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        var currentEvent = null;

        var calendar = $('#full_calendar_events').fullCalendar({
            header: {
                left: 'prev,next',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            height: {{($editable == 'true') ? 500 : 450}},
            defaultView: 'month',
            editable: {{$editable}},
            eventLimit: true,
            events: SITEURL + '/calendar-event',
            displayEventTime: false,
            selectable: {{$selectable}},
            selectHelper: {{$selectable}},

            select: function (event_start, event_end) {
                @if($selectable == 'true')
                var dateStr = $.fullCalendar.formatDate(event_start, "Y-MM-DD");
                $('#createEventForm')[0].reset();
                $('#create_start').val(dateStr + ' 00:00:00');
                $('#create_end').val(dateStr + ' 00:00:00');
                $('#create_activity_date').val(dateStr);
                new bootstrap.Modal(document.getElementById('createEventModal')).show();
                @endif
                calendar.fullCalendar('unselect');
            },

            eventClick: function (event) {
                currentEvent = event;

                var start = moment(event.start).format('D MMM YYYY');
                var end   = event.end ? moment(event.end).format('D MMM YYYY') : start;

                if (event.type === 'holiday') {
                    // Holidays are read-only entries, not teacher-logged activities —
                    // show just the name/date, no activity fields, no edit actions.
                    $('#det_title').text('Holiday — ' + event.title);
                    $('#det_date').text(start === end ? start : start + ' – ' + end);
                    ['det_type_row','det_grade_row','det_location_row','det_duration_row',
                     'det_participants_row','det_count_row','det_description_row',
                     'det_purpose_row','det_skills_row','det_outcome_row','det_photo_row']
                        .forEach(function (id) { $('#' + id).hide(); });
                    @if($selectable == 'true')
                    $('#det_actions').hide();
                    @endif
                    new bootstrap.Modal(document.getElementById('eventDetailModal')).show();
                    return;
                }

                var canEdit = isAdmin || (event.created_by == currentUserId);

                // Populate detail modal
                $('#det_title').text(event.title);
                $('#det_date').text(start === end ? start : start + ' – ' + end);

                setDetRow('det_type_row',         'det_type',         event.activity_type);
                setDetRow('det_grade_row',        'det_grade',        event.grade);
                setDetRow('det_location_row',     'det_location',     event.location);
                setDetRow('det_duration_row',     'det_duration',     event.duration);
                setDetRow('det_participants_row', 'det_participants',  event.participants);
                setDetRow('det_count_row',        'det_count',        event.participant_count);
                setDetRow('det_description_row',  'det_description',  event.description);
                setDetRow('det_purpose_row',      'det_purpose',      event.purpose);
                setDetRow('det_skills_row',       'det_skills',       event.skills_values);
                setDetRow('det_outcome_row',      'det_outcome',      event.outcome);

                if (event.photo_url) {
                    $('#det_photo').attr('href', event.photo_url);
                    $('#det_photo_row').show();
                } else {
                    $('#det_photo_row').hide();
                }

                @if($selectable == 'true')
                if (canEdit) {
                    $('#det_actions').show();
                } else {
                    $('#det_actions').hide();
                }
                @endif

                new bootstrap.Modal(document.getElementById('eventDetailModal')).show();
            }@if($selectable == 'false'),

            dayClick: function (date) {
                calendar.fullCalendar('changeView', 'agendaDay');
                calendar.fullCalendar('gotoDate', date);
            }@endif
        });

        function setDetRow(rowId, fieldId, value) {
            if (value) {
                $('#' + fieldId).text(value);
                $('#' + rowId).show();
            } else {
                $('#' + rowId).hide();
            }
        }

        @if($selectable == 'true')
        // Create form submit
        $('#createEventForm').on('submit', function (e) {
            e.preventDefault();
            var d = $('#create_activity_date').val();
            if (d) { $('#create_start').val(d + ' 00:00:00'); $('#create_end').val(d + ' 00:00:00'); }
            var formData = new FormData(this);
            formData.append('type', 'create');
            $.ajax({
                url: SITEURL + '/calendar-crud-ajax',
                data: formData,
                type: 'POST',
                contentType: false,
                processData: false,
                success: function (data) {
                    calendar.fullCalendar('renderEvent', {
                        id: data.id, title: data.title, start: data.start, end: data.end,
                        activity_type: data.activity_type, grade: data.grade, description: data.description,
                        purpose: data.purpose, location: data.location, duration: data.duration,
                        participants: data.participants, participant_count: data.participant_count,
                        skills_values: data.skills_values, photo_url: data.photo_url,
                        outcome: data.outcome, publish_to_calendar: data.publish_to_calendar,
                        created_by: data.created_by,
                    }, true);
                    bootstrap.Modal.getInstance(document.getElementById('createEventModal')).hide();
                    displayMessage("Activity logged.");
                },
                error: function () { displayError("Failed to save. Please try again."); }
            });
        });

        // Delete from detail modal
        $('#btn_delete_event').on('click', function () {
            if (!currentEvent) return;
            if (!confirm('Delete this activity? This cannot be undone.')) return;
            $.ajax({
                url: SITEURL + '/calendar-crud-ajax',
                data: { id: currentEvent.id, type: 'delete' },
                type: 'POST',
                success: function () {
                    calendar.fullCalendar('removeEvents', currentEvent.id);
                    bootstrap.Modal.getInstance(document.getElementById('eventDetailModal')).hide();
                    displayMessage("Activity deleted.");
                },
                error: function () { displayError("Could not delete. You may not have permission."); }
            });
        });
        @endif

        function displayMessage(message) {
            toastr.success(message, 'Events');
        }
        function displayError(message) {
            toastr.error(message, 'Events');
        }
    });
</script>
