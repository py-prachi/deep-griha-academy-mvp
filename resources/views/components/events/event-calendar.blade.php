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
                        <span class="text-muted">Activity Type</span>
                        <div class="fw-semibold" id="det_type"></div>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted">Date</span>
                        <div class="fw-semibold" id="det_date"></div>
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
                        <span class="text-muted">Outcome</span>
                        <div id="det_outcome"></div>
                    </div>
                    <div class="col-12" id="det_photo_row">
                        <span class="text-muted">Photo</span>
                        <div class="mt-1">
                            <img id="det_photo" src="" class="img-fluid rounded" style="max-height:300px;" alt="Event photo">
                        </div>
                    </div>
                </div>
            </div>
            @if($selectable == 'true')
            <div class="modal-footer" id="det_actions">
                <button type="button" class="btn btn-outline-danger btn-sm" id="btn_delete_event">
                    <i class="bi bi-trash me-1"></i> Delete
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="btn_edit_event">
                    <i class="bi bi-pencil me-1"></i> Edit
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
                <form id="createEventForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="create_start" name="start">
                    <input type="hidden" id="create_end" name="end">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" required placeholder="e.g. Home Visit – Rajan">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Activity Type</label>
                            <input type="text" class="form-control" name="activity_type" placeholder="e.g. Home Visit, Exposure Visit">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Location</label>
                            <input type="text" class="form-control" name="location">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Duration</label>
                            <input type="text" class="form-control" name="duration" placeholder="e.g. 45 minutes, 1 day">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Participant Count</label>
                            <input type="number" class="form-control" name="participant_count" min="1">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Participants</label>
                            <input type="text" class="form-control" name="participants" placeholder="e.g. 40 students + 3 teachers">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="What happened during the activity?"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Purpose / Action Taken</label>
                            <textarea class="form-control" name="purpose" rows="2" placeholder="Objectives or discussion points"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Skills / Values</label>
                            <input type="text" class="form-control" name="skills_values" placeholder="e.g. Teamwork, Discipline, Respect">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Outcome</label>
                            <textarea class="form-control" name="outcome" rows="2" placeholder="What was the result?"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Photo</label>
                            <input type="file" class="form-control" name="photo" accept="image/*">
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

{{-- Edit modal --}}
<div class="modal fade" id="editEventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Activity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editEventForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="edit_event_id" name="id">
                    <input type="hidden" id="edit_start" name="start">
                    <input type="hidden" id="edit_end" name="end">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_title" name="title" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Activity Type</label>
                            <input type="text" class="form-control" id="edit_activity_type" name="activity_type">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Location</label>
                            <input type="text" class="form-control" id="edit_location" name="location">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Duration</label>
                            <input type="text" class="form-control" id="edit_duration" name="duration">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Participant Count</label>
                            <input type="number" class="form-control" id="edit_participant_count" name="participant_count" min="1">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Participants</label>
                            <input type="text" class="form-control" id="edit_participants" name="participants">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Purpose / Action Taken</label>
                            <textarea class="form-control" id="edit_purpose" name="purpose" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Skills / Values</label>
                            <input type="text" class="form-control" id="edit_skills_values" name="skills_values">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Outcome</label>
                            <textarea class="form-control" id="edit_outcome" name="outcome" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Replace Photo</label>
                            <input type="file" class="form-control" name="photo" accept="image/*">
                            <div class="form-text">Leave empty to keep existing photo.</div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="editEventForm" class="btn btn-primary"><i class="bi bi-check2 me-1"></i> Update Activity</button>
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
            height: {{($editable == 'true') ? 500 : "parent"}},
            defaultView: 'month',
            editable: {{$editable}},
            eventLimit: true,
            events: SITEURL + '/calendar-event',
            displayEventTime: false,
            selectable: {{$selectable}},
            selectHelper: {{$selectable}},

            select: function (event_start, event_end) {
                @if($selectable == 'true')
                $('#create_start').val($.fullCalendar.formatDate(event_start, "Y-MM-DD HH:mm:ss"));
                $('#create_end').val($.fullCalendar.formatDate(event_end, "Y-MM-DD HH:mm:ss"));
                $('#createEventForm')[0].reset();
                $('#create_start').val($.fullCalendar.formatDate(event_start, "Y-MM-DD HH:mm:ss"));
                $('#create_end').val($.fullCalendar.formatDate(event_end, "Y-MM-DD HH:mm:ss"));
                new bootstrap.Modal(document.getElementById('createEventModal')).show();
                @endif
                calendar.fullCalendar('unselect');
            },

            eventResize: function (event, delta) {
                $.ajax({
                    url: SITEURL + '/calendar-crud-ajax',
                    data: {
                        title: event.title,
                        start: $.fullCalendar.formatDate(event.start, "Y-MM-DD"),
                        end:   $.fullCalendar.formatDate(event.end,   "Y-MM-DD"),
                        id:    event.id,
                        type:  'edit'
                    },
                    type: "POST",
                    success: function () { displayMessage("Event updated"); }
                });
            },

            eventClick: function (event) {
                currentEvent = event;
                var canEdit = isAdmin || (event.created_by == currentUserId);

                // Populate detail modal
                $('#det_title').text(event.title);

                var start = moment(event.start).format('D MMM YYYY');
                var end   = event.end ? moment(event.end).format('D MMM YYYY') : start;
                $('#det_date').text(start === end ? start : start + ' – ' + end);

                setDetRow('det_type_row',         'det_type',         event.activity_type);
                setDetRow('det_location_row',     'det_location',     event.location);
                setDetRow('det_duration_row',     'det_duration',     event.duration);
                setDetRow('det_participants_row', 'det_participants',  event.participants);
                setDetRow('det_count_row',        'det_count',        event.participant_count);
                setDetRow('det_description_row',  'det_description',  event.description);
                setDetRow('det_purpose_row',      'det_purpose',      event.purpose);
                setDetRow('det_skills_row',       'det_skills',       event.skills_values);
                setDetRow('det_outcome_row',      'det_outcome',      event.outcome);

                if (event.photo_url) {
                    $('#det_photo').attr('src', event.photo_url);
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
            }
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
                        id: data.id, title: data.title, start: data.start, end: data.end
                    }, true);
                    bootstrap.Modal.getInstance(document.getElementById('createEventModal')).hide();
                    displayMessage("Activity logged.");
                },
                error: function () { displayError("Failed to save. Please try again."); }
            });
        });

        // Open edit modal from detail modal
        $('#btn_edit_event').on('click', function () {
            if (!currentEvent) return;
            bootstrap.Modal.getInstance(document.getElementById('eventDetailModal')).hide();
            $('#edit_event_id').val(currentEvent.id);
            $('#edit_start').val($.fullCalendar.formatDate(currentEvent.start, "Y-MM-DD HH:mm:ss"));
            $('#edit_end').val(currentEvent.end ? $.fullCalendar.formatDate(currentEvent.end, "Y-MM-DD HH:mm:ss") : $.fullCalendar.formatDate(currentEvent.start, "Y-MM-DD HH:mm:ss"));
            $('#edit_title').val(currentEvent.title);
            $('#edit_activity_type').val(currentEvent.activity_type || '');
            $('#edit_location').val(currentEvent.location || '');
            $('#edit_duration').val(currentEvent.duration || '');
            $('#edit_participant_count').val(currentEvent.participant_count || '');
            $('#edit_participants').val(currentEvent.participants || '');
            $('#edit_description').val(currentEvent.description || '');
            $('#edit_purpose').val(currentEvent.purpose || '');
            $('#edit_skills_values').val(currentEvent.skills_values || '');
            $('#edit_outcome').val(currentEvent.outcome || '');
            setTimeout(function () {
                new bootstrap.Modal(document.getElementById('editEventModal')).show();
            }, 300);
        });

        // Edit form submit
        $('#editEventForm').on('submit', function (e) {
            e.preventDefault();
            var formData = new FormData(this);
            formData.append('type', 'edit');
            $.ajax({
                url: SITEURL + '/calendar-crud-ajax',
                data: formData,
                type: 'POST',
                contentType: false,
                processData: false,
                success: function (data) {
                    calendar.fullCalendar('removeEvents', data.id);
                    calendar.fullCalendar('renderEvent', {
                        id: data.id, title: data.title, start: data.start, end: data.end
                    }, true);
                    bootstrap.Modal.getInstance(document.getElementById('editEventModal')).hide();
                    displayMessage("Activity updated.");
                },
                error: function () { displayError("Failed to update. Please try again."); }
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
