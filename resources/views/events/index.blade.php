@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h1 class="display-6 mb-0"><i class="bi bi-calendar-event me-2"></i>Events</h1>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-primary" id="btn_log_activity">
                                <i class="bi bi-plus me-1"></i> Log Activity
                            </button>
                            <a href="{{ route('events.report') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-table me-1"></i> View Report
                            </a>
                        </div>
                    </div>
                    <p class="text-muted small mb-3">Click <strong>Log Activity</strong> to record an activity, or click and drag on the calendar to pick a date.</p>
                    <div class="row bg-white p-4 shadow-sm">
                        @include('components.events.event-calendar', ['editable' => 'true', 'selectable' => 'true'])
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@push('scripts')
<script>
    document.getElementById('btn_log_activity').addEventListener('click', function () {
        var today = new Date().toISOString().slice(0, 10);
        document.getElementById('createEventForm').reset();
        document.getElementById('create_start').value        = today + ' 00:00:00';
        document.getElementById('create_end').value          = today + ' 00:00:00';
        document.getElementById('create_activity_date').value = today;
        new bootstrap.Modal(document.getElementById('createEventModal')).show();
    });

    if (new URLSearchParams(window.location.search).get('open') === '1') {
        document.getElementById('btn_log_activity').click();
    }
</script>
@endpush
@endsection
