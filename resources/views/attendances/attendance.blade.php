@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/fullcalendar5.9.0.min.css') }}">
<script src="{{ asset('js/fullcalendar5.9.0.main.min.js') }}"></script>
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-calendar2-week"></i> View Attendance
                    </h1>

                    <h5><i class="bi bi-person"></i> Student Name: {{$student->first_name}} {{$student->last_name}}</h5>
                    <div class="row mt-3">
                        <div class="col bg-white p-3 border shadow-sm">
                            <div id="attendanceCalendar"></div>
                        </div>
                    </div>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Inline attendance marking for selected date --}}
                    @if(
                        $selected_date &&
                        !$attendance_for_date &&
                        in_array(auth()->user()->role, ['admin','teacher'])
                    )
                        <div class="row mt-3">
                            <div class="col bg-white border shadow-sm p-3">
                                <h6 class="mb-2">
                                    Mark attendance for <strong>{{ $selected_date }}</strong>
                                </h6>

                                <form method="POST" action="{{ route('attendances.store') }}">
                                    @csrf
                                    <input type="hidden" name="attendance_date" value="{{ $selected_date }}">
                                    <input type="hidden" name="session_id" value="{{ $current_school_session_id }}">
                                    <input type="hidden" name="attendance_date" value="{{ $selected_date }}">
                                    <input type="hidden" name="class_id" value="{{ $class_id }}">
                                    <input type="hidden" name="section_id" value="{{ $section_id }}">
                                    <input type="hidden" name="course_id" value="{{ $course_id }}">
                                    <input type="hidden" name="student_ids[]" value="{{ $student->id }}">

                                    <label class="me-3">
                                        <input type="radio" name="status[{{ $student->id }}]" value="on" checked>
                                        Present
                                    </label>

                                    <label class="me-3">
                                        <input type="radio" name="status[{{ $student->id }}]" value="off">
                                        Absent
                                    </label>

                                    <button type="submit" class="btn btn-sm btn-primary ms-3">
                                        Save
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif


                    <div class="row mt-4">
                        <div class="col bg-white border shadow-sm p-3">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th scope="col">Status</th>
                                        <th scope="col">Date</th>
                                        <th scope="col">Context</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($attendances as $attendance)
                                        <tr>
                                            <td>
                                                @if(in_array(auth()->user()->role, ['admin', 'teacher']))

    <form method="POST" action="{{ route('attendance.update', $attendance->id) }}">
        @csrf

        <label class="me-2">
            <input type="checkbox" name="present" {{ $attendance->status == "on" ? 'checked' : '' }}>
            Present
        </label>

        <button type="submit" class="btn btn-sm btn-primary">Update</button>
    </form>
@else
    @if ($attendance->status == "on")
        <span class="badge bg-success">PRESENT</span>
    @else
        <span class="badge bg-danger">ABSENT</span>
    @endif
@endif

                                                
                                            </td>
                                            <td>{{$attendance->created_at}}</td>
                                            <td>{{($attendance->section == null)?$attendance->course->course_name:$attendance->section->section_name}}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@php
$events = array();
if(count($attendances) > 0){
    foreach ($attendances as $attendance){
        if($attendance->status == "on"){
            $events[] = ['title'=> "Present", 'start' => $attendance->created_at, 'color'=>'green'];
        } else {
            $events[] = ['title'=> "Absent", 'start' => $attendance->created_at, 'color'=>'red'];
        }
    }
}
@endphp
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('attendanceCalendar');
    var attEvents = @json($events);
                            
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 350,
        events: attEvents,
        dateClick: function(info) {
            const url = new URL(window.location.href);
            url.searchParams.set('date', info.dateStr);
            window.location.href = url.toString();
        }   
    });
    calendar.render();
});
</script>
@endsection
