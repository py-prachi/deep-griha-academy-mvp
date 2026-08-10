@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3"><i class="bi bi-calendar2-week"></i> Attendance</h1>

                    @include('session-messages')

                    @if(!empty($not_assigned) && $not_assigned)
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        You are not assigned as a Class Teacher for the current session.
                        Please contact the admin to get assigned.
                    </div>
                    @endif

                    @if(!empty($ct_assignments))
                    <p class="text-muted small mb-3">You are the Class Teacher for more than one class — pick one below.</p>
                    <div class="row">
                        @foreach ($ct_assignments as $assignment)
                        <div class="col-12">
                            <div class="card my-3">
                                <div class="card-header bg-transparent">
                                    <i class="bi bi-diagram-2"></i>
                                    {{ $assignment->schoolClass->class_name }} {{ $assignment->section->section_name }}
                                </div>
                                <div class="card-body text-dark">
                                    <div class="list-group mb-2">
                                        <a href="{{ route('attendance.create.show', ['class_id' => $assignment->class_id, 'section_id' => $assignment->section_id]) }}">
                                            Take Attendance
                                        </a>
                                        <a href="{{ route('attendance.history', ['class_id' => $assignment->class_id, 'section_id' => $assignment->section_id]) }}">
                                            Attendance History
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="row">
                        <div class="col">
                            <div class="row">
                                @foreach ($classes_and_sections['school_classes'] as $school_class)
                                <div class="col-12">
                                    <div class="card my-3">
                                        <div class="card-header bg-transparent">
                                            <i class="bi bi-diagram-2"></i> {{$school_class->class_name}}
                                        </div>
                                        <div class="card-body text-dark">
                                            @if ($academic_setting->attendance_type == 'course')
                                                @foreach ($courses as $course)
                                                    @if ($course->class_id == $school_class->id)
                                                    <h6>
                                                        Course: {{$course->course_name}}
                                                    </h6>
                                                    <div class="list-group mb-2">
                                                        <a href="{{ url('attendances/view', [
    'class_id'   => $school_class->id,
    'section_id' => $course->section_id,
    'course_id'  => $course->id,
]) }}">
    View Attendance
</a>

<a href="{{ url('attendances/history', [
    'class_id'   => $school_class->id,
    'section_id' => $course->section_id,
]) }}">
    Attendance History
</a>

                                                    </div>   
                                                    @endif
                                                @endforeach
                                            @else
                                            <div class="tab-content">
                                                <div class="accordion" id="accordionClass{{$school_class->id}}">
                                                    @foreach ($classes_and_sections['school_sections']->where('class_id', $school_class->id) as $school_section)
                                                        <div class="accordion-item">
                                                            <h2 class="accordion-header" id="headingClass{{$school_class->id}}Section{{$school_section->id}}">
                                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseClass{{$school_class->id}}Section{{$school_section->id}}" aria-expanded="false" aria-controls="collapseClass{{$school_class->id}}Section{{$school_section->id}}">
                                                                    {{$school_section->section_name}}
                                                                </button>
                                                            </h2>
                                                            <div id="collapseClass{{$school_class->id}}Section{{$school_section->id}}" class="accordion-collapse collapse" aria-labelledby="headingClass{{$school_class->id}}Section{{$school_section->id}}" data-bs-parent="#accordionClass{{$school_class->id}}">
                                                                <div class="accordion-body">
                                                                    <div class="list-group mb-2">
                                                                        <a href="{{ url('attendances/view', [
    'class_id'   => $school_class->id,
    'section_id' => $school_section->id,
]) }}">
    View Attendance
</a>

<a href="{{ url('attendances/history', [
    'class_id'   => $school_class->id,
    'section_id' => $school_section->id,
]) }}">
    Attendance History
</a>

                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                        {{-- <div class="card-footer bg-transparent">Total Students: 120</div> --}}
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
