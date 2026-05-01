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
                        <a href="{{ route('events.report') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-table me-1"></i> View Report
                        </a>
                    </div>
                    <p class="text-muted small mb-3">Click and drag on the calendar to log a new activity.</p>
                    <div class="row bg-white p-4 shadow-sm">
                        @include('components.events.event-calendar', ['editable' => 'true', 'selectable' => 'true'])
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
